'use client';

import { useEffect, useRef, useState, useTransition } from 'react';
import { serveOrderAction, confirmOrderAction } from '../servis/actions';
import { respondCallAction, closeCallAction } from '../cagrilar/actions';

/* ─── Types ─────────────────────────────────────────────────────────────── */

type OrderItem = { id: number; name: string; quantity: number };

type ServiceOrder = {
  id: number;
  order_number: string;
  status: string;
  table: string | null;
  type: string;
  created_at: string;
  items: OrderItem[];
};

type Call = {
  id: number;
  table: string;
  call_type: string;
  type_label: string;
  status: string;
  created_at: string;
};

/* ─── Helpers ────────────────────────────────────────────────────────────── */

function elapsedSec(dateStr: string) {
  return Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000);
}

function formatTime(sec: number) {
  if (sec < 60) return `${sec}s`;
  if (sec < 3600) return `${Math.floor(sec / 60)}:${String(sec % 60).padStart(2, '0')}`;
  return `${Math.floor(sec / 3600)}sa`;
}

function ElapsedBadge({ dateStr, urgent }: { dateStr: string; urgent?: boolean }) {
  const [sec, setSec] = useState(() => elapsedSec(dateStr));
  useEffect(() => {
    const id = setInterval(() => setSec(elapsedSec(dateStr)), 1000);
    return () => clearInterval(id);
  }, [dateStr]);

  const isLate = sec >= 300;
  return (
    <span
      className={[
        'rounded-full px-2 py-0.5 font-mono text-xs font-bold tabular-nums',
        urgent || isLate
          ? 'bg-red-100 text-red-700 animate-pulse'
          : 'bg-gray-100 text-gray-600',
      ].join(' ')}
    >
      {formatTime(sec)}
    </span>
  );
}

const CALL_ICONS: Record<string, string> = {
  waiter: '🔔',
  napkin: '🧻',
  water:  '💧',
  ketchup:'🍅',
  bill:   '🧾',
  other:  '📢',
};

const CALL_COLORS: Record<string, { card: string; badge: string; btn: string }> = {
  waiter:  { card: 'border-blue-200   bg-blue-50',   badge: 'bg-blue-100   text-blue-800',   btn: 'bg-blue-500   hover:bg-blue-600' },
  napkin:  { card: 'border-gray-200   bg-gray-50',   badge: 'bg-gray-100   text-gray-700',   btn: 'bg-gray-500   hover:bg-gray-600' },
  water:   { card: 'border-cyan-200   bg-cyan-50',   badge: 'bg-cyan-100   text-cyan-800',   btn: 'bg-cyan-500   hover:bg-cyan-600' },
  ketchup: { card: 'border-red-200    bg-red-50',    badge: 'bg-red-100    text-red-800',    btn: 'bg-red-500    hover:bg-red-600' },
  bill:    { card: 'border-orange-200 bg-orange-50', badge: 'bg-orange-100 text-orange-800', btn: 'bg-orange-500 hover:bg-orange-600' },
  other:   { card: 'border-purple-200 bg-purple-50', badge: 'bg-purple-100 text-purple-800', btn: 'bg-purple-500 hover:bg-purple-600' },
};

/* ─── Ready Order Card ───────────────────────────────────────────────────── */

