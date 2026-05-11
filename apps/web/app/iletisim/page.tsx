import { ContactForm } from './ContactForm';

export default async function ContactPage({ searchParams }: { searchParams: Promise<{ sent?: string }> }) {
  const params = await searchParams;

  return (
    <main className="py-20">
      <div className="container grid gap-10 md:grid-cols-[0.8fr_1fr]">
        <div>
          <p className="eyebrow">İletişim</p>
          <h1 className="mt-3 text-4xl font-semibold tracking-[-0.04em] md:text-6xl">Demo talep edin</h1>
          <p className="mt-6 text-lg leading-8 text-[var(--muted)]">
            Ekibimiz işletmenizin operasyon modeline göre QR ödeme, sadakat ve raporlama akışını anlatsın.
          </p>
        </div>
        <div>
          {params.sent === '1' ? (
            <div className="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
              Talebiniz alındı, ekibimiz size dönecek.
            </div>
          ) : null}
          <ContactForm />
        </div>
      </div>
    </main>
  );
}
