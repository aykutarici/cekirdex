import { redirect } from 'next/navigation';
import Link from 'next/link';
import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';

type BillTable = {
  table_id: number;
  table_name: string;
  total: number;
  paid: number;
  remaining: number;
  order_count: number;
};

export default async function HesaplarPage() {
  const token = await getAuthToken();
  if (!token) redirect('/giris');

  let tables: BillTable[] = [];
  try {
    const data = await apiFetch<{ data: BillTable[] }>('/api/v1/panel/bills', { token });
    tables = data.data;
  } catch {
    redirect('/giris');
  }

  return (
    <div className="p-6">
      <h1 className="text-2xl font-semibold tracking-tight">💳 Hesaplar</h1>
      <p className="mt-1 text-sm text-[var(--muted)]">Açık masaların hesapları</p>

      {tables.length === 0 ? (
        <div className="mt-8 rounded-2xl border border-dashed border-[var(--border)] p-12 text-center text-[var(--muted)]">
          <p className="text-4xl">💳</p>
          <p className="mt-2 font-medium">Açık hesap yok</p>
        </div>
      ) : (
        <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {tables.map((t) => (
            <Link
              key={t.table_id}
              href={`/panel/hesaplar/${t.table_id}`}
              className="card block p-5 transition-shadow hover:shadow-md"
            >
              <div className="flex items-start justify-between">
                <h2 className="text-lg font-semibold">{t.table_name}</h2>
                <span className="rounded-full bg-[var(--bg-soft)] px-2 py-0.5 text-xs text-[var(--muted)]">
                  {t.order_count} sipariş
                </span>
              </div>
              <div className="mt-4 space-y-1 text-sm">
                <div className="flex justify-between">
                  <span className="text-[var(--muted)]">Toplam</span>
                  <span className="font-semibold">{Number(t.total).toLocaleString('tr-TR')} ₺</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-[var(--muted)]">Ödenen</span>
                  <span className="font-semibold text-green-600">{Number(t.paid).toLocaleString('tr-TR')} ₺</span>
                </div>
                <div className="flex justify-between border-t border-[var(--border)] pt-1">
                  <span className="font-semibold">Kalan</span>
                  <span className={`font-bold text-lg ${t.remaining > 0 ? 'text-orange-600' : 'text-green-600'}`}>
                    {Number(t.remaining).toLocaleString('tr-TR')} ₺
                  </span>
                </div>
              </div>
            </Link>
          ))}
        </div>
      )}
    </div>
  );
}
