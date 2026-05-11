import Image from 'next/image';
import Link from 'next/link';

const modules = [
  ['QR’dan ödeme', 'Masa QR ile güvenli tahsilat; hesap bölme ve dijital ödeme seçenekleri.'],
  ['Sadakat yönetimi', 'Çekirdek puanı ve kampanyalarla misafiri geri getirin.'],
  ['Operasyon yönetimi', 'Mutfak ekranı, masa akışı ve garson süreçleri tek panelde.'],
  ['Analiz & raporlama', 'Şube ve kanal bazlı performans; geliri ve sadakat etkisini ölçün.'],
];

const metrics = [
  ['500+', 'Restoran & Otel'],
  ['2M+', 'Mutlu Misafir'],
  ['₺2.5B+', 'İşlem Hacmi'],
  ['98%', 'Müşteri Memnuniyeti'],
];

const faqs = [
  ['Çekirdex hangi işletmeler için uygun?', 'Restoran, kafe, otel outlet ve çoklu şubeli işletmeler için uygundur.'],
  ['QR ödeme nasıl çalışıyor?', 'Misafir masa QR kodunu tarar; menü ve ödeme tarayıcı üzerinden tamamlanır.'],
  ['Sadakat sistemi nasıl entegre ediliyor?', 'Çekirdek puanı işletme kurallarına göre tanımlanır ve tek cüzdanda takip edilir.'],
  ['Kurulum süreci ne kadar sürüyor?', 'Temel kurulum dakikalar içinde tamamlanır; ekibimiz şube ve menü yapınıza göre destek verir.'],
  ['Verilerim güvende mi?', 'Ödeme verileri güvenli altyapı üzerinden işlenir; kart bilgileri işletmenizde saklanmaz.'],
  ['Fiyatlandırma nasıl yapılıyor?', 'Sabit aylık yerine işlem bazlı şeffaf komisyon modeli sunulur.'],
];

