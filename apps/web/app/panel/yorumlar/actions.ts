'use server';

import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';
import { redirect } from 'next/navigation';
import { revalidatePath } from 'next/cache';

export async function toggleReviewVisibilityAction(reviewId: number, _fd: FormData): Promise<void> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');
  await apiFetch(`/api/v1/panel/reviews/${reviewId}/toggle-visibility`, { method: 'POST', token });
  revalidatePath('/panel/yorumlar');
}

export async function deleteReviewAction(reviewId: number, _fd: FormData): Promise<void> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');
  await apiFetch(`/api/v1/panel/reviews/${reviewId}`, { method: 'DELETE', token });
  revalidatePath('/panel/yorumlar');
}
