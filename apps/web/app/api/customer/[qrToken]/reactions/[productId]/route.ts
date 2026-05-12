import { NextRequest, NextResponse } from 'next/server';
import { apiUrl } from '@/lib/api';
import { getCustomerToken } from '@/lib/customerSession';

type Params = { qrToken: string; productId: string };

export async function GET(
  _req: NextRequest,
  { params }: { params: Promise<Params> },
) {
  const { qrToken, productId } = await params;
  try {
    const res = await fetch(
      apiUrl(`/api/v1/tables/${qrToken}/products/${productId}/reactions`),
      { headers: { Accept: 'application/json' }, cache: 'no-store' },
    );
    const data = await res.json();
    return NextResponse.json(data, { status: res.status });
  } catch {
    return NextResponse.json({ likes: 0, favorites: 0, user_liked: false, user_favorited: false });
  }
}

export async function POST(
  req: NextRequest,
  { params }: { params: Promise<Params> },
) {
  const { qrToken, productId } = await params;
  const token = await getCustomerToken();
  if (!token) return NextResponse.json({ ok: false, message: 'Giriş gerekli' }, { status: 401 });

  const body = await req.json().catch(() => ({}));
  const reactionType: string = body.type ?? 'like';

  const endpoint =
    reactionType === 'favorite'
      ? `/api/v1/tables/${qrToken}/products/${productId}/favorite`
      : `/api/v1/tables/${qrToken}/products/${productId}/like`;

  try {
    const res = await fetch(apiUrl(endpoint), {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
    });
    const data = await res.json();
    return NextResponse.json(data, { status: res.status });
  } catch {
    return NextResponse.json({ ok: false }, { status: 500 });
  }
}
