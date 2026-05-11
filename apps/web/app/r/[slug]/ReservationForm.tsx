'use client';

import { useActionState } from 'react';
import { createReservationAction } from './actions';

export function ReservationForm({ slug }: { slug: string }) {
  const [error, formAction, isPending] = useActionState(createReservationAction, null);

  return (
    <form action={formAction} className="card mt-8 grid max-w-2xl gap-4 p-6">
      <h2 className="text-2xl font-semibold">Rezervasyon talebi oluştur</h2>
      {error ? (
        <div className="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
          {error}
        </div>
      ) : null}
      <input type="hidden" name="slug" value={slug} />
      <input name="contact_name" required placeholder="Ad soyad" className="rounded-xl border border-[var(--border)] px-4 py-3" />
      <input name="contact_phone" required placeholder="Telefon" className="rounded-xl border border-[var(--border)] px-4 py-3" />
      <input name="contact_email" type="email" placeholder="E-posta" className="rounded-xl border border-[var(--border)] px-4 py-3" />
      <input name="reserved_for" required type="datetime-local" className="rounded-xl border border-[var(--border)] px-4 py-3" />
      <input name="party_size" required min={1} defaultValue={2} type="number" className="rounded-xl border border-[var(--border)] px-4 py-3" />
      <textarea name="note" placeholder="Not" className="rounded-xl border border-[var(--border)] px-4 py-3" />
      <button className="btn btn-primary justify-self-start" type="submit" disabled={isPending}>
        {isPending ? 'Gönderiliyor…' : 'Rezervasyon gönder'}
      </button>
    </form>
  );
}
