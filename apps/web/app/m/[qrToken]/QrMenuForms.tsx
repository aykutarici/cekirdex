'use client';

import { useActionState } from 'react';
import { callWaiterAction, createOrderAction } from './actions';

type Product = {
  id: number;
  name: string;
  effective_price: number;
};

export function CallWaiterForm({ qrToken }: { qrToken: string }) {
  const [error, formAction, isPending] = useActionState(callWaiterAction, null);

  return (
    <div className="mt-6">
      <form action={formAction}>
        <input type="hidden" name="qr_token" value={qrToken} />
        <button className="btn btn-secondary" type="submit" disabled={isPending}>
          {isPending ? 'Çağrı yapılıyor…' : 'Garson çağır'}
        </button>
      </form>
      {error ? (
        <p className="mt-2 text-sm text-red-700">{error}</p>
      ) : null}
    </div>
  );
}

export function OrderProductForm({ qrToken, product }: { qrToken: string; product: Product }) {
  const [error, formAction, isPending] = useActionState(createOrderAction, null);

  return (
    <div className="flex flex-wrap items-center justify-between gap-3 border-t border-[var(--border)] pt-3">
      <div>
        <span>{product.name}</span>
        <strong className="ml-3">₺{product.effective_price}</strong>
      </div>
      <div className="flex flex-col items-end gap-1">
        <form action={formAction} className="flex items-center gap-2">
          <input type="hidden" name="qr_token" value={qrToken} />
          <input type="hidden" name="product_id" value={product.id} />
          <input
            className="w-16 rounded-lg border border-[var(--border)] px-2 py-1"
            name="quantity"
            type="number"
            min={1}
            defaultValue={1}
          />
          <button
            className="rounded-full bg-[var(--ink)] px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
            type="submit"
            disabled={isPending}
          >
            {isPending ? '…' : 'Sipariş ver'}
          </button>
        </form>
        {error ? (
          <p className="text-xs text-red-700">{error}</p>
        ) : null}
      </div>
    </div>
  );
}
