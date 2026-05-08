<?php

namespace App\Http\Controllers\Cekirdex\Panel;

use App\Cekirdex\Models\CekirdexBillClosure;
use App\Cekirdex\Models\CekirdexOrder;
use App\Cekirdex\Models\CekirdexPayment;
use App\Cekirdex\Models\CekirdexTable;
use App\Cekirdex\Services\BillService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BillController extends Controller
{
    public function __construct(private BillService $bills) {}

    private function rid(): int
    {
        return (int) Auth::guard('cekirdex')->user()->cekirdex_restaurant_id;
    }

    /**
     * Tüm açık adisyonu olan masaları listele + son kapatılan adisyonlar.
     */
    public function index()
    {
        $rid = $this->rid();
        $tables = CekirdexTable::where('cekirdex_restaurant_id', $rid)
            ->where('is_active', true)
            ->with(['activeOrders'])
            ->orderBy('name')
            ->get()
            ->map(function ($t) {
                $sum = $this->bills->summary($t);
                return [
                    'table'      => $t,
                    'subtotal'   => $sum['subtotal'],
                    'total'      => $sum['total'],
                    'paid'       => $sum['paid'],
                    'remaining'  => $sum['remaining'],
                    'open'       => $sum['has_open_orders'],
                    'order_count' => count($sum['orders']),
                ];
            });

        $recentClosures = CekirdexBillClosure::where('cekirdex_restaurant_id', $rid)
            ->with('table')
            ->latest()->limit(15)->get();

        return view('cekirdex.panel.bills.index', compact('tables', 'recentClosures'));
    }

    /**
     * Bir masanın hesap detayı (panel sayfası).
     */
    public function show(int $tableId)
    {
        $rid = $this->rid();
        $table = CekirdexTable::where('cekirdex_restaurant_id', $rid)->findOrFail($tableId);
        $bill = $this->bills->summary($table);
        return view('cekirdex.panel.bills.show', compact('table', 'bill'));
    }

    /**
     * Garson nakit/POS ile manuel ödeme alır. Toplam veya kısmi olabilir.
     */
    public function recordPayment(Request $request, int $tableId)
    {
        $rid = $this->rid();
        $table = CekirdexTable::where('cekirdex_restaurant_id', $rid)->findOrFail($tableId);
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:waiter_card,waiter_cash,bank_transfer',
            'note'   => 'nullable|string|max:200',
        ]);

        $sum = $this->bills->summary($table);
        if ($data['amount'] > $sum['remaining'] + 0.01) {
            return back()->withErrors(['amount' => 'Tutar kalan hesaptan büyük olamaz ('.number_format($sum['remaining'],2,',','.').' ₺).']);
        }

        CekirdexPayment::create([
            'cekirdex_restaurant_id' => $rid,
            'cekirdex_table_id'      => $table->id,
            'amount'                 => $data['amount'],
            'status'                 => 'paid',
            'method'                 => $data['method'],
            'provider'               => 'manual',
            'split_mode'             => CekirdexPayment::SPLIT_AMOUNT,
            'portion_label'          => $data['note'] ?? null,
            'payer_name'             => $data['note'] ?? null,
            'confirmed_by_user_id'   => Auth::guard('cekirdex')->id(),
            'confirmed_at'           => now(),
            'paid_at'                => now(),
        ]);

        return back()->with('success', 'Ödeme kaydedildi.');
    }

    /**
     * Bir ödemeyi sil/iade et (yanlış girilmişse).
     */
    public function voidPayment(Request $request, int $tableId, int $paymentId)
    {
        $rid = $this->rid();
        $payment = CekirdexPayment::where('cekirdex_restaurant_id', $rid)
            ->where('cekirdex_table_id', $tableId)
            ->findOrFail($paymentId);
        $payment->update(['status' => 'cancelled']);
        return back()->with('success', 'Ödeme iptal edildi.');
    }

    /**
     * Adisyonu kapat. Kalan = 0 olmalı. Fatura teslim seçeneği zorunlu.
     */
    public function close(Request $request, int $tableId)
    {
        $rid = $this->rid();
        $table = CekirdexTable::where('cekirdex_restaurant_id', $rid)->findOrFail($tableId);
        $data = $request->validate([
            'delivery_method'  => 'required|in:emailed,printed,handed,none',
            'recipient_email'  => 'required_if:delivery_method,emailed|nullable|email|max:160',
            'note'             => 'nullable|string|max:500',
        ]);

        $sum = $this->bills->summary($table);
        if (!$sum['has_open_orders']) {
            return back()->withErrors(['close' => 'Bu masada açık adisyon yok.']);
        }
        if ($sum['remaining'] > 0.01) {
            return back()->withErrors(['close' => 'Kalan ödenmedi. Önce ödemeyi tamamlayın (kalan: '.number_format($sum['remaining'],2,',','.').' ₺).']);
        }

        $emailSent = false;
        if ($data['delivery_method'] === 'emailed' && !empty($data['recipient_email'])) {
            $emailSent = $this->sendBillEmail($table, $sum, $data['recipient_email']);
        }

        DB::transaction(function () use ($rid, $table, $sum, $data, $emailSent, $request) {
            // Açık siparişleri 'closed' yap
            $orderIds = collect($sum['orders'])->pluck('id')->all();
            CekirdexOrder::whereIn('id', $orderIds)->update(['status' => 'closed']);

            CekirdexBillClosure::create([
                'cekirdex_restaurant_id' => $rid,
                'cekirdex_table_id'      => $table->id,
                'closed_by_user_id'      => Auth::guard('cekirdex')->id(),
                'delivery_method'        => $data['delivery_method'],
                'recipient_email'        => $data['recipient_email'] ?? null,
                'email_sent'             => $emailSent,
                'subtotal'               => $sum['subtotal'],
                'tax'                    => $sum['tax'],
                'service_charge'         => $sum['service_charge'],
                'total'                  => $sum['total'],
                'paid'                   => $sum['paid'],
                'change_returned'        => max(0, round($sum['paid'] - $sum['total'], 2)),
                'orders_snapshot'        => $sum['orders'],
                'items_snapshot'         => $sum['items'],
                'payments_snapshot'      => $sum['payments'],
                'ip_address'             => $request->ip(),
                'note'                   => $data['note'] ?? null,
            ]);
        });

        return redirect()->route('cekirdex.panel.bills.index')
            ->with('success', 'Adisyon kapatıldı'.($emailSent ? ' ve fatura e-postası gönderildi.' : '.'));
    }

    private function sendBillEmail(CekirdexTable $table, array $summary, string $to): bool
    {
        try {
            $restaurant = $table->restaurant;
            $body = "Sayın Misafirimiz,\n\n";
            $body .= $restaurant->name." — ".$table->name." adisyon özetiniz:\n\n";
            foreach ($summary['items'] as $it) {
                $body .= "  ".$it['quantity'].' × '.$it['name'].'  — '.number_format($it['subtotal'],2,',','.')." ₺\n";
            }
            $body .= "\n  Ara toplam : ".number_format($summary['subtotal'],2,',','.')." ₺\n";
            $body .= "  KDV        : ".number_format($summary['tax'],2,',','.')." ₺\n";
            $body .= "  Servis     : ".number_format($summary['service_charge'],2,',','.')." ₺\n";
            $body .= "  Toplam     : ".number_format($summary['total'],2,',','.')." ₺\n";
            $body .= "  Ödenen     : ".number_format($summary['paid'],2,',','.')." ₺\n\n";
            $body .= "Bizi tercih ettiğiniz için teşekkür ederiz.\n";
            $body .= $restaurant->name."\n";

            Mail::raw($body, function ($m) use ($to, $restaurant, $table) {
                $m->to($to)
                  ->subject($restaurant->name.' — Adisyon Özeti ('.$table->name.')');
            });
            return true;
        } catch (\Throwable $e) {
            Log::error('Çekirdex bill email failed: '.$e->getMessage());
            return false;
        }
    }
}
