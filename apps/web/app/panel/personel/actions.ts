'use server';

import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';
import { redirect } from 'next/navigation';
import { revalidatePath } from 'next/cache';

export async function createStaffAction(
  _prevState: string | null,
  formData: FormData,
): Promise<string | null> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');

  try {
    await apiFetch('/api/v1/panel/staff', {
      method: 'POST',
      token,
      body: JSON.stringify({
        name: String(formData.get('name') ?? ''),
        email: String(formData.get('email') ?? ''),
        phone: formData.get('phone') ? String(formData.get('phone')) : undefined,
        role: String(formData.get('role') ?? 'waiter'),
        password: String(formData.get('password') ?? ''),
      }),
    });
    revalidatePath('/panel/personel');
    redirect('/panel/personel');
  } catch (err) {
    return err instanceof Error ? err.message : 'Personel eklenemedi.';
  }
}

export async function deleteStaffAction(staffId: number): Promise<{ error?: string }> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');

  try {
    await apiFetch(`/api/v1/panel/staff/${staffId}`, { method: 'DELETE', token });
    revalidatePath('/panel/personel');
    return {};
  } catch (err) {
    return { error: err instanceof Error ? err.message : 'Personel silinemedi.' };
  }
}

export async function updateStaffAction(
  staffId: number,
  _prevState: string | null,
  formData: FormData,
): Promise<string | null> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');

  try {
    await apiFetch(`/api/v1/panel/staff/${staffId}`, {
      method: 'PUT',
      token,
      body: JSON.stringify({
        name: String(formData.get('name') ?? ''),
        role: String(formData.get('role') ?? 'waiter'),
        is_active: formData.get('is_active') === 'true',
      }),
    });
    revalidatePath('/panel/personel');
    return null;
  } catch (err) {
    return err instanceof Error ? err.message : 'Personel güncellenemedi.';
  }
}
