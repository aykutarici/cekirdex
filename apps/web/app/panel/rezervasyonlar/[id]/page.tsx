import Link from 'next/link';
import { redirect, notFound } from 'next/navigation';
import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';
import {
  confirmReservationDetailAction,
  cancelReservationDetailAction,
  noShowReservationDetailAction,
  completeReservationDetailAction,
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
  admin_note: string | null;
  table: string | null;
  created_at: string;
};

const statusConfig: Record<string, { label: string; cls: string }> = {
  pending:   { label: 'Onay bekliyor', cls: 'bg-yellow-100 text-yellow-800' },
  confirmed: { label: 'Onaylandı',     cls: 'bg-green-100 text-green-800' },
  cancelled: { label: 'İptal',         cls: 'bg-red-100 text-red-700' },
  completed: { label: 'Tamamlandı',    cls: 'bg-gray-100 text-gray-600' },
  no_show:   { label: 'Gelmedi',       cls: 'bg-orange-100 text-orange-700' },
};

export default async function ReservationDetailPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const numId = parseInt(id, 10);
  if (isNaN(numId)) notFound();

  const token = await getAuthToken();
  if (!token) redirect('/giris');

  let reservation: Reservation;
  try {
    const data = await apiFetch<{ data: Reservation }>(
      `/api/v1/panel/reservations/${numId}`,
      { token },
    );
    reservation = data.data;
  } catch {
    notFound();
  }

  const cfg = statusConfig[reservation.status] ?? { label: reservation.status, cls: 'bg-gray-100 text-gray-600' };

  const confirmAction = confirmReservationDetailAction.bind(null, numId);
  const cancelAction = cancelReservationDetailAction.bind(null, numId);
  const noShowAction = noShowReservationDetailAction.bind(null, numId);
  const completeAction = completeReservationDetailAction.bind(null, numId);

  return (
    <div className="p-6">
      <Link
        href="/panel/rezervasyonlar"
        className="text-sm text-[var(--muted)] hover:text-[var(--ink)]"
      >
        ← Rezervasyonlar
      </Link>

      <div className="mt-4 flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 className="text-2xl font-semibold tracking-tight">{reservation.contact_name}</h1>
          <p className="mt-1 font-mono text-sm text-[var(--muted)]">{reservation.public_code}</p>
        </div>
        <span className={`rounded-full px-3 py-1 text-sm font-semibold ${cfg.cls}`}>
          {cfg.label}
        </span>
      </div>

      <div className="card mt-6 p-6">
        <dl className="grid gap-x-8 gap-y-5 sm:grid-cols-2">
          <div>
            <dt className="text-xs font-medium text-[var(--muted)]">Misafir adı</dt>
            <dd className="mt-1 font-semibold">{reservation.contact_name}</dd>
          </div>
          <div>
            <dt className="text-xs font-medium text-[var(--muted)]">Telefon</dt>
            <dd className="mt-1">{reservation.contact_phone ?? '—'}</dd>
          </div>
          <div>
            <dt className="text-xs font-medium text-[var(--muted)]">E-posta</dt>
            <dd className="mt-1">{reservation.contact_email ?? '—'}</dd>
          </div>
          <div>
            <dt className="text-xs font-medium text-[var(--muted)]">Kişi sayısı</dt>
            <dd className="mt-1 font-semibold">{reservation.party_size} kişi</dd>
          </div>
          <div>
            <dt className="text-xs font-medium text-[var(--muted)]">Rezervasyon tarihi</dt>
            <dd className="mt-1 font-semibold">
              {new Date(reservation.reserved_for).toLocaleString('tr-TR', {
                dateStyle: 'long',
                timeStyle: 'short',
              })}
            </dd>
          </div>
          <div>
            <dt className="text-xs font-medium text-[var(--muted)]">Durum</dt>
            <dd className="mt-1">
              <span className={`rounded-full px-2 py-0.5 text-xs font-semibold ${cfg.cls}`}>
                {cfg.label}
              </span>
            </dd>
          </div>
          <div>
            <dt className="text-xs font-medium text-[var(--muted)]">Oluşturulma tarihi</dt>
            <dd className="mt-1 text-sm text-[var(--muted)]">
              {new Date(reservation.created_at).toLocaleString('tr-TR', {
                dateStyle: 'medium',
                timeStyle: 'short',
              })}
            </dd>
          </div>
          <div>
            <dt className="text-xs font-medium text-[var(--muted)]">Rezervasyon kodu</dt>
            <dd className="mt-1 font-mono text-sm">{reservation.public_code}</dd>
          </div>
          {reservation.note && (
            <div className="sm:col-span-2">
              <dt className="text-xs font-medium text-[var(--muted)]">Not</dt>
              <dd className="mt-1 text-sm">{reservation.note}</dd>
            </div>
          )}
        </dl>
      </div>

      {/* İşlem butonları */}
      <div className="mt-6 flex flex-wrap gap-2">
        {reservation.status === 'pending' && (
          <form action={confirmAction}>
            <button
              type="submit"
              className="rounded-xl bg-green-500 px-4 py-2 text-sm font-semibold text-white hover:bg-green-600"
            >
              ✓ Onayla
            </button>
          </form>
        )}
        {reservation.status === 'confirmed' && (
          <>
            <form action={completeAction}>
              <button
                type="submit"
                className="rounded-xl bg-gray-500 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-600"
              >
                Tamamlandı
              </button>
            </form>
            <form action={noShowAction}>
              <button
                type="submit"
                className="rounded-xl bg-orange-400 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-500"
              >
                Gelmedi
              </button>
            </form>
          </>
        )}
        {!['cancelled', 'completed', 'no_show'].includes(reservation.status) && (
          <form action={cancelAction}>
            <button
              type="submit"
              className="rounded-xl border border-red-200 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50"
            >
              İptal Et
            </button>
          </form>
        )}
      </div>
    </div>
  );
}
