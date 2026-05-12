'use client';

import { useActionState, useEffect, useState } from 'react';
import { addReviewAction } from './actions';

type Review = {
  id: number;
  rating: number;
  content: string | null;
  user_name: string;
  created_at: string;
};

function StarRating({ value, onChange }: { value: number; onChange: (v: number) => void }) {
  return (
    <div className="flex gap-1">
      {[1, 2, 3, 4, 5].map((star) => (
        <button
          key={star}
          type="button"
          onClick={() => onChange(star)}
          className={`text-2xl transition ${star <= value ? 'opacity-100' : 'opacity-30'}`}
        >
          ⭐
        </button>
      ))}
    </div>
  );
}

export function ReviewModal({
  qrToken,
  productId,
  productName,
  isLoggedIn,
  onClose,
}: {
  qrToken: string;
  productId: number;
  productName: string;
  isLoggedIn: boolean;
  onClose: () => void;
}) {
  const [reviews, setReviews] = useState<Review[]>([]);
  const [loadingReviews, setLoadingReviews] = useState(true);
  const [rating, setRating] = useState(5);

  const reviewAction = addReviewAction.bind(null, qrToken, productId);
  const [error, formAction, pending] = useActionState(reviewAction, null);

  useEffect(() => {
    fetch(`/api/customer/${qrToken}/reviews/${productId}`)
      .then((r) => (r.ok ? r.json() : { reviews: [] }))
      .then((d) => setReviews(d.reviews ?? []))
      .catch(() => setReviews([]))
      .finally(() => setLoadingReviews(false));
  }, [qrToken, productId]);

  return (
    <div className="fixed inset-0 z-50 flex items-end justify-center bg-black/40 px-4 pb-0 sm:items-center sm:pb-4">
      <div className="w-full max-w-lg rounded-t-3xl bg-white p-6 shadow-2xl sm:rounded-3xl">
        <div className="flex items-start justify-between">
          <div>
            <h3 className="text-lg font-bold">{productName}</h3>
            <p className="text-sm text-[var(--muted)]">Yorumlar</p>
          </div>
          <button onClick={onClose} className="rounded-full p-1 text-[var(--muted)] hover:bg-[var(--bg-soft)]">
            ✕
          </button>
        </div>

        {/* Yorum listesi */}
        <div className="mt-4 max-h-52 space-y-3 overflow-y-auto">
          {loadingReviews ? (
            <p className="text-sm text-[var(--muted)]">Yorumlar yükleniyor…</p>
          ) : reviews.length === 0 ? (
            <p className="text-sm text-[var(--muted)]">Henüz yorum yok.</p>
          ) : (
            reviews.map((r) => (
              <div key={r.id} className="rounded-xl bg-[var(--bg-soft)] p-3">
                <div className="flex items-center justify-between">
                  <span className="text-xs font-semibold">{r.user_name}</span>
                  <span className="text-xs text-amber-500">{'⭐'.repeat(r.rating)}</span>
                </div>
                {r.content && <p className="mt-1 text-sm text-[var(--muted)]">{r.content}</p>}
                <p className="mt-1 text-[10px] text-[var(--muted)]">
                  {new Date(r.created_at).toLocaleDateString('tr-TR')}
                </p>
              </div>
            ))
          )}
        </div>

        {/* Yorum formu */}
        {isLoggedIn ? (
          <div className="mt-5 border-t border-[var(--border)] pt-4">
            <p className="mb-3 text-sm font-semibold">Yorum Yap</p>
            {error && (
              <p className="mb-3 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700">{error}</p>
            )}
            <form action={formAction} className="space-y-3">
              <input type="hidden" name="rating" value={rating} />
              <StarRating value={rating} onChange={setRating} />
              <textarea
                name="comment"
                rows={3}
                placeholder="Ürün hakkında düşünceleriniz…"
                className="input resize-none text-sm"
              />
              <button
                type="submit"
                disabled={pending}
                className="w-full rounded-xl bg-[var(--primary)] py-2.5 text-sm font-bold text-white disabled:opacity-50"
              >
                {pending ? 'Gönderiliyor…' : 'Yorumu Gönder'}
              </button>
            </form>
          </div>
        ) : (
          <p className="mt-4 rounded-xl bg-[var(--bg-soft)] p-3 text-center text-xs text-[var(--muted)]">
            Yorum yapmak için giriş yapın
          </p>
        )}
      </div>
    </div>
  );
}
