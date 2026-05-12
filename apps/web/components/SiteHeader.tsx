import Image from 'next/image';
import Link from 'next/link';
import { getAuthToken } from '@/lib/session';
import { logoutAction } from '@/app/giris/actions';

const nav = [
  ['Ürün', '/#modules'],
  ['Çözümler', '/restoranlar'],
  ['Fiyatlandırma', '/fiyatlandirma'],
  ['Kaynaklar', 'https://ininia.com/blog'],
  ['Hakkımızda', 'https://ininia.com/hakkimizda'],
];

export async function SiteHeader() {
  const token = await getAuthToken();
  const isLoggedIn = !!token;

  return (
    <header className="sticky top-0 z-50 border-b border-[var(--border)] bg-[rgba(250,250,247,0.9)] backdrop-blur">
      <div className="container flex min-h-16 items-center justify-between gap-4 py-3">
        <Link href="/" className="flex items-center gap-2 font-bold">
          <Image src="/cekirdex/brand-logo.png" alt="" width={36} height={36} className="rounded-xl" priority />
          <span>Çekirdex</span>
        </Link>
        <nav className="hidden items-center gap-7 text-sm font-medium text-[var(--muted)] md:flex">
          {nav.map(([label, href]) => (
            <Link key={href} href={href}>
              {label}
            </Link>
          ))}
        </nav>
        <div className="flex items-center gap-3">
          {isLoggedIn ? (
            <>
              <Link
                href="/panel"
                className="hidden items-center gap-1.5 text-sm font-semibold md:inline-flex"
              >
                <span className="inline-block h-2 w-2 rounded-full bg-emerald-500" />
                Panel
              </Link>
              <form action={logoutAction}>
                <button
                  type="submit"
                  className="hidden rounded-xl border border-[var(--border)] px-3 py-1.5 text-sm font-medium text-[var(--muted)] transition hover:border-red-200 hover:text-red-600 md:inline-flex"
                >
                  Çıkış yap
                </button>
              </form>
            </>
          ) : (
            <>
              <Link href="/giris" className="hidden text-sm font-semibold md:inline-flex">
                Giriş yap
              </Link>
              <Link href="/iletisim" className="btn btn-primary">
                Demo talep et
              </Link>
            </>
          )}
        </div>
      </div>
    </header>
  );
}
