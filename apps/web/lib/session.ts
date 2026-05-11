import { cookies } from 'next/headers';

export const authCookieName = 'cekirdex_token';

export async function getAuthToken(): Promise<string | undefined> {
  return (await cookies()).get(authCookieName)?.value;
}

export async function setAuthToken(token: string): Promise<void> {
  (await cookies()).set(authCookieName, token, {
    httpOnly: true,
    sameSite: 'lax',
    secure: process.env.NODE_ENV === 'production',
    path: '/',
    maxAge: 60 * 60 * 24 * 30,
  });
}

export async function clearAuthToken(): Promise<void> {
  (await cookies()).delete(authCookieName);
}
