'use client';

import { useActionState } from 'react';
import { cancelReservationAction } from './actions';

export function CancelReservationForm({ publicCode }: { publicCode: string }) {
  const [error, formAction, isPending] = useActionState(cancelReservationAction, null);

  return (
    <div>
      <form action={formAction}>
        <input type="hidden" name="public_code" value={publicCode} />
        <button className="btn btn-secondary" type="submit" disabled={isPending}>
          {isPending ? 'İptal ediliyor…' : 'Rezervasyonu iptal et'}
        </button>
      </form>
      {error ? (
        <p className="mt-2 text-sm text-red-700">{error}</p>
      ) : null}
    </div>
  );
}
