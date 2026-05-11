'use client';

import Link from 'next/link';
import { useEffect, useState } from 'react';
import { usePathname } from 'next/navigation';
import { logoutAction } from '@/app/giris/actions';

type NavItem = { href: string; label: string; icon: string };

const operasyonItems: NavItem[] = [
  { href: '/panel',            label: 'Gösterge paneli', icon: '⬛' },
  { href: '/panel/kds',        label: 'KDS (Mutfak)',    icon: '🔥' },
  { href: '/panel/servis',     label: 'Servis ekranı',   icon: '🛎' },
  { href: '/panel/hesaplar',   label: 'Hesaplar',        icon: '💳' },
  { href: '/panel/siparisler', label: 'Siparişler',      icon: '🧾' },
  { href: '/panel/paket',      label: 'Paket siparişler',icon: '📦' },
  { href: '/panel/rezervasyonlar', label: 'Rezervasyonlar', icon: '📅' },
  { href: '/panel/cagrilar',   label: 'Çağrılar',        icon: '🔔' },
];

const yonetimItems: NavItem[] = [
  { href: '/panel/menu',       label: 'Menü',            icon: '🍽' },
  { href: '/panel/masalar',    label: 'Masalar & QR',    icon: '🪑' },
  { href: '/panel/yorumlar',   label: 'Yorumlar',        icon: '⭐' },
  { href: '/panel/personel',   label: 'Personel',        icon: '👥' },
  { href: '/panel/ayarlar',    label: 'Ayarlar',         icon: '⚙️' },
];

type BadgeCounts = { calls: number; orders: number };

function NavLink({
  href,
  label,
  icon,
  badge,
  active,
}: NavItem & { badge?: number; active: boolean }) {
  return (
    <Link
      href={href}
      className={[
        'relative flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
        active
          ? 'bg-[var(--ink)] text-white'
          : 'text-[var(--muted)] hover:bg-[var(--border)] hover:text-[var(--ink)]',
      ].join(' ')}
    >
      <span className="text-base leading-none">{icon}</span>
      {label}
      {badge && badge > 0 ? (
        <span className="ml-auto flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">
          {badge > 99 ? '99+' : badge}
        </span>
      ) : null}
    </Link>
  );
}

export default function PanelLayout({ children }: { children: React.ReactNode }) {
  const pathname = usePathname();
  const [badges, setBadges] = useState<BadgeCounts>({ calls: 0, orders: 0 });

  useEffect(() => {
    let alive = true;

    async function fetchBadges() {
      try {
        const [callsRes, ordersRes] = await Promise.all([
          fetch('/api/panel/calls'),
          fetch('/api/panel/kds'),
        ]);
        const callsData = callsRes.ok ? await callsRes.json() : { data: [] };
        const ordersData = ordersRes.ok ? await ordersRes.json() : { data: [] };
        if (alive) {
          setBadges({
            calls: Array.isArray(callsData.data) ? callsData.data.length : 0,
            orders: Array.isArray(ordersData.data)
              ? ordersData.data.filter((o: { status: string }) => o.status === 'new').length
              : 0,
          });
        }
      } catch {
        // polling hataları sessizce geç
      }
    }

    fetchBadges();
    const id = setInterval(fetchBadges, 10_000);
    return () => {
      alive = false;
      clearInterval(id);
    };
  }, []);

  function isActive(href: string) {
    return href === '/panel' ? pathname === '/panel' : pathname.startsWith(href);
  }

  const mobileItems = [
    operasyonItems[0],
    operasyonItems[1],
    operasyonItems[2],
    operasyonItems[4],
    yonetimItems[0],
  ];

  return (
    <div className="flex min-h-[calc(100vh-4rem)]">
      {/* Sidebar */}
      <aside className="hidden w-60 shrink-0 border-r border-[var(--border)] bg-[var(--bg-soft)] md:flex md:flex-col">
        <nav className="flex flex-col gap-0.5 overflow-y-auto p-3 pt-4">
          {/* Operasyon */}
          <p className="mb-1 mt-1 px-3 text-[10px] font-bold uppercase tracking-widest text-[var(--muted)]">
            Operasyon
          </p>
          {operasyonItems.map((item) => (
            <NavLink
              key={item.href}
              {...item}
              active={isActive(item.href)}
              badge={
                item.href === '/panel/cagrilar'
                  ? badges.calls
                  : item.href === '/panel/kds'
                    ? badges.orders
                    : undefined
              }
            />
          ))}

          {/* Yönetim */}
          <p className="mb-1 mt-4 px-3 text-[10px] font-bold uppercase tracking-widest text-[var(--muted)]">
            Yönetim
          </p>
          {yonetimItems.map((item) => (
            <NavLink key={item.href} {...item} active={isActive(item.href)} />
          ))}
        </nav>

        <div className="mt-auto border-t border-[var(--border)] p-3">
          <form action={logoutAction}>
            <button
              type="submit"
              className="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-[var(--muted)] transition-colors hover:bg-[var(--border)] hover:text-[var(--ink)]"
            >
              <span className="text-base leading-none">🚪</span>
              Çıkış yap
            </button>
          </form>
        </div>
      </aside>

      {/* Mobile tab bar */}
      <div className="fixed bottom-0 left-0 right-0 z-40 flex border-t border-[var(--border)] bg-white md:hidden">
        {mobileItems.map(({ href, label, icon }) => {
          const active = isActive(href);
          return (
            <Link
              key={href}
              href={href}
              className={[
                'flex flex-1 flex-col items-center gap-0.5 py-2 text-[10px] font-medium',
                active ? 'text-[var(--ink)]' : 'text-[var(--muted)]',
              ].join(' ')}
            >
              <span className="text-xl leading-none">{icon}</span>
              {label.split(' ')[0]}
            </Link>
          );
        })}
      </div>

      {/* Main content */}
      <main className="flex-1 overflow-auto pb-20 md:pb-0">{children}</main>
    </div>
  );
}
