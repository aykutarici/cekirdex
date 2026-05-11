import { redirect } from 'next/navigation';
import Link from 'next/link';
import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';
import { updateOrderStatusAction } from '../actions';

type OrderItem = {
  id: number;
  name: string;
  quantity: number;
  unit_price: number;
  subtotal: number;
  note: string | null;
};

type OrderDetail = {
  id: number;
  order_number: string;
  public_code: string;
  status: string;
  status_label: string;
  payment_status: string;
  total: number;
  table: string | null;
  type: string;
  customer_name: string | null;
  customer_phone: string | null;
  note: string | null;
  items: OrderItem[];
  created_at: string;
};

const statusColors: Record<string, string> = {
  new:       'bg-blue-100 text-blue-800',
  preparing: 'bg-yellow-100 text-yellow-800',
  ready:     'bg-green-100 text-green-800',
  delivered: 'bg-gray-100 text-gray-600',
  cancelled: 'bg-red-100 text-red-700',
};

const nextStatuses: Record<string, Array<{ value: string; label: string; cls: string }>> = {
  new:       [{ value: 'preparing', label: 'Hazırlanıyor', cls: 'bg-yellow-500 text-white' }, { value: 'cancelled', label: 'İptal', cls: 'border border-red-200 text-red-600 bg-white' }],
  preparing: [{ value: 'ready', label: 'Hazır', cls: 'bg-green-500 text-white' }, { value: 'cancelled', label: 'İptal', cls: 'border border-red-200 text-red-600 bg-white' }],
  ready:     [{ value: 'delivered', label: 'Teslim Edildi', cls: 'bg-gray-700 text-white' }],
};

export default async function OrderDetailPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  const token = await getAuthToken();
  if (!token) redirect('/giris');

  let order: OrderDetail;
  try {
    const data = await apiFetch<{ data: OrderDetail }>(`/api/v1/panel/orders/${id}`, { token });
    order = data.data;
  } catch {
    redirect('/panel/siparisler');
  }

  const actions = nextStatuses[order.status] ?? [];

  return (
    <div className="p-6">
      <Link href="/panel/siparisler" className="text-sm text-[var(--muted)] hover:text-[var(--ink)]">
        ← Siparişler
      </Link>

      <div className="mt-4 flex flex-wrap items-start justify-between gap-4">
        <div>
          <div className="flex items-center gap-3">
            <h1 className="font-mono text-2xl font-bold">{order.order_number}</h1>
            <span className={`rounded-full px-2 py-0.5 text-xs font-semibold ${statusColors[order.status] ?? 'bg-gray-100'}`}>
              {order.status_label}
            </span>
          </div>
          <p className="mt-1 text-sm text-[var(--muted)]">
            {order.table ?? order.type} · {new Date(order.created_at).toLocaleString('tr-TR')}
          </p>
        </div>

        <div className="flex gap-2">
          {actions.map(({ value, label, cls }) => {
            const statusAction = updateOrderStatusAction.bind(null, order.id, value);
            return (
              <form key={value} action={statusAction}>
                <button type="submit" className={`rounded-xl px-4 py-2 text-sm font-semibold transition hover:opacity-80 ${cls}`}>
                  {label}
                </button>
              </form>
            );
          })}
        </div>
      </div>

      <div className="mt-6 grid gap-6 lg:grid-cols-2">
        {/* Ürünler */}
        <div className="card overflow-hidden p-0">
          <div className="border-b border-[var(--border)] bg-[var(--bg-soft)] px-5 py-3">
            <h2 className="font-semibold">Sipariş Kalemleri</h2>
          </div>
          <div className="divide-y divide-[var(--border)]">
            {order.items.map((item) => (
              <div key={item.id} className="flex items-center justify-between px-5 py-3 text-sm">
                <div>
                  <span className="font-semibold">{item.quantity}×</span> {item.name}
                  {item.note && <span className="ml-1 text-xs text-gray-500">({item.note})</span>}
                  <div className="text-xs text-[var(--muted)]">{Number(item.unit_price).toLocaleString('tr-TR')} ₺/adet</div>
                </div>
                <span className="font-semibold">{Number(item.subtotal).toLocaleString('tr-TR')} ₺</span>
              </div>
            ))}
          </div>
          <div className="flex justify-between border-t border-[var(--border)] bg-[var(--bg-soft)] px-5 py-3 font-bold">
            <span>Toplam</span>
            <span>{Number(order.total).toLocaleString('tr-TR')} ₺</span>
          </div>
        </div>

        {/* Bilgiler */}
        <div className="card p-5 space-y-3 text-sm">
          <h2 className="font-semibold">Sipariş Bilgileri</h2>
          {[
            { label: 'Müşteri', value: order.customer_name },
            { label: 'Telefon', value: order.customer_phone },
            { label: 'Masa', value: order.table },
            { label: 'Not', value: order.note },
            { label: 'Ödeme', value: order.payment_status === 'paid' ? 'Ödendi' : 'Bekliyor' },
          ].map(({ label, value }) => (
            <div key={label} className="flex gap-3">
              <span className="w-20 text-[var(--muted)]">{label}</span>
              <span className="font-medium">{value ?? '—'}</span>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
