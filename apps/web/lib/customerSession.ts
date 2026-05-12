import { cookies } from 'next/headers';

export const customerAuthCookieName = 'cekirdex_customer_token';

export async function getCustomerToken(): Promise<string | undefined> {
  return (await cookies()).get(customerAuthCookieName)?.value;
}

export async function setCustomerToken(token: string): Promise<void> {
  (await cookies()).set(customerAuthCookieName, token, {
    httpOnly: true,
    sameSite: 'lax',
    secure: process.env.NODE_ENV === 'production',
    path: '/',
    maxAge: 60 * 60 * 24 * 30,
  });
}

export async function clearCustomerToken(): Promise<void> {
  (await cookies()).delete(customerAuthCookieName);
}
