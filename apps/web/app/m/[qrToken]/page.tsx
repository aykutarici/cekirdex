import { apiFetch } from '@/lib/api';
import { CallWaiterForm, OrderProductForm } from './QrMenuForms';

type MenuResponse = {
  restaurant: { name: string };
  table: { name: string };
  categories: Array<{ id: number; name: string; products: Array<{ id: number; name: string; effective_price: number }> }>;
};

export default async function QrMenuPage({ params, searchParams }: { params: Promise<{ qrToken: string }>; searchParams: Promise<{ called?: string }> }) {
  const { qrToken } = await params;
  const { called } = await searchParams;
  let data: MenuResponse | null = null;

  try {
    data = await apiFetch<MenuResponse>(`/api/v1/tables/${qrToken}/menu`);
  } catch {
    data = null;
  }

  return (
    <main className="py-12">
      <div className="container">
        <p className="eyebrow">QR Menü</p>
        <h1 className="mt-3 text-4xl font-semibold tracking-[-0.04em]">{data?.restaurant.name ?? 'Menü hazırlanıyor'}</h1>
        <p className="mt-3 text-[var(--muted)]">{data?.table.name ?? 'Bu QR için menü API bağlantısı bekleniyor.'}</p>
        <div className="flex items-center gap-3">
          <CallWaiterForm qrToken={qrToken} />
          {called === '1' ? <span className="text-sm text-emerald-700">Çağrı alındı.</span> : null}
        </div>
        <div className="mt-8 grid gap-5">
          {(data?.categories ?? []).map((category) => (
            <section key={category.id} className="card p-5">
              <h2 className="font-bold">{category.name}</h2>
              <div className="mt-4 grid gap-3">
                {category.products.map((product) => (
                  <OrderProductForm key={product.id} qrToken={qrToken} product={product} />
                ))}
              </div>
            </section>
          ))}
        </div>
      </div>
    </main>
  );
}
