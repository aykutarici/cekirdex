'use client';

import { useActionState, useState } from 'react';
import { storeTakeawayOrderAction } from './actions';

type Product = { id: number; name: string; effective_price: number };
type Category = { id: number; name: string; products: Product[] };

type Props = {
  slug: string;
  categories: Category[];
  acceptsTakeaway: boolean;
  acceptsDelivery: boolean;
};

type CartItem = { productId: number; name: string; price: number; quantity: number };

export function TakeawayOrderForm({ slug, categories, acceptsTakeaway, acceptsDelivery }: Props) {
  const [cart, setCart] = useState<CartItem[]>([]);
  const [orderType, setOrderType] = useState<'takeaway' | 'delivery'>(
    acceptsTakeaway ? 'takeaway' : 'delivery',
  );

  const [error, formAction, isPending] = useActionState(storeTakeawayOrderAction, null);

  function addToCart(product: Product) {
    setCart((prev) => {
      const existing = prev.find((c) => c.productId === product.id);
      if (existing) {
        return prev.map((c) =>
          c.productId === product.id ? { ...c, quantity: c.quantity + 1 } : c,
        );
      }
      return [...prev, { productId: product.id, name: product.name, price: product.effective_price, quantity: 1 }];
    });
  }

  function removeFromCart(productId: number) {
    setCart((prev) =>
      prev
        .map((c) => (c.productId === productId ? { ...c, quantity: c.quantity - 1 } : c))
        .filter((c) => c.quantity > 0),
    );
  }

  const total = cart.reduce((sum, c) => sum + c.price * c.quantity, 0);

  return (
    <div className="mt-10">
      <h2 className="text-2xl font-semibold tracking-tight">Paket Sipariş</h2>
      <p className="mt-1 text-sm text-[var(--muted)]">
        Menüden ürün seçin ve siparişinizi verin
      </p>

      {/* Sipariş tipi */}
      <div className="mt-4 flex gap-2">
        {acceptsTakeaway && (
          <button
            type="button"
            onClick={() => setOrderType('takeaway')}
            className={[
              'rounded-full px-4 py-2 text-sm font-semibold transition',
              orderType === 'takeaway'
                ? 'bg-[var(--ink)] text-white'
                : 'border border-[var(--border)] bg-white text-[var(--muted)]',
            ].join(' ')}
          >
            🥡 Gel Al
          </button>
        )}
        {acceptsDelivery && (
          <button
            type="button"
            onClick={() => setOrderType('delivery')}
            className={[
              'rounded-full px-4 py-2 text-sm font-semibold transition',
              orderType === 'delivery'
                ? 'bg-[var(--ink)] text-white'
                : 'border border-[var(--border)] bg-white text-[var(--muted)]',
            ].join(' ')}
          >
            🛵 Teslimat
          </button>
        )}
      </div>

      {/* Ürün seçimi */}
      <div className="mt-6 grid gap-4">
        {categories.map((cat) => (
          <div key={cat.id} className="card p-4">
            <p className="mb-3 text-sm font-bold text-[var(--muted)]">{cat.name}</p>
            <div className="space-y-2">
              {cat.products.map((product) => {
                const inCart = cart.find((c) => c.productId === product.id);
                return (
                  <div key={product.id} className="flex items-center justify-between gap-2">
                    <div>
                      <span className="text-sm font-medium">{product.name}</span>
                      <span className="ml-2 text-sm text-[var(--primary)]">
                        ₺{product.effective_price}
                      </span>
                    </div>
                    <div className="flex items-center gap-2">
                      {inCart ? (
                        <>
                          <button
                            type="button"
                            onClick={() => removeFromCart(product.id)}
                            className="flex h-7 w-7 items-center justify-center rounded-full border border-[var(--border)] text-sm font-bold hover:bg-red-50"
                          >
                            −
                          </button>
                          <span className="w-5 text-center text-sm font-semibold">
                            {inCart.quantity}
                          </span>
                        </>
                      ) : null}
                      <button
                        type="button"
                        onClick={() => addToCart(product)}
                        className="flex h-7 w-7 items-center justify-center rounded-full bg-[var(--ink)] text-sm font-bold text-white"
                      >
                        +
                      </button>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        ))}
      </div>

      {/* Sepet özeti ve form */}
      {cart.length > 0 && (
        <div className="card mt-6 p-6">
          <h3 className="font-semibold">Sepet ({cart.length} ürün)</h3>
          <div className="mt-3 space-y-1 text-sm">
            {cart.map((c) => (
              <div key={c.productId} className="flex justify-between">
                <span>
                  {c.quantity}× {c.name}
                </span>
                <span className="font-medium">{(c.price * c.quantity).toLocaleString('tr-TR')} TL</span>
              </div>
            ))}
            <div className="mt-2 border-t border-[var(--border)] pt-2 text-base font-bold">
              <div className="flex justify-between">
                <span>Toplam</span>
                <span>{total.toLocaleString('tr-TR')} TL</span>
              </div>
            </div>
          </div>

          {error && (
            <div className="mt-4 rounded-xl bg-red-50 p-3 text-sm text-red-700">{error}</div>
          )}

          <form action={formAction} className="mt-4 space-y-3">
            <input type="hidden" name="slug" value={slug} />
            <input type="hidden" name="order_type" value={orderType} />
            <input type="hidden" name="items" value={JSON.stringify(cart.map((c) => ({ product_id: c.productId, quantity: c.quantity })))} />

            <div>
              <label className="mb-1 block text-sm font-medium">Ad Soyad *</label>
              <input
                type="text"
                name="contact_name"
                required
                className="input"
                placeholder="Adınız"
              />
            </div>
            <div>
              <label className="mb-1 block text-sm font-medium">Telefon *</label>
              <input
                type="tel"
                name="contact_phone"
                required
                className="input"
                placeholder="05XX XXX XX XX"
              />
            </div>
            {orderType === 'delivery' && (
              <div>
                <label className="mb-1 block text-sm font-medium">Teslimat Adresi *</label>
                <textarea
                  name="delivery_address"
                  required
                  rows={3}
                  className="input resize-none"
                  placeholder="Adres bilgilerinizi girin"
                />
              </div>
            )}
            <div>
              <label className="mb-1 block text-sm font-medium">Not (opsiyonel)</label>
              <input
                type="text"
                name="note"
                className="input"
                placeholder="Özel istek varsa belirtin"
              />
            </div>
            <button
              type="submit"
              disabled={isPending || cart.length === 0}
              className="btn btn-primary w-full disabled:opacity-50"
            >
              {isPending ? 'Sipariş veriliyor…' : 'Sipariş Ver'}
            </button>
          </form>
        </div>
      )}
    </div>
  );
}
