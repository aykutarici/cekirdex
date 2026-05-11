'use client';

import { useActionState } from 'react';
import { createTableAction } from './actions';

export default function NewTableForm() {
  const [error, formAction, pending] = useActionState(createTableAction, null);

  return (
    <form action={formAction} className="flex flex-wrap items-end gap-4">
      {error && (
        <div className="w-full rounded-xl bg-red-50 p-3 text-sm text-red-700">{error}</div>
      )}
      <div className="flex-1 min-w-36">
        <label className="mb-1 block text-sm font-medium">Masa adı *</label>
        <input
          type="text"
          name="name"
          required
          className="w-full rounded-xl border border-[var(--border)] bg-white px-4 py-2.5 text-sm outline-none focus:border-[var(--primary)]"
          placeholder="Örn: Masa 1"
        />
      </div>
      <div className="w-32">
        <label className="mb-1 block text-sm font-medium">Kapasite</label>
        <input
          type="number"
          name="capacity"
          min="1"
          max="50"
          defaultValue={4}
          className="w-full rounded-xl border border-[var(--border)] bg-white px-4 py-2.5 text-sm outline-none focus:border-[var(--primary)]"
        />
      </div>
      <button
        type="submit"
        disabled={pending}
        className="btn btn-primary text-sm disabled:opacity-50"
      >
        {pending ? 'Ekleniyor…' : '+ Masa Ekle'}
      </button>
    </form>
  );
}
