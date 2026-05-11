import { redirect } from 'next/navigation';
import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';
import NewProductForm from './NewProductForm';

type Category = { id: number; name: string };

export default async function YeniUrunPage() {
  const token = await getAuthToken();
  if (!token) redirect('/giris');

  let categories: Category[] = [];
  try {
    const data = await apiFetch<{ categories: Array<Category & { products: unknown[] }> }>('/api/v1/panel/menu', { token });
    categories = data.categories.map(({ id, name }) => ({ id, name }));
  } catch {
    redirect('/giris');
  }

  return (
    <div className="p-6">
      <NewProductForm categories={categories} />
    </div>
  );
}
