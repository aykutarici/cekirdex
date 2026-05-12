'use client';

import { useEffect, useState } from 'react';

type Reactions = {
  likes: number;
  favorites: number;
  user_liked: boolean;
  user_favorited: boolean;
};

export function ProductActions({
  qrToken,
  productId,
  isLoggedIn,
  onReviewClick,
}: {
  qrToken: string;
  productId: number;
  isLoggedIn: boolean;
  onReviewClick: () => void;
}) {
  const [reactions, setReactions] = useState<Reactions | null>(null);
  const [liking, setLiking] = useState(false);
  const [favoriting, setFavoriting] = useState(false);

  useEffect(() => {
    fetch(`/api/customer/${qrToken}/reactions/${productId}`)
      .then((r) => (r.ok ? r.json() : null))
      .then((d) => d && setReactions(d))
      .catch(() => null);
  }, [qrToken, productId]);

  async function handleLike() {
    if (!isLoggedIn || liking) return;
    setLiking(true);
    try {
      const res = await fetch(`/api/customer/${qrToken}/reactions/${productId}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ type: 'like' }),
      });
      if (res.ok) {
        const updated: Reactions = await res.json();
        setReactions(updated);
      }
    } finally {
      setLiking(false);
    }
  }

  async function handleFavorite() {
    if (!isLoggedIn || favoriting) return;
    setFavoriting(true);
    try {
      const res = await fetch(`/api/customer/${qrToken}/reactions/${productId}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ type: 'favorite' }),
      });
      if (res.ok) {
        const updated: Reactions = await res.json();
        setReactions(updated);
      }
    } finally {
      setFavoriting(false);
    }
  }

  return (
    <div className="mt-2 flex items-center gap-3">
      <button
        onClick={handleLike}
        disabled={liking || !isLoggedIn}
        title={isLoggedIn ? 'Beğen' : 'Beğenmek için giriş yapın'}
        className={[
          'flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium transition',
          reactions?.user_liked
            ? 'bg-blue-100 text-blue-700'
            : 'bg-[var(--bg-soft)] text-[var(--muted)] hover:bg-blue-50',
          !isLoggedIn ? 'cursor-default opacity-70' : 'cursor-pointer',
        ].join(' ')}
      >
        👍 {reactions?.likes ?? 0}
      </button>

      <button
        onClick={handleFavorite}
        disabled={favoriting || !isLoggedIn}
        title={isLoggedIn ? 'Favorile' : 'Favorilere eklemek için giriş yapın'}
        className={[
          'flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium transition',
          reactions?.user_favorited
            ? 'bg-pink-100 text-pink-700'
            : 'bg-[var(--bg-soft)] text-[var(--muted)] hover:bg-pink-50',
          !isLoggedIn ? 'cursor-default opacity-70' : 'cursor-pointer',
        ].join(' ')}
      >
        ❤️ {reactions?.favorites ?? 0}
      </button>

      <button
        onClick={onReviewClick}
        className="flex items-center gap-1 rounded-full bg-[var(--bg-soft)] px-3 py-1 text-xs font-medium text-[var(--muted)] hover:bg-amber-50 hover:text-amber-700"
      >
        ⭐ Yorum
      </button>
    </div>
  );
}
