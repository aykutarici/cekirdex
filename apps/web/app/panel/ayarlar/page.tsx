import { redirect } from 'next/navigation';
import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';

type Restaurant = {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  address: string | null;
  city: string | null;
  phone: string | null;
  email: string | null;
  currency: string;
  tax_rate: number;
  service_charge_rate: number;
  accepts_takeaway: boolean;
  accepts_delivery: boolean;
  delivery_fee: number | null;
  delivery_min_amount: number | null;
  accepts_reservations: boolean;
  is_active: boolean;
};

function Row({ label, value }: { label: string; value: React.ReactNode }) {
  return (
    <div className="flex gap-4 border-b border-[var(--border)] px-5 py-3 last:border-0">
      <dt className="w-44 shrink-0 text-sm text-[var(--muted)]">{label}</dt>
      <dd className="text-sm font-medium">{value ?? '—'}</dd>
    </div>
  );
}

function Badge({ active, labelOn, labelOff }: { active: boolean; labelOn: string; labelOff: string }) {
  return (
    <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-700'}`}>
      {active ? labelOn : labelOff}
    </span>
  );
}

export default async function SettingsPage() {
  const token = await getAuthToken();
  if (!token) redirect('/giris');

  let restaurant: Restaurant;
  try {
    const data = await apiFetch<{ restaurant: Restaurant }>('/api/v1/panel/settings', { token });
    restaurant = data.restaurant;
  } catch {
    redirect('/giris');
  }

  return (
    <div className="p-6">
      <h1 className="text-2xl font-semibold tracking-tight">Restoran ayarları</h1>
      <p className="mt-1 text-sm text-[var(--muted)]">Restoranınızın genel bilgileri ve servis seçenekleri</p>

      {/* Genel bilgiler */}
      <div className="card mt-6 overflow-hidden p-0">
        <div className="border-b border-[var(--border)] bg-[var(--bg-soft)] px-5 py-3">
          <h2 className="font-semibold">Genel bilgiler</h2>
        </div>
        <dl>
          <Row label="Restoran adı" value={restaurant.name} />
          <Row label="Kısa URL (slug)" value={<span className="font-mono">{restaurant.slug}</span>} />
          <Row label="Açıklama" value={restaurant.description} />
          <Row label="Adres" value={restaurant.address} />
          <Row label="Şehir" value={restaurant.city} />
          <Row label="Telefon" value={restaurant.phone} />
          <Row label="E-posta" value={restaurant.email} />
          <Row label="Durum" value={<Badge active={restaurant.is_active} labelOn="Aktif" labelOff="Pasif" />} />
        </dl>
      </div>

      {/* Mali ayarlar */}
      <div className="card mt-6 overflow-hidden p-0">
        <div className="border-b border-[var(--border)] bg-[var(--bg-soft)] px-5 py-3">
          <h2 className="font-semibold">Mali ayarlar</h2>
        </div>
        <dl>
          <Row label="Para birimi" value={restaurant.currency} />
          <Row label="KDV oranı" value={`%${restaurant.tax_rate}`} />
          <Row label="Servis ücreti" value={`%${restaurant.service_charge_rate}`} />
        </dl>
      </div>

      {/* Servis seçenekleri */}
      <div className="card mt-6 overflow-hidden p-0">
        <div className="border-b border-[var(--border)] bg-[var(--bg-soft)] px-5 py-3">
          <h2 className="font-semibold">Servis seçenekleri</h2>
        </div>
        <dl>
          <Row label="Paket servis" value={<Badge active={restaurant.accepts_takeaway} labelOn="Açık" labelOff="Kapalı" />} />
          <Row label="Ev teslimatı" value={<Badge active={restaurant.accepts_delivery} labelOn="Açık" labelOff="Kapalı" />} />
          {restaurant.accepts_delivery && (
            <>
              <Row label="Teslimat ücreti" value={restaurant.delivery_fee != null ? `${Number(restaurant.delivery_fee).toLocaleString('tr-TR')} ₺` : null} />
              <Row label="Min. sipariş (teslimat)" value={restaurant.delivery_min_amount != null ? `${Number(restaurant.delivery_min_amount).toLocaleString('tr-TR')} ₺` : null} />
            </>
          )}
          <Row label="Rezervasyon" value={<Badge active={restaurant.accepts_reservations} labelOn="Açık" labelOff="Kapalı" />} />
        </dl>
      </div>

      {/* Public menü linki */}
      <div className="card mt-6 p-5">
        <p className="text-sm font-semibold">Public menü sayfası</p>
        <a
          href={`/r/${restaurant.slug}`}
          target="_blank"
          rel="noreferrer"
          className="mt-2 inline-block rounded-lg bg-[var(--bg-soft)] px-3 py-2 font-mono text-xs hover:underline"
        >
          /r/{restaurant.slug}
        </a>
      </div>
    </div>
  );
}
