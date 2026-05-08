@extends('cekirdex.panel.layout')

@section('title', 'Çağrılar')

@push('styles')
<style>
.status-pills { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 18px; }
.status-pills a { padding: 8px 14px; background: #fff; border: 1px solid var(--c-line); border-radius: 99px; font-size: .85rem; font-weight: 600; color: var(--c-text-soft); text-decoration: none; }
.status-pills a:hover { border-color: var(--c-accent); color: var(--c-accent-d); }
.status-pills a.is-active { background: linear-gradient(135deg,#ff8a4c,#ff6b35); color: #fff; border-color: transparent; box-shadow: 0 6px 14px -4px rgba(255,107,53,.45); }
.call-actions { display: flex; gap: 6px; }
</style>
@endpush

@section('content')
<div class="pp-head">
    <div>
        <h1>Garson Çağrıları</h1>
        <div class="sub">Bekleyen <strong>{{ $counts['pending'] }}</strong> çağrı.</div>
    </div>
</div>

<div class="status-pills">
    <a href="{{ route('cekirdex.panel.calls.index', ['status' => 'pending']) }}" class="@if($status === 'pending') is-active @endif">Bekleyen ({{ $counts['pending'] }})</a>
    <a href="{{ route('cekirdex.panel.calls.index', ['status' => 'responded']) }}" class="@if($status === 'responded') is-active @endif">Yanıtlanan ({{ $counts['responded'] }})</a>
    <a href="{{ route('cekirdex.panel.calls.index', ['status' => 'closed']) }}" class="@if($status === 'closed') is-active @endif">Kapanan ({{ $counts['closed'] }})</a>
    <a href="{{ route('cekirdex.panel.calls.index', ['status' => 'all']) }}" class="@if($status === 'all') is-active @endif">Tümü</a>
</div>

<div class="card">
    @if($calls->isEmpty())
        <p style="color:var(--c-muted);text-align:center;padding:30px 0">Bu kategoride çağrı yok.</p>
    @else
        <table class="pp-table">
            <thead>
                <tr>
                    <th>Masa</th>
                    <th>Tip</th>
                    <th>Mesaj</th>
                    <th>Durum</th>
                    <th>Zaman</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($calls as $c)
                <tr>
                    <td><strong>{{ optional($c->table)->name ?? '—' }}</strong></td>
                    <td><span class="badge badge-{{ $c->status }}">{{ $c->type_label }}</span></td>
                    <td class="muted">{{ $c->message ?: '—' }}</td>
                    <td><span class="badge badge-{{ $c->status }}">{{ $c->status_label }}</span></td>
                    <td class="muted">{{ $c->created_at->diffForHumans() }}</td>
                    <td class="call-actions">
                        @if($c->status === 'pending')
                            <form method="POST" action="{{ route('cekirdex.panel.calls.respond', $c->id) }}">
                                @csrf <button type="submit" class="btn btn-sm"><i class="fas fa-check"></i> Yanıtla</button>
                            </form>
                        @elseif($c->status === 'responded')
                            <form method="POST" action="{{ route('cekirdex.panel.calls.close', $c->id) }}">
                                @csrf <button type="submit" class="btn btn-sm btn-ghost">Kapat</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div style="margin-top:14px">{{ $calls->links() }}</div>
    @endif
</div>
@endsection
