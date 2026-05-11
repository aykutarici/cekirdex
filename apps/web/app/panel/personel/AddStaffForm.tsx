'use client';

import { useActionState } from 'react';
import { createStaffAction } from './actions';

export default function AddStaffForm() {
  const [error, formAction, pending] = useActionState(createStaffAction, null);

  return (
    <form action={formAction} className="space-y-4">
      {error && (
        <div className="rounded-xl bg-red-50 p-3 text-sm text-red-700">{error}</div>
      )}

      <div className="grid gap-4 sm:grid-cols-2">
        <div>
          <label className="mb-1 block text-sm font-medium">Ad Soyad *</label>
          <input
            type="text"
            name="name"
            required
            className="w-full rounded-xl border border-[var(--border)] bg-white px-4 py-2.5 text-sm outline-none focus:border-[var(--primary)]"
            placeholder="Ad Soyad"
          />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium">E-posta *</label>
          <input
            type="email"
            name="email"
            required
            className="w-full rounded-xl border border-[var(--border)] bg-white px-4 py-2.5 text-sm outline-none focus:border-[var(--primary)]"
            placeholder="ornek@email.com"
          />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium">Şifre *</label>
          <input
            type="password"
            name="password"
            required
            minLength={8}
            className="w-full rounded-xl border border-[var(--border)] bg-white px-4 py-2.5 text-sm outline-none focus:border-[var(--primary)]"
            placeholder="En az 8 karakter"
          />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium">Telefon</label>
          <input
            type="tel"
            name="phone"
            className="w-full rounded-xl border border-[var(--border)] bg-white px-4 py-2.5 text-sm outline-none focus:border-[var(--primary)]"
            placeholder="0555 000 0000"
          />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium">Rol *</label>
          <select
            name="role"
            className="w-full rounded-xl border border-[var(--border)] bg-white px-4 py-2.5 text-sm outline-none focus:border-[var(--primary)]"
          >
            <option value="waiter">Garson</option>
            <option value="kitchen">Mutfak</option>
            <option value="manager">Yönetici</option>
            <option value="owner">Sahip</option>
          </select>
        </div>
      </div>

      <button
        type="submit"
        disabled={pending}
        className="btn btn-primary text-sm disabled:opacity-50"
      >
        {pending ? 'Ekleniyor…' : '+ Personel Ekle'}
      </button>
    </form>
  );
}
