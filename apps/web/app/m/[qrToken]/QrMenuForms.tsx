'use client';

import { useActionState, useState } from 'react';
import { callWaiterAction, createOrderAction } from './actions';
import { ProductActions } from './ProductActions';
import { ReviewModal } from './ReviewModal';

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

export function OrderProductForm({
  qrToken,
  product,
  isLoggedIn,
}: {
  qrToken: string;
  product: Product;
  isLoggedIn: boolean;
}) {
  const [error, formAction, isPending] = useActionState(createOrderAction, null);
  const [reviewOpen, setReviewOpen] = useState(false);

  return (
    <>
      <div className="flex flex-wrap items-start justify-between gap-3 border-t border-[var(--border)] pt-3">
        <div className="min-w-0 flex-1">
          <div className="flex items-baseline gap-2">
            <span className="font-medium">{product.name}</span>
            <strong className="text-[var(--primary)]">₺{product.effective_price}</strong>
          </div>
          <ProductActions
            qrToken={qrToken}
            productId={product.id}
            isLoggedIn={isLoggedIn}
            onReviewClick={() => setReviewOpen(true)}
          />
        </div>
        <div className="flex flex-col items-end gap-1">
          <form action={formAction} className="flex items-center gap-2">
            <input type="hidden" name="qr_token" value={qrToken} />
            <input type="hidden" name="product_id" value={product.id} />
            <input
              className="w-16 rounded-lg border border-[var(--border)] px-2 py-1 text-center text-sm"
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

      {reviewOpen && (
        <ReviewModal
          qrToken={qrToken}
          productId={product.id}
          productName={product.name}
          isLoggedIn={isLoggedIn}
          onClose={() => setReviewOpen(false)}
        />
      )}
    </>
  );
}
