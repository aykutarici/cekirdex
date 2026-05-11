export default function AdminPage() {
  return (
    <main className="py-12">
      <div className="container">
        <p className="eyebrow">Ininia admin</p>
        <h1 className="mt-3 text-4xl font-semibold tracking-[-0.04em]">Sistem yönetimi</h1>
        <p className="mt-5 max-w-2xl text-[var(--muted)]">
          Ininia personeli için sistem admin ekranı Next.js içinde ayrıldı. Yetki kontrolü `system.admin` permission’ı ile yapılacak.
        </p>
      </div>
    </main>
  );
}
