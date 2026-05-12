import { NextRequest, NextResponse } from 'next/server';
import { apiUrl } from '@/lib/api';

type Params = { qrToken: string; productId: string };

export async function GET(
  _req: NextRequest,
  { params }: { params: Promise<Params> },
) {
  const { qrToken, productId } = await params;
  try {
    const res = await fetch(
      apiUrl(`/api/v1/tables/${qrToken}/products/${productId}/reviews`),
      { headers: { Accept: 'application/json' }, cache: 'no-store' },
    );
    const data = await res.json();
    return NextResponse.json(data, { status: res.status });
  } catch {
    return NextResponse.json({ data: [] });
  }
}
