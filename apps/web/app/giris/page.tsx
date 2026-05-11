import Link from 'next/link';

export default function LoginPage() {
  return (
    <main className="py-20">
      <div className="container max-w-3xl">
        <p className="eyebrow">Giriş</p>
        <h1 className="mt-3 text-4xl font-semibold tracking-[-0.04em] md:text-6xl">Hangi hesaba giriş yapmak istiyorsunuz?</h1>
        <div className="mt-8 grid gap-4 md:grid-cols-2">
          <Link href="/giris/restoran" className="card p-6 transition hover:-translate-y-1 hover:shadow-xl">
            <p className="text-sm font-semibold text-[var(--brand)]">Restoran paneli</p>
            <h2 className="mt-3 text-2xl font-semibold">Restoran / İninia personeli</h2>
            <p className="mt-3 text-sm leading-6 text-[var(--muted)]">
              İşletme sahibi, yönetici, garson, mutfak ekibi veya İninia admin hesabınızla devam edin.
            </p>
            <span className="btn btn-primary mt-6 inline-flex">Restoran girişi</span>
          </Link>
          <Link href="/giris/misafir" className="card p-6 transition hover:-translate-y-1 hover:shadow-xl">
            <p className="text-sm font-semibold text-[var(--brand)]">Misafir hesabı</p>
            <h2 className="mt-3 text-2xl font-semibold">Son kullanıcı / misafir</h2>
            <p className="mt-3 text-sm leading-6 text-[var(--muted)]">
              Sipariş takibi, sadakat, favoriler ve rezervasyonlar için misafir hesabınızla devam edin.
            </p>
            <span className="btn btn-secondary mt-6 inline-flex">Misafir girişi</span>
          </Link>
        </div>
      </div>
    </main>
  );
}
