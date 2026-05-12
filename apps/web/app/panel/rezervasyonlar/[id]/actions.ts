'use server';

import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';
import { redirect } from 'next/navigation';
import { revalidatePath } from 'next/cache';

function revalidate(id: number) {
  revalidatePath('/panel/rezervasyonlar');
  revalidatePath(`/panel/rezervasyonlar/${id}`);
}

export async function confirmReservationDetailAction(id: number, _fd: FormData): Promise<void> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');
  await apiFetch(`/api/v1/panel/reservations/${id}/confirm`, { method: 'POST', token });
  revalidate(id);
}

export async function cancelReservationDetailAction(id: number, _fd: FormData): Promise<void> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');
  await apiFetch(`/api/v1/panel/reservations/${id}/cancel`, { method: 'POST', token });
  revalidate(id);
}

export async function noShowReservationDetailAction(id: number, _fd: FormData): Promise<void> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');
  await apiFetch(`/api/v1/panel/reservations/${id}/no-show`, { method: 'POST', token });
  revalidate(id);
}

export async function completeReservationDetailAction(id: number, _fd: FormData): Promise<void> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');
  await apiFetch(`/api/v1/panel/reservations/${id}/complete`, { method: 'POST', token });
  revalidate(id);
}
