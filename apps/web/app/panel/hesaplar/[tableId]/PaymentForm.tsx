'use client';

import { useActionState } from 'react';
import { recordPaymentAction } from './actions';

export default function PaymentForm({ tableId }: { tableId: number }) {
  const boundAction = recordPaymentAction.bind(null, tableId);
  const [error, formAction, pending] = useActionState(boundAction, null);

  return (
    <form action={formAction} className="space-y-4">
      {error && (
        <div className="rounded-xl bg-red-50 p-3 text-sm text-red-700">{error}</div>
      )}

      <div>
        <label className="mb-1 block text-sm font-medium">Tutar (₺)</label>
        <input
          type="number"
          name="amount"
          min="0.01"
          step="0.01"
          required
          className="w-full rounded-xl border border-[var(--border)] bg-white px-4 py-2.5 text-sm outline-none focus:border-[var(--primary)] focus:ring-2 focus:ring-[var(--primary)]/20"
          placeholder="0,00"
        />
      </div>

      <div>
        <label className="mb-1 block text-sm font-medium">Ödeme yöntemi</label>
        <select
          name="method"
          className="w-full rounded-xl border border-[var(--border)] bg-white px-4 py-2.5 text-sm outline-none focus:border-[var(--primary)]"
        >
          <option value="cash">Nakit</option>
          <option value="card">Kredi/Banka kartı</option>
          <option value="transfer">Havale</option>
          <option value="other">Diğer</option>
        </select>
      </div>

      <div>
        <label className="mb-1 block text-sm font-medium">Not (opsiyonel)</label>
        <input
          type="text"
          name="note"
          className="w-full rounded-xl border border-[var(--border)] bg-white px-4 py-2.5 text-sm outline-none focus:border-[var(--primary)]"
          placeholder="Opsiyonel not"
        />
      </div>

      <button
        type="submit"
        disabled={pending}
        className="btn btn-primary w-full disabled:opacity-50"
      >
        {pending ? 'Kaydediliyor…' : 'Ödeme Kaydet'}
      </button>
    </form>
  );
}
