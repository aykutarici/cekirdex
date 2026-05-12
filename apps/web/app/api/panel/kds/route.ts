import { NextResponse } from 'next/server';
import { getAuthToken } from '@/lib/session';
import { apiUrl } from '@/lib/api';

export async function GET() {
  const token = await getAuthToken();
  if (!token) return NextResponse.json({ ok: false, message: 'Oturum yok' }, { status: 401 });

  try {
    const res = await fetch(apiUrl('/api/v1/panel/kds/feed'), {
      headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' },
      cache: 'no-store',
    });
    const json = await res.json();
    // Normalize: backend returns { orders: [...] }, frontend expects { data: [...] }
    return NextResponse.json({ data: json.orders ?? json.data ?? [] }, { status: res.status });
  } catch {
    return NextResponse.json({ data: [] }, { status: 500 });
  }
}
