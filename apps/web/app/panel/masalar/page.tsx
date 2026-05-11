import { redirect } from 'next/navigation';
import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';

type Table = {
  id: number;
  name: string;
  code: string;
  qr_token: string;
  capacity: number;
  is_active: boolean;
};

export default async function TablesPage() {
  const token = await getAuthToken();
  if (!token) redirect('/giris');

  let tables: Table[] = [];
  try {
    const data = await apiFetch<{ data: Table[] }>('/api/v1/panel/tables', { token });
    tables = data.data;
  } catch {
    redirect('/giris');
  }

  const apiBaseUrl = process.env.NEXT_PUBLIC_API_URL ?? 'http://localhost:8080';

  return (
    <div className="p-6">
      <h1 className="text-2xl font-semibold tracking-tight">Masalar</h1>
      <p className="mt-1 text-sm text-[var(--muted)]">
        {tables.length} masa · {tables.filter((t) => t.is_active).length} aktif
      </p>

      <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        {tables.length === 0 ? (
          <p className="col-span-full rounded-xl border border-dashed border-[var(--border)] p-8 text-center text-[var(--muted)]">
            Henüz masa yok.
          </p>
        ) : (
          tables.map((table) => (
            <div key={table.id} className={`card p-5 ${!table.is_active ? 'opacity-60' : ''}`}>
              <div className="flex items-start justify-between">
                <div>
                  <h3 className="font-semibold">{table.name}</h3>
                  <p className="mt-0.5 text-xs text-[var(--muted)]">Kod: {table.code} · {table.capacity} kişilik</p>
                </div>
                {!table.is_active && (
                  <span className="rounded-full bg-red-100 px-2 py-0.5 text-xs text-red-700">Pasif</span>
                )}
              </div>

              <div className="mt-4 rounded-lg bg-[var(--bg-soft)] p-3">
                <p className="text-xs text-[var(--muted)]">QR Menü bağlantısı</p>
                <a
                  href={`/m/${table.qr_token}`}
                  target="_blank"
                  rel="noreferrer"
                  className="mt-1 block truncate text-xs font-medium text-[var(--ink)] hover:underline"
                >
                  /m/{table.qr_token}
                </a>
              </div>

              <a
                href={`${apiBaseUrl}/m/${table.qr_token}`}
                target="_blank"
                rel="noreferrer"
                className="btn mt-3 block w-full text-center text-sm"
              >
                QR'ı aç
              </a>
            </div>
          ))
        )}
      </div>
    </div>
  );
}
