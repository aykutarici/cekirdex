'use server';

import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';
import { redirect } from 'next/navigation';
import { revalidatePath } from 'next/cache';

export async function updateSettingsAction(
  _prevState: string | null,
  formData: FormData,
): Promise<string | null> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');

  try {
    await apiFetch('/api/v1/panel/settings', {
      method: 'PUT',
      token,
      body: JSON.stringify({
        name: String(formData.get('name') ?? ''),
        description: formData.get('description') ? String(formData.get('description')) : null,
        address: formData.get('address') ? String(formData.get('address')) : null,
        city: formData.get('city') ? String(formData.get('city')) : null,
        phone: formData.get('phone') ? String(formData.get('phone')) : null,
        email: formData.get('email') ? String(formData.get('email')) : null,
        currency: String(formData.get('currency') ?? 'TRY'),
        tax_rate: Number(formData.get('tax_rate') ?? 0),
        service_charge_rate: Number(formData.get('service_charge_rate') ?? 0),
        accepts_takeaway: formData.get('accepts_takeaway') === 'on',
        accepts_delivery: formData.get('accepts_delivery') === 'on',
        accepts_reservations: formData.get('accepts_reservations') === 'on',
        delivery_fee: formData.get('delivery_fee') ? Number(formData.get('delivery_fee')) : null,
        delivery_min_amount: formData.get('delivery_min_amount') ? Number(formData.get('delivery_min_amount')) : null,
      }),
    });
    revalidatePath('/panel/ayarlar');
    return null;
  } catch (err) {
    return err instanceof Error ? err.message : 'Ayarlar kaydedilemedi.';
  }
}

export async function updatePasswordAction(
  _prevState: string | null,
  formData: FormData,
): Promise<string | null> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');

  try {
    await apiFetch('/api/v1/panel/settings/password', {
      method: 'PUT',
      token,
      body: JSON.stringify({
        current_password: String(formData.get('current_password') ?? ''),
        password: String(formData.get('password') ?? ''),
        password_confirmation: String(formData.get('password_confirmation') ?? ''),
      }),
    });
    return null;
  } catch (err) {
    return err instanceof Error ? err.message : 'Şifre güncellenemedi.';
  }
}
