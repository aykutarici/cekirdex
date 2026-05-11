'use client';

import Link from 'next/link';
import { useActionState } from 'react';
import { createCategoryAction } from '../actions';

export default function YeniKategoriPage() {
  const [error, formAction, pending] = useActionState(createCategoryAction, null);

  return (
    <div className="p-6">
      <Link href="/panel/menu" className="text-sm text-[var(--muted)] hover:text-[var(--ink)]">
        ← Menü
      </Link>

      <h1 className="mt-4 text-2xl font-semibold tracking-tight">Yeni Kategori</h1>

      <div className="card mt-6 max-w-lg p-6">
        {error && (
          <div className="mb-4 rounded-xl bg-red-50 p-3 text-sm text-red-700">{error}</div>
        )}

        <form action={formAction} className="space-y-4">
          <div>
            <label className="mb-1 block text-sm font-medium">Kategori adı *</label>
            <input
              type="text"
              name="name"
              required
              className="w-full rounded-xl border border-[var(--border)] bg-white px-4 py-2.5 text-sm outline-none focus:border-[var(--primary)] focus:ring-2 focus:ring-[var(--primary)]/20"
              placeholder="Örn: Başlangıçlar"
            />
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
              {pending ? 'Kaydediliyor…' : 'Kategori Oluştur'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
