import Link from 'next/link';

type SimplePageProps = {
  eyebrow?: string;
  title: string;
  description: string;
  children?: React.ReactNode;
};

export function SimplePage({ eyebrow, title, description, children }: SimplePageProps) {
  return (
    <main className="py-20">
      <div className="container">
        {eyebrow ? <p className="eyebrow">{eyebrow}</p> : null}
        <h1 className="mt-3 max-w-3xl text-4xl font-semibold tracking-[-0.04em] md:text-6xl">{title}</h1>
        <p className="mt-6 max-w-2xl text-lg leading-8 text-[var(--muted)]">{description}</p>
        <div className="mt-8 flex flex-wrap gap-3">
          <Link href="/iletisim" className="btn btn-primary">
            Demo talep et
          </Link>
          <Link href="/" className="btn btn-ghost">
            Anasayfa
          </Link>
        </div>
        {children ? <div className="mt-12">{children}</div> : null}
      </div>
    </main>
  );
}
