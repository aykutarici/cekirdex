'use client';

import { useActionState, useState } from 'react';
import { customerRegisterAction, customerLoginAction } from './actions';

type Mode = 'idle' | 'login' | 'register';

export function CustomerAuth({ qrToken, isLoggedIn }: { qrToken: string; isLoggedIn: boolean }) {
  const [mode, setMode] = useState<Mode>('idle');

  const registerAction = customerRegisterAction.bind(null, qrToken);
  const loginAction = customerLoginAction.bind(null, qrToken);

  const [registerError, registerFormAction, registerPending] = useActionState(registerAction, null);
  const [loginError, loginFormAction, loginPending] = useActionState(loginAction, null);

  if (isLoggedIn) {
    return (
      <div className="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        ✓ Giriş yapıldı — yorum ve reaksiyon özelliklerine erişebilirsiniz.
      </div>
    );
  }

  if (mode === 'idle') {
    return (
      <div className="mb-4 rounded-2xl border border-[var(--border)] bg-white p-4">
        <p className="text-sm font-medium text-[var(--ink)]">Yorum ve reaksiyonlar için giriş yapın</p>
        <p className="mt-0.5 text-xs text-[var(--muted)]">Zorunlu değil — sadece yorum/beğeni için gerekli</p>
        <div className="mt-3 flex gap-2">
          <button
            onClick={() => setMode('login')}
            className="rounded-xl border border-[var(--border)] px-4 py-2 text-xs font-semibold hover:bg-[var(--bg-soft)]"
          >
            Giriş yap
          </button>
          <button
            onClick={() => setMode('register')}
            className="rounded-xl bg-[var(--primary)] px-4 py-2 text-xs font-semibold text-white"
          >
            Kayıt ol
          </button>
        </div>
      </div>
    );
  }

  if (mode === 'login') {
    return (
      <div className="mb-4 rounded-2xl border border-[var(--border)] bg-white p-4">
        <div className="flex items-center justify-between">
          <p className="text-sm font-semibold">Giriş Yap</p>
          <button onClick={() => setMode('idle')} className="text-xs text-[var(--muted)]">✕ İptal</button>
        </div>
        {loginError && (
          <p className="mt-2 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700">{loginError}</p>
        )}
        <form action={loginFormAction} className="mt-3 space-y-3">
          <div>
            <label className="mb-1 block text-xs font-medium">Telefon numarası</label>
            <input
              type="tel"
              name="phone"
              required
              placeholder="05XX XXX XX XX"
              className="input text-sm"
            />
          </div>
          <button
            type="submit"
            disabled={loginPending}
            className="w-full rounded-xl bg-[var(--ink)] py-2.5 text-sm font-semibold text-white disabled:opacity-50"
          >
            {loginPending ? 'Giriş yapılıyor…' : 'Giriş Yap'}
          </button>
        </form>
      </div>
    );
  }

  return (
    <div className="mb-4 rounded-2xl border border-[var(--border)] bg-white p-4">
      <div className="flex items-center justify-between">
        <p className="text-sm font-semibold">Kayıt Ol</p>
        <button onClick={() => setMode('idle')} className="text-xs text-[var(--muted)]">✕ İptal</button>
      </div>
      {registerError && (
        <p className="mt-2 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700">{registerError}</p>
      )}
      <form action={registerFormAction} className="mt-3 space-y-3">
        <div>
          <label className="mb-1 block text-xs font-medium">Adınız *</label>
          <input
            type="text"
            name="name"
            required
            placeholder="Ad Soyad"
            className="input text-sm"
          />
        </div>
        <div>
          <label className="mb-1 block text-xs font-medium">Telefon *</label>
          <input
            type="tel"
            name="phone"
            required
            placeholder="05XX XXX XX XX"
            className="input text-sm"
          />
        </div>
        <div>
          <label className="mb-1 block text-xs font-medium">E-posta (opsiyonel)</label>
          <input
            type="email"
            name="email"
            placeholder="ornek@mail.com"
            className="input text-sm"
          />
        </div>
        <button
          type="submit"
          disabled={registerPending}
          className="w-full rounded-xl bg-[var(--primary)] py-2.5 text-sm font-semibold text-white disabled:opacity-50"
        >
          {registerPending ? 'Kaydediliyor…' : 'Kayıt Ol'}
        </button>
      </form>
    </div>
  );
}
