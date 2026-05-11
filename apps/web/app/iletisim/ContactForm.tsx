'use client';

import { useActionState } from 'react';
import { contactAction } from './actions';

export function ContactForm() {
  const [error, formAction, isPending] = useActionState(contactAction, null);

  return (
    <form className="card grid gap-4 p-6" action={formAction}>
      {error ? (
        <div className="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
          {error}
        </div>
      ) : null}
      <input name="name" required placeholder="Ad soyad" className="rounded-xl border border-[var(--border)] bg-white px-4 py-3" />
      <input name="email" required type="email" placeholder="E-posta" className="rounded-xl border border-[var(--border)] bg-white px-4 py-3" />
      <input name="restaurant_name" placeholder="İşletme adı" className="rounded-xl border border-[var(--border)] bg-white px-4 py-3" />
      <textarea name="message" required placeholder="Mesajınız" rows={5} className="rounded-xl border border-[var(--border)] bg-white px-4 py-3" />
      <input name="website" tabIndex={-1} autoComplete="off" className="hidden" />
      <button className="btn btn-primary justify-self-start" type="submit" disabled={isPending}>
        {isPending ? 'Gönderiliyor…' : 'Gönder'}
      </button>
    </form>
  );
}
