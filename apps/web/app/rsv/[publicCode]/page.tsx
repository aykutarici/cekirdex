import { apiFetch } from '@/lib/api';
import { CancelReservationForm } from './CancelReservationForm';

type ReservationResponse = {
  reservation: {
    public_code: string;
    contact_name: string;
    reserved_for: string;
    party_size: number;
    status: string;
    status_label: string;
    restaurant?: { name: string } | null;
  };
};

export default async function ReservationTrackPage({ params }: { params: Promise<{ publicCode: string }> }) {
  const { publicCode } = await params;

  let data: ReservationResponse | null = null;
  try {
    data = await apiFetch<ReservationResponse>(`/api/v1/reservations/${publicCode}`);
  } catch {
    return (
      <main className="py-20">
        <div className="container max-w-3xl">
          <p className="eyebrow">Rezervasyon takip</p>
          <h1 className="mt-3 text-4xl font-semibold tracking-[-0.04em]">Rezervasyon bulunamadı</h1>
          <p className="mt-4 text-[var(--muted)]">Bu kod ile bir rezervasyon bulunamadı ya da süresi geçmiş olabilir.</p>
        </div>
      </main>
    );
  }

  const reservedFor = new Intl.DateTimeFormat('tr-TR', { dateStyle: 'long', timeStyle: 'short' }).format(new Date(data.reservation.reserved_for));

  return (
    <main className="py-20">
      <div className="container max-w-3xl">
        <p className="eyebrow">Rezervasyon takip</p>
        <h1 className="mt-3 text-4xl font-semibold tracking-[-0.04em]">{data.reservation.restaurant?.name ?? 'Rezervasyon'}</h1>
        <div className="card mt-8 grid gap-4 p-6">
          <p><strong>Kod:</strong> {data.reservation.public_code}</p>
          <p><strong>Misafir:</strong> {data.reservation.contact_name}</p>
          <p><strong>Tarih:</strong> {reservedFor}</p>
          <p><strong>Kişi:</strong> {data.reservation.party_size}</p>
          <p><strong>Durum:</strong> {data.reservation.status_label}</p>
          {!['cancelled', 'completed', 'no_show'].includes(data.reservation.status) ? (
            <CancelReservationForm publicCode={data.reservation.public_code} />
          ) : null}
        </div>
      </div>
    </main>
  );
}
