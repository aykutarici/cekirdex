'use client';

import { useEffect, useState } from 'react';

type OrderItem = { id: number; name: string; quantity: number; subtotal: number; status: string };

type Order = {
  order_number: string;
  public_code: string;
  status: string;
  status_label: string;
  payment_status: string;
  total: number;
  restaurant?: { name: string } | null;
  table?: { name: string } | null;
  items: OrderItem[];
  created_at?: string;
  updated_at?: string;
};

const ACTIVE_STATUSES = ['new', 'confirmed', 'preparing', 'ready'];

const STATUS_COLORS: Record<string, string> = {
  new: 'bg-blue-100 text-blue-800',
  confirmed: 'bg-indigo-100 text-indigo-800',
  preparing: 'bg-amber-100 text-amber-800',
  ready: 'bg-green-100 text-green-800',
  served: 'bg-gray-100 text-gray-600',
  cancelled: 'bg-red-100 text-red-700',
  completed: 'bg-gray-100 text-gray-600',
};

function PulsingDot({ active }: { active: boolean }) {
  if (!active) return null;
  return (
    <span className="relative mr-2 inline-flex h-3 w-3">
      <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75" />
      <span className="relative inline-flex h-3 w-3 rounded-full bg-green-500" />
    </span>
  );
}

export function OrderTracker({
  publicCode,
  initialOrder,
}: {
  publicCode: string;
  initialOrder: Order;
}) {
  const [order, setOrder] = useState<Order>(initialOrder);
  const [lastUpdated, setLastUpdated] = useState<Date>(new Date());

  const isActive = ACTIVE_STATUSES.includes(order.status);

  useEffect(() => {
    if (!isActive) return;

    async function poll() {
      try {
        const res = await fetch(`/api/orders/${publicCode}`);
        if (!res.ok) return;
        const data = await res.json();
        if (data.order) {
          setOrder(data.order);
          setLastUpdated(new Date());
        }
      } catch {
        // sessiz hata
      }
    }

    const id = setInterval(poll, 10_000);
    return () => clearInterval(id);
  }, [publicCode, isActive]);

  const statusCls = STATUS_COLORS[order.status] ?? 'bg-gray-100 text-gray-600';

  return (
    <div className="card mt-8 p-6">
      {/* Başlık */}
      <div className="flex items-center justify-between">
        <div className="flex items-center">
          <PulsingDot active={isActive} />
          <span className={`rounded-full px-3 py-1 text-sm font-bold ${statusCls}`}>
            {order.status_label}
          </span>
        </div>
        {isActive && (
          <span className="text-xs text-[var(--muted)]">
            Son güncelleme: {lastUpdated.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit', second: '2-digit' })}
          </span>
        )}
      </div>

      {/* Meta bilgiler */}
      <div className="mt-6 grid gap-4 sm:grid-cols-3">
        <div>
          <p className="text-xs text-[var(--muted)]">Sipariş kodu</p>
          <p className="mt-0.5 font-mono font-bold">{order.public_code}</p>
        </div>
        <div>
          <p className="text-xs text-[var(--muted)]">Sipariş no</p>
          <p className="mt-0.5 font-mono font-bold">{order.order_number}</p>
        </div>
        <div>
          <p className="text-xs text-[var(--muted)]">Toplam</p>
          <p className="mt-0.5 font-bold">{order.total.toLocaleString('tr-TR')} TL</p>
        </div>
        {order.table && (
          <div>
            <p className="text-xs text-[var(--muted)]">Masa</p>
            <p className="mt-0.5 font-medium">{order.table.name}</p>
          </div>
        )}
        {order.created_at && (
          <div>
            <p className="text-xs text-[var(--muted)]">Sipariş tarihi</p>
            <p className="mt-0.5 text-sm text-[var(--muted)]">
              {new Date(order.created_at).toLocaleString('tr-TR', {
                dateStyle: 'medium',
                timeStyle: 'short',
              })}
            </p>
          </div>
        )}
      </div>

      {/* Ürünler */}
      <div className="mt-6 divide-y divide-[var(--border)]">
        {order.items.map((item) => (
          <div key={item.id} className="flex items-center justify-between py-3">
            <div>
              <span className="font-medium">
                {item.quantity}× {item.name}
              </span>
            </div>
            <div className="text-right">
              <p className="font-semibold">{item.subtotal.toLocaleString('tr-TR')} TL</p>
            </div>
          </div>
        ))}
      </div>

      {/* Durum açıklaması */}
      {isActive && (
        <div className="mt-5 rounded-xl bg-[var(--bg-soft)] p-4 text-sm text-[var(--muted)]">
          <PulsingDot active={true} />
          Siparişiniz takip ediliyor — her 10 saniyede bir güncellenir.
        </div>
      )}
    </div>
  );
}
