# Çekirdex Mimari Rehberi

Bu dosya projeye yeni katılan geliştiricilerin frontend, backend, auth ve Docker düzenini hızlıca anlaması için tutulur.

## Uygulama Sınırları

- `apps/web`: Next.js App Router uygulamasıdır. Public pazarlama sayfaları, restoran sayfası, QR menü, auth formları ve panel giriş ekranları burada yaşar.
- Laravel root: API-only backend olarak çalışır. Route girişi `routes/api.php`, controllerlar `app/Http/Controllers/Api/V1` altındadır.
- `docker-compose.yml`: MySQL, Redis, Laravel FPM, backend Nginx, worker ve Next.js servislerini birlikte çalıştırır.

## Backend Düzeni

- API rotaları versiyonlu tutulur: `/api/v1/...`.
- Public müşteri endpointleri `PublicRestaurantController` içinde toplanır.
- Panel endpointleri `Api/V1/Panel/PanelController` içindedir ve `api.actor` + `api.permission` middlewareleriyle korunur.
- Auth endpointleri `Api/V1/Auth/AuthController` içindedir. Staff/admin ve guest tokenları aynı bearer mekanizmasıyla taşınır.
- Role/permission modelleri `app/Cekirdex/Models` altında, yetki çözümü `PermissionService` içindedir.
- Yeni API eklenecekse önce route, sonra controller payload kontratı, ardından Next tarafındaki typed fetch/action eklenmelidir.

## Frontend Düzeni

- API çağrıları `apps/web/lib/api.ts` üzerinden yapılır.
- Auth tokenı server tarafında httpOnly cookie olarak `apps/web/lib/session.ts` ile yönetilir.
- Mutasyonlar Next server action olarak ilgili route klasöründe tutulur: `app/giris/actions.ts`, `app/m/[qrToken]/actions.ts` gibi.
- Public data-fetching sayfaları server component olarak kalmalı, browser tokenına ihtiyaç duyulmayan çağrılar client component'e taşınmamalıdır.
- Panel gibi token isteyen sayfalar `getAuthToken()` ile cookie okur ve `apiFetch(..., { token })` kullanır.

## Bağlı Ana Akışlar

- Restoran kayıt: Next `/kayit` → `POST /api/v1/auth/staff/register` → token cookie → `/panel`
- Giriş seçimi: Next `/giris` → restoran ve misafir giriş sayfalarına yönlendirir.
- Personel giriş: Next `/giris/restoran` → `POST /api/v1/auth/staff/login` → token cookie → `/panel`
- Misafir giriş: Next `/giris/misafir` → `POST /api/v1/auth/guest/login` → token cookie → `/`
- QR menü: Next `/m/{qrToken}` → `GET /api/v1/tables/{qrToken}/menu`
- QR sipariş: Next `/m/{qrToken}` → `POST /api/v1/tables/{qrToken}/orders` → `/o/{publicCode}`
- Garson çağrı: Next `/m/{qrToken}` → `POST /api/v1/tables/{qrToken}/calls`
- Rezervasyon: Next `/r/{slug}` → `POST /api/v1/restaurants/{slug}/reservations` → `/rsv/{publicCode}`
- Panel özet: Next `/panel` → `GET /api/v1/panel/dashboard`

## Veri Güvenliği

- Testler `phpunit.xml` içinde SQLite in-memory olacak şekilde zorlanır.
- Yerel geliştirme MySQL verisini korumak için şema silen komutlar kullanılmaz.
- Migration gerekiyorsa `php artisan migrate` kullanılır; seed yeniden çalıştırma ihtiyacı varsa mevcut veri etkisi kontrol edilir.
