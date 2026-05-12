'use client';

import { useEffect, useState } from 'react';

type BillItem = {
  id: number;
  name: string;
  quantity: number;
  unit_price: number;
  subtotal: number;
};

type BillPayload = {
  has_open_orders: boolean;
  items: BillItem[];
  subtotal: number;
  tax: number;
  total: number;
  paid: number;
  remaining: number;
};

type BillData = {
  data: BillPayload;
};

export function CustomerBill({ qrToken }: { qrToken: string }) {
  const [bill, setBill] = useState<BillPayload | null>(null);
  const [loading, setLoading] = useState(true);
  const [paymentRequested, setPaymentRequested] = useState(false);

  async function fetchBill() {
    try {
      const res = await fetch(`/api/customer/${qrToken}/bill`);
      if (!res.ok) return;
      const data: BillData = await res.json();
      setBill(data.data);
    } catch {
      // sessizce başarısız ol
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    fetchBill();
    const id = setInterval(fetchBill, 30_000);
    return () => clearInterval(id);
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [qrToken]);

  if (loading) return null;
  if (!bill?.has_open_orders) return null;

  return (
    <div className="mb-6 rounded-2xl border border-[var(--border)] bg-white p-5">
      <div className="flex items-center justify-between">
        <h2 className="text-base font-bold">Açık Hesabım</h2>
        <span className="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800">
          {bill.items.length} kalem
        </span>
      </div>

      <div className="mt-4 space-y-2">
        {bill.items.map((item) => (
          <div key={item.id} className="flex items-center justify-between text-sm">
            <span className="text-[var(--muted)]">
              <span className="font-semibold text-[var(--ink)]">{item.quantity}×</span> {item.name}
            </span>
            <span className="font-medium">{item.subtotal.toLocaleString('tr-TR')} TL</span>
          </div>
        ))}
      </div>

      <div className="mt-4 space-y-1 border-t border-[var(--border)] pt-4 text-sm">
        <div className="flex justify-between text-[var(--muted)]">
          <span>Ara toplam</span>
          <span>{bill.subtotal.toLocaleString('tr-TR')} TL</span>
        </div>
        {bill.tax > 0 && (
          <div className="flex justify-between text-[var(--muted)]">
            <span>KDV</span>
            <span>{bill.tax.toLocaleString('tr-TR')} TL</span>
          </div>
        )}
        <div className="flex justify-between font-bold text-[var(--ink)]">
          <span>Toplam</span>
          <span>{bill.total.toLocaleString('tr-TR')} TL</span>
        </div>
        {bill.paid > 0 && (
          <div className="flex justify-between text-emerald-700">
            <span>Ödendi</span>
            <span>−{bill.paid.toLocaleString('tr-TR')} TL</span>
          </div>
        )}
        {bill.remaining > 0 && (
          <div className="flex justify-between text-base font-bold text-[var(--primary)]">
            <span>Kalan</span>
            <span>{bill.remaining.toLocaleString('tr-TR')} TL</span>
          </div>
        )}
      </div>

      {bill.remaining > 0 && (
        <button
          onClick={() => setPaymentRequested(true)}
          disabled={paymentRequested}
          className="mt-4 w-full rounded-xl bg-[var(--primary)] py-3 text-sm font-bold text-white disabled:opacity-60"
        >
          {paymentRequested ? '✓ Ödeme talebi iletildi' : 'Ödeme talep et'}
        </button>
      )}
    </div>
  );
}
