import { redirect } from 'next/navigation';
import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';
import { SettingsForm, PasswordForm } from './SettingsForm';

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

      <div className="mt-6 space-y-6">
        <SettingsForm restaurant={restaurant} />

        {/* Public menü linki */}
        <div className="card p-5">
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

        <PasswordForm />
      </div>
    </div>
  );
}
