'use server';

import { redirect } from 'next/navigation';
import { apiFetch } from '@/lib/api';

type OrderResponse = {
  order: {
    public_code: string;
  };
};

export async function createOrderAction(
  _prevState: string | null,
  formData: FormData,
): Promise<string | null> {
  const qrToken = String(formData.get('qr_token') ?? '');
  const productId = Number(formData.get('product_id'));
  const quantity = Number(formData.get('quantity') ?? 1);

  let response: OrderResponse;
  try {
    response = await apiFetch<OrderResponse>(`/api/v1/tables/${qrToken}/orders`, {
      method: 'POST',
      body: JSON.stringify({
        items: [{ product_id: productId, quantity }],
      }),
    });
  } catch (err) {
    return err instanceof Error ? err.message : 'Sipariş oluşturulurken bir hata oluştu.';
  }

  redirect(`/o/${response.order.public_code}`);
}

export async function callWaiterAction(
  _prevState: string | null,
  formData: FormData,
): Promise<string | null> {
  const qrToken = String(formData.get('qr_token') ?? '');

  try {
    await apiFetch(`/api/v1/tables/${qrToken}/calls`, {
      method: 'POST',
      body: JSON.stringify({
        call_type: 'waiter',
        message: 'QR menüden çağrı oluşturuldu.',
      }),
    });
  } catch (err) {
    return err instanceof Error ? err.message : 'Çağrı oluşturulurken bir hata oluştu.';
  }

  redirect(`/m/${qrToken}?called=1`);
}
