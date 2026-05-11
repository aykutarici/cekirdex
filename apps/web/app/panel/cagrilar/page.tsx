'use client';

import { useEffect, useState, useTransition } from 'react';
import { respondCallAction, closeCallAction } from './actions';

type Call = {
  id: number;
  table: string;
  type: string;
  type_label: string;
  status: string;
  created_at: string;
};

const typeBadge: Record<string, string> = {
  waiter:   'bg-blue-100 text-blue-800',
  napkin:   'bg-gray-100 text-gray-700',
  water:    'bg-cyan-100 text-cyan-800',
  ketchup:  'bg-red-100 text-red-700',
  bill:     'bg-orange-100 text-orange-700',
  other:    'bg-purple-100 text-purple-700',
};

function timeAgo(dateStr: string): string {
  const diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000);
  if (diff < 60) return `${diff}s önce`;
  if (diff < 3600) return `${Math.floor(diff / 60)} dk önce`;
  return `${Math.floor(diff / 3600)} sa önce`;
}

function CallCard({ call, onUpdate }: { call: Call; onUpdate: () => void }) {
  const [pending, startTransition] = useTransition();
  const [error, setError] = useState<string | null>(null);
  const badgeCls = typeBadge[call.type] ?? typeBadge.other;
  const isResponded = call.status === 'responded';

  function respond() {
    setError(null);
    startTransition(async () => {
      const res = await respondCallAction(call.id);
      if (res.error) setError(res.error);
      else onUpdate();
    });
  }

  function close() {
    setError(null);
    startTransition(async () => {
      const res = await closeCallAction(call.id);
      if (res.error) setError(res.error);
      else onUpdate();
    });
  }

  return (
    <div className={`card p-4 ${isResponded ? 'opacity-70' : ''}`}>
      <div className="flex items-start justify-between gap-2">
        <div>
          <p className="font-semibold">📍 {call.table}</p>
          <p className="mt-0.5 text-xs text-[var(--muted)]">{timeAgo(call.created_at)}</p>
        </div>
        <span className={`rounded-full px-2 py-0.5 text-xs font-semibold ${badgeCls}`}>
          {call.type_label}
        </span>
      </div>

      {error && <p className="mt-2 text-xs text-red-600">{error}</p>}

      <div className="mt-3 flex gap-2">
        {!isResponded && (
          <button
            onClick={respond}
            disabled={pending}
            className="flex-1 rounded-xl bg-blue-500 py-2 text-sm font-bold text-white transition hover:bg-blue-600 disabled:opacity-50"
          >
            Yanıtla
          </button>
        )}
        <button
          onClick={close}
          disabled={pending}
          className="flex-1 rounded-xl border border-gray-200 bg-white py-2 text-sm font-bold text-gray-600 transition hover:bg-gray-50 disabled:opacity-50"
        >
          Kapat
        </button>
      </div>
    </div>
  );
}

export default function CagrilarPage() {
  const [calls, setCalls] = useState<Call[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  async function load() {
    try {
      const res = await fetch('/api/panel/calls');
      if (!res.ok) throw new Error('Yüklenemedi');
      const data: { data: Call[] } = await res.json();
      setCalls(data.data ?? []);
      setError(null);
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Hata');
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    load();
    const id = setInterval(load, 8_000);
    return () => clearInterval(id);
  }, []);

  const pending = calls.filter((c) => c.status === 'pending');
  const responded = calls.filter((c) => c.status === 'responded');

  return (
    <div className="p-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold tracking-tight">🔔 Çağrılar</h1>
          <p className="mt-1 text-sm text-[var(--muted)]">Masa çağrıları ve talepleri</p>
        </div>
        <div className="flex items-center gap-2">
          {loading && <span className="text-sm text-gray-400">Yükleniyor…</span>}
          {pending.length > 0 && (
            <span className="animate-pulse rounded-full bg-red-500 px-3 py-1 text-sm font-bold text-white">
              {pending.length} bekliyor
            </span>
          )}
        </div>
      </div>

      {error && (
        <div className="mt-4 rounded-xl bg-red-50 p-4 text-sm text-red-700">{error}</div>
      )}

      {calls.length === 0 && !loading ? (
        <div className="mt-8 rounded-2xl border border-dashed border-[var(--border)] p-12 text-center text-[var(--muted)]">
          <p className="text-4xl">🔕</p>
          <p className="mt-2 font-medium">Aktif çağrı yok</p>
        </div>
      ) : (
        <div className="mt-6 space-y-6">
          {pending.length > 0 && (
            <section>
              <p className="eyebrow mb-3">Bekleyen ({pending.length})</p>
              <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                {pending.map((c) => <CallCard key={c.id} call={c} onUpdate={load} />)}
              </div>
            </section>
          )}

          {responded.length > 0 && (
            <section>
              <p className="eyebrow mb-3">Yanıtlandı ({responded.length})</p>
              <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                {responded.map((c) => <CallCard key={c.id} call={c} onUpdate={load} />)}
              </div>
            </section>
          )}
        </div>
      )}
    </div>
  );
}
