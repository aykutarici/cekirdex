'use server';

import { redirect } from 'next/navigation';
import { apiFetch } from '@/lib/api';
import { setAuthToken } from '@/lib/session';

type RegisterResponse = {
  access_token: string;
};

export async function registerAction(
  _prevState: string | null,
  formData: FormData,
): Promise<string | null> {
  let response: RegisterResponse;
  try {
    response = await apiFetch<RegisterResponse>('/api/v1/auth/staff/register', {
      method: 'POST',
      body: JSON.stringify({
        restaurant_name: String(formData.get('restaurant_name') ?? ''),
        city: String(formData.get('city') ?? ''),
        phone: String(formData.get('phone') ?? ''),
        name: String(formData.get('name') ?? ''),
        email: String(formData.get('email') ?? ''),
        password: String(formData.get('password') ?? ''),
      }),
    });
  } catch (err) {
    return err instanceof Error ? err.message : 'Kayıt sırasında bir hata oluştu.';
  }

  await setAuthToken(response.access_token);
  redirect('/panel');
}
