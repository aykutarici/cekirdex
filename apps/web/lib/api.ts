const publicUrl = process.env.NEXT_PUBLIC_API_URL ?? 'http://localhost:8080';
const internalUrl = process.env.API_INTERNAL_URL ?? publicUrl;

export function apiUrl(path: string, serverSide = true): string {
  const base = serverSide ? internalUrl : publicUrl;
  return `${base}${path.startsWith('/') ? path : `/${path}`}`;
}

export async function apiFetch<T>(path: string, init?: RequestInit & { token?: string }): Promise<T> {
  const headers = new Headers(init?.headers);
  headers.set('Accept', 'application/json');

  if (!(init?.body instanceof FormData)) {
    headers.set('Content-Type', 'application/json');
  }

  if (init?.token) {
    headers.set('Authorization', `Bearer ${init.token}`);
  }

  const response = await fetch(apiUrl(path), {
    ...init,
    headers,
    cache: 'no-store',
  });

  if (!response.ok) {
    let message = `API isteği başarısız: ${response.status}`;
    try {
      const body = await response.json();
      message = body.message ?? message;
    } catch {
      // Response body JSON değilse status mesajı yeterli.
    }

    throw new Error(message);
  }

  return response.json() as Promise<T>;
}
