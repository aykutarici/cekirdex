import { redirect } from 'next/navigation';
import Link from 'next/link';
import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';
import { confirmTakeawayAction, advanceTakeawayAction, cancelTakeawayAction } from '../actions';

type TakeawayItem = { id: number; name: string; quantity: number; unit_price: number; subtotal: number; note: string | null };

type TakeawayDetail = {
  id: number;
  order_number: string;
  status: string;
  status_label: string;
  type: string;
  customer_name: string | null;
  customer_phone: string | null;
  customer_address: string | null;
  note: string | null;
  total: number;
  items: TakeawayItem[];
  created_at: string;
};

const statusColors: Record<string, string> = {
  new:       'bg-blue-100 text-blue-800',
  confirmed: 'bg-indigo-100 text-indigo-800',
  preparing: 'bg-yellow-100 text-yellow-800',
  ready:     'bg-green-100 text-green-800',
  delivered: 'bg-gray-100 text-gray-600',
  cancelled: 'bg-red-100 text-red-700',
};

export default async function TakeawayDetailPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  const token = await getAuthToken();
  if (!token) redirect('/giris');

  let order: TakeawayDetail;
  try {
    const data = await apiFetch<{ data: TakeawayDetail }>(`/api/v1/panel/takeaway/${id}`, { token });
    order = data.data;
  } catch {
    redirect('/panel/paket');
  }

  const confirmAction = confirmTakeawayAction.bind(null, order.id);
  const advanceAction = advanceTakeawayAction.bind(null, order.id);
  const cancelAction = cancelTakeawayAction.bind(null, order.id);

  return (
    <div className="p-6">
      <Link href="/panel/paket" className="text-sm text-[var(--muted)] hover:text-[var(--ink)]">
        ← Paket siparişler
      </Link>

      <div className="mt-4 flex flex-wrap items-start justify-between gap-4">
        <div>
          <div className="flex items-center gap-3">
            <h1 className="text-2xl font-semibold font-mono">{order.order_number}</h1>
            <span className={`rounded-full px-2 py-0.5 text-xs font-semibold ${statusColors[order.status] ?? 'bg-gray-100'}`}>
              {order.status_label}
            </span>
          </div>
          <p className="mt-1 text-sm text-[var(--muted)]">
            {order.type === 'takeaway' ? 'Gel-al' : 'Teslimat'} · {new Date(order.created_at).toLocaleString('tr-TR')}
          </p>
        </div>

        <div className="flex gap-2">
          {order.status === 'new' && (
            <form action={confirmAction}>
              <button type="submit" className="rounded-xl bg-indigo-500 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-600">
                Onayla
              </button>
            </form>
          )}
          {(order.status === 'confirmed' || order.status === 'preparing') && (
            <form action={advanceAction}>
              <button type="submit" className="rounded-xl bg-green-500 px-4 py-2 text-sm font-semibold text-white hover:bg-green-600">
                İlerlet
              </button>
            </form>
          )}
          {!['delivered', 'cancelled'].includes(order.status) && (
            <form action={cancelAction}>
              <button type="submit" className="rounded-xl border border-red-200 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">
                İptal
              </button>
            </form>
          )}
        </div>
      </div>

      <div className="mt-6 grid gap-6 lg:grid-cols-2">
        <div className="card overflow-hidden p-0">
          <div className="border-b border-[var(--border)] bg-[var(--bg-soft)] px-5 py-3">
            <h2 className="font-semibold">Sipariş Detayı</h2>
          </div>
          <div className="divide-y divide-[var(--border)]">
            {order.items.map((item) => (
              <div key={item.id} className="flex items-center justify-between px-5 py-3 text-sm">
                <div>
                  <span className="font-semibold">{item.quantity}×</span> {item.name}
                  {item.note && <span className="ml-1 text-xs text-gray-500">({item.note})</span>}
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

        <div className="card p-5 space-y-3 text-sm">
          <h2 className="font-semibold">Müşteri Bilgileri</h2>
          <div className="flex gap-3">
            <span className="w-32 text-[var(--muted)]">Ad</span>
            <span className="font-medium">{order.customer_name ?? '—'}</span>
          </div>
          <div className="flex gap-3">
            <span className="w-32 text-[var(--muted)]">Telefon</span>
            <span className="font-medium">{order.customer_phone ?? '—'}</span>
          </div>
          {order.type !== 'takeaway' && (
            <div className="flex gap-3">
              <span className="w-32 text-[var(--muted)]">Adres</span>
              <span className="font-medium">{order.customer_address ?? '—'}</span>
            </div>
          )}
          {order.note && (
            <div className="flex gap-3">
              <span className="w-32 text-[var(--muted)]">Not</span>
              <span className="font-medium">{order.note}</span>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
