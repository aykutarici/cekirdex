import { redirect } from 'next/navigation';
import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';
import { AddStaffForm, DeleteStaffButton } from './StaffActions';

type Staff = {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  role: string;
  is_active: boolean;
  last_login_at: string | null;
};

const roleLabels: Record<string, string> = {
  super_admin: 'Süper admin',
  owner:   'Sahip',
  manager: 'Yönetici',
  waiter:  'Garson',
  kitchen: 'Mutfak',
};

export default async function StaffPage() {
  const token = await getAuthToken();
  if (!token) redirect('/giris');

  let staff: Staff[] = [];
  try {
    const data = await apiFetch<{ data: Staff[] }>('/api/v1/panel/staff', { token });
    staff = data.data;
  } catch {
    redirect('/giris');
  }

  return (
    <div className="p-6">
      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 className="text-2xl font-semibold tracking-tight">Personel</h1>
          <p className="mt-1 text-sm text-[var(--muted)]">{staff.length} çalışan</p>
        </div>
        <AddStaffForm />
      </div>

      <div className="card mt-6 overflow-hidden p-0">
        {staff.length === 0 ? (
          <p className="p-8 text-center text-[var(--muted)]">Henüz personel eklenmemiş.</p>
        ) : (
          <div className="divide-y divide-[var(--border)]">
            {staff.map((s) => (
              <div key={s.id} className="flex items-center gap-4 px-5 py-4">
                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[var(--bg-soft)] text-sm font-bold text-[var(--ink)]">
                  {s.name.charAt(0).toUpperCase()}
                </div>
                <div className="min-w-0 flex-1">
                  <div className="flex items-center gap-2">
                    <p className="font-medium">{s.name}</p>
                    {!s.is_active && (
                      <span className="rounded-full bg-red-100 px-2 py-0.5 text-xs text-red-700">Pasif</span>
                    )}
                  </div>
                  <p className="text-xs text-[var(--muted)]">{s.email}</p>
                  {s.phone && <p className="text-xs text-[var(--muted)]">{s.phone}</p>}
                </div>
                <div className="shrink-0 text-right">
                  <span className="rounded-full bg-[var(--bg-soft)] px-2.5 py-1 text-xs font-medium">
                    {roleLabels[s.role] ?? s.role}
                  </span>
                  {s.last_login_at && (
                    <p className="mt-1 text-xs text-[var(--muted)]">
                      Son giriş: {new Date(s.last_login_at).toLocaleDateString('tr-TR')}
                    </p>
                  )}
                </div>
                <DeleteStaffButton staffId={s.id} />
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
