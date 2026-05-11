'use client';

import Link from 'next/link';
import { useActionState } from 'react';
import { staffLoginAction } from '../actions';

export default function RestaurantLoginPage() {
  const [error, formAction, isPending] = useActionState(staffLoginAction, null);

  return (
    <main className="py-20">
      <div className="container max-w-lg">
        <Link href="/giris" className="text-sm font-semibold text-[var(--muted)]">
          ← Giriş seçeneklerine dön
        </Link>
        <p className="eyebrow mt-8">Restoran girişi</p>
        <h1 className="mt-3 text-4xl font-semibold tracking-[-0.04em]">
          Restoran panelinize giriş yapın
        </h1>
        <p className="mt-4 text-[var(--muted)]">
          İşletme sahibi, personel veya İninia admin hesabınız için e-posta adresinizi kullanın.
        </p>
        <form action={formAction} className="card mt-8 grid gap-4 p-6">
          {error ? (
            <div className="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
              {error}
            </div>
          ) : null}
          <input
            name="email"
            type="email"
            required
            autoComplete="email"
            placeholder="Personel e-posta"
            className="rounded-xl border border-[var(--border)] px-4 py-3"
          />
          <input
            name="password"
            type="password"
            required
            autoComplete="current-password"
            placeholder="Şifre"
            className="rounded-xl border border-[var(--border)] px-4 py-3"
          />
          <button type="submit" disabled={isPending} className="btn btn-primary">
            {isPending ? 'Giriş yapılıyor…' : 'Restoran paneline gir'}
          </button>
          <p className="text-sm text-[var(--muted)]">
            Restoran hesabınız yoksa <Link className="font-semibold text-[var(--ink)]" href="/kayit">işletmenizi kaydedin</Link>.
          </p>
        </form>
      </div>
    </main>
  );
}
