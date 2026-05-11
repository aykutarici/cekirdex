'use server';

import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';
import { redirect } from 'next/navigation';
import { revalidatePath } from 'next/cache';

export async function confirmTakeawayAction(orderId: number, _fd: FormData): Promise<void> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');
  await apiFetch(`/api/v1/panel/takeaway/${orderId}/confirm`, { method: 'POST', token });
  revalidatePath('/panel/paket');
}

export async function advanceTakeawayAction(orderId: number, _fd: FormData): Promise<void> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');
  await apiFetch(`/api/v1/panel/takeaway/${orderId}/advance`, { method: 'POST', token });
  revalidatePath('/panel/paket');
}

export async function cancelTakeawayAction(orderId: number, _fd: FormData): Promise<void> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');
  await apiFetch(`/api/v1/panel/takeaway/${orderId}/cancel`, { method: 'POST', token });
  revalidatePath('/panel/paket');
}
