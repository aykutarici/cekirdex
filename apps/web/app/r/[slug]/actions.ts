'use server';

import { redirect } from 'next/navigation';
import { apiFetch } from '@/lib/api';

type ReservationResponse = {
  reservation: {
    public_code: string;
  };
};

type TakeawayOrderResponse = {
  order: {
    public_code: string;
  };
};

export async function storeTakeawayOrderAction(
  _prevState: string | null,
  formData: FormData,
): Promise<string | null> {
  const slug = String(formData.get('slug') ?? '');
  const itemsRaw = String(formData.get('items') ?? '[]');

  let items: Array<{ product_id: number; quantity: number }> = [];
  try {
    items = JSON.parse(itemsRaw);
  } catch {
    return 'Sepet verisi geçersiz.';
  }

  let response: TakeawayOrderResponse;
  try {
    response = await apiFetch<TakeawayOrderResponse>(`/api/v1/restaurants/${slug}/order`, {
      method: 'POST',
      body: JSON.stringify({
        order_type: String(formData.get('order_type') ?? 'takeaway'),
        contact_name: String(formData.get('contact_name') ?? ''),
        contact_phone: String(formData.get('contact_phone') ?? ''),
        delivery_address: String(formData.get('delivery_address') ?? '') || undefined,
        note: String(formData.get('note') ?? '') || undefined,
        items,
      }),
    });
  } catch (err) {
    return err instanceof Error ? err.message : 'Sipariş oluşturulurken bir hata oluştu.';
  }

  redirect(`/o/${response.order.public_code}`);
}

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
