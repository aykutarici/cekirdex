import { apiFetch } from '@/lib/api';

type OrderResponse = {
  order: {
    order_number: string;
    public_code: string;
    status_label: string;
    payment_status: string;
    total: number;
    restaurant?: { name: string } | null;
    table?: { name: string } | null;
    items: Array<{ id: number; name: string; quantity: number; subtotal: number; status: string }>;
  };
};

export default async function OrderTrackPage({ params }: { params: Promise<{ publicCode: string }> }) {
  const { publicCode } = await params;

  let data: OrderResponse | null = null;
  try {
    data = await apiFetch<OrderResponse>(`/api/v1/orders/${publicCode}`);
  } catch {
    return (
      <main className="py-20">
        <div className="container max-w-3xl">
          <p className="eyebrow">Sipariş takip</p>
          <h1 className="mt-3 text-4xl font-semibold tracking-[-0.04em]">Sipariş bulunamadı</h1>
          <p className="mt-4 text-[var(--muted)]">Bu kod ile bir sipariş bulunamadı ya da süresi geçmiş olabilir.</p>
        </div>
      </main>
    );
  }

  return (
    <main className="py-20">
      <div className="container max-w-3xl">
        <p className="eyebrow">Sipariş takip</p>
        <h1 className="mt-3 text-4xl font-semibold tracking-[-0.04em]">{data.order.restaurant?.name ?? 'Sipariş'}</h1>
        <div className="card mt-8 p-6">
          <div className="grid gap-4 md:grid-cols-3">
            <div>
              <p className="text-sm text-[var(--muted)]">Kod</p>
              <strong>{data.order.public_code}</strong>
            </div>
            <div>
              <p className="text-sm text-[var(--muted)]">Durum</p>
              <strong>{data.order.status_label}</strong>
            </div>
            <div>
              <p className="text-sm text-[var(--muted)]">Toplam</p>
              <strong>{data.order.total.toLocaleString('tr-TR')} TL</strong>
            </div>
          </div>
          <div className="mt-6 divide-y divide-[var(--border)]">
            {data.order.items.map((item) => (
              <div key={item.id} className="flex justify-between py-3">
                <span>{item.quantity}x {item.name}</span>
                <strong>{item.subtotal.toLocaleString('tr-TR')} TL</strong>
              </div>
            ))}
          </div>
        </div>
      </div>
    </main>
  );
}
