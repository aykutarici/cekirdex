import Image from 'next/image';
import Link from 'next/link';

const columns = [
  {
    title: 'Ürün',
    links: [
      ['Modüller', '/#modules'],
      ['Restoranlar için', '/restoranlar'],
      ['Misafirler için', '/musteriler'],
      ['Fiyatlandırma', '/fiyatlandirma'],
    ],
  },
  {
    title: 'Şirket',
    links: [
      ['İninia', 'https://ininia.com'],
      ['Hakkımızda', 'https://ininia.com/hakkimizda'],
      ['Blog', 'https://ininia.com/blog'],
    ],
  },
  {
    title: 'Yasal',
    links: [
      ['Gizlilik', '/gizlilik'],
      ['Kullanım koşulları', '/kullanim-kosullari'],
    ],
  },
  {
    title: 'İletişim',
    links: [
      ['Bize yazın', '/iletisim'],
      ['cekirdex@ininia.com', 'mailto:cekirdex@ininia.com'],
    ],
  },
];

export function SiteFooter() {
  return (
    <footer className="border-t border-[var(--border)] bg-[var(--bg-soft)]">
      <div className="container grid gap-10 py-14 md:grid-cols-[1.4fr_repeat(4,1fr)]">
        <div>
          <Link href="/" className="flex items-center gap-2 font-bold">
            <Image src="/cekirdex/brand-logo.png" alt="" width={38} height={38} className="rounded-xl" />
            <span>Çekirdex</span>
          </Link>
          <p className="mt-4 max-w-xs text-sm leading-6 text-[var(--muted)]">
            QR ödeme, sadakat ve operasyonu tek platformda birleştiren modern hospitality altyapısı.
          </p>
        </div>
        {columns.map((column) => (
          <div key={column.title}>
            <h3 className="text-sm font-bold">{column.title}</h3>
            <ul className="mt-4 space-y-2 text-sm text-[var(--muted)]">
              {column.links.map(([label, href]) => (
                <li key={href}>
                  <Link href={href}>{label}</Link>
                </li>
              ))}
            </ul>
          </div>
        ))}
      </div>
      <div className="border-t border-[var(--border)] py-5 text-center text-xs text-[var(--muted)]">
        © {new Date().getFullYear()} Çekirdex · Bir İninia ürünüdür.
      </div>
    </footer>
  );
}
