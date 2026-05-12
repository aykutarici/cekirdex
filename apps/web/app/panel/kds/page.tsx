'use client';

import { useEffect, useRef, useState, useTransition } from 'react';
import { advanceOrderAction, cancelOrderAction } from './actions';

type OrderItem = { id: number; name: string; quantity: number; note: string | null };

type KdsOrder = {
  id: number;
  order_number: string;
  status: string;
  status_label: string;
  table: string | null;
  type: string;
  created_at: string;
  items: OrderItem[];
};

function elapsedSeconds(dateStr: string): number {
  return Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000);
}

function formatElapsed(sec: number): string {
  if (sec < 60) return `${sec}s`;
  if (sec < 3600) return `${Math.floor(sec / 60)}:${String(sec % 60).padStart(2, '0')}`;
  return `${Math.floor(sec / 3600)}sa`;
}

function urgencyClass(sec: number): string {
  if (sec < 300) return 'text-emerald-400';
  if (sec < 600) return 'text-amber-400';
  return 'text-red-400 animate-pulse';
}

function urgencyBorder(sec: number, isReady: boolean): string {
  if (isReady) return 'border-emerald-500/60';
  if (sec >= 600) return 'border-red-500/60';
  if (sec >= 300) return 'border-amber-500/40';
  return 'border-slate-600/50';
}

function ElapsedTimer({ dateStr, isReady }: { dateStr: string; isReady: boolean }) {
  const [sec, setSec] = useState(() => elapsedSeconds(dateStr));
  useEffect(() => {
    const id = setInterval(() => setSec(elapsedSeconds(dateStr)), 1000);
    return () => clearInterval(id);
  }, [dateStr]);
  return (
    <span className={`font-mono text-sm font-bold tabular-nums ${isReady ? 'text-emerald-400' : urgencyClass(sec)}`}>
      {formatElapsed(sec)}
    </span>
  );
}

function OrderCard({ order, onUpdate, isNew }: { order: KdsOrder; onUpdate: () => void; isNew?: boolean }) {
  const [pending, startTransition] = useTransition();
  const [error, setError] = useState<string | null>(null);
  const sec = elapsedSeconds(order.created_at);
  const isReady = order.status === 'ready';
  const isPreparing = order.status === 'preparing';

  function advance() {
    setError(null);
    startTransition(async () => {
      const res = await advanceOrderAction(order.id);
      if (res.error) setError(res.error);
      else onUpdate();
    });
  }

  function cancel() {
    setError(null);
    startTransition(async () => {
      const res = await cancelOrderAction(order.id);
      if (res.error) setError(res.error);
      else onUpdate();
    });
  }

  return (
    <div
      className={[
        'flex flex-col rounded-xl border bg-slate-800/80 backdrop-blur transition-all',
        urgencyBorder(sec, isReady),
        isNew ? 'ring-2 ring-blue-500/50 ring-offset-1 ring-offset-slate-900' : '',
      ].join(' ')}
    >
      {/* Card header */}
      <div className="flex items-center justify-between border-b border-slate-700/50 px-4 py-3">
        <div className="flex items-center gap-2">
          <span className="font-mono text-lg font-black tracking-tight text-white">
            {order.order_number}
          </span>
          {isNew && (
            <span className="animate-pulse rounded-full bg-blue-500 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">
              Yeni
            </span>
          )}
        </div>
        <ElapsedTimer dateStr={order.created_at} isReady={isReady} />
      </div>

      {/* Table / type */}
      <div className="px-4 pt-2">
        <span className="rounded-md bg-slate-700/60 px-2 py-0.5 text-xs font-semibold text-slate-300">
          {order.table ?? order.type}
        </span>
      </div>

      {/* Items */}
      <ul className="flex-1 space-y-1 px-4 py-3">
        {order.items.map((item) => (
          <li key={item.id} className="flex items-baseline gap-2">
            <span className="w-6 shrink-0 rounded bg-slate-700 text-center text-xs font-black text-white">
              {item.quantity}
            </span>
            <span className="text-sm font-medium text-slate-200">{item.name}</span>
            {item.note && (
              <span className="ml-auto shrink-0 rounded bg-amber-900/40 px-1.5 py-0.5 text-[10px] text-amber-300">
                {item.note}
              </span>
            )}
          </li>
        ))}
      </ul>

      {error && <p className="px-4 pb-1 text-xs text-red-400">{error}</p>}

      {/* Actions */}
      <div className="flex gap-2 border-t border-slate-700/50 p-3">
        {!isReady && (
          <button
            onClick={advance}
            disabled={pending}
            className={[
              'flex-1 rounded-lg py-2.5 text-sm font-bold transition disabled:opacity-50',
              isPreparing
                ? 'bg-emerald-500 text-white hover:bg-emerald-400'
                : 'bg-blue-500 text-white hover:bg-blue-400',
            ].join(' ')}
          >
            {pending ? '…' : isPreparing ? '✓ Hazır' : '▶ Hazırlanıyor'}
          </button>
        )}
        {isReady && (
          <div className="flex-1 rounded-lg bg-emerald-500/20 py-2.5 text-center text-sm font-bold text-emerald-400">
            Serviste bekleniyor
          </div>
        )}
        <button
          onClick={cancel}
          disabled={pending}
          className="rounded-lg border border-red-800/50 bg-red-900/20 px-3 py-2.5 text-sm font-bold text-red-400 transition hover:bg-red-900/40 disabled:opacity-50"
        >
          İptal
        </button>
      </div>
    </div>
  );
}

