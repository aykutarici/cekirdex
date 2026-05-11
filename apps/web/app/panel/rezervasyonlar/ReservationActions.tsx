'use client';

import { useTransition } from 'react';
import {
  confirmReservationPanelAction,
  cancelReservationPanelAction,
  noShowReservationPanelAction,
  completeReservationPanelAction,
} from './actions';

type Props = { id: number; status: string };

export default function ReservationActions({ id, status }: Props) {
  const [pending, startTransition] = useTransition();

  function run(action: (id: number) => Promise<{ error?: string }>) {
    startTransition(async () => {
      const res = await action(id);
      if (res.error) alert(res.error);
    });
  }

  return (
    <div className="flex flex-wrap gap-1">
      {status === 'pending' && (
        <>
          <button
            disabled={pending}
            onClick={() => run(confirmReservationPanelAction)}
            className="rounded-md bg-green-600 px-2 py-1 text-xs font-medium text-white hover:bg-green-700 disabled:opacity-50"
          >
            Onayla
          </button>
          <button
            disabled={pending}
            onClick={() => run(cancelReservationPanelAction)}
            className="rounded-md bg-red-100 px-2 py-1 text-xs font-medium text-red-700 hover:bg-red-200 disabled:opacity-50"
          >
            İptal
          </button>
        </>
      )}
      {status === 'confirmed' && (
        <>
          <button
            disabled={pending}
            onClick={() => run(completeReservationPanelAction)}
            className="rounded-md bg-blue-600 px-2 py-1 text-xs font-medium text-white hover:bg-blue-700 disabled:opacity-50"
          >
            Tamamlandı
          </button>
          <button
            disabled={pending}
            onClick={() => run(noShowReservationPanelAction)}
            className="rounded-md bg-orange-100 px-2 py-1 text-xs font-medium text-orange-700 hover:bg-orange-200 disabled:opacity-50"
          >
            Gelmedi
          </button>
          <button
            disabled={pending}
            onClick={() => run(cancelReservationPanelAction)}
            className="rounded-md bg-red-100 px-2 py-1 text-xs font-medium text-red-700 hover:bg-red-200 disabled:opacity-50"
          >
            İptal
          </button>
        </>
      )}
    </div>
  );
}
