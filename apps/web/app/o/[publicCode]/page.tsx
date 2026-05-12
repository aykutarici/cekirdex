import { apiFetch } from '@/lib/api';
import { OrderTracker } from './OrderTracker';

type OrderResponse = {
  order: {
    order_number: string;
    public_code: string;
    status: string;
    status_label: string;
    payment_status: string;
    total: number;
    restaurant?: { name: string } | null;
    table?: { name: string } | null;
    items: Array<{ id: number; name: string; quantity: number; subtotal: number; status: string }>;
    created_at?: string;
    updated_at?: string;
  };
};

export default async function OrderTrackPage({
  params,
}: {
  params: Promise<{ publicCode: string }>;
}) {
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
          <p className="mt-4 text-[var(--muted)]">
            Bu kod ile bir sipariş bulunamadı ya da süresi geçmiş olabilir.
          </p>
        </div>
      </main>
    );
  }

  return (
    <main className="py-20">
      <div className="container max-w-3xl">
        <p className="eyebrow">Sipariş takip</p>
        <h1 className="mt-3 text-4xl font-semibold tracking-[-0.04em]">
          {data.order.restaurant?.name ?? 'Sipariş'}
        </h1>
        <OrderTracker publicCode={publicCode} initialOrder={data.order} />
      </div>
    </main>
  );
}
