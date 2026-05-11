# Çekirdex Web

Çekirdex iki ayrı uygulama olarak çalışır:

- `apps/web`: Public web, restoran sayfaları, QR menü, giriş/kayıt ve panel giriş noktaları için Next.js uygulaması.
- Laravel root uygulaması: Mobil uygulama ve web clientları için API-only backend.

Docker Compose yerel MySQL ve Redis ile birlikte Laravel API, Laravel worker, Nginx ve Next.js servislerini ayağa kaldırır.

## Hızlı Başlangıç

```bash
cp .env.docker.example .env.docker
docker compose up -d --build
docker compose exec backend php artisan migrate
docker compose exec backend php artisan db:seed
```

> Veri kaybına neden olan `migrate:fresh`, `db:wipe`, `DROP` veya `TRUNCATE` komutlarını kullanmayın.

## Servisler

- Next.js: `http://localhost:3000`
- Laravel API Nginx: `http://localhost:8080`
- API health: `http://localhost:8080/api/health`
- MySQL: `127.0.0.1:3307`

## Önemli API Akışları

- `POST /api/v1/auth/staff/register`: Restoran ve ilk owner hesabı oluşturur.
- `POST /api/v1/auth/staff/login`: Restoran/Ininia personeli token üretir.
- `POST /api/v1/auth/guest/register`: Misafir hesabı oluşturur.
- `GET /api/v1/restaurants/{slug}`: Public restoran bilgisi.
- `POST /api/v1/restaurants/{slug}/reservations`: Rezervasyon talebi oluşturur.
- `GET /api/v1/reservations/{publicCode}`: Rezervasyon takip.
- `GET /api/v1/tables/{qrToken}/menu`: QR menü.
- `POST /api/v1/tables/{qrToken}/orders`: QR masadan sipariş oluşturur.
- `POST /api/v1/tables/{qrToken}/calls`: Garson çağrısı oluşturur.
- `GET /api/v1/orders/{publicCode}`: Sipariş takip.
- `GET /api/v1/panel/dashboard`: Tokenlı restoran panel özeti.

Detaylı mimari ve klasör rehberi için `docs/architecture.md` dosyasına bakın.

## Geliştirme Komutları

```bash
docker compose exec backend php artisan test
docker compose exec backend php artisan route:list --path=api
docker compose build web
```
