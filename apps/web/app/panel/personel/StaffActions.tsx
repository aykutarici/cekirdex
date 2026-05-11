'use client';

import { useActionState, useState } from 'react';
import { createStaffAction, deleteStaffAction } from './actions';

const roleOptions = [
  { value: 'owner', label: 'Sahip' },
  { value: 'manager', label: 'Yönetici' },
  { value: 'waiter', label: 'Garson' },
  { value: 'kitchen', label: 'Mutfak' },
];

export function AddStaffForm() {
  const [error, formAction, isPending] = useActionState(createStaffAction, null);
  const [open, setOpen] = useState(false);

  if (!open) {
    return (
      <button
        onClick={() => setOpen(true)}
        className="btn btn-primary text-sm"
      >
        + Personel Ekle
      </button>
    );
  }

  return (
    <div className="card p-5">
      <div className="mb-4 flex items-center justify-between">
        <h3 className="font-semibold">Yeni Personel</h3>
        <button onClick={() => setOpen(false)} className="text-[var(--muted)] hover:text-[var(--ink)]">✕</button>
      </div>

      {error && (
        <div className="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
          {error}
        </div>
      )}

      <form action={formAction} className="grid gap-3 sm:grid-cols-2">
        <div className="grid gap-1">
          <label className="text-xs font-medium text-[var(--muted)]">Ad Soyad *</label>
          <input name="name" required className="input" placeholder="Ahmet Yılmaz" />
        </div>
        <div className="grid gap-1">
          <label className="text-xs font-medium text-[var(--muted)]">E-posta *</label>
          <input name="email" type="email" required className="input" placeholder="ahmet@restoran.com" />
        </div>
        <div className="grid gap-1">
          <label className="text-xs font-medium text-[var(--muted)]">Telefon</label>
          <input name="phone" type="tel" className="input" placeholder="+90 555 000 0000" />
        </div>
        <div className="grid gap-1">
          <label className="text-xs font-medium text-[var(--muted)]">Rol</label>
          <select name="role" className="input">
            {roleOptions.map((r) => (
              <option key={r.value} value={r.value}>{r.label}</option>
            ))}
          </select>
        </div>
        <div className="grid gap-1">
          <label className="text-xs font-medium text-[var(--muted)]">Şifre *</label>
          <input name="password" type="password" required minLength={8} className="input" placeholder="En az 8 karakter" />
        </div>

        <div className="sm:col-span-2 flex justify-end gap-2">
          <button type="button" onClick={() => setOpen(false)} className="btn">İptal</button>
          <button type="submit" disabled={isPending} className="btn btn-primary disabled:opacity-60">
            {isPending ? 'Ekleniyor…' : 'Ekle'}
          </button>
        </div>
      </form>
    </div>
  );
}

export function DeleteStaffButton({ staffId }: { staffId: number }) {
  const [pending, setPending] = useState(false);

  async function handleDelete() {
    if (!confirm('Bu personeli silmek istediğinize emin misiniz?')) return;
    setPending(true);
    const res = await deleteStaffAction(staffId);
    if (res.error) {
      alert(res.error);
      setPending(false);
    }
  }

  return (
    <button
      disabled={pending}
      onClick={handleDelete}
      className="rounded-md px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50 disabled:opacity-50"
    >
      {pending ? '…' : 'Sil'}
    </button>
  );
}
