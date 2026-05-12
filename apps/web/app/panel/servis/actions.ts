'use server';

import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';
import { redirect } from 'next/navigation';

export async function serveOrderAction(orderId: number): Promise<{ error?: string }> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');

  try {
    await apiFetch(`/api/v1/panel/service/${orderId}/serve`, {
      method: 'POST',
      token,
    });
    return {};
  } catch (err) {
    return { error: err instanceof Error ? err.message : 'Servis edilemedi.' };
  }
}

export async function confirmOrderAction(orderId: number): Promise<{ error?: string }> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');

  try {
    await apiFetch(`/api/v1/panel/orders/${orderId}/confirm`, {
      method: 'POST',
      token,
    });
    return {};
  } catch (err) {
    return { error: err instanceof Error ? err.message : 'Sipariş onaylanamadı.' };
  }
}
