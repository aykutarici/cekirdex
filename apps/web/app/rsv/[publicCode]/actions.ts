'use server';

import { redirect } from 'next/navigation';
import { apiFetch } from '@/lib/api';

export async function cancelReservationAction(
  _prevState: string | null,
  formData: FormData,
): Promise<string | null> {
  const publicCode = String(formData.get('public_code') ?? '');

  try {
    await apiFetch(`/api/v1/reservations/${publicCode}/cancel`, {
      method: 'POST',
      body: JSON.stringify({}),
    });
  } catch (err) {
    return err instanceof Error ? err.message : 'İptal işlemi sırasında bir hata oluştu.';
  }

  redirect(`/rsv/${publicCode}`);
}