function ReadyCard({ order, onUpdate, isNew }: { order: ServiceOrder; onUpdate: () => void; isNew?: boolean }) {
  const [pending, startTransition] = useTransition();
  const [error, setError] = useState<string | null>(null);

  function serve() {
    setError(null);
    startTransition(async () => {
      const res = await serveOrderAction(order.id);
      if (res.error) setError(res.error);
      else onUpdate();
    });
  }

  return (
    <div
      className={[
        'flex flex-col rounded-2xl border-2 border-emerald-300 bg-white shadow-sm transition-all',
        isNew ? 'ring-2 ring-emerald-400 ring-offset-2' : '',
      ].join(' ')}
    >
      <div className="flex items-center justify-between border-b border-emerald-100 bg-emerald-50 px-4 py-3 rounded-t-2xl">
        <div className="flex items-center gap-2">
          <span className="font-mono text-lg font-black text-emerald-900">{order.order_number}</span>
          {isNew && (
            <span className="animate-bounce rounded-full bg-emerald-500 px-2 py-0.5 text-[10px] font-bold text-white">
              Hazır!
            </span>
          )}
        </div>
        <ElapsedBadge dateStr={order.created_at} urgent={isNew} />
      </div>

      <div className="flex-1 px-4 pt-3">
        <div className="mb-2 flex items-center gap-2">
          <span className="rounded-lg bg-emerald-100 px-2.5 py-1 text-sm font-bold text-emerald-800">
            {order.table ?? order.type}
          </span>
        </div>
        <ul className="space-y-1.5 rounded-xl bg-gray-50 p-3">
          {order.items.map((item) => (
            <li key={item.id} className="flex items-center gap-2 text-sm">
              <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded bg-emerald-600 text-[11px] font-bold text-white">
                {item.quantity}
              </span>
              <span className="font-medium text-gray-800">{item.name}</span>
            </li>
          ))}
        </ul>
      </div>

      {error && <p className="px-4 pt-2 text-xs text-red-600">{error}</p>}

      <div className="p-3 pt-3">
        <button
          onClick={serve}
          disabled={pending}
          className="w-full rounded-xl bg-emerald-600 py-3 text-sm font-bold text-white transition hover:bg-emerald-700 disabled:opacity-50"
        >
          {pending ? 'İşleniyor…' : '✓ Servis edildi'}
        </button>
      </div>
    </div>
  );
}

/* ─── Call Card ──────────────────────────────────────────────────────────── */

function CallCard({ call, onUpdate }: { call: Call; onUpdate: () => void }) {
  const [pending, startTransition] = useTransition();
  const [error, setError] = useState<string | null>(null);
  const colors = CALL_COLORS[call.call_type] ?? CALL_COLORS.other;
  const icon = CALL_ICONS[call.call_type] ?? '📢';
  const isResponded = call.status === 'responded';

  function respond() {
    setError(null);
    startTransition(async () => {
      const res = await respondCallAction(call.id);
      if (res.error) setError(res.error);
      else onUpdate();
    });
  }

  function close() {
    setError(null);
    startTransition(async () => {
      const res = await closeCallAction(call.id);
      if (res.error) setError(res.error);
      else onUpdate();
    });
  }

  return (
    <div className={`flex flex-col rounded-2xl border-2 ${colors.card} ${isResponded ? 'opacity-60' : ''} transition-all`}>
      <div className="flex items-start justify-between p-4 pb-3">
        <div className="flex items-center gap-2">
          <span className="text-2xl leading-none">{icon}</span>
          <div>
            <p className="font-bold text-gray-900">{call.table}</p>
            <p className="text-xs text-gray-500">{call.type_label}</p>
          </div>
        </div>
        <ElapsedBadge dateStr={call.created_at} urgent={!isResponded} />
      </div>

      {error && <p className="px-4 pb-1 text-xs text-red-600">{error}</p>}

      <div className="flex gap-2 border-t border-black/5 p-3">
        {!isResponded && (
          <button
            onClick={respond}
            disabled={pending}
            className={`flex-1 rounded-xl py-2.5 text-sm font-bold text-white transition disabled:opacity-50 ${colors.btn}`}
          >
            {pending ? '…' : 'Yolda'}
          </button>
        )}
        <button
          onClick={close}
          disabled={pending}
          className={`rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-bold text-gray-600 transition hover:bg-gray-50 disabled:opacity-50 ${isResponded ? 'flex-1' : ''}`}
        >
          Kapat
        </button>
      </div>
    </div>
  );
}

/* ─── New Order Card ─────────────────────────────────────────────────────── */

