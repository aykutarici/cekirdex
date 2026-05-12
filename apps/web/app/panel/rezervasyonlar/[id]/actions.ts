'use server';

import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';
import { redirect } from 'next/navigation';
import { revalidatePath } from 'next/cache';

function revalidate(id: number) {
  revalidatePath('/panel/rezervasyonlar');
  revalidatePath(`/panel/rezervasyonlar/${id}`);
}

async function runAction(id: number, endpoint: string): Promise<void> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');
  try {
    await apiFetch(`/api/v1/panel/reservations/${id}/${endpoint}`, { method: 'POST', token });
  } catch {
    // Hata durumunda sayfayı yenile
  }
  revalidate(id);
}

export async function confirmReservationDetailAction(id: number, _fd: FormData): Promise<void> {
  await runAction(id, 'confirm');
}

export async function cancelReservationDetailAction(id: number, _fd: FormData): Promise<void> {
  await runAction(id, 'cancel');
}

export async function noShowReservationDetailAction(id: number, _fd: FormData): Promise<void> {
  await runAction(id, 'no-show');
}

export async function completeReservationDetailAction(id: number, _fd: FormData): Promise<void> {
  await runAction(id, 'complete');
}
