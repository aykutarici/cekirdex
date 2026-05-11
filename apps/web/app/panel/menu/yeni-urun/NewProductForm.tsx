'use client';

import Link from 'next/link';
import { useActionState } from 'react';
import { createProductAction } from '../actions';

type Category = { id: number; name: string };

export default function NewProductForm({ categories }: { categories: Category[] }) {
  const [error, formAction, pending] = useActionState(createProductAction, null);

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
            <input type="checkbox" id="is_active" name="is_active" defaultChecked className="h-4 w-4 rounded" />
            <label htmlFor="is_active" className="text-sm font-medium">Aktif olarak yayınla</label>
          </div>

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