function NewOrderCard({ order, onUpdate }: { order: ServiceOrder; onUpdate: () => void }) {
  const [pending, startTransition] = useTransition();
  const [error, setError] = useState<string | null>(null);

  function confirm() {
    setError(null);
    startTransition(async () => {
      const res = await confirmOrderAction(order.id);
      if (res.error) setError(res.error);
      else onUpdate();
    });
  }

  return (
    <div className="flex flex-col rounded-2xl border-2 border-blue-200 bg-blue-50 transition-all">
      <div className="flex items-center justify-between border-b border-blue-100 bg-blue-100/60 px-4 py-3 rounded-t-2xl">
        <span className="font-mono text-base font-black text-blue-900">{order.order_number}</span>
        <div className="flex items-center gap-2">
          <span className="rounded-lg bg-blue-200/60 px-2 py-0.5 text-xs font-semibold text-blue-800">
            {order.table ?? order.type}
          </span>
          <ElapsedBadge dateStr={order.created_at} />
        </div>
      </div>

      <ul className="flex-1 space-y-1 px-4 py-3">
        {order.items.map((item) => (
          <li key={item.id} className="flex items-center gap-2 text-sm">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded bg-blue-600 text-[11px] font-bold text-white">
              {item.quantity}
            </span>
            <span className="font-medium text-gray-800">{item.name}</span>
          </li>
        ))}
      </ul>

      {error && <p className="px-4 pb-1 text-xs text-red-600">{error}</p>}

      <div className="p-3">
        <button
          onClick={confirm}
          disabled={pending}
          className="w-full rounded-xl bg-blue-600 py-2.5 text-sm font-bold text-white transition hover:bg-blue-700 disabled:opacity-50"
        >
          {pending ? '…' : '✓ Onayla'}
        </button>
      </div>
    </div>
  );
}

/* ─── Section Header ─────────────────────────────────────────────────────── */

function SectionHeader({
  label,
  count,
  urgent,
  colorCls,
}: {
  label: string;
  count: number;
  urgent?: boolean;
  colorCls: string;
}) {
  return (
    <div className="flex shrink-0 items-center justify-between border-b px-5 py-3">
      <h2 className="text-sm font-bold uppercase tracking-wider text-gray-600">{label}</h2>
      <span
        className={[
          'flex h-7 min-w-[1.75rem] items-center justify-center rounded-full px-2 text-sm font-black',
          colorCls,
          urgent && count > 0 ? 'animate-pulse' : '',
        ].join(' ')}
      >
        {count}
      </span>
    </div>
  );
}

/* ─── Main Page ──────────────────────────────────────────────────────────── */

