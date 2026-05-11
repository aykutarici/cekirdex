<?php

use App\Http\Controllers\Cekirdex\Auth\LoginController as CekirdexLoginController;
use App\Http\Controllers\Cekirdex\Auth\RegisterController as CekirdexRegisterController;
use App\Http\Controllers\Cekirdex\ContactController as CekirdexContactController;
use App\Http\Controllers\Cekirdex\Customer\BillController as CustomerBillController;
use App\Http\Controllers\Cekirdex\Customer\CustomerAuthController;
use App\Http\Controllers\Cekirdex\Customer\MenuController as CustomerMenuController;
use App\Http\Controllers\Cekirdex\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Cekirdex\Customer\ProductReactionController;
use App\Http\Controllers\Cekirdex\Customer\ProductReviewController;
use App\Http\Controllers\Cekirdex\Customer\ReservationController as CustomerReservationController;
use App\Http\Controllers\Cekirdex\Customer\RestaurantPublicController;
use App\Http\Controllers\Cekirdex\Customer\TakeawayController;
use App\Http\Controllers\Cekirdex\LandingController;
use App\Http\Controllers\Cekirdex\Panel\BillController as PanelBillController;
use App\Http\Controllers\Cekirdex\Panel\CallController as PanelCallController;
use App\Http\Controllers\Cekirdex\Panel\DashboardController as PanelDashboardController;
use App\Http\Controllers\Cekirdex\Panel\KdsController as PanelKdsController;
use App\Http\Controllers\Cekirdex\Panel\WaiterFloorController as PanelWaiterFloorController;
use App\Http\Controllers\Cekirdex\Panel\MenuController as PanelMenuController;
use App\Http\Controllers\Cekirdex\Panel\OrderController as PanelOrderController;
use App\Http\Controllers\Cekirdex\Panel\ReservationController as PanelReservationController;
use App\Http\Controllers\Cekirdex\Panel\ReviewController as PanelReviewController;
use App\Http\Controllers\Cekirdex\Panel\SettingsController as PanelSettingsController;
use App\Http\Controllers\Cekirdex\Panel\StockImageController as PanelStockImageController;
use App\Http\Controllers\Cekirdex\Panel\TableController as PanelTableController;
use App\Http\Controllers\Cekirdex\Panel\TakeawayOrderController as PanelTakeawayOrderController;
use App\Http\Controllers\Cekirdex\Panel\WaiterOrderController as PanelWaiterOrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Çekirdex Routes
|--------------------------------------------------------------------------
| Bu dosya routes/web.php içinden kök URL grubunda yüklenir (path öneki yok).
| Tüm route'lar 'cekirdex.' name prefix'iyle çalışır.
*/

// ── Public Landing ───────────────────────────────────────────────────
Route::get('/',                 [LandingController::class, 'index'])->name('landing');
Route::get('/restoranlar',      [LandingController::class, 'forRestaurants'])->name('for-restaurants');
Route::get('/musteriler',       [LandingController::class, 'forGuests'])->name('for-guests');
Route::get('/fiyatlandirma',    [LandingController::class, 'pricing'])->name('pricing');
Route::get('/iletisim',         [LandingController::class, 'contact'])->name('contact');
Route::post('/iletisim',        [CekirdexContactController::class, 'submit'])->name('contact.submit');
Route::get('/gizlilik',         [LandingController::class, 'privacy'])->name('privacy');
Route::get('/kullanim-kosullari',[LandingController::class, 'terms'])->name('terms');

// ── Stok görsel SVG (public, CDN cache'li) ─────────────────────────
Route::get('/stock-image/{slug}.svg', [PanelStockImageController::class, 'image'])->name('stock.image');

// ── Halka açık restoran landing (slug ile, QR olmadan) ──────────────
Route::get('/r/{slug}',                        [RestaurantPublicController::class, 'show'])->name('public.show');
Route::post('/r/{slug}/order',                 [TakeawayController::class, 'place'])->name('public.takeaway.place');
Route::get('/r/{slug}/availability',           [CustomerReservationController::class, 'availability'])->name('public.reservation.availability');
Route::post('/r/{slug}/reservation',           [CustomerReservationController::class, 'store'])->name('public.reservation.store');

