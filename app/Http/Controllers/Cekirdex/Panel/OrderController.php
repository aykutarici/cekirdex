<?php

namespace App\Http\Controllers\Cekirdex\Panel;

use App\Cekirdex\Models\CekirdexOrder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    private function rid(): int
    {
        return (int) Auth::guard('cekirdex')->user()->cekirdex_restaurant_id;
    }

    public function index(Request $request)
    {
        $rid = $this->rid();
        $status = $request->query('status');

        $q = CekirdexOrder::with(['table', 'items'])
            ->where('cekirdex_restaurant_id', $rid)
            ->latest();

        if ($status && array_key_exists($status, CekirdexOrder::STATUSES)) {
            $q->where('status', $status);
        }

        $orders = $q->paginate(20)->withQueryString();

        $counts = [];
        foreach (array_keys(CekirdexOrder::STATUSES) as $key) {
            $counts[$key] = CekirdexOrder::where('cekirdex_restaurant_id', $rid)->where('status', $key)->count();
        }

        return view('cekirdex.panel.orders.index', compact('orders', 'counts', 'status'));
    }

    public function show(int $id)
    {
        $order = CekirdexOrder::with(['table', 'items'])
            ->where('cekirdex_restaurant_id', $this->rid())->findOrFail($id);
        return view('cekirdex.panel.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, int $id)
    {
        $order = CekirdexOrder::where('cekirdex_restaurant_id', $this->rid())->findOrFail($id);
        $data = $request->validate([
            'status' => 'required|in:'.implode(',', array_keys(CekirdexOrder::STATUSES)),
        ]);
        $order->update(['status' => $data['status']]);
        return back()->with('success', 'Sipariş durumu güncellendi.');
    }
}
