import { redirect } from 'next/navigation';
import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';

type Order = {
  id: number;
  order_number: string;
  public_code: string;
  status: string;
  status_label: string;
  payment_status: string;
  total: number;
  table: string | null;
  created_at: string;
};

const statusColors: Record<string, string> = {
  new: 'bg-blue-100 text-blue-800',
  preparing: 'bg-yellow-100 text-yellow-800',
  ready: 'bg-green-100 text-green-800',
  delivered: 'bg-gray-100 text-gray-600',
  cancelled: 'bg-red-100 text-red-700',
};

const paymentColors: Record<string, string> = {
  pending: 'bg-orange-100 text-orange-700',
  paid: 'bg-green-100 text-green-700',
  refunded: 'bg-gray-100 text-gray-600',
};

export default async function OrdersPage() {
  const token = await getAuthToken();
  if (!token) redirect('/giris');

  let orders: Order[] = [];
  try {
    const data = await apiFetch<{ data: Order[] }>('/api/v1/panel/orders', { token });
    orders = data.data;
  } catch {
    redirect('/giris');
  }

  return (
    <div className="p-6">
      <h1 className="text-2xl font-semibold tracking-tight">Siparişler</h1>
      <p className="mt-1 text-sm text-[var(--muted)]">Son 50 sipariş</p>

      <div className="card mt-6 overflow-hidden p-0">
        {orders.length === 0 ? (
          <p className="p-8 text-center text-[var(--muted)]">Henüz sipariş yok.</p>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="border-b border-[var(--border)] bg-[var(--bg-soft)]">
                  <th className="px-4 py-3 text-left font-semibold">Sipariş No</th>
                  <th className="px-4 py-3 text-left font-semibold">Masa</th>
                  <th className="px-4 py-3 text-left font-semibold">Durum</th>
                  <th className="px-4 py-3 text-left font-semibold">Ödeme</th>
                  <th className="px-4 py-3 text-right font-semibold">Tutar</th>
                  <th className="px-4 py-3 text-right font-semibold">Tarih</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-[var(--border)]">
                {orders.map((order) => (
                  <tr key={order.id} className="hover:bg-[var(--bg-soft)]">
                    <td className="px-4 py-3">
                      <span className="font-mono font-semibold">{order.order_number}</span>
                      <br />
                      <span className="text-xs text-[var(--muted)]">{order.public_code}</span>
                    </td>
                    <td className="px-4 py-3 text-[var(--muted)]">{order.table ?? '—'}</td>
                    <td className="px-4 py-3">
                      <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${statusColors[order.status] ?? 'bg-gray-100 text-gray-600'}`}>
                        {order.status_label}
                      </span>
                    </td>
                    <td className="px-4 py-3">
                      <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${paymentColors[order.payment_status] ?? 'bg-gray-100 text-gray-600'}`}>
                        {order.payment_status === 'paid' ? 'Ödendi' : order.payment_status === 'refunded' ? 'İade' : 'Bekliyor'}
                      </span>
                    </td>
                    <td className="px-4 py-3 text-right font-semibold">{order.total.toLocaleString('tr-TR')} ₺</td>
                    <td className="px-4 py-3 text-right text-xs text-[var(--muted)]">
                      {new Date(order.created_at).toLocaleString('tr-TR', { dateStyle: 'short', timeStyle: 'short' })}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}
