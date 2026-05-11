'use server';

import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';
import { redirect } from 'next/navigation';

export async function respondCallAction(callId: number): Promise<{ error?: string }> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');

  try {
    await apiFetch(`/api/v1/panel/calls/${callId}/respond`, {
      method: 'POST',
      token,
    });
    return {};
  } catch (err) {
    return { error: err instanceof Error ? err.message : 'Çağrı yanıtlanamadı.' };
  }
}

export async function closeCallAction(callId: number): Promise<{ error?: string }> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');

  try {
    await apiFetch(`/api/v1/panel/calls/${callId}/close`, {
      method: 'POST',
      token,
    });
    return {};
  } catch (err) {
    return { error: err instanceof Error ? err.message : 'Çağrı kapatılamadı.' };
  }
}
