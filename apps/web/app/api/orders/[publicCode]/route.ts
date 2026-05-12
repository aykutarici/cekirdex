import { NextRequest, NextResponse } from 'next/server';
import { apiUrl } from '@/lib/api';

export async function GET(
  _req: NextRequest,
  { params }: { params: Promise<{ publicCode: string }> },
) {
  const { publicCode } = await params;
  try {
    const res = await fetch(apiUrl(`/api/v1/orders/${publicCode}/feed`), {
      headers: { Accept: 'application/json' },
      cache: 'no-store',
    });

    if (!res.ok) {
      // Fallback: temel sipariş endpoint'ini dene
      const fallback = await fetch(apiUrl(`/api/v1/orders/${publicCode}`), {
        headers: { Accept: 'application/json' },
        cache: 'no-store',
      });
      const data = await fallback.json();
      return NextResponse.json(data, { status: fallback.status });
    }

    const data = await res.json();
    return NextResponse.json(data, { status: res.status });
  } catch {
    return NextResponse.json({ ok: false }, { status: 500 });
  }
}
