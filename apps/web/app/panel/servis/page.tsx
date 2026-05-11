'use client';

import { useEffect, useState, useTransition } from 'react';
import { serveOrderAction } from './actions';

type OrderItem = { id: number; name: string; quantity: number };

type ServiceOrder = {
  id: number;
  order_number: string;
  table: string | null;
  type: string;
  created_at: string;
  items: OrderItem[];
};

function timeAgo(dateStr: string): string {
  const diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000);
  if (diff < 60) return `${diff}s önce`;
  if (diff < 3600) return `${Math.floor(diff / 60)} dk önce`;
  return `${Math.floor(diff / 3600)} sa önce`;
}

function ServiceCard({ order, onUpdate }: { order: ServiceOrder; onUpdate: () => void }) {
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
    <div className="flex flex-col rounded-2xl border-2 border-green-300 bg-green-50 p-5">
      <div className="flex items-start justify-between gap-2">
        <div>
          <p className="font-mono text-xl font-bold">{order.order_number}</p>
          <p className="mt-1 text-sm font-semibold text-green-700">
            📍 {order.table ?? order.type}
          </p>
          <p className="text-xs text-gray-500">{timeAgo(order.created_at)}</p>
        </div>
        <span className="rounded-full bg-green-200 px-3 py-1 text-sm font-bold text-green-800">Hazır</span>
      </div>

      <ul className="mt-3 space-y-1 rounded-xl bg-white/60 p-3">
        {order.items.map((item) => (
          <li key={item.id} className="text-sm">
            <span className="font-bold">{item.quantity}×</span> {item.name}
          </li>
        ))}
      </ul>

      {error && <p className="mt-2 text-xs text-red-600">{error}</p>}

      <button
        onClick={serve}
        disabled={pending}
        className="mt-4 rounded-xl bg-green-600 py-3 text-base font-bold text-white transition hover:bg-green-700 disabled:opacity-50"
      >
        {pending ? 'İşleniyor…' : '✓ Servis edildi'}
      </button>
    </div>
  );
}

export default function ServisPage() {
  const [orders, setOrders] = useState<ServiceOrder[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  async function load() {
    try {
      const res = await fetch('/api/panel/service');
      if (!res.ok) throw new Error('Yüklenemedi');
      const data: { data: ServiceOrder[] } = await res.json();
      setOrders(data.data ?? []);
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

  return (
    <div className="p-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold tracking-tight">🛎 Servis Ekranı</h1>
          <p className="mt-1 text-sm text-[var(--muted)]">Teslim bekleyen siparişler</p>
        </div>
        <div className="flex items-center gap-2">
          {loading && <span className="text-sm text-gray-400">Yükleniyor…</span>}
          <span className="rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800">
            {orders.length} sipariş
          </span>
        </div>
      </div>

      {error && (
        <div className="mt-4 rounded-xl bg-red-50 p-4 text-sm text-red-700">{error}</div>
      )}

      <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        {orders.length === 0 && !loading ? (
          <div className="col-span-full rounded-2xl border border-dashed border-[var(--border)] p-12 text-center text-[var(--muted)]">
            <p className="text-4xl">✅</p>
            <p className="mt-2 font-medium">Tüm siparişler servis edildi</p>
          </div>
        ) : (
          orders.map((order) => (
            <ServiceCard key={order.id} order={order} onUpdate={load} />
          ))
        )}
      </div>
    </div>
  );
}
