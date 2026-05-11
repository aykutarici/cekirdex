'use server';

import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';
import { redirect } from 'next/navigation';
import { revalidatePath } from 'next/cache';

export async function recordPaymentAction(
  tableId: number,
  _prevState: string | null,
  formData: FormData,
): Promise<string | null> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');

  try {
    await apiFetch(`/api/v1/panel/bills/${tableId}/payments`, {
      method: 'POST',
      token,
      body: JSON.stringify({
        amount: Number(formData.get('amount')),
        method: String(formData.get('method') ?? 'cash'),
        note: formData.get('note') ? String(formData.get('note')) : undefined,
      }),
    });
    revalidatePath(`/panel/hesaplar/${tableId}`);
    return null;
  } catch (err) {
    return err instanceof Error ? err.message : 'Ödeme kaydedilemedi.';
  }
}

export async function closeBillAction(tableId: number, _formData: FormData): Promise<void> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');

  await apiFetch(`/api/v1/panel/bills/${tableId}/close`, {
    method: 'POST',
    token,
  });
  redirect('/panel/hesaplar');
}

export async function cancelPaymentAction(
  tableId: number,
  paymentId: number,
  _formData: FormData,
): Promise<void> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');

  await apiFetch(`/api/v1/panel/bills/${tableId}/payments/${paymentId}`, {
    method: 'DELETE',
    token,
  });
  revalidatePath(`/panel/hesaplar/${tableId}`);
}
