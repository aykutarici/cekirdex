'use server';

import { redirect } from 'next/navigation';
import { apiFetch } from '@/lib/api';

export async function contactAction(
  _prevState: string | null,
  formData: FormData,
): Promise<string | null> {
  try {
    await apiFetch('/api/v1/contact', {
      method: 'POST',
      body: JSON.stringify({
        name: String(formData.get('name') ?? ''),
        email: String(formData.get('email') ?? ''),
        restaurant_name: String(formData.get('restaurant_name') ?? ''),
        message: String(formData.get('message') ?? ''),
        website: String(formData.get('website') ?? ''),
      }),
    });
  } catch (err) {
    return err instanceof Error ? err.message : 'Mesaj gönderilirken bir hata oluştu.';
  }

  redirect('/iletisim?sent=1');
}
