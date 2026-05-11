import { redirect } from 'next/navigation';
import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';

type Reservation = {
  id: number;
  public_code: string;
  guest_name: string;
  guest_phone: string | null;
  guest_email: string | null;
  party_size: number;
  reserved_at: string;
  status: string;
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

export default async function ReservationsPage() {
  const token = await getAuthToken();
  if (!token) redirect('/giris');

  let reservations: Reservation[] = [];
  try {
    const data = await apiFetch<{ data: Reservation[] }>('/api/v1/panel/reservations', { token });
    reservations = data.data;
  } catch {
    redirect('/giris');
  }

  return (
    <div className="p-6">
      <h1 className="text-2xl font-semibold tracking-tight">Rezervasyonlar</h1>
      <p className="mt-1 text-sm text-[var(--muted)]">{reservations.length} rezervasyon</p>

      <div className="card mt-6 overflow-hidden p-0">
        {reservations.length === 0 ? (
          <p className="p-8 text-center text-[var(--muted)]">Henüz rezervasyon yok.</p>
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
                </tr>
              </thead>
              <tbody className="divide-y divide-[var(--border)]">
                {reservations.map((r) => {
                  const cfg = statusConfig[r.status] ?? { label: r.status, cls: 'bg-gray-100 text-gray-600' };
                  return (
                    <tr key={r.id} className="hover:bg-[var(--bg-soft)]">
                      <td className="px-4 py-3">
                        <p className="font-medium">{r.guest_name}</p>
                        <p className="text-xs text-[var(--muted)]">{r.guest_phone ?? r.guest_email ?? '—'}</p>
                      </td>
                      <td className="px-4 py-3 text-[var(--muted)]">
                        {new Date(r.reserved_at).toLocaleString('tr-TR', { dateStyle: 'medium', timeStyle: 'short' })}
                      </td>
                      <td className="px-4 py-3">{r.party_size} kişi</td>
                      <td className="px-4 py-3">
                        <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${cfg.cls}`}>
                          {cfg.label}
                        </span>
                      </td>
                      <td className="max-w-[200px] truncate px-4 py-3 text-xs text-[var(--muted)]">
                        {r.note ?? '—'}
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
