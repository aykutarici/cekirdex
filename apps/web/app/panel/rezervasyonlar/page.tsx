import Link from 'next/link';
import { redirect } from 'next/navigation';
import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';
import {
  confirmReservationAction,
  cancelReservationAction,
  noShowReservationAction,
  completeReservationAction,
} from './actions';

type Reservation = {
  id: number;
  public_code: string;
  contact_name: string;
  contact_phone: string | null;
  contact_email: string | null;
  party_size: number;
  reserved_for: string;
  status: string;
  status_label?: string;
  note: string | null;
  created_at: string;
};

const statusConfig: Record<string, { label: string; cls: string }> = {
  pending:   { label: 'Onay bekliyor', cls: 'bg-yellow-100 text-yellow-800' },
  confirmed: { label: 'Onaylandı',     cls: 'bg-green-100 text-green-800' },
  cancelled: { label: 'İptal',         cls: 'bg-red-100 text-red-700' },
  completed: { label: 'Tamamlandı',    cls: 'bg-gray-100 text-gray-600' },
  no_show:   { label: 'Gelmedi',       cls: 'bg-orange-100 text-orange-700' },
};

const filterTabs = [
  { key: 'all',       label: 'Tümü' },
  { key: 'pending',   label: 'Bekliyor' },
  { key: 'confirmed', label: 'Onaylı' },
  { key: 'cancelled', label: 'İptal' },
];

export default async function ReservationsPage({
  searchParams,
}: {
  searchParams: Promise<{ filter?: string }>;
}) {
  const { filter } = await searchParams;
  const activeFilter = filter ?? 'all';

  const token = await getAuthToken();
  if (!token) redirect('/giris');

  let reservations: Reservation[] = [];
  try {
    const data = await apiFetch<{ data: Reservation[] }>('/api/v1/panel/reservations', { token });
    reservations = data.data;
  } catch {
    redirect('/giris');
  }

  const filtered =
    activeFilter === 'all'
      ? reservations
      : reservations.filter((r) => r.status === activeFilter);

  return (
    <div className="p-6">
      <h1 className="text-2xl font-semibold tracking-tight">Rezervasyonlar</h1>
      <p className="mt-1 text-sm text-[var(--muted)]">{reservations.length} rezervasyon</p>

      {/* Filter tabs */}
      <div className="mt-4 flex gap-2 border-b border-[var(--border)]">
        {filterTabs.map(({ key, label }) => {
          const count = key === 'all' ? reservations.length : reservations.filter((r) => r.status === key).length;
          return (
            <a
              key={key}
              href={key === 'all' ? '/panel/rezervasyonlar' : `/panel/rezervasyonlar?filter=${key}`}
              className={[
                'flex items-center gap-1.5 border-b-2 px-3 py-2 text-sm font-medium transition-colors',
                activeFilter === key
                  ? 'border-[var(--ink)] text-[var(--ink)]'
                  : 'border-transparent text-[var(--muted)] hover:text-[var(--ink)]',
              ].join(' ')}
            >
              {label}
              <span className={`rounded-full px-1.5 py-0.5 text-[10px] font-bold ${activeFilter === key ? 'bg-[var(--ink)] text-white' : 'bg-[var(--bg-soft)]'}`}>
                {count}
              </span>
            </a>
          );
        })}
      </div>

      <div className="card mt-4 overflow-hidden p-0">
        {filtered.length === 0 ? (
          <p className="p-8 text-center text-[var(--muted)]">Bu filtrede rezervasyon yok.</p>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="border-b border-[var(--border)] bg-[var(--bg-soft)]">
                  <th className="px-4 py-3 text-left font-semibold">Misafir</th>
                  <th className="px-4 py-3 text-left font-semibold">Tarih / Saat</th>
                  <th className="px-4 py-3 text-left font-semibold">Kişi</th>
                  <th className="px-4 py-3 text-left font-semibold">Durum</th>
                  <th className="px-4 py-3 text-left font-semibold">Not</th>
                  <th className="px-4 py-3 text-right font-semibold">İşlemler</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-[var(--border)]">
                {filtered.map((r) => {
                  const cfg = statusConfig[r.status] ?? { label: r.status, cls: 'bg-gray-100 text-gray-600' };
                  const confirmAction = confirmReservationAction.bind(null, r.id);
                  const cancelAction = cancelReservationAction.bind(null, r.id);
                  const noShowAction = noShowReservationAction.bind(null, r.id);
                  const completeAction = completeReservationAction.bind(null, r.id);

                  return (
                    <tr key={r.id} className="hover:bg-[var(--bg-soft)]">
                      <td className="px-4 py-3">
                        <Link href={`/panel/rezervasyonlar/${r.id}`} className="hover:underline">
                          <p className="font-medium">{r.contact_name}</p>
                          <p className="text-xs text-[var(--muted)]">{r.contact_phone ?? r.contact_email ?? '—'}</p>
                        </Link>
                      </td>
                      <td className="px-4 py-3 text-[var(--muted)]">
                        {new Date(r.reserved_for).toLocaleString('tr-TR', { dateStyle: 'medium', timeStyle: 'short' })}
                      </td>
                      <td className="px-4 py-3">{r.party_size} kişi</td>
                      <td className="px-4 py-3">
                        <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${cfg.cls}`}>
                          {cfg.label}
                        </span>
                      </td>
                      <td className="max-w-[160px] truncate px-4 py-3 text-xs text-[var(--muted)]">
                        {r.note ?? '—'}
                      </td>
                      <td className="px-4 py-3 text-right">
                        <div className="flex justify-end gap-1">
                          {r.status === 'pending' && (
                            <form action={confirmAction}>
                              <button type="submit" className="rounded-lg bg-green-500 px-2.5 py-1 text-xs font-semibold text-white hover:bg-green-600">
                                Onayla
                              </button>
                            </form>
                          )}
                          {r.status === 'confirmed' && (
                            <>
                              <form action={completeAction}>
                                <button type="submit" className="rounded-lg bg-gray-500 px-2.5 py-1 text-xs font-semibold text-white hover:bg-gray-600">
                                  Tamamlandı
                                </button>
                              </form>
                              <form action={noShowAction}>
                                <button type="submit" className="rounded-lg bg-orange-400 px-2.5 py-1 text-xs font-semibold text-white hover:bg-orange-500">
                                  Gelmedi
                                </button>
                              </form>
                            </>
                          )}
                          {!['cancelled', 'completed', 'no_show'].includes(r.status) && (
                            <form action={cancelAction}>
                              <button type="submit" className="rounded-lg border border-red-200 px-2.5 py-1 text-xs font-semibold text-red-600 hover:bg-red-50">
                                İptal
                              </button>
                            </form>
                          )}
                        </div>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}
