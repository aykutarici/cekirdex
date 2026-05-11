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
    status: string;
    total: number;
    table: string | null;
    created_at: string;
  }>;
};

const statusColors: Record<string, string> = {
  new: 'bg-blue-100 text-blue-800',
  preparing: 'bg-yellow-100 text-yellow-800',
  ready: 'bg-green-100 text-green-800',
  delivered: 'bg-gray-100 text-gray-600',
  cancelled: 'bg-red-100 text-red-700',
};

export default async function PanelPage() {
  const token = await getAuthToken();
  if (!token) redirect('/giris');

  let dashboard: DashboardResponse;
  try {
    dashboard = await apiFetch<DashboardResponse>('/api/v1/panel/dashboard', { token });
  } catch {
    redirect('/giris');
  }

  const metrics = [
    { label: 'Siparişler', value: dashboard.metrics.orders, href: '/panel/siparisler', color: 'text-orange-600' },
    { label: 'Masalar', value: dashboard.metrics.tables, href: '/panel/masalar', color: 'text-blue-600' },
    { label: 'Rezervasyonlar', value: dashboard.metrics.reservations, href: '/panel/rezervasyonlar', color: 'text-purple-600' },
    { label: 'Personel', value: dashboard.metrics.staff, href: '/panel/personel', color: 'text-green-600' },
  ];

  return (
    <div className="p-6">
      <h1 className="text-2xl font-semibold tracking-tight">Gösterge paneli</h1>
      <p className="mt-1 text-sm text-[var(--muted)]">Restoranınızın anlık durumu</p>

      {/* Metrikler */}
      <div className="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        {metrics.map(({ label, value, href, color }) => (
          <a
            key={label}
            href={href}
            className="card block p-5 transition-shadow hover:shadow-md"
          >
            <p className="text-sm text-[var(--muted)]">{label}</p>
            <p className={`mt-2 text-3xl font-bold ${color}`}>{value}</p>
          </a>
        ))}
      </div>

      {/* Hızlı erişim */}
      <div className="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {[
          { href: '/panel/siparisler', label: 'Sipariş yönetimi', desc: 'Aktif siparişleri takip et', icon: '🧾' },
          { href: '/panel/menu', label: 'Menü yönetimi', desc: 'Ürün ve kategori düzenle', icon: '🍽' },
          { href: '/panel/masalar', label: 'Masa ve QR', desc: 'Masaları görüntüle, QR indir', icon: '🪑' },
          { href: '/panel/rezervasyonlar', label: 'Rezervasyonlar', desc: 'Gelen rezervasyonları gör', icon: '📅' },
          { href: '/panel/personel', label: 'Personel', desc: 'Çalışan listesi ve roller', icon: '👥' },
          { href: '/panel/ayarlar', label: 'Restoran ayarları', desc: 'İletişim, vergi, hizmetler', icon: '⚙️' },
        ].map(({ href, label, desc, icon }) => (
          <a key={href} href={href} className="card flex items-start gap-4 p-5 transition-shadow hover:shadow-md">
            <span className="mt-0.5 text-2xl leading-none">{icon}</span>
            <div>
              <p className="font-semibold">{label}</p>
              <p className="mt-0.5 text-sm text-[var(--muted)]">{desc}</p>
            </div>
          </a>
        ))}
      </div>

      {/* Son siparişler */}
      <section className="card mt-8 overflow-hidden p-0">
        <div className="flex items-center justify-between border-b border-[var(--border)] px-5 py-4">
          <h2 className="font-semibold">Son siparişler</h2>
          <a href="/panel/siparisler" className="text-sm font-medium text-[var(--muted)] hover:text-[var(--ink)]">
            Tümünü gör →
          </a>
        </div>
        <div className="divide-y divide-[var(--border)]">
          {dashboard.recent_orders.length ? (
            dashboard.recent_orders.map((order) => (
              <div key={order.id} className="flex items-center gap-4 px-5 py-3">
                <div className="min-w-0 flex-1">
                  <p className="font-mono text-sm font-semibold">{order.order_number}</p>
                  <p className="text-xs text-[var(--muted)]">{order.table ?? 'Masa yok'}</p>
                </div>
                <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${statusColors[order.status] ?? 'bg-gray-100 text-gray-600'}`}>
                  {order.status_label}
                </span>
                <p className="shrink-0 text-sm font-semibold">{order.total.toLocaleString('tr-TR')} ₺</p>
              </div>
            ))
          ) : (
            <p className="p-5 text-sm text-[var(--muted)]">Henüz sipariş yok.</p>
          )}
        </div>
      </section>
    </div>
  );
}
