'use client';

import { useActionState } from 'react';
import { updateSettingsAction, updatePasswordAction } from './actions';

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

function Field({ label, children }: { label: string; children: React.ReactNode }) {
  return (
    <div>
      <label className="mb-1 block text-sm font-medium">{label}</label>
      {children}
    </div>
  );
}

const inputCls =
  'w-full rounded-xl border border-[var(--border)] bg-white px-4 py-2.5 text-sm outline-none focus:border-[var(--primary)] focus:ring-2 focus:ring-[var(--primary)]/20';

export function SettingsForm({ restaurant }: { restaurant: Restaurant }) {
  const [error, formAction, pending] = useActionState(updateSettingsAction, null);

  return (
    <form action={formAction} className="space-y-6">
      {error && <div className="rounded-xl bg-red-50 p-3 text-sm text-red-700">{error}</div>}

      {/* Genel bilgiler */}
      <div className="card p-5">
        <h2 className="mb-4 font-semibold">Genel Bilgiler</h2>
        <div className="grid gap-4 sm:grid-cols-2">
          <Field label="Restoran adı *">
            <input type="text" name="name" required defaultValue={restaurant.name} className={inputCls} />
          </Field>
          <Field label="E-posta">
            <input type="email" name="email" defaultValue={restaurant.email ?? ''} className={inputCls} />
          </Field>
          <Field label="Telefon">
            <input type="tel" name="phone" defaultValue={restaurant.phone ?? ''} className={inputCls} />
          </Field>
          <Field label="Şehir">
            <input type="text" name="city" defaultValue={restaurant.city ?? ''} className={inputCls} />
          </Field>
          <div className="sm:col-span-2">
            <Field label="Adres">
              <input type="text" name="address" defaultValue={restaurant.address ?? ''} className={inputCls} />
            </Field>
          </div>
          <div className="sm:col-span-2">
            <Field label="Açıklama">
              <textarea
                name="description"
                rows={3}
                defaultValue={restaurant.description ?? ''}
                className={inputCls}
              />
            </Field>
          </div>
        </div>
        <div className="mt-3 flex items-center gap-2">
          <input type="checkbox" id="is_active" name="is_active" defaultChecked={restaurant.is_active} className="h-4 w-4 rounded" />
          <label htmlFor="is_active" className="text-sm font-medium">Restoran aktif</label>
        </div>
      </div>

      {/* Mali ayarlar */}
      <div className="card p-5">
        <h2 className="mb-4 font-semibold">Mali Ayarlar</h2>
        <div className="grid gap-4 sm:grid-cols-3">
          <Field label="Para birimi">
            <select name="currency" defaultValue={restaurant.currency} className={inputCls}>
              <option value="TRY">TRY (₺)</option>
              <option value="USD">USD ($)</option>
              <option value="EUR">EUR (€)</option>
            </select>
          </Field>
          <Field label="KDV oranı (%)">
            <input type="number" name="tax_rate" min="0" max="100" step="0.1" defaultValue={restaurant.tax_rate} className={inputCls} />
          </Field>
          <Field label="Servis ücreti (%)">
            <input type="number" name="service_charge_rate" min="0" max="100" step="0.1" defaultValue={restaurant.service_charge_rate} className={inputCls} />
          </Field>
        </div>
      </div>

      {/* Servis seçenekleri */}
      <div className="card p-5">
        <h2 className="mb-4 font-semibold">Servis Seçenekleri</h2>
        <div className="space-y-3">
          <div className="flex items-center gap-2">
            <input type="checkbox" id="accepts_takeaway" name="accepts_takeaway" defaultChecked={restaurant.accepts_takeaway} className="h-4 w-4 rounded" />
            <label htmlFor="accepts_takeaway" className="text-sm font-medium">Paket servis kabul et</label>
          </div>
          <div className="flex items-center gap-2">
            <input type="checkbox" id="accepts_delivery" name="accepts_delivery" defaultChecked={restaurant.accepts_delivery} className="h-4 w-4 rounded" />
            <label htmlFor="accepts_delivery" className="text-sm font-medium">Ev teslimatı kabul et</label>
          </div>
          <div className="grid gap-4 sm:grid-cols-2">
            <Field label="Teslimat ücreti (₺)">
              <input type="number" name="delivery_fee" min="0" step="0.01" defaultValue={restaurant.delivery_fee ?? ''} className={inputCls} placeholder="—" />
            </Field>
            <Field label="Min. sipariş tutarı (₺)">
              <input type="number" name="delivery_min_amount" min="0" step="0.01" defaultValue={restaurant.delivery_min_amount ?? ''} className={inputCls} placeholder="—" />
            </Field>
          </div>
          <div className="flex items-center gap-2">
            <input type="checkbox" id="accepts_reservations" name="accepts_reservations" defaultChecked={restaurant.accepts_reservations} className="h-4 w-4 rounded" />
            <label htmlFor="accepts_reservations" className="text-sm font-medium">Rezervasyon kabul et</label>
          </div>
        </div>
      </div>

      <button type="submit" disabled={pending} className="btn btn-primary disabled:opacity-50">
        {pending ? 'Kaydediliyor…' : 'Değişiklikleri Kaydet'}
      </button>
    </form>
  );
}

export function PasswordForm() {
  const [error, formAction, isPending] = useActionState(updatePasswordAction, null);

  return (
    <form action={formAction} className="card p-5 space-y-4 max-w-md">
      <h2 className="font-semibold">Şifre Değiştir</h2>
      {error && <div className="rounded-xl bg-red-50 p-3 text-sm text-red-700">{error}</div>}
      <Field label="Mevcut şifre">
        <input type="password" name="current_password" required className={inputCls} />
      </Field>
      <Field label="Yeni şifre">
        <input type="password" name="password" required minLength={8} className={inputCls} />
      </Field>
      <Field label="Yeni şifre tekrar">
        <input type="password" name="password_confirmation" required minLength={8} className={inputCls} />
      </Field>
      <button type="submit" disabled={isPending} className="btn btn-ghost disabled:opacity-50">
        {isPending ? 'Değiştiriliyor…' : 'Şifreyi Değiştir'}
      </button>
    </form>
  );
}
