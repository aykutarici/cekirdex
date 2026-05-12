import { NextRequest, NextResponse } from 'next/server';
import { apiUrl } from '@/lib/api';

export async function GET(
  _req: NextRequest,
  { params }: { params: Promise<{ qrToken: string }> },
) {
  const { qrToken } = await params;
  try {
    const res = await fetch(apiUrl(`/api/v1/tables/${qrToken}/bill`), {
      headers: { Accept: 'application/json' },
      cache: 'no-store',
    });
    const data = await res.json();
    return NextResponse.json(data, { status: res.status });
  } catch {
    return NextResponse.json({ has_open_orders: false }, { status: 500 });
  }
}
