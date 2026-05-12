'use client';

import { useEffect, useState, useTransition } from 'react';
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

const statusConfig: Record<string, { label: string; bg: string; border: string }> = {
  new:        { label: 'Yeni',        bg: 'bg-blue-50',   border: 'border-blue-300' },
  confirmed:  { label: 'Onaylı',      bg: 'bg-blue-50',   border: 'border-blue-300' },
  preparing:  { label: 'Hazırlanıyor',bg: 'bg-yellow-50', border: 'border-yellow-300' },
  ready:      { label: 'Hazır',       bg: 'bg-green-50',  border: 'border-green-300' },
};

function timeAgo(dateStr: string): string {
  const diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000);
  if (diff < 60) return `${diff}s`;
  if (diff < 3600) return `${Math.floor(diff / 60)}dk`;
  return `${Math.floor(diff / 3600)}sa`;
}

function OrderCard({ order, onUpdate }: { order: KdsOrder; onUpdate: () => void }) {
  const [pending, startTransition] = useTransition();
  const [error, setError] = useState<string | null>(null);
  const cfg = statusConfig[order.status] ?? { label: order.status_label, bg: 'bg-gray-50', border: 'border-gray-200' };
  const isReady = order.status === 'ready';

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
    <div className={`flex flex-col rounded-2xl border-2 ${cfg.border} ${cfg.bg} p-4`}>
      <div className="flex items-start justify-between gap-2">
        <div>
          <p className="font-mono text-lg font-bold leading-none">{order.order_number}</p>
          <p className="mt-1 text-sm font-medium text-gray-600">
            {order.table ?? order.type} · {timeAgo(order.created_at)}
          </p>
        </div>
        <span className={`rounded-full px-2 py-0.5 text-xs font-semibold ${
          order.status === 'ready'
            ? 'bg-green-200 text-green-800'
            : order.status === 'preparing'
              ? 'bg-yellow-200 text-yellow-800'
              : 'bg-blue-200 text-blue-800'
        }`}>
          {cfg.label}
        </span>
      </div>

      <ul className="mt-3 space-y-1">
        {order.items.map((item) => (
          <li key={item.id} className="text-sm">
            <span className="font-semibold">{item.quantity}×</span> {item.name}
            {item.note && <span className="ml-1 text-xs text-gray-500">({item.note})</span>}
          </li>
        ))}
      </ul>

      {error && <p className="mt-2 text-xs text-red-600">{error}</p>}

      <div className="mt-4 flex gap-2">
        {!isReady && (
          <button
            onClick={advance}
            disabled={pending}
            className="flex-1 rounded-xl bg-green-500 py-2 text-sm font-bold text-white transition hover:bg-green-600 disabled:opacity-50"
          >
            {order.status === 'preparing' ? '✓ Hazır' : '▶ Hazırlanıyor'}
          </button>
        )}
        <button
          onClick={cancel}
          disabled={pending}
          className="rounded-xl border border-red-200 bg-white px-3 py-2 text-sm font-bold text-red-600 transition hover:bg-red-50 disabled:opacity-50"
        >
          İptal
        </button>
      </div>
    </div>
  );
}

export default function KdsPage() {
  const [orders, setOrders] = useState<KdsOrder[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  async function load() {
    try {
      const res = await fetch('/api/panel/kds');
      if (!res.ok) throw new Error('Yüklenemedi');
      const data: { data: KdsOrder[] } = await res.json();
      setOrders(data.data ?? []);
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

  const groups: Record<string, KdsOrder[]> = {
    new:       orders.filter((o) => o.status === 'new' || o.status === 'confirmed'),
    preparing: orders.filter((o) => o.status === 'preparing'),
    ready:     orders.filter((o) => o.status === 'ready'),
  };

  const columnConfig = [
    { key: 'new',      label: 'Yeni / Onaylı',  headerCls: 'bg-blue-100 text-blue-800' },
    { key: 'preparing',label: 'Hazırlanıyor',    headerCls: 'bg-yellow-100 text-yellow-800' },
    { key: 'ready',    label: 'Hazır',           headerCls: 'bg-green-100 text-green-800' },
  ];

  return (
    <div className="flex h-screen flex-col bg-gray-100">
      <header className="flex items-center justify-between border-b border-gray-200 bg-white px-6 py-3">
        <h1 className="text-xl font-bold">KDS — Mutfak Ekranı</h1>
        <div className="flex items-center gap-3">
          {loading && <span className="text-sm text-gray-400">Yükleniyor…</span>}
          {error && <span className="text-sm text-red-600">{error}</span>}
          <span className="text-sm text-gray-500">
            {orders.length} aktif sipariş
          </span>
        </div>
      </header>

      <div className="flex flex-1 gap-4 overflow-hidden p-4">
        {columnConfig.map(({ key, label, headerCls }) => (
          <div key={key} className="flex flex-1 flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <div className={`flex items-center justify-between px-4 py-2 ${headerCls} rounded-t-2xl`}>
              <span className="font-bold">{label}</span>
              <span className="rounded-full bg-white/60 px-2 py-0.5 text-xs font-semibold">
                {groups[key]?.length ?? 0}
              </span>
            </div>
            <div className="flex-1 space-y-3 overflow-y-auto p-3">
              {groups[key]?.length === 0 ? (
                <p className="py-8 text-center text-sm text-gray-400">Sipariş yok</p>
              ) : (
                groups[key]?.map((order) => (
                  <OrderCard key={order.id} order={order} onUpdate={load} />
                ))
              )}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
