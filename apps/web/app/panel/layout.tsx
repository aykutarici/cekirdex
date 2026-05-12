'use client';

import Link from 'next/link';
import { useEffect, useState } from 'react';
import { usePathname } from 'next/navigation';
import { logoutAction } from '@/app/giris/actions';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import {
  faGauge,
  faFire,
  faBell,
  faPersonWalkingArrowRight,
  faCreditCard,
  faReceipt,
  faBoxOpen,
  faCalendarDays,
  faPhoneVolume,
  faUtensils,
  faChair,
  faStar,
  faUsers,
  faGear,
  faRightFromBracket,
  type IconDefinition,
} from '@fortawesome/free-solid-svg-icons';

type NavItem = { href: string; label: string; icon: IconDefinition };

const operasyonItems: NavItem[] = [
  { href: '/panel',                 label: 'Gösterge paneli',  icon: faGauge },
  { href: '/panel/kds',             label: 'KDS (Mutfak)',     icon: faFire },
  { href: '/panel/garson',          label: 'Garson Ekranı',    icon: faPersonWalkingArrowRight },
  { href: '/panel/servis',          label: 'Servis ekranı',    icon: faBell },
  { href: '/panel/hesaplar',        label: 'Hesaplar',         icon: faCreditCard },
  { href: '/panel/siparisler',      label: 'Siparişler',       icon: faReceipt },
  { href: '/panel/paket',           label: 'Paket siparişler', icon: faBoxOpen },
  { href: '/panel/rezervasyonlar',  label: 'Rezervasyonlar',   icon: faCalendarDays },
  { href: '/panel/cagrilar',        label: 'Çağrılar',         icon: faPhoneVolume },
];

const yonetimItems: NavItem[] = [
  { href: '/panel/menu',      label: 'Menü',        icon: faUtensils },
  { href: '/panel/masalar',   label: 'Masalar & QR', icon: faChair },
  { href: '/panel/yorumlar',  label: 'Yorumlar',    icon: faStar },
  { href: '/panel/personel',  label: 'Personel',    icon: faUsers },
  { href: '/panel/ayarlar',   label: 'Ayarlar',     icon: faGear },
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
        'relative flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
        active
          ? 'bg-[var(--ink)] text-white'
          : 'text-[var(--muted)] hover:bg-[var(--border)] hover:text-[var(--ink)]',
      ].join(' ')}
    >
      <FontAwesomeIcon icon={icon} className="w-4 shrink-0" />
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
              className="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-[var(--muted)] transition-colors hover:bg-[var(--border)] hover:text-[var(--ink)]"
            >
              <FontAwesomeIcon icon={faRightFromBracket} className="w-4 shrink-0" />
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
                'flex flex-1 flex-col items-center gap-1 py-2.5 text-[10px] font-medium',
                active ? 'text-[var(--ink)]' : 'text-[var(--muted)]',
              ].join(' ')}
            >
              <FontAwesomeIcon icon={icon} className="h-5 w-5" />
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
