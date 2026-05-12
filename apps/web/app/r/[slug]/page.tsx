import { apiFetch } from '@/lib/api';
import { ReservationForm } from './ReservationForm';
import { TakeawayOrderForm } from './TakeawayOrderForm';

type Product = { id: number; name: string; effective_price: number };
type Category = { id: number; name: string; products: Product[] };

type RestaurantResponse = {
  restaurant: {
    name: string;
    description?: string | null;
    city?: string | null;
    accepts_takeaway?: boolean;
    accepts_delivery?: boolean;
  };
  categories?: Category[];
};

export default async function RestaurantPublicPage({
  params,
}: {
  params: Promise<{ slug: string }>;
}) {
  const { slug } = await params;
  let data: RestaurantResponse | null = null;

  try {
    data = await apiFetch<RestaurantResponse>(`/api/v1/restaurants/${slug}`);
  } catch {
    data = null;
  }

  const acceptsTakeaway = data?.restaurant.accepts_takeaway ?? false;
  const acceptsDelivery = data?.restaurant.accepts_delivery ?? false;
  const showTakeaway = acceptsTakeaway || acceptsDelivery;

  return (
    <main className="py-20">
      <div className="container">
        <p className="eyebrow">Restoran</p>
        <h1 className="mt-3 text-4xl font-semibold tracking-[-0.04em] md:text-6xl">
          {data?.restaurant.name ?? 'Restoran bulunamadı'}
        </h1>
        <p className="mt-6 max-w-2xl text-lg leading-8 text-[var(--muted)]">
          {data?.restaurant.description ??
            'Restoran public API yanıtı hazır olduğunda bu sayfa canlı verilerle dolacak.'}
        </p>

        {data ? <ReservationForm slug={slug} /> : null}

        {data && showTakeaway && (data.categories?.length ?? 0) > 0 ? (
          <TakeawayOrderForm
            slug={slug}
            categories={data.categories ?? []}
            acceptsTakeaway={acceptsTakeaway}
            acceptsDelivery={acceptsDelivery}
          />
        ) : null}
      </div>
    </main>
  );
}
