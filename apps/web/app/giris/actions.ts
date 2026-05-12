'use server';

import { redirect } from 'next/navigation';
import { apiFetch } from '@/lib/api';
import { clearAuthToken, setAuthToken } from '@/lib/session';

export async function logoutAction(): Promise<void> {
  const { getAuthToken } = await import('@/lib/session');
  const token = await getAuthToken();
  if (token) {
    try {
      await apiFetch('/api/v1/auth/logout', { method: 'POST', token });
    } catch {
      // Backend token iptal edemese bile çıkış yap
    }
  }
  await clearAuthToken();
  redirect('/giris');
}

type LoginResponse = {
  access_token: string;
  actor: {
    account_type: 'staff' | 'admin' | 'guest';
  };
};

export async function staffLoginAction(
  _prevState: string | null,
  formData: FormData,
): Promise<string | null> {
  let response: LoginResponse;
  try {
    response = await apiFetch<LoginResponse>('/api/v1/auth/staff/login', {
      method: 'POST',
      body: JSON.stringify({
        email: String(formData.get('email') ?? ''),
        password: String(formData.get('password') ?? ''),
      }),
    });
  } catch (err) {
    return err instanceof Error ? err.message : 'Giriş yapılırken bir hata oluştu.';
  }

  await setAuthToken(response.access_token);

  if (response.actor.account_type === 'admin') {
    redirect('/admin');
  }

  redirect('/panel');
}

export async function guestLoginAction(
  _prevState: string | null,
  formData: FormData,
): Promise<string | null> {
  let response: LoginResponse;
  try {
    response = await apiFetch<LoginResponse>('/api/v1/auth/guest/login', {
      method: 'POST',
      body: JSON.stringify({
        login: String(formData.get('login') ?? ''),
        password: String(formData.get('password') ?? ''),
      }),
    });
  } catch (err) {
    return err instanceof Error ? err.message : 'Giriş yapılırken bir hata oluştu.';
  }

  await setAuthToken(response.access_token);
  redirect('/');
}
