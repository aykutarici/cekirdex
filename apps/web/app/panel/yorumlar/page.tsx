import { redirect } from 'next/navigation';
import { apiFetch } from '@/lib/api';
import { getAuthToken } from '@/lib/session';
import { toggleReviewVisibilityAction, deleteReviewAction } from './actions';

type Review = {
  id: number;
  product_name: string;
  customer_name: string | null;
  rating: number;
  content: string | null;
  is_visible: boolean;
  created_at: string;
};

function Stars({ rating }: { rating: number }) {
  return (
    <span className="text-base leading-none">
      {Array.from({ length: 5 }).map((_, i) => (
        <span key={i} className={i < rating ? 'text-yellow-400' : 'text-gray-200'}>★</span>
      ))}
    </span>
  );
}

export default async function YorumlarPage() {
  const token = await getAuthToken();
  if (!token) redirect('/giris');

  let reviews: Review[] = [];
  try {
    const data = await apiFetch<{ data: Review[] }>('/api/v1/panel/reviews', { token });
    reviews = data.data;
  } catch {
    redirect('/giris');
  }

  return (
    <div className="p-6">
      <h1 className="text-2xl font-semibold tracking-tight">⭐ Yorumlar</h1>
      <p className="mt-1 text-sm text-[var(--muted)]">{reviews.length} yorum</p>

      {reviews.length === 0 ? (
        <div className="mt-8 rounded-2xl border border-dashed border-[var(--border)] p-12 text-center text-[var(--muted)]">
          <p className="text-4xl">⭐</p>
          <p className="mt-2 font-medium">Henüz yorum yok</p>
        </div>
      ) : (
        <div className="card mt-6 overflow-hidden p-0">
          <div className="divide-y divide-[var(--border)]">
            {reviews.map((review) => {
              const toggleAction = toggleReviewVisibilityAction.bind(null, review.id);
              const deleteAction = deleteReviewAction.bind(null, review.id);

              return (
                <div key={review.id} className={`px-5 py-4 ${!review.is_visible ? 'opacity-60' : ''}`}>
                  <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                      <div className="flex items-center gap-2">
                        <p className="font-semibold">{review.product_name}</p>
                        {!review.is_visible && (
                          <span className="rounded-full bg-red-100 px-2 py-0.5 text-xs text-red-700">Gizli</span>
                        )}
                      </div>
                      <div className="mt-1 flex items-center gap-2">
                        <Stars rating={review.rating} />
                        <span className="text-xs text-[var(--muted)]">
                          {review.customer_name ?? 'Anonim'} ·{' '}
                          {new Date(review.created_at).toLocaleDateString('tr-TR')}
                        </span>
                      </div>
                    </div>
                    <div className="flex gap-2">
                      <form action={toggleAction}>
                        <button
                          type="submit"
                          className="rounded-lg border border-[var(--border)] px-3 py-1.5 text-xs font-semibold transition hover:bg-[var(--bg-soft)]"
                        >
                          {review.is_visible ? 'Gizle' : 'Göster'}
                        </button>
                      </form>
                      <form action={deleteAction}>
                        <button
                          type="submit"
                          className="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50"
                          onClick={(e) => {
                            if (!confirm('Bu yorumu silmek istediğinize emin misiniz?')) {
                              e.preventDefault();
                            }
                          }}
                        >
                          Sil
                        </button>
                      </form>
                    </div>
                  </div>
                  {review.content && (
                    <p className="mt-2 text-sm text-[var(--muted)]">{review.content}</p>
                  )}
                </div>
              );
            })}
          </div>
        </div>
      )}
    </div>
  );
}
