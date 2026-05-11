'use server';

import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';
import { redirect } from 'next/navigation';
import { revalidatePath } from 'next/cache';

export async function toggleStockAction(productId: number, _fd: FormData): Promise<void> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');
  await apiFetch(`/api/v1/panel/menu/products/${productId}/toggle-stock`, { method: 'POST', token });
  revalidatePath('/panel/menu');
}

export async function toggleActiveAction(productId: number, _fd: FormData): Promise<void> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');
  await apiFetch(`/api/v1/panel/menu/products/${productId}/toggle-active`, { method: 'POST', token });
  revalidatePath('/panel/menu');
}

export async function deleteCategoryAction(categoryId: number, _fd: FormData): Promise<void> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');
  await apiFetch(`/api/v1/panel/menu/categories/${categoryId}`, { method: 'DELETE', token });
  revalidatePath('/panel/menu');
}

export async function deleteProductAction(productId: number, _fd: FormData): Promise<void> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');
  await apiFetch(`/api/v1/panel/menu/products/${productId}`, { method: 'DELETE', token });
  revalidatePath('/panel/menu');
}

export async function createCategoryAction(
  _prevState: string | null,
  formData: FormData,
): Promise<string | null> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');
  try {
    await apiFetch('/api/v1/panel/menu/categories', {
      method: 'POST',
      token,
      body: JSON.stringify({
        name: String(formData.get('name') ?? ''),
        is_active: true,
      }),
    });
    revalidatePath('/panel/menu');
    redirect('/panel/menu');
  } catch (err) {
    return err instanceof Error ? err.message : 'Kategori oluşturulamadı.';
  }
}

export async function createProductAction(
  _prevState: string | null,
  formData: FormData,
): Promise<string | null> {
  const token = await getAuthToken();
  if (!token) redirect('/giris');
  try {
    await apiFetch('/api/v1/panel/menu/products', {
      method: 'POST',
      token,
      body: JSON.stringify({
        category_id: Number(formData.get('category_id')),
        name: String(formData.get('name') ?? ''),
        description: formData.get('description') ? String(formData.get('description')) : null,
        price: Number(formData.get('price')),
        is_active: formData.get('is_active') === 'on',
        is_in_stock: true,
      }),
    });
    revalidatePath('/panel/menu');
    redirect('/panel/menu');
  } catch (err) {
    return err instanceof Error ? err.message : 'Ürün oluşturulamadı.';
  }
}
