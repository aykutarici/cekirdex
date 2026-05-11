'use client';

import { useActionState } from 'react';
import { registerAction } from './actions';

export default function RegisterPage() {
  const [error, formAction, isPending] = useActionState(registerAction, null);

  return (
    <main className="py-20">
      <div className="container max-w-lg">
        <p className="eyebrow">Kayıt</p>
        <h1 className="mt-3 text-4xl font-semibold tracking-[-0.04em]">İşletmenizi Çekirdex'e taşıyın</h1>
        <form action={formAction} className="card mt-8 grid gap-4 p-6">
          {error ? (
            <div className="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
              {error}
            </div>
          ) : null}
          <input name="restaurant_name" required placeholder="İşletme adı" className="rounded-xl border border-[var(--border)] px-4 py-3" />
          <input name="city" placeholder="Şehir" className="rounded-xl border border-[var(--border)] px-4 py-3" />
          <input name="phone" placeholder="Telefon" className="rounded-xl border border-[var(--border)] px-4 py-3" />
          <input name="name" required placeholder="Ad soyad" className="rounded-xl border border-[var(--border)] px-4 py-3" />
          <input name="email" required type="email" placeholder="E-posta" className="rounded-xl border border-[var(--border)] px-4 py-3" />
          <input name="password" required minLength={8} type="password" placeholder="Şifre" className="rounded-xl border border-[var(--border)] px-4 py-3" />
          <button type="submit" disabled={isPending} className="btn btn-primary">
            {isPending ? 'Hesap oluşturuluyor…' : 'Hesap oluştur'}
          </button>
          <p className="text-sm text-[var(--muted)]">Restoran ve ilk owner hesabı API üzerinden oluşturulur.</p>
        </form>
      </div>
    </main>
  );
}
