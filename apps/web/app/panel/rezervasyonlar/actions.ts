'use server';

import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';
import { redirect } from 'next/navigation';
import { revalidatePath } from 'next/cache';

export async function confirmReservationAction(id: number, _fd: FormData): Promise<void> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');
  await apiFetch(`/api/v1/panel/reservations/${id}/confirm`, { method: 'POST', token });
  revalidatePath('/panel/rezervasyonlar');
}

export async function cancelReservationAction(id: number, _fd: FormData): Promise<void> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');
  await apiFetch(`/api/v1/panel/reservations/${id}/cancel`, { method: 'POST', token });
  revalidatePath('/panel/rezervasyonlar');
}

export async function noShowReservationAction(id: number, _fd: FormData): Promise<void> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');
  await apiFetch(`/api/v1/panel/reservations/${id}/no-show`, { method: 'POST', token });
  revalidatePath('/panel/rezervasyonlar');
}

export async function completeReservationAction(id: number, _fd: FormData): Promise<void> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');
  await apiFetch(`/api/v1/panel/reservations/${id}/complete`, { method: 'POST', token });
  revalidatePath('/panel/rezervasyonlar');
}

// Eski ReservationActions.tsx için (useTransition ile kullanılır)
export async function confirmReservationPanelAction(id: number): Promise<{ error?: string }> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');
  try {
    await apiFetch(`/api/v1/panel/reservations/${id}/confirm`, { method: 'POST', token });
    revalidatePath('/panel/rezervasyonlar');
    return {};
  } catch (err) {
    return { error: err instanceof Error ? err.message : 'İşlem başarısız.' };
  }
}

export async function cancelReservationPanelAction(id: number): Promise<{ error?: string }> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');
  try {
    await apiFetch(`/api/v1/panel/reservations/${id}/cancel`, { method: 'POST', token });
    revalidatePath('/panel/rezervasyonlar');
    return {};
  } catch (err) {
    return { error: err instanceof Error ? err.message : 'İşlem başarısız.' };
  }
}

export async function noShowReservationPanelAction(id: number): Promise<{ error?: string }> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');
  try {
    await apiFetch(`/api/v1/panel/reservations/${id}/no-show`, { method: 'POST', token });
    revalidatePath('/panel/rezervasyonlar');
    return {};
  } catch (err) {
    return { error: err instanceof Error ? err.message : 'İşlem başarısız.' };
  }
}

export async function completeReservationPanelAction(id: number): Promise<{ error?: string }> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');
  try {
    await apiFetch(`/api/v1/panel/reservations/${id}/complete`, { method: 'POST', token });
    revalidatePath('/panel/rezervasyonlar');
    return {};
  } catch (err) {
    return { error: err instanceof Error ? err.message : 'İşlem başarısız.' };
  }
}
