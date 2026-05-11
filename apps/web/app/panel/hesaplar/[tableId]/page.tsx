import { redirect } from 'next/navigation';
import Link from 'next/link';
import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';
import { closeBillAction, cancelPaymentAction } from './actions';
import PaymentForm from './PaymentForm';

type BillItem = {
  id: number;
  name: string;
  quantity: number;
  unit_price: number;
  subtotal: number;
};

type Payment = {
  id: number;
  amount: number;
  method: string;
  method_label: string;
  created_at: string;
};

type BillDetail = {
  table_id: number;
  table_name: string;
  total: number;
  paid: number;
  remaining: number;
  items: BillItem[];
  payments: Payment[];
};

export default async function BillDetailPage({
  params,
}: {
  params: Promise<{ tableId: string }>;
}) {
  const { tableId } = await params;
  const token = await getAuthToken();
  if (!token) redirect('/giris');

  let bill: BillDetail;
  try {
    const data = await apiFetch<{ data: BillDetail }>(`/api/v1/panel/bills/${tableId}`, { token });
    bill = data.data;
  } catch {
    redirect('/panel/hesaplar');
  }

  const closeBill = closeBillAction.bind(null, Number(tableId));

  return (
    <div className="p-6">
      <div className="flex items-center gap-2">
        <Link href="/panel/hesaplar" className="text-sm text-[var(--muted)] hover:text-[var(--ink)]">
          ← Hesaplar
        </Link>
      </div>

      <div className="mt-4 flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 className="text-2xl font-semibold tracking-tight">{bill.table_name} — Hesap</h1>
          <p className="mt-1 text-sm text-[var(--muted)]">
            Toplam: <strong>{Number(bill.total).toLocaleString('tr-TR')} ₺</strong>
            {' · '}
            Ödenen: <strong className="text-green-600">{Number(bill.paid).toLocaleString('tr-TR')} ₺</strong>
            {' · '}
            Kalan: <strong className={bill.remaining > 0 ? 'text-orange-600' : 'text-green-600'}>
              {Number(bill.remaining).toLocaleString('tr-TR')} ₺
            </strong>
          </p>
        </div>
        <form action={closeBill}>
          <button
            type="submit"
            className="rounded-xl bg-gray-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-black"
          >
            Hesabı Kapat
          </button>
        </form>
      </div>

      <div className="mt-6 grid gap-6 lg:grid-cols-2">
        {/* Ürünler */}
        <div className="card overflow-hidden p-0">
          <div className="border-b border-[var(--border)] bg-[var(--bg-soft)] px-5 py-3">
            <h2 className="font-semibold">Sipariş Kalemleri</h2>
          </div>
          <div className="divide-y divide-[var(--border)]">
            {bill.items.map((item) => (
              <div key={item.id} className="flex items-center justify-between px-5 py-3 text-sm">
                <div>
                  <span className="font-semibold">{item.quantity}×</span> {item.name}
                  <span className="ml-1 text-xs text-[var(--muted)]">
                    ({Number(item.unit_price).toLocaleString('tr-TR')} ₺/adet)
                  </span>
                </div>
                <span className="font-semibold">{Number(item.subtotal).toLocaleString('tr-TR')} ₺</span>
              </div>
            ))}
          </div>
          <div className="flex justify-between border-t border-[var(--border)] bg-[var(--bg-soft)] px-5 py-3 font-bold">
            <span>Toplam</span>
            <span>{Number(bill.total).toLocaleString('tr-TR')} ₺</span>
          </div>
        </div>

        {/* Sağ kolon: Ödemeler + Form */}
        <div className="space-y-6">
          {/* Ödemeler */}
          <div className="card overflow-hidden p-0">
            <div className="border-b border-[var(--border)] bg-[var(--bg-soft)] px-5 py-3">
              <h2 className="font-semibold">Ödemeler</h2>
            </div>
            {bill.payments.length === 0 ? (
              <p className="p-5 text-sm text-[var(--muted)]">Henüz ödeme yok.</p>
            ) : (
              <div className="divide-y divide-[var(--border)]">
                {bill.payments.map((payment) => {
                  const cancelAction = cancelPaymentAction.bind(null, Number(tableId), payment.id);
                  return (
                    <div key={payment.id} className="flex items-center justify-between px-5 py-3 text-sm">
                      <div>
                        <span className="font-semibold">{Number(payment.amount).toLocaleString('tr-TR')} ₺</span>
                        <span className="ml-2 rounded-full bg-[var(--bg-soft)] px-2 py-0.5 text-xs">
                          {payment.method_label}
                        </span>
                      </div>
                      <form action={cancelAction}>
                        <button
                          type="submit"
                          className="rounded-lg px-2 py-1 text-xs text-red-600 hover:bg-red-50"
                        >
                          İptal
                        </button>
                      </form>
                    </div>
                  );
                })}
              </div>
            )}
          </div>

          {/* Yeni ödeme formu */}
          {bill.remaining > 0 && (
            <div className="card p-5">
              <h2 className="mb-4 font-semibold">Ödeme Ekle</h2>
              <PaymentForm tableId={Number(tableId)} />
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
