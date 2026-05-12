'use client';

import Link from 'next/link';
import { useActionState, useState } from 'react';
import { createProductAction } from '../actions';

type Category = { id: number; name: string };
type StockImage = { slug: string; url: string; label?: string };

export default function NewProductForm({
  categories,
  stockImages,
}: {
  categories: Category[];
  stockImages: StockImage[];
}) {
  const [error, formAction, pending] = useActionState(createProductAction, null);
  const [selectedImageSlug, setSelectedImageSlug] = useState<string>('');

  return (
    <>
      <Link href="/panel/menu" className="text-sm text-[var(--muted)] hover:text-[var(--ink)]">
        ← Menü
      </Link>

      <h1 className="mt-4 text-2xl font-semibold tracking-tight">Yeni Ürün</h1>

      <div className="card mt-6 max-w-lg p-6">
        {error && (
          <div className="mb-4 rounded-xl bg-red-50 p-3 text-sm text-red-700">{error}</div>
        )}

        <form action={formAction} className="space-y-4">
          <div>
            <label className="mb-1 block text-sm font-medium">Kategori *</label>
            <select
              name="category_id"
              required
              className="w-full rounded-xl border border-[var(--border)] bg-white px-4 py-2.5 text-sm outline-none focus:border-[var(--primary)]"
            >
              <option value="">Kategori seçin</option>
              {categories.map((cat) => (
                <option key={cat.id} value={cat.id}>{cat.name}</option>
              ))}
            </select>
          </div>

          <div>
            <label className="mb-1 block text-sm font-medium">Ürün adı *</label>
            <input
              type="text"
              name="name"
              required
              className="w-full rounded-xl border border-[var(--border)] bg-white px-4 py-2.5 text-sm outline-none focus:border-[var(--primary)] focus:ring-2 focus:ring-[var(--primary)]/20"
              placeholder="Örn: Mercimek Çorbası"
            />
          </div>

          <div>
            <label className="mb-1 block text-sm font-medium">Açıklama</label>
            <textarea
              name="description"
              rows={3}
              className="w-full rounded-xl border border-[var(--border)] bg-white px-4 py-2.5 text-sm outline-none focus:border-[var(--primary)] focus:ring-2 focus:ring-[var(--primary)]/20"
              placeholder="Ürün açıklaması (opsiyonel)"
            />
          </div>

          <div>
            <label className="mb-1 block text-sm font-medium">Fiyat (₺) *</label>
            <input
              type="number"
              name="price"
              min="0"
              step="0.01"
              required
              className="w-full rounded-xl border border-[var(--border)] bg-white px-4 py-2.5 text-sm outline-none focus:border-[var(--primary)] focus:ring-2 focus:ring-[var(--primary)]/20"
              placeholder="0,00"
            />
          </div>

          <div className="flex items-center gap-2">
            <input
              type="checkbox"
              id="is_active"
              name="is_active"
              defaultChecked
              className="h-4 w-4 rounded"
            />
            <label htmlFor="is_active" className="text-sm font-medium">
              Aktif olarak yayınla
            </label>
          </div>

          {/* Stok görsel seçici */}
          {stockImages.length > 0 && (
            <div>
              <label className="mb-2 block text-sm font-medium">
                Stok görsel seç{' '}
                <span className="font-normal text-[var(--muted)]">(opsiyonel)</span>
              </label>
              <input type="hidden" name="image_slug" value={selectedImageSlug} />
              <div className="grid grid-cols-4 gap-2 sm:grid-cols-5">
                {/* Görsel seçimi kaldır butonu */}
                {selectedImageSlug && (
                  <button
                    type="button"
                    onClick={() => setSelectedImageSlug('')}
                    className="col-span-full mb-1 rounded-lg border border-dashed border-[var(--border)] py-1 text-xs text-[var(--muted)] hover:bg-[var(--bg-soft)]"
                  >
                    ✕ Seçimi kaldır
                  </button>
                )}
                {stockImages.map((img) => (
                  <button
                    key={img.slug}
                    type="button"
                    onClick={() => setSelectedImageSlug(img.slug === selectedImageSlug ? '' : img.slug)}
                    className={[
                      'overflow-hidden rounded-xl border-2 transition',
                      img.slug === selectedImageSlug
                        ? 'border-[var(--primary)] ring-2 ring-[var(--primary)]/30'
                        : 'border-[var(--border)] hover:border-[var(--primary)]/40',
                    ].join(' ')}
                    title={img.label ?? img.slug}
                  >
                    {/* eslint-disable-next-line @next/next/no-img-element */}
                    <img
                      src={img.url}
                      alt={img.label ?? img.slug}
                      className="aspect-square w-full object-cover"
                      loading="lazy"
                    />
                  </button>
                ))}
              </div>
              {selectedImageSlug && (
                <p className="mt-1 text-xs text-[var(--muted)]">
                  Seçili: <span className="font-medium">{selectedImageSlug}</span>
                </p>
              )}
            </div>
          )}

          <div className="flex gap-3 pt-2">
            <Link href="/panel/menu" className="btn btn-ghost flex-1 text-sm">
              İptal
            </Link>
            <button
              type="submit"
              disabled={pending}
              className="btn btn-primary flex-1 text-sm disabled:opacity-50"
            >
              {pending ? 'Kaydediliyor…' : 'Ürün Oluştur'}
            </button>
          </div>
        </form>
      </div>
    </>
  );
}
