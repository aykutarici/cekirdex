<?php

namespace App\Http\Controllers\Cekirdex\Panel;

use App\Cekirdex\Models\CekirdexOrder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TakeawayOrderController extends Controller
{
    private function rid(): int
    {
        return (int) Auth::guard('cekirdex')->user()->cekirdex_restaurant_id;
    }

    public function index(Request $request)
    {
        $rid = $this->rid();
        $tab = $request->input('tab', 'active');

        $base = CekirdexOrder::where('cekirdex_restaurant_id', $rid)
            ->whereIn('order_type', ['takeaway', 'delivery'])
            ->with('items')
            ->latest();

        if ($tab === 'active') {
            $orders = $base->whereIn('status', ['new', 'confirmed', 'preparing', 'ready'])->get();
        } elseif ($tab === 'completed') {
            $orders = $base->whereIn('status', ['delivered', 'served', 'closed'])->paginate(30);
        } elseif ($tab === 'cancelled') {
            $orders = $base->where('status', 'cancelled')->paginate(30);
        } else {
            $orders = $base->paginate(30);
        }

        $counts = [
            'active'    => CekirdexOrder::where('cekirdex_restaurant_id', $rid)
                ->whereIn('order_type', ['takeaway', 'delivery'])
                ->whereIn('status', ['new', 'confirmed', 'preparing', 'ready'])->count(),
            'completed' => CekirdexOrder::where('cekirdex_restaurant_id', $rid)
                ->whereIn('order_type', ['takeaway', 'delivery'])
                ->whereIn('status', ['delivered', 'served', 'closed'])->count(),
            'cancelled' => CekirdexOrder::where('cekirdex_restaurant_id', $rid)
                ->whereIn('order_type', ['takeaway', 'delivery'])
                ->where('status', 'cancelled')->count(),
        ];

        return view('cekirdex.panel.takeaway.index', compact('orders', 'counts', 'tab'));
    }

    public function show(int $id)
    {
        $rid = $this->rid();
        $order = CekirdexOrder::where('cekirdex_restaurant_id', $rid)
            ->whereIn('order_type', ['takeaway', 'delivery'])
            ->with('items')
            ->findOrFail($id);
        return view('cekirdex.panel.takeaway.show', compact('order'));
    }

    /** Restoran siparişi onaylar (ETA gir). */
    public function confirm(Request $request, int $id)
    {
        $rid = $this->rid();
        $data = $request->validate([
            'eta_minutes' => 'required|integer|min:5|max:240',
        ]);
        $order = CekirdexOrder::where('cekirdex_restaurant_id', $rid)
            ->whereIn('order_type', ['takeaway', 'delivery'])
            ->findOrFail($id);
        if ($order->status !== 'new') {
            return back()->with('error', 'Bu sipariş artık onaylanamaz.');
        }
        $order->update([
            'status'      => 'confirmed',
            'eta_minutes' => $data['eta_minutes'],
        ]);
        $this->sendStatusEmail($order, 'confirmed');
        return back()->with('success', 'Sipariş onaylandı.');
    }

    /** Hazırlanıyor / Hazır / Teslim edildi gibi durum geçişleri. */
    public function advance(int $id)
    {
        $rid = $this->rid();
        $order = CekirdexOrder::where('cekirdex_restaurant_id', $rid)
            ->whereIn('order_type', ['takeaway', 'delivery'])
            ->findOrFail($id);

        $next = match ($order->status) {
            'new'        => 'confirmed',
            'confirmed'  => 'preparing',
            'preparing'  => 'ready',
            'ready'      => 'delivered',
            default      => $order->status,
        };
        $patch = ['status' => $next];
        if ($next === 'ready')     $patch['ready_at']     = now();
        if ($next === 'delivered') $patch['delivered_at'] = now();
        $order->update($patch);

        if (in_array($next, ['ready', 'delivered'])) {
            $this->sendStatusEmail($order, $next);
        }
        return back()->with('success', 'Sipariş durumu güncellendi.');
    }

    public function cancel(Request $request, int $id)
    {
        $rid = $this->rid();
        $data = $request->validate([
            'reason' => 'nullable|string|max:300',
        ]);
        $order = CekirdexOrder::where('cekirdex_restaurant_id', $rid)
            ->whereIn('order_type', ['takeaway', 'delivery'])
            ->findOrFail($id);
        $order->update([
            'status' => 'cancelled',
            'note'   => trim(($order->note ? $order->note."\n" : '').'İPTAL: '.($data['reason'] ?? '')),
        ]);
        $this->sendStatusEmail($order, 'cancelled');
        return back()->with('success', 'Sipariş iptal edildi.');
    }

    /** Live feed (panel polling için). */
    public function feed()
    {
        $rid = $this->rid();
        $orders = CekirdexOrder::where('cekirdex_restaurant_id', $rid)
            ->whereIn('order_type', ['takeaway', 'delivery'])
            ->whereIn('status', ['new', 'confirmed', 'preparing', 'ready'])
            ->latest()->limit(50)->get(['id','order_number','order_type','status','total','created_at']);
        return response()->json([
            'ok'     => true,
            'orders' => $orders->map(fn ($o) => [
                'id'           => $o->id,
                'number'       => $o->order_number,
                'type'         => $o->type_label,
                'status'       => $o->status,
                'status_label' => $o->status_label,
                'total'        => (float) $o->total,
                'minutes_ago'  => $o->created_at->diffInMinutes(now()),
            ]),
        ]);
    }

    private function sendStatusEmail(CekirdexOrder $order, string $kind): void
    {
        if (empty($order->contact_email)) return;
        try {
            $r = $order->restaurant;
            $subject = $r->name.' — Sipariş #'.$order->order_number;
            $body = "Sayın ".$order->contact_name.",\n\n";
            $body .= match ($kind) {
                'confirmed' => "Siparişiniz onaylandı! Tahmini hazırlama süresi: ".$order->eta_minutes." dakika.\n",
                'ready'     => $order->order_type === 'takeaway'
                    ? "Siparişiniz hazır! Restoranımıza gelebilirsiniz.\n"
                    : "Siparişiniz hazır, kuryeye verildi.\n",
                'delivered' => "Siparişiniz teslim edildi. Afiyet olsun!\n",
                'cancelled' => "Maalesef siparişiniz iptal edildi.\n",
                default     => "Sipariş durumu: ".$order->status_label."\n",
            };
            $body .= "\nTakip: ".url('/o/'.$order->public_code)."\n\n".$r->name;

            Mail::raw($body, function ($m) use ($order, $subject) {
                $m->to($order->contact_email)->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::warning('Çekirdex takeaway status email failed: '.$e->getMessage());
        }
    }
}
