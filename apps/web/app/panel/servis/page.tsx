'use client';

import { useEffect, useState, useTransition } from 'react';
import { serveOrderAction, confirmOrderAction } from './actions';

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

function ReadyCard({ order, onUpdate }: { order: ServiceOrder; onUpdate: () => void }) {
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
            {order.table ?? order.type}
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

function PendingCard({ order, onUpdate }: { order: ServiceOrder; onUpdate: () => void }) {
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
    <div className="flex flex-col rounded-2xl border-2 border-blue-200 bg-blue-50 p-5">
      <div className="flex items-start justify-between gap-2">
        <div>
          <p className="font-mono text-xl font-bold">{order.order_number}</p>
          <p className="mt-1 text-sm font-semibold text-blue-700">
            {order.table ?? order.type}
          </p>
          <p className="text-xs text-gray-500">{timeAgo(order.created_at)}</p>
        </div>
        <span className="rounded-full bg-blue-200 px-3 py-1 text-sm font-bold text-blue-800">Bekliyor</span>
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
        onClick={confirm}
        disabled={pending}
        className="mt-4 rounded-xl bg-blue-600 py-3 text-base font-bold text-white transition hover:bg-blue-700 disabled:opacity-50"
      >
        {pending ? 'İşleniyor…' : '✓ Onayla'}
      </button>
    </div>
  );
}

export default function ServisPage() {
  const [readyOrders, setReadyOrders] = useState<ServiceOrder[]>([]);
  const [pendingOrders, setPendingOrders] = useState<ServiceOrder[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  async function load() {
    try {
      const res = await fetch('/api/panel/service');
      if (!res.ok) throw new Error('Yüklenemedi');
      const data: { data: (ServiceOrder & { status: string })[] } = await res.json();
      const all = data.data ?? [];
      // Service feed returns new+ready together — split by status
      setReadyOrders(all.filter((o) => o.status === 'ready'));
      setPendingOrders(all.filter((o) => o.status !== 'ready'));
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

  const totalCount = readyOrders.length + pendingOrders.length;

  return (
    <div className="p-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold tracking-tight">Servis Ekranı</h1>
          <p className="mt-1 text-sm text-[var(--muted)]">Aktif siparişler</p>
        </div>
        <div className="flex items-center gap-2">
          {loading && <span className="text-sm text-gray-400">Yükleniyor…</span>}
          <span className="rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800">
            {totalCount} sipariş
          </span>
        </div>
      </div>

      {error && (
        <div className="mt-4 rounded-xl bg-red-50 p-4 text-sm text-red-700">{error}</div>
      )}

      {/* Hazır siparişler */}
      {readyOrders.length > 0 && (
        <div className="mt-6">
          <h2 className="mb-3 flex items-center gap-2 text-base font-bold text-green-700">
            <span className="rounded-full bg-green-100 px-2 py-0.5 text-xs">{readyOrders.length}</span>
            Hazır — Teslim Bekliyor
          </h2>
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            {readyOrders.map((order) => (
              <ReadyCard key={order.id} order={order} onUpdate={load} />
            ))}
          </div>
        </div>
      )}

      {/* Yeni / Bekleyen siparişler */}
      {pendingOrders.length > 0 && (
        <div className="mt-8">
          <h2 className="mb-3 flex items-center gap-2 text-base font-bold text-blue-700">
            <span className="rounded-full bg-blue-100 px-2 py-0.5 text-xs">{pendingOrders.length}</span>
            Yeni / Bekleyen Siparişler
          </h2>
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            {pendingOrders.map((order) => (
              <PendingCard key={order.id} order={order} onUpdate={load} />
            ))}
          </div>
        </div>
      )}

      {totalCount === 0 && !loading && (
        <div className="mt-6 rounded-2xl border border-dashed border-[var(--border)] p-12 text-center text-[var(--muted)]">
          <p className="text-4xl text-emerald-600">✓</p>
          <p className="mt-2 font-medium">Bekleyen sipariş yok</p>
        </div>
      )}
    </div>
  );
}
