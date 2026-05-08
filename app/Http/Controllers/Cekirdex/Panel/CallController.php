<?php

namespace App\Http\Controllers\Cekirdex\Panel;

use App\Cekirdex\Models\CekirdexCall;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CallController extends Controller
{
    private function rid(): int
    {
        return (int) Auth::guard('cekirdex')->user()->cekirdex_restaurant_id;
    }

    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        $q = CekirdexCall::with('table')
            ->where('cekirdex_restaurant_id', $this->rid())
            ->latest();

        if ($status !== 'all') {
            $q->where('status', $status);
        }

        $calls = $q->paginate(30)->withQueryString();

        $counts = [
            'pending'   => CekirdexCall::where('cekirdex_restaurant_id', $this->rid())->where('status', 'pending')->count(),
            'responded' => CekirdexCall::where('cekirdex_restaurant_id', $this->rid())->where('status', 'responded')->count(),
            'closed'    => CekirdexCall::where('cekirdex_restaurant_id', $this->rid())->where('status', 'closed')->count(),
        ];

        return view('cekirdex.panel.calls.index', compact('calls', 'counts', 'status'));
    }

    public function respond(int $id)
    {
        $call = CekirdexCall::where('cekirdex_restaurant_id', $this->rid())->findOrFail($id);
        $call->update([
            'status'              => 'responded',
            'responded_by_user_id'=> Auth::guard('cekirdex')->id(),
            'responded_at'        => now(),
        ]);
        return back()->with('success', 'Çağrıya yanıt verildi olarak işaretlendi.');
    }

    public function close(int $id)
    {
        $call = CekirdexCall::where('cekirdex_restaurant_id', $this->rid())->findOrFail($id);
        $call->update(['status' => 'closed']);
        return back()->with('success', 'Çağrı kapatıldı.');
    }

    /** Bekleyen çağrılar — JSON feed (panelde anlık bildirim) */
    public function feed(Request $request)
    {
        $calls = CekirdexCall::with('table')
            ->where('cekirdex_restaurant_id', $this->rid())
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->limit(30)
            ->get()
            ->map(fn ($c) => [
                'id'    => $c->id,
                'type'  => $c->call_type,
                'label' => $c->type_label,
                'note'  => $c->message,
                'table' => optional($c->table)->name ?? '—',
                'minutes_ago' => (int) $c->created_at->diffInMinutes(now()),
            ]);
        return response()->json(['ok' => true, 'count' => $calls->count(), 'calls' => $calls]);
    }
}
