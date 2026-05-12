'use client';

import Image from 'next/image';
import { useActionState, useState } from 'react';
import { callWaiterAction, createOrderAction } from './actions';
import { ProductActions } from './ProductActions';
import { ReviewModal } from './ReviewModal';

type Product = {
  id: number;
  name: string;
  description: string | null;
  effective_price: number;
  discount_price: number | null;
  image_url: string | null;
  is_popular: boolean;
  is_new: boolean;
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
      <div className="flex gap-3 border-t border-[var(--border)] pt-4">
        {/* Ürün görseli */}
        <div className="relative h-20 w-20 shrink-0 overflow-hidden rounded-xl sm:h-24 sm:w-24">
          <Image
            src={product.image_url ?? '/cekirdex/placeholder-food.svg'}
            alt={product.name}
            fill
            className="object-cover"
            unoptimized={!product.image_url}
          />
        </div>

        {/* İçerik */}
        <div className="min-w-0 flex-1">
          <div className="flex flex-wrap items-start justify-between gap-x-2 gap-y-0.5">
            <div className="flex flex-wrap items-center gap-1.5">
              <span className="font-semibold leading-tight">{product.name}</span>
              {product.is_popular && (
                <span className="rounded-full bg-orange-100 px-1.5 py-0.5 text-[10px] font-medium text-orange-700">Popüler</span>
              )}
              {product.is_new && (
                <span className="rounded-full bg-emerald-100 px-1.5 py-0.5 text-[10px] font-medium text-emerald-700">Yeni</span>
              )}
            </div>
            <div className="flex items-baseline gap-1.5">
              {product.discount_price && (
                <span className="text-xs text-[var(--muted)] line-through">₺{product.effective_price}</span>
              )}
              <strong className="text-base text-[var(--primary)]">₺{product.discount_price ?? product.effective_price}</strong>
            </div>
          </div>

          {product.description && (
            <p className="mt-0.5 line-clamp-2 text-xs text-[var(--muted)]">{product.description}</p>
          )}

          <ProductActions
            qrToken={qrToken}
            productId={product.id}
            isLoggedIn={isLoggedIn}
            onReviewClick={() => setReviewOpen(true)}
          />

          <form action={formAction} className="mt-2 flex items-center gap-2">
            <input type="hidden" name="qr_token" value={qrToken} />
            <input type="hidden" name="product_id" value={product.id} />
            <input
              className="w-14 rounded-lg border border-[var(--border)] px-2 py-1 text-center text-sm"
              name="quantity"
              type="number"
              min={1}
              defaultValue={1}
            />
            <button
              className="rounded-full bg-[var(--ink)] px-4 py-1.5 text-sm font-semibold text-white disabled:opacity-60"
              type="submit"
              disabled={isPending}
            >
              {isPending ? '…' : 'Sipariş ver'}
            </button>
          </form>
          {error && <p className="mt-1 text-xs text-red-700">{error}</p>}
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
