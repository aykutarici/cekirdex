import { redirect } from 'next/navigation';
import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';

type Product = {
  id: number;
  name: string;
  description: string | null;
  price: number;
  is_active: boolean;
  is_in_stock: boolean;
  is_popular: boolean;
};

type Category = {
  id: number;
  name: string;
  is_active: boolean;
  products: Product[];
};

export default async function MenuPage() {
  const token = await getAuthToken();
  if (!token) redirect('/giris');

  let categories: Category[] = [];
  try {
    const data = await apiFetch<{ categories: Category[] }>('/api/v1/panel/menu', { token });
    categories = data.categories;
  } catch {
    redirect('/giris');
  }

  const totalProducts = categories.reduce((s, c) => s + c.products.length, 0);
  const activeProducts = categories.reduce((s, c) => s + c.products.filter((p) => p.is_active).length, 0);

  return (
    <div className="p-6">
      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 className="text-2xl font-semibold tracking-tight">Menü</h1>
          <p className="mt-1 text-sm text-[var(--muted)]">
            {categories.length} kategori · {activeProducts}/{totalProducts} aktif ürün
          </p>
        </div>
      </div>

      <div className="mt-6 space-y-6">
        {categories.length === 0 ? (
          <p className="rounded-xl border border-dashed border-[var(--border)] p-8 text-center text-[var(--muted)]">
            Henüz kategori yok.
          </p>
        ) : (
          categories.map((cat) => (
            <div key={cat.id} className="card overflow-hidden p-0">
              <div className="flex items-center justify-between border-b border-[var(--border)] bg-[var(--bg-soft)] px-5 py-3">
                <div className="flex items-center gap-2">
                  <h2 className="font-semibold">{cat.name}</h2>
                  {!cat.is_active && (
                    <span className="rounded-full bg-red-100 px-2 py-0.5 text-xs text-red-700">Pasif</span>
                  )}
                </div>
                <span className="text-xs text-[var(--muted)]">{cat.products.length} ürün</span>
              </div>

              {cat.products.length === 0 ? (
                <p className="px-5 py-4 text-sm text-[var(--muted)]">Bu kategoride ürün yok.</p>
              ) : (
                <div className="divide-y divide-[var(--border)]">
                  {cat.products.map((p) => (
                    <div key={p.id} className="flex items-center gap-4 px-5 py-3">
                      <div className="min-w-0 flex-1">
                        <div className="flex items-center gap-2">
                          <p className="font-medium">{p.name}</p>
                          {p.is_popular && (
                            <span className="rounded-full bg-orange-100 px-2 py-0.5 text-xs text-orange-700">Popüler</span>
                          )}
                          {!p.is_active && (
                            <span className="rounded-full bg-red-100 px-2 py-0.5 text-xs text-red-700">Pasif</span>
                          )}
                          {!p.is_in_stock && (
                            <span className="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">Stok yok</span>
                          )}
                        </div>
                        {p.description && (
                          <p className="mt-0.5 truncate text-xs text-[var(--muted)]">{p.description}</p>
                        )}
                      </div>
                      <p className="shrink-0 font-semibold">{Number(p.price).toLocaleString('tr-TR')} ₺</p>
                    </div>
                  ))}
                </div>
              )}
            </div>
          ))
        )}
      </div>
    </div>
  );
}