const COLUMNS = [
  {
    key: 'new' as const,
    label: 'Yeni / Onaylı',
    accent: 'from-blue-600 to-blue-700',
    dot: 'bg-blue-400',
    counter: 'bg-blue-500/20 text-blue-300',
  },
  {
    key: 'preparing' as const,
    label: 'Hazırlanıyor',
    accent: 'from-amber-600 to-amber-700',
    dot: 'bg-amber-400',
    counter: 'bg-amber-500/20 text-amber-300',
  },
  {
    key: 'ready' as const,
    label: 'Hazır — Teslim Bekliyor',
    accent: 'from-emerald-600 to-emerald-700',
    dot: 'bg-emerald-400',
    counter: 'bg-emerald-500/20 text-emerald-300',
  },
];

export default function KdsPage() {
  const [orders, setOrders] = useState<KdsOrder[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [lastCount, setLastCount] = useState(0);
  const [newIds, setNewIds] = useState<Set<number>>(new Set());
  const prevIdsRef = useRef<Set<number>>(new Set());

  async function load() {
    try {
      const res = await fetch('/api/panel/kds');
      if (!res.ok) throw new Error('Yüklenemedi');
      const data: { data: KdsOrder[] } = await res.json();
      const fetched = data.data ?? [];

      const fetchedIds = new Set(fetched.map((o) => o.id));
      const incoming = new Set<number>();
      fetchedIds.forEach((id) => {
        if (!prevIdsRef.current.has(id)) incoming.add(id);
      });
      if (incoming.size > 0) setNewIds(incoming);
      prevIdsRef.current = fetchedIds;

      setOrders(fetched);
      setLastCount(fetched.length);
      setError(null);
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Hata oluştu');
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    load();
    const id = setInterval(load, 5_000);
    return () => clearInterval(id);
  }, []);

  // Clear "new" highlight after 8 seconds
  useEffect(() => {
    if (newIds.size === 0) return;
    const t = setTimeout(() => setNewIds(new Set()), 8_000);
    return () => clearTimeout(t);
  }, [newIds]);

  const groups = {
    new: orders.filter((o) => o.status === 'new' || o.status === 'confirmed'),
    preparing: orders.filter((o) => o.status === 'preparing'),
    ready: orders.filter((o) => o.status === 'ready'),
  };

  const now = new Date();

  return (
    <div className="flex h-[calc(100vh-4rem)] flex-col bg-slate-900">
      {/* Top bar */}
      <header className="flex shrink-0 items-center justify-between border-b border-slate-700/60 bg-slate-900/80 px-5 py-3 backdrop-blur">
        <div className="flex items-center gap-3">
          <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-500">
            <svg className="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
              <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h7a1 1 0 100-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3z"/>
            </svg>
          </div>
          <div>
            <h1 className="text-sm font-bold uppercase tracking-widest text-white">Mutfak Ekranı</h1>
            <p className="text-[10px] text-slate-500">
              {now.toLocaleDateString('tr-TR', { weekday: 'long', day: 'numeric', month: 'long' })}
            </p>
          </div>
        </div>

        <div className="flex items-center gap-3">
          {error && (
            <span className="rounded-full bg-red-900/50 px-3 py-1 text-xs font-semibold text-red-400">
              {error}
            </span>
          )}
          <div className="flex items-center gap-2">
            <span className={`h-2 w-2 rounded-full ${loading ? 'bg-amber-400 animate-pulse' : 'bg-emerald-400'}`} />
            <span className="text-xs text-slate-400">{loading ? 'Yenileniyor' : 'Canlı'}</span>
          </div>
          <div className="rounded-full bg-slate-800 px-3 py-1 text-xs font-bold text-slate-300">
            {lastCount} aktif sipariş
          </div>
        </div>
      </header>

      {/* Columns */}
      <div className="flex flex-1 gap-0 overflow-hidden">
        {COLUMNS.map(({ key, label, accent, dot, counter }) => (
          <div key={key} className="flex flex-1 flex-col border-r border-slate-700/40 last:border-r-0">
            {/* Column header */}
            <div className={`flex items-center justify-between bg-gradient-to-b ${accent} px-4 py-2.5`}>
              <div className="flex items-center gap-2">
                <span className={`h-2 w-2 rounded-full ${dot}`} />
                <span className="text-sm font-bold text-white">{label}</span>
              </div>
              <span className={`rounded-full px-2.5 py-0.5 text-xs font-bold ${counter}`}>
                {groups[key].length}
              </span>
            </div>

            {/* Cards */}
            <div className="flex-1 space-y-3 overflow-y-auto p-3">
              {groups[key].length === 0 ? (
                <div className="flex flex-col items-center justify-center pt-16 text-center">
                  <div className="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-slate-800">
                    <span className="text-2xl text-slate-600">○</span>
                  </div>
                  <p className="text-sm text-slate-600">Sipariş yok</p>
                </div>
              ) : (
                groups[key].map((order) => (
                  <OrderCard
                    key={order.id}
                    order={order}
                    onUpdate={load}
                    isNew={newIds.has(order.id)}
                  />
                ))
              )}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
