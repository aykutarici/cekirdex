'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { logoutAction } from '@/app/giris/actions';

const navItems = [
  { href: '/panel', label: 'Gösterge paneli', icon: '⬛' },
  { href: '/panel/siparisler', label: 'Siparişler', icon: '🧾' },
  { href: '/panel/menu', label: 'Menü', icon: '🍽' },
  { href: '/panel/masalar', label: 'Masalar', icon: '🪑' },
  { href: '/panel/rezervasyonlar', label: 'Rezervasyonlar', icon: '📅' },
  { href: '/panel/personel', label: 'Personel', icon: '👥' },
  { href: '/panel/ayarlar', label: 'Ayarlar', icon: '⚙️' },
];

export default function PanelLayout({ children }: { children: React.ReactNode }) {
  const pathname = usePathname();

  return (
    <div className="flex min-h-[calc(100vh-4rem)]">
      {/* Sidebar */}
      <aside className="hidden w-56 shrink-0 border-r border-[var(--border)] bg-[var(--bg-soft)] md:flex md:flex-col">
        <nav className="flex flex-col gap-0.5 p-3 pt-4">
          {navItems.map(({ href, label, icon }) => {
            const active = href === '/panel' ? pathname === '/panel' : pathname.startsWith(href);
            return (
              <Link
                key={href}
                href={href}
                className={[
                  'flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                  active
                    ? 'bg-[var(--ink)] text-white'
                    : 'text-[var(--muted)] hover:bg-[var(--border)] hover:text-[var(--ink)]',
                ].join(' ')}
              >
                <span className="text-base leading-none">{icon}</span>
                {label}
              </Link>
            );
          })}
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
        {navItems.slice(0, 5).map(({ href, label, icon }) => {
          const active = href === '/panel' ? pathname === '/panel' : pathname.startsWith(href);
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
              {label}
            </Link>
          );
        })}
      </div>

      {/* Main content */}
      <main className="flex-1 overflow-auto pb-20 md:pb-0">
        {children}
      </main>
    </div>
  );
}
