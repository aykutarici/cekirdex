'use server';

import { redirect } from 'next/navigation';
import { apiFetch } from '@/lib/api';

type ReservationResponse = {
  reservation: {
    public_code: string;
  };
};

export async function createReservationAction(
  _prevState: string | null,
  formData: FormData,
): Promise<string | null> {
  const slug = String(formData.get('slug') ?? '');

  let response: ReservationResponse;
  try {
    response = await apiFetch<ReservationResponse>(`/api/v1/restaurants/${slug}/reservations`, {
      method: 'POST',
      body: JSON.stringify({
        contact_name: String(formData.get('contact_name') ?? ''),
        contact_phone: String(formData.get('contact_phone') ?? ''),
        contact_email: String(formData.get('contact_email') ?? ''),
        reserved_for: String(formData.get('reserved_for') ?? ''),
        party_size: Number(formData.get('party_size') ?? 2),
        note: String(formData.get('note') ?? ''),
      }),
    });
  } catch (err) {
    return err instanceof Error ? err.message : 'Rezervasyon oluşturulurken bir hata oluştu.';
  }

  redirect(`/rsv/${response.reservation.public_code}`);
}
