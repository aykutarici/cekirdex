'use server';

import { redirect } from 'next/navigation';
import { apiFetch } from '@/lib/api';
import { setCustomerToken, getCustomerToken } from '@/lib/customerSession';

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

export async function customerRegisterAction(
  qrToken: string,
  _prev: string | null,
  formData: FormData,
): Promise<string | null> {
  const email = String(formData.get('email') ?? '').trim();
  try {
    const data = await apiFetch<{ token: string }>(`/api/v1/tables/${qrToken}/auth/register`, {
      method: 'POST',
      body: JSON.stringify({
        name: String(formData.get('name') ?? ''),
        phone: String(formData.get('phone') ?? ''),
        ...(email ? { email } : {}),
      }),
    });
    await setCustomerToken(data.token);
    return null;
  } catch (err) {
    return err instanceof Error ? err.message : 'Kayıt başarısız.';
  }
}

export async function customerLoginAction(
  qrToken: string,
  _prev: string | null,
  formData: FormData,
): Promise<string | null> {
  try {
    const data = await apiFetch<{ token: string }>(`/api/v1/tables/${qrToken}/auth/login`, {
      method: 'POST',
      body: JSON.stringify({
        phone: String(formData.get('phone') ?? ''),
      }),
    });
    await setCustomerToken(data.token);
    return null;
  } catch (err) {
    return err instanceof Error ? err.message : 'Giriş başarısız.';
  }
}

export async function addReviewAction(
  qrToken: string,
  productId: number,
  _prev: string | null,
  formData: FormData,
): Promise<string | null> {
  const token = await getCustomerToken();
  if (!token) return 'Yorum yapmak için giriş yapın.';
  try {
    await apiFetch(`/api/v1/tables/${qrToken}/products/${productId}/reviews`, {
      method: 'POST',
      token,
      body: JSON.stringify({
        rating: Number(formData.get('rating') ?? 5),
        comment: String(formData.get('comment') ?? ''),
      }),
    });
    return null;
  } catch (err) {
    return err instanceof Error ? err.message : 'Yorum eklenemedi.';
  }
}
