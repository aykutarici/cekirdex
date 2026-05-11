import { redirect } from 'next/navigation';
import Link from 'next/link';
import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';
import { confirmTakeawayAction, advanceTakeawayAction, cancelTakeawayAction } from './actions';

type TakeawayOrder = {
  id: number;
  order_number: string;
  status: string;
  status_label: string;
  customer_name: string | null;
  customer_phone: string | null;
  total: number;
  type: string;
  created_at: string;
};

const statusColors: Record<string, string> = {
  new:        'bg-blue-100 text-blue-800',
  confirmed:  'bg-indigo-100 text-indigo-800',
  preparing:  'bg-yellow-100 text-yellow-800',
  ready:      'bg-green-100 text-green-800',
  delivered:  'bg-gray-100 text-gray-600',
  cancelled:  'bg-red-100 text-red-700',
};

export default async function PaketPage() {
  const token = await getAuthToken();
  if (!token) redirect('/giris');

  let orders: TakeawayOrder[] = [];
  try {
    const data = await apiFetch<{ data: TakeawayOrder[] }>('/api/v1/panel/takeaway', { token });
    orders = data.data;
  } catch {
    redirect('/giris');
  }

  return (
    <div className="p-6">
      <h1 className="text-2xl font-semibold tracking-tight">📦 Paket Siparişler</h1>
      <p className="mt-1 text-sm text-[var(--muted)]">{orders.length} sipariş</p>

      {orders.length === 0 ? (
        <div className="mt-8 rounded-2xl border border-dashed border-[var(--border)] p-12 text-center text-[var(--muted)]">
          <p className="text-4xl">📦</p>
          <p className="mt-2 font-medium">Paket sipariş yok</p>
        </div>
      ) : (
        <div className="card mt-6 overflow-hidden p-0">
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="border-b border-[var(--border)] bg-[var(--bg-soft)]">
                  <th className="px-4 py-3 text-left font-semibold">Sipariş No</th>
                  <th className="px-4 py-3 text-left font-semibold">Müşteri</th>
                  <th className="px-4 py-3 text-left font-semibold">Tür</th>
                  <th className="px-4 py-3 text-left font-semibold">Durum</th>
                  <th className="px-4 py-3 text-right font-semibold">Tutar</th>
                  <th className="px-4 py-3 text-right font-semibold">İşlemler</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-[var(--border)]">
                {orders.map((order) => {
                  const confirmAction = confirmTakeawayAction.bind(null, order.id);
                  const advanceAction = advanceTakeawayAction.bind(null, order.id);
                  const cancelAction = cancelTakeawayAction.bind(null, order.id);

                  return (
                    <tr key={order.id} className="hover:bg-[var(--bg-soft)]">
                      <td className="px-4 py-3">
                        <Link href={`/panel/paket/${order.id}`} className="font-mono font-semibold hover:underline">
                          {order.order_number}
                        </Link>
                        <p className="text-xs text-[var(--muted)]">
                          {new Date(order.created_at).toLocaleString('tr-TR', { dateStyle: 'short', timeStyle: 'short' })}
                        </p>
                      </td>
                      <td className="px-4 py-3">
                        <p>{order.customer_name ?? '—'}</p>
                        {order.customer_phone && (
                          <p className="text-xs text-[var(--muted)]">{order.customer_phone}</p>
                        )}
                      </td>
                      <td className="px-4 py-3 capitalize text-[var(--muted)]">
                        {order.type === 'takeaway' ? 'Gel-al' : 'Teslimat'}
                      </td>
                      <td className="px-4 py-3">
                        <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${statusColors[order.status] ?? 'bg-gray-100 text-gray-600'}`}>
                          {order.status_label}
                        </span>
                      </td>
                      <td className="px-4 py-3 text-right font-semibold">
                        {Number(order.total).toLocaleString('tr-TR')} ₺
                      </td>
                      <td className="px-4 py-3 text-right">
                        <div className="flex justify-end gap-1">
                          {order.status === 'new' && (
                            <form action={confirmAction}>
                              <button type="submit" className="rounded-lg bg-indigo-500 px-2.5 py-1 text-xs font-semibold text-white hover:bg-indigo-600">
                                Onayla
                              </button>
                            </form>
                          )}
                          {(order.status === 'confirmed' || order.status === 'preparing') && (
                            <form action={advanceAction}>
                              <button type="submit" className="rounded-lg bg-green-500 px-2.5 py-1 text-xs font-semibold text-white hover:bg-green-600">
                                İlerlet
                              </button>
                            </form>
                          )}
                          {!['delivered', 'cancelled'].includes(order.status) && (
                            <form action={cancelAction}>
                              <button type="submit" className="rounded-lg border border-red-200 px-2.5 py-1 text-xs font-semibold text-red-600 hover:bg-red-50">
                                İptal
                              </button>
                            </form>
                          )}
                        </div>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        </div>
      )}
    </div>
  );
}
