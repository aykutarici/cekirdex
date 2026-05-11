'use server';

import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';
import { redirect } from 'next/navigation';
import { revalidatePath } from 'next/cache';

export async function regenerateQrAction(tableId: number, _formData: FormData): Promise<void> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');
  await apiFetch(`/api/v1/panel/tables/${tableId}/regenerate-qr`, { method: 'POST', token });
  revalidatePath('/panel/masalar');
}

export async function createTableAction(
  _prevState: string | null,
  formData: FormData,
): Promise<string | null> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');
  try {
    await apiFetch('/api/v1/panel/tables', {
      method: 'POST',
      token,
      body: JSON.stringify({
        name: String(formData.get('name') ?? ''),
        capacity: Number(formData.get('capacity') ?? 4),
      }),
    });
    revalidatePath('/panel/masalar');
    redirect('/panel/masalar');
  } catch (err) {
    return err instanceof Error ? err.message : 'Masa oluşturulamadı.';
  }
}

export async function deleteTableAction(tableId: number, _formData: FormData): Promise<void> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');
  await apiFetch(`/api/v1/panel/tables/${tableId}`, { method: 'DELETE', token });
  revalidatePath('/panel/masalar');
}
