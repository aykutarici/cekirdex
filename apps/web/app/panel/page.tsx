import Link from 'next/link';
import { redirect } from 'next/navigation';
import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';

type DashboardResponse = {
  metrics: {
    orders: number;
    tables: number;
    reservations: number;
    staff: number;
  };
  recent_orders: Array<{
    id: number;
    order_number: string;
    public_code: string;
    status_label: string;
    total: number;
    table: string | null;
  }>;
};

export default async function PanelPage() {
  const token = await getAuthToken();

  if (!token) {
    return (
      <main className="py-12">
        <div className="container max-w-2xl">
          <p className="eyebrow">Restoran paneli</p>
          <h1 className="mt-3 text-4xl font-semibold tracking-[-0.04em]">Önce giriş yapın</h1>
          <p className="mt-4 text-[var(--muted)]">Panel verileri Laravel API'den güvenli bearer token ile alınır.</p>
          <Link className="btn btn-primary mt-8 inline-flex" href="/giris">Giriş yap</Link>
        </div>
      </main>
    );
  }

  let dashboard: DashboardResponse;
  try {
    dashboard = await apiFetch<DashboardResponse>('/api/v1/panel/dashboard', { token });
  } catch {
    // Server Component'ten cookie silinemez (Next.js 15+).
    // Geçersiz token olduğunda sadece login'e yönlendir;
    // cookie logout sayfasından server action ile silinir.
    redirect('/giris');
  }

  return (
    <main className="py-12">
      <div className="container">
        <p className="eyebrow">Restoran paneli</p>
        <h1 className="mt-3 text-4xl font-semibold tracking-[-0.04em]">Operasyon merkezi</h1>
        <div className="mt-8 grid gap-4 md:grid-cols-3">
          {[
            ['Siparişler', dashboard.metrics.orders],
            ['Masalar', dashboard.metrics.tables],
            ['Rezervasyonlar', dashboard.metrics.reservations],
            ['Personel', dashboard.metrics.staff],
          ].map(([item, value]) => (
            <div key={item} className="card p-5">
              <p className="text-sm text-[var(--muted)]">{item}</p>
              <p className="mt-2 text-3xl font-semibold">{value}</p>
            </div>
          ))}
        </div>
        <section className="card mt-8 overflow-hidden p-0">
          <div className="border-b border-[var(--border)] p-5">
            <h2 className="text-xl font-semibold">Son siparişler</h2>
          </div>
          <div className="divide-y divide-[var(--border)]">
            {dashboard.recent_orders.length ? dashboard.recent_orders.map((order) => (
              <div key={order.id} className="grid gap-2 p-5 md:grid-cols-4">
                <strong>{order.order_number}</strong>
                <span>{order.table ?? 'Masa yok'}</span>
                <span>{order.status_label}</span>
                <span className="font-semibold">{order.total.toLocaleString('tr-TR')} TL</span>
              </div>
            )) : (
              <p className="p-5 text-[var(--muted)]">Henüz sipariş yok.</p>
            )}
          </div>
        </section>
      </div>
    </main>
  );
}
