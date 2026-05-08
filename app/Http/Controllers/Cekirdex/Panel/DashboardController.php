<?php

namespace App\Http\Controllers\Cekirdex\Panel;

use App\Cekirdex\Models\CekirdexCall;
use App\Cekirdex\Models\CekirdexOrder;
use App\Cekirdex\Models\CekirdexProduct;
use App\Cekirdex\Models\CekirdexTable;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::guard('cekirdex')->user();
        $rid  = $user->cekirdex_restaurant_id;

        $todayOrders = CekirdexOrder::where('cekirdex_restaurant_id', $rid)
            ->whereDate('created_at', today())->count();
        $todayRevenue = (float) CekirdexOrder::where('cekirdex_restaurant_id', $rid)
            ->whereDate('created_at', today())
            ->whereNotIn('status', ['cancelled'])
            ->sum('total');

        $activeOrders = CekirdexOrder::where('cekirdex_restaurant_id', $rid)
            ->whereIn('status', ['new', 'confirmed', 'preparing', 'ready'])->count();
        $pendingCalls = CekirdexCall::where('cekirdex_restaurant_id', $rid)
            ->where('status', 'pending')->count();

        $totals = [
            'tables'     => CekirdexTable::where('cekirdex_restaurant_id', $rid)->count(),
            'products'   => CekirdexProduct::where('cekirdex_restaurant_id', $rid)->count(),
            'all_orders' => CekirdexOrder::where('cekirdex_restaurant_id', $rid)->count(),
        ];

        $recentOrders = CekirdexOrder::with('table')
            ->where('cekirdex_restaurant_id', $rid)
            ->latest()->limit(8)->get();

        $recentCalls = CekirdexCall::with('table')
            ->where('cekirdex_restaurant_id', $rid)
            ->where('status', 'pending')
            ->latest()->limit(8)->get();

        $outOfStock = CekirdexProduct::where('cekirdex_restaurant_id', $rid)
            ->where('is_active', true)
            ->where('is_in_stock', false)
            ->orderBy('name')->limit(20)->get();

        return view('cekirdex.panel.dashboard', compact(
            'user', 'todayOrders', 'todayRevenue',
            'activeOrders', 'pendingCalls', 'totals',
            'recentOrders', 'recentCalls', 'outOfStock'
        ));
    }

    /**
     * Hafif JSON feed: sayfa polling'i ile yeni sipariş/çağrı sayılarını
     * gerçek zamanlı tazelemek için kullanılır.
     */
    public function feed(Request $request)
    {
        $rid = (int) Auth::guard('cekirdex')->user()->cekirdex_restaurant_id;

        return response()->json([
            'ok'              => true,
            'today_orders'    => CekirdexOrder::where('cekirdex_restaurant_id', $rid)
                                    ->whereDate('created_at', today())->count(),
            'today_revenue'   => (float) CekirdexOrder::where('cekirdex_restaurant_id', $rid)
                                    ->whereDate('created_at', today())
                                    ->whereNotIn('status', ['cancelled'])->sum('total'),
            'active_orders'   => CekirdexOrder::where('cekirdex_restaurant_id', $rid)
                                    ->whereIn('status', ['new', 'confirmed', 'preparing', 'ready'])->count(),
            'pending_calls'   => CekirdexCall::where('cekirdex_restaurant_id', $rid)
                                    ->where('status', 'pending')->count(),
            'new_orders'      => CekirdexOrder::where('cekirdex_restaurant_id', $rid)
                                    ->where('status', 'new')->count(),
            'last_order_id'   => (int) CekirdexOrder::where('cekirdex_restaurant_id', $rid)->max('id'),
            'last_call_id'    => (int) CekirdexCall::where('cekirdex_restaurant_id', $rid)->max('id'),
            'fetched_at'      => now()->toIso8601String(),
        ]);
    }
}
