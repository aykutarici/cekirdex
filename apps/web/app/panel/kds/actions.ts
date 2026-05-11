'use server';

import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';
import { redirect } from 'next/navigation';

export async function advanceOrderAction(orderId: number): Promise<{ error?: string }> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');

  try {
    await apiFetch(`/api/v1/panel/kds/${orderId}/advance`, {
      method: 'POST',
      token,
    });
    return {};
  } catch (err) {
    return { error: err instanceof Error ? err.message : 'Sipariş ilerletilemedi.' };
  }
}

export async function cancelOrderAction(orderId: number): Promise<{ error?: string }> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');

  try {
    await apiFetch(`/api/v1/panel/kds/${orderId}/cancel`, {
      method: 'POST',
      token,
    });
    return {};
  } catch (err) {
    return { error: err instanceof Error ? err.message : 'Sipariş iptal edilemedi.' };
  }
}