// ── Müşteri sipariş takip ve rezervasyon takip (login gerekmez) ─────
Route::get('/o/{publicCode}',                  [TakeawayController::class, 'track'])->name('public.order.track');
Route::get('/o/{publicCode}/feed',             [TakeawayController::class, 'trackFeed'])->name('public.order.feed');
Route::get('/rsv/{publicCode}',                [CustomerReservationController::class, 'show'])->name('public.reservation.show');
Route::get('/rsv/{publicCode}/qr.png',         [CustomerReservationController::class, 'qrPng'])->name('public.reservation.qr');
Route::post('/rsv/{publicCode}/cancel',        [CustomerReservationController::class, 'cancel'])->name('public.reservation.cancel');

// ── Müşteri Tarafı (QR'dan açılır, auth yok) ────────────────────────
Route::prefix('m')->name('customer.')->group(function () {
    Route::get('/{qrToken}',                  [CustomerMenuController::class, 'show'])->name('menu');
    Route::post('/{qrToken}/order',           [CustomerOrderController::class, 'place'])->name('order.place');
    Route::post('/{qrToken}/call',            [CustomerOrderController::class, 'call'])->name('call');
    Route::get('/{qrToken}/status/{orderId}', [CustomerOrderController::class, 'status'])->name('order.status');
    Route::post('/{qrToken}/my-orders',       [CustomerOrderController::class, 'myOrders'])->name('my-orders');

    // Hesap (Bill)
    Route::get('/{qrToken}/bill',             [CustomerBillController::class, 'show'])->name('bill.show');
    Route::post('/{qrToken}/bill/pay',        [CustomerBillController::class, 'pay'])->name('bill.pay');

    // Müşteri auth (modal/AJAX)
    Route::post('/{qrToken}/auth/register',   [CustomerAuthController::class, 'register'])->name('auth.register');
    Route::post('/{qrToken}/auth/login',      [CustomerAuthController::class, 'login'])->name('auth.login');
    Route::post('/{qrToken}/auth/logout',     [CustomerAuthController::class, 'logout'])->name('auth.logout');
    Route::get('/{qrToken}/auth/me',          [CustomerAuthController::class, 'whoami'])->name('auth.me');

    // Reaksiyonlar (beğen / favori)
    Route::get('/{qrToken}/products/{id}/summary',    [ProductReactionController::class, 'summary'])->name('product.summary');
    Route::post('/{qrToken}/products/{id}/like',      [ProductReactionController::class, 'toggleLike'])->name('product.like');
    Route::post('/{qrToken}/products/{id}/favorite',  [ProductReactionController::class, 'toggleFavorite'])->name('product.fav');
    Route::get('/{qrToken}/favorites',                 [ProductReactionController::class, 'myFavorites'])->name('favorites');

    // Yorumlar
    Route::get('/{qrToken}/products/{id}/reviews',    [ProductReviewController::class, 'index'])->name('product.reviews');
    Route::post('/{qrToken}/products/{id}/reviews',   [ProductReviewController::class, 'store'])->name('product.reviews.store');
    Route::delete('/{qrToken}/reviews/{reviewId}',    [ProductReviewController::class, 'destroy'])->name('reviews.destroy');
});