export default function GarsonPage() {
  const [readyOrders, setReadyOrders] = useState<ServiceOrder[]>([]);
  const [newOrders, setNewOrders] = useState<ServiceOrder[]>([]);
  const [calls, setCalls] = useState<Call[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const prevReadyRef = useRef<Set<number>>(new Set());
  const [newReadyIds, setNewReadyIds] = useState<Set<number>>(new Set());

  async function load() {
    try {
      const [svcRes, callRes] = await Promise.all([
        fetch('/api/panel/service'),
        fetch('/api/panel/calls'),
      ]);
      if (!svcRes.ok || !callRes.ok) throw new Error('Yüklenemedi');
      const svcData: { data: (ServiceOrder & { status: string })[] } = await svcRes.json();
      const callData: { data: Call[] } = await callRes.json();

      const all = svcData.data ?? [];
      const ready = all.filter((o) => o.status === 'ready');
      const newOrd = all.filter((o) => o.status !== 'ready');

      // Detect newly-ready orders
      const readyIds = new Set(ready.map((o) => o.id));
      const incoming = new Set<number>();
      readyIds.forEach((id) => {
        if (!prevReadyRef.current.has(id)) incoming.add(id);
      });
      if (incoming.size > 0) setNewReadyIds(incoming);
      prevReadyRef.current = readyIds;

      setReadyOrders(ready);
      setNewOrders(newOrd);
      setCalls(callData.data ?? []);
      setError(null);
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Hata');
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    load();
    const id = setInterval(load, 5_000);
    return () => clearInterval(id);
  }, []);

  useEffect(() => {
    if (newReadyIds.size === 0) return;
    const t = setTimeout(() => setNewReadyIds(new Set()), 8_000);
    return () => clearTimeout(t);
  }, [newReadyIds]);

  const pendingCalls = calls.filter((c) => c.status === 'pending');
  const respondedCalls = calls.filter((c) => c.status === 'responded');
  const now = new Date();

  const totalUrgent = readyOrders.length + pendingCalls.length;

  return (
    <div className="flex h-[calc(100vh-4rem)] flex-col bg-gray-50">
      {/* Top bar */}
      <header className="flex shrink-0 items-center justify-between border-b bg-white px-5 py-3 shadow-sm">
        <div className="flex items-center gap-3">
          <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600">
            <svg className="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
            </svg>
          </div>
          <div>
            <h1 className="text-sm font-bold uppercase tracking-widest text-gray-800">Garson Ekranı</h1>
            <p className="text-[10px] text-gray-400">
              {now.toLocaleDateString('tr-TR', { weekday: 'long', day: 'numeric', month: 'long' })}
            </p>
          </div>
        </div>

        <div className="flex items-center gap-3">
          {error && (
            <span className="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-600">{error}</span>
          )}
          <div className="flex items-center gap-2">
            <span className={`h-2 w-2 rounded-full ${loading ? 'bg-amber-400 animate-pulse' : 'bg-emerald-400'}`} />
            <span className="text-xs text-gray-400">{loading ? 'Yenileniyor' : 'Canlı'}</span>
          </div>
          {totalUrgent > 0 && (
            <span className="animate-pulse rounded-full bg-red-500 px-3 py-1 text-sm font-bold text-white">
              {totalUrgent} acil görev
            </span>
          )}
        </div>
      </header>

      {/* 3-column layout */}
      <div className="flex flex-1 gap-0 overflow-hidden">
        {/* Column 1: Hazır Siparişler */}
        <div className="flex flex-1 flex-col border-r bg-white overflow-hidden">
          <SectionHeader
            label="Servis Edilecek"
            count={readyOrders.length}
            urgent
            colorCls={readyOrders.length > 0 ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-500'}
          />
          <div className="flex-1 space-y-3 overflow-y-auto p-3">
            {readyOrders.length === 0 ? (
              <div className="flex flex-col items-center justify-center pt-16 text-center">
                <div className="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50">
                  <svg className="h-7 w-7 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <p className="text-sm font-medium text-gray-400">Tüm siparişler teslim edildi</p>
              </div>
            ) : (
              readyOrders.map((order) => (
                <ReadyCard
                  key={order.id}
                  order={order}
                  onUpdate={load}
                  isNew={newReadyIds.has(order.id)}
                />
              ))
            )}
          </div>
        </div>

        {/* Column 2: Çağrılar */}
        <div className="flex flex-1 flex-col border-r overflow-hidden" style={{ background: '#FFFBF5' }}>
          <SectionHeader
            label="Masa Çağrıları"
            count={pendingCalls.length}
            urgent
            colorCls={pendingCalls.length > 0 ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-500'}
          />
          <div className="flex-1 space-y-3 overflow-y-auto p-3">
            {pendingCalls.length === 0 && respondedCalls.length === 0 ? (
              <div className="flex flex-col items-center justify-center pt-16 text-center">
                <div className="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-amber-50">
                  <svg className="h-7 w-7 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                  </svg>
                </div>
                <p className="text-sm font-medium text-gray-400">Çağrı yok</p>
              </div>
            ) : (
              <>
                {pendingCalls.map((c) => (
                  <CallCard key={c.id} call={c} onUpdate={load} />
                ))}
                {respondedCalls.length > 0 && (
                  <>
                    <p className="pt-1 text-xs font-semibold uppercase tracking-wider text-gray-400">
                      Yanıtlandı ({respondedCalls.length})
                    </p>
                    {respondedCalls.map((c) => (
                      <CallCard key={c.id} call={c} onUpdate={load} />
                    ))}
                  </>
                )}
              </>
            )}
          </div>
        </div>

        {/* Column 3: Yeni / Onay Bekleyen Siparişler */}
        <div className="flex flex-1 flex-col overflow-hidden bg-white">
          <SectionHeader
            label="Onay Bekleyen"
            count={newOrders.length}
            colorCls={newOrders.length > 0 ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-500'}
          />
          <div className="flex-1 space-y-3 overflow-y-auto p-3">
            {newOrders.length === 0 ? (
              <div className="flex flex-col items-center justify-center pt-16 text-center">
                <div className="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-blue-50">
                  <svg className="h-7 w-7 text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                  </svg>
                </div>
                <p className="text-sm font-medium text-gray-400">Bekleyen sipariş yok</p>
              </div>
            ) : (
              newOrders.map((order) => (
                <NewOrderCard key={order.id} order={order} onUpdate={load} />
              ))
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