export default function HomePage() {
  return (
    <main>
      <section className="relative overflow-hidden border-b border-[var(--border)] py-20 md:py-28">
        <div className="absolute inset-0 bg-[radial-gradient(circle_at_80%_10%,rgba(242,106,61,0.08),transparent_36%),radial-gradient(circle_at_8%_90%,rgba(137,167,146,0.08),transparent_28%)]" />
        <div className="container relative grid items-center gap-12 md:grid-cols-[1fr_0.95fr]">
          <div>
            <h1 className="max-w-xl text-4xl font-semibold leading-tight tracking-[-0.04em] md:text-6xl">
              Restoran ve oteller için <span className="text-[var(--primary)]">sadakatin</span> yeni çekirdeği
            </h1>
            <p className="mt-6 max-w-2xl text-lg leading-8 text-[var(--muted)]">
              Çekirdex; QR’dan ödeme, sadakat puanı, operasyon yönetimi ve raporlamayı tek platformda toplar.
              Misafir deneyimini hızlandırır, işletmenizin gelirini artırır.
            </p>
            <div className="mt-8 flex flex-wrap gap-3">
              <Link href="/iletisim" className="btn btn-primary">
                Demo talep et
              </Link>
              <Link href="#modules" className="btn btn-ghost">
                Nasıl çalışır?
              </Link>
            </div>
          </div>
          <Image src="/cekirdex/hero-ecosystem.png" alt="" width={1024} height={768} priority />
        </div>
        <div className="container relative mt-12 flex flex-wrap gap-3 border-t border-[var(--border)] pt-7">
          {['QR’dan Ödeme', 'Sadakat & Puan', 'Raporlama', 'Operasyon Yönetimi'].map((chip) => (
            <span key={chip} className="rounded-full border border-[var(--border)] bg-white px-4 py-2 text-sm font-semibold text-[var(--muted)]">
              {chip}
            </span>
          ))}
        </div>
      </section>

      <section className="py-14">
        <div className="container text-center">
          <p className="text-base font-semibold text-[var(--muted)]">
            Türkiye’nin önde gelen restoran ve otelleri Çekirdex ile büyüyor.
          </p>
          <div className="mt-7 flex flex-wrap justify-center gap-3 text-xs font-bold tracking-[0.12em] text-gray-400">
            {['BIGCHEFS', 'KAHVE DÜNYASI', 'MADO', 'divan', 'NUSR-ET', 'THE MARMARA'].map((brand) => (
              <span key={brand} className="rounded-xl border border-[var(--border)] bg-white px-5 py-3">
                {brand}
              </span>
            ))}
          </div>
        </div>
      </section>

      <section id="modules" className="border-y border-[var(--border)] bg-[var(--bg-soft)] py-20">
        <div className="container">
          <p className="eyebrow">Her şey tek platformda</p>
          <h2 className="mt-3 text-3xl font-semibold tracking-[-0.03em] md:text-5xl">İhtiyacınız olan tüm modüller</h2>
          <div className="mt-10 grid gap-4 md:grid-cols-4">
            {modules.map(([title, text]) => (
              <article key={title} className="card p-6">
                <div className="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-[rgba(242,106,61,0.1)] text-xl text-[var(--primary)]">•</div>
                <h3 className="font-bold">{title}</h3>
                <p className="mt-3 text-sm leading-6 text-[var(--muted)]">{text}</p>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section className="py-20">
        <div className="container grid items-center gap-12 md:grid-cols-2">
          <div className="rounded-3xl bg-[var(--dark)] p-6 text-white shadow-2xl">
            <div className="mb-5 flex gap-2">
              <span className="h-3 w-3 rounded-full bg-zinc-600" />
              <span className="h-3 w-3 rounded-full bg-zinc-600" />
              <span className="h-3 w-3 rounded-full bg-zinc-600" />
            </div>
            <div className="grid gap-3 md:grid-cols-2">
              <div className="rounded-2xl bg-white/5 p-4">
                <p className="text-xs text-zinc-400">Günlük gelir</p>
                <strong className="text-2xl">₺128.400</strong>
              </div>
              <div className="rounded-2xl bg-white/5 p-4">
                <p className="text-xs text-zinc-400">Misafir</p>
                <strong className="text-2xl">842</strong>
              </div>
            </div>
            <div className="mt-5 h-44 rounded-2xl bg-[linear-gradient(180deg,rgba(242,106,61,0.3),rgba(242,106,61,0.02))]" />
          </div>
          <div>
            <p className="eyebrow">Canlı veriler</p>
            <h2 className="mt-3 text-3xl font-semibold tracking-[-0.03em] md:text-5xl">İşinizi anlık verilerle yönetin</h2>
            <ul className="mt-6 space-y-3 text-[var(--muted)]">
              <li>Tüm şubelerinizi tek ekrandan yönetin.</li>
              <li>Gerçek zamanlı satış, sipariş ve masa durumunu takip edin.</li>
              <li>Performansı artıran akıllı içgörüler alın.</li>
            </ul>
            <Link href="/restoranlar" className="mt-7 inline-flex font-bold text-[var(--primary)]">
              Tüm özellikleri keşfet
            </Link>
          </div>
        </div>
      </section>

      <section className="py-6">
        <div className="container card grid gap-6 bg-[var(--bg-soft)] p-7 md:grid-cols-4">
          {metrics.map(([value, label]) => (
            <div key={label}>
              <strong className="text-2xl font-extrabold">{value}</strong>
              <p className="text-sm text-[var(--muted)]">{label}</p>
            </div>
          ))}
        </div>
      </section>

      <section className="border-y border-[var(--border)] bg-[var(--bg-soft)] py-20">
        <div className="container grid items-center gap-12 md:grid-cols-2">
          <div>
            <p className="eyebrow">Çekirdek ekosistemi</p>
            <h2 className="mt-3 text-3xl font-semibold tracking-[-0.03em] md:text-5xl">Sadakat puanları sınırları kaldırır</h2>
            <p className="mt-5 text-lg leading-8 text-[var(--muted)]">
              Çekirdex ekosistemindeki tüm restoran ve otellerde kazanılan ve harcanabilen puanlarla misafirin cebinde,
              deneyimi her yerde.
            </p>
          </div>
          <Image src="/cekirdex/loyalty-ecosystem.png" alt="" width={1200} height={675} />
        </div>
      </section>

      <section className="py-20">
        <div className="container card bg-[linear-gradient(145deg,rgba(242,106,61,0.08),#fff)] p-10 text-center">
          <h2 className="mx-auto max-w-2xl text-3xl font-semibold tracking-[-0.03em]">
            Çekirdex ile operasyonunuzu büyütmeye hazır mısınız?
          </h2>
          <p className="mx-auto mt-4 max-w-xl text-[var(--muted)]">
            Hemen demo talep edin, ekibimiz size özel çözümleri anlatsın.
          </p>
          <Link href="/iletisim" className="btn btn-primary mt-7">
            Demo talep et
          </Link>
        </div>
      </section>

      <section className="pb-20">
        <div className="container">
          <h2 className="text-center text-3xl font-semibold tracking-[-0.03em]">Merak ettikleriniz</h2>
          <div className="mt-10 grid gap-3 md:grid-cols-2">
            {faqs.map(([question, answer]) => (
              <details key={question} className="card p-5">
                <summary className="cursor-pointer font-bold">{question}</summary>
                <p className="mt-3 text-sm leading-6 text-[var(--muted)]">{answer}</p>
              </details>
            ))}
          </div>
        </div>
      </section>
    </main>
  );
}
