import { apiFetch } from '@/lib/api';
import { ReservationForm } from './ReservationForm';

type RestaurantResponse = {
  restaurant: {
    name: string;
    description?: string | null;
    city?: string | null;
  };
};

export default async function RestaurantPublicPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  let data: RestaurantResponse | null = null;

  try {
    data = await apiFetch<RestaurantResponse>(`/api/v1/restaurants/${slug}`);
  } catch {
    data = null;
  }

  return (
    <main className="py-20">
      <div className="container">
        <p className="eyebrow">Restoran</p>
        <h1 className="mt-3 text-4xl font-semibold tracking-[-0.04em] md:text-6xl">{data?.restaurant.name ?? 'Restoran bulunamadı'}</h1>
        <p className="mt-6 max-w-2xl text-lg leading-8 text-[var(--muted)]">
          {data?.restaurant.description ?? 'Restoran public API yanıtı hazır olduğunda bu sayfa canlı verilerle dolacak.'}
        </p>
        {data ? <ReservationForm slug={slug} /> : null}
      </div>
    </main>
  );
}