// ── Auth ─────────────────────────────────────────────────────────────
Route::middleware('cekirdex.guest')->group(function () {
    Route::get('/giris',    [CekirdexLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/giris',   [CekirdexLoginController::class, 'login'])->name('login.submit');
    Route::get('/kayit',    [CekirdexRegisterController::class, 'showRegisterForm'])->name('register');
    Route::post('/kayit',   [CekirdexRegisterController::class, 'register'])->name('register.submit');
});
Route::post('/cikis', [CekirdexLoginController::class, 'logout'])->name('logout');

// ── Restoran Paneli ─────────────────────────────────────────────────
Route::prefix('panel')->name('panel.')->middleware('cekirdex.auth')->group(function () {
    Route::get('/', [PanelDashboardController::class, 'index'])->name('dashboard');

    // Menü
    Route::get('/menu', [PanelMenuController::class, 'index'])->name('menu.index');
    Route::post('/menu/category',                [PanelMenuController::class, 'storeCategory'])->name('menu.category.store');
    Route::put('/menu/category/{id}',            [PanelMenuController::class, 'updateCategory'])->name('menu.category.update');
    Route::delete('/menu/category/{id}',         [PanelMenuController::class, 'destroyCategory'])->name('menu.category.destroy');
    Route::post('/menu/product',                 [PanelMenuController::class, 'storeProduct'])->name('menu.product.store');
    Route::put('/menu/product/{id}',             [PanelMenuController::class, 'updateProduct'])->name('menu.product.update');
    Route::delete('/menu/product/{id}',          [PanelMenuController::class, 'destroyProduct'])->name('menu.product.destroy');
    Route::post('/menu/product/{id}/toggle-stock', [PanelMenuController::class, 'toggleStock'])->name('menu.product.toggle-stock');

    // Görsel kütüphanesi (stok + galeri)
    Route::get('/stock-images',                  [PanelStockImageController::class, 'browse'])->name('stock.browse');

    // Masalar
    Route::get('/masalar', [PanelTableController::class, 'index'])->name('tables.index');
    Route::post('/masalar',                       [PanelTableController::class, 'store'])->name('tables.store');
    Route::put('/masalar/{id}',                   [PanelTableController::class, 'update'])->name('tables.update');
    Route::delete('/masalar/{id}',                [PanelTableController::class, 'destroy'])->name('tables.destroy');
    Route::post('/masalar/{id}/qr-yenile',        [PanelTableController::class, 'regenerateQr'])->name('tables.regenerate-qr');

    // Siparişler
    Route::get('/siparisler',           [PanelOrderController::class, 'index'])->name('orders.index');
    Route::get('/siparisler/{id}',      [PanelOrderController::class, 'show'])->name('orders.show');
    Route::patch('/siparisler/{id}/durum', [PanelOrderController::class, 'updateStatus'])->name('orders.update-status');

    // Hesap (Bill) — adisyon yönetimi
    Route::get('/hesaplar',                       [PanelBillController::class, 'index'])->name('bills.index');
    Route::get('/hesaplar/{tableId}',             [PanelBillController::class, 'show'])->name('bills.show');
    Route::post('/hesaplar/{tableId}/odeme',      [PanelBillController::class, 'recordPayment'])->name('bills.record-payment');
    Route::delete('/hesaplar/{tableId}/odeme/{paymentId}', [PanelBillController::class, 'voidPayment'])->name('bills.void-payment');
    Route::post('/hesaplar/{tableId}/kapat',      [PanelBillController::class, 'close'])->name('bills.close');

    // Garson — masaya doğrudan sipariş ekleme
    Route::get('/hesaplar/{tableId}/yeni-siparis', [PanelWaiterOrderController::class, 'create'])->name('bills.waiter-order');
    Route::post('/hesaplar/{tableId}/yeni-siparis', [PanelWaiterOrderController::class, 'store'])->name('bills.waiter-order.store');

    // Paket Siparişler (takeaway / delivery)
    Route::get('/paket',                       [PanelTakeawayOrderController::class, 'index'])->name('takeaway.index');
    Route::get('/paket/feed',                  [PanelTakeawayOrderController::class, 'feed'])->name('takeaway.feed');
    Route::get('/paket/{id}',                  [PanelTakeawayOrderController::class, 'show'])->name('takeaway.show');
    Route::post('/paket/{id}/onayla',          [PanelTakeawayOrderController::class, 'confirm'])->name('takeaway.confirm');
    Route::post('/paket/{id}/ilerle',          [PanelTakeawayOrderController::class, 'advance'])->name('takeaway.advance');
    Route::post('/paket/{id}/iptal',           [PanelTakeawayOrderController::class, 'cancel'])->name('takeaway.cancel');

    // Rezervasyonlar
    Route::get('/rezervasyonlar',                  [PanelReservationController::class, 'index'])->name('reservations.index');
    Route::get('/rezervasyonlar/{id}',             [PanelReservationController::class, 'show'])->name('reservations.show');
    Route::post('/rezervasyonlar/{id}/onayla',     [PanelReservationController::class, 'confirm'])->name('reservations.confirm');
    Route::post('/rezervasyonlar/{id}/reddet',     [PanelReservationController::class, 'reject'])->name('reservations.reject');
    Route::post('/rezervasyonlar/{id}/durum',      [PanelReservationController::class, 'setStatus'])->name('reservations.set-status');

    // Yorumlar (sahip silebilir/gizleyebilir)
    Route::get('/yorumlar',                  [PanelReviewController::class, 'index'])->name('reviews.index');
    Route::delete('/yorumlar/{id}',          [PanelReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::post('/yorumlar/{id}/gizle',      [PanelReviewController::class, 'toggleVisibility'])->name('reviews.toggle');

    // Çağrılar
    Route::get('/cagrilar',                 [PanelCallController::class, 'index'])->name('calls.index');
    Route::post('/cagrilar/{id}/yanitla',   [PanelCallController::class, 'respond'])->name('calls.respond');
    Route::post('/cagrilar/{id}/kapat',     [PanelCallController::class, 'close'])->name('calls.close');
    Route::get('/cagrilar/feed',            [PanelCallController::class, 'feed'])->name('calls.feed');

    // KDS — Mutfak Ekranı
    Route::get('/mutfak',                   [PanelKdsController::class, 'index'])->name('kds.index');
    Route::get('/mutfak/feed',              [PanelKdsController::class, 'feed'])->name('kds.feed');
    Route::post('/mutfak/{id}/ilerle',      [PanelKdsController::class, 'advance'])->name('kds.advance');
    Route::post('/mutfak/{id}/durum',       [PanelKdsController::class, 'setStatus'])->name('kds.set-status');

    // Servis — Garson ekranı (yeni → mutfak, hazır → teslim)
    Route::get('/servis',                   [PanelWaiterFloorController::class, 'index'])->name('service.index');
    Route::get('/servis/feed',             [PanelWaiterFloorController::class, 'feed'])->name('service.feed');
    Route::post('/servis/{id}/ilerle',     [PanelWaiterFloorController::class, 'advance'])->name('service.advance');
    Route::post('/servis/{id}/durum',      [PanelWaiterFloorController::class, 'setStatus'])->name('service.set-status');

    // Dashboard feed (yeni sipariş bildirimi için)
    Route::get('/feed',                     [PanelDashboardController::class, 'feed'])->name('dashboard.feed');

    // Ayarlar
    Route::get('/ayarlar',                  [PanelSettingsController::class, 'general'])->name('settings.general');
    Route::post('/ayarlar',                 [PanelSettingsController::class, 'updateGeneral'])->name('settings.general.update');
    Route::get('/ayarlar/hizmetler',        [PanelSettingsController::class, 'services'])->name('settings.services');
    Route::post('/ayarlar/hizmetler',       [PanelSettingsController::class, 'updateServices'])->name('settings.services.update');

    // Personel
    Route::get('/personel',                 [PanelSettingsController::class, 'staff'])->name('staff.index');
    Route::post('/personel',                [PanelSettingsController::class, 'storeStaff'])->name('staff.store');
    Route::put('/personel/{id}',            [PanelSettingsController::class, 'updateStaff'])->name('staff.update');
    Route::delete('/personel/{id}',         [PanelSettingsController::class, 'destroyStaff'])->name('staff.destroy');

    // Profil
    Route::get('/profil',                   [PanelSettingsController::class, 'profile'])->name('profile');
    Route::post('/profil',                  [PanelSettingsController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profil/sifre',            [PanelSettingsController::class, 'updatePassword'])->name('profile.password');
});
