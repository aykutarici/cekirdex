'use server';

import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';
import { redirect } from 'next/navigation';
import { revalidatePath } from 'next/cache';

export async function updateOrderStatusAction(
  orderId: number,
  status: string,
  _fd: FormData,
): Promise<void> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');
  await apiFetch(`/api/v1/panel/orders/${orderId}/status`, {
    method: 'POST',
    token,
    body: JSON.stringify({ status }),
  });
  revalidatePath('/panel/siparisler');
}
