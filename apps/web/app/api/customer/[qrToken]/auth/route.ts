import { NextRequest, NextResponse } from 'next/server';
import { apiUrl } from '@/lib/api';
import { getCustomerToken } from '@/lib/customerSession';

export async function GET(
  _req: NextRequest,
  { params }: { params: Promise<{ qrToken: string }> },
) {
  const { qrToken } = await params;
  const token = await getCustomerToken();
  if (!token) return NextResponse.json({ authenticated: false }, { status: 401 });

  try {
    const res = await fetch(apiUrl(`/api/v1/tables/${qrToken}/auth/me`), {
      headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' },
      cache: 'no-store',
    });
    const data = await res.json();
    return NextResponse.json(data, { status: res.status });
  } catch {
    return NextResponse.json({ authenticated: false }, { status: 500 });
  }
}
