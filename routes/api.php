<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\CustomerAuthController;
use App\Http\Controllers\Api\V1\CustomerReactionController;
use App\Http\Controllers\Api\V1\CustomerReviewController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\Panel\BillController;
use App\Http\Controllers\Api\V1\Panel\CallController;
use App\Http\Controllers\Api\V1\Panel\KdsController;
use App\Http\Controllers\Api\V1\Panel\MenuApiController;
use App\Http\Controllers\Api\V1\Panel\PanelController;
use App\Http\Controllers\Api\V1\Panel\ReservationApiController;
use App\Http\Controllers\Api\V1\Panel\ReviewApiController;
use App\Http\Controllers\Api\V1\Panel\ServiceController;
use App\Http\Controllers\Api\V1\Panel\SettingsApiController;
use App\Http\Controllers\Api\V1\Panel\StaffApiController;
use App\Http\Controllers\Api\V1\Panel\TableApiController;
use App\Http\Controllers\Api\V1\Panel\TakeawayApiController;
use App\Http\Controllers\Api\V1\PublicRestaurantController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class)->name('api.health');

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('/contact', ContactController::class)->middleware('throttle:contact')->name('contact');

    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('/staff/login', [AuthController::class, 'staffLogin'])->name('staff.login');
        Route::post('/staff/register', [AuthController::class, 'staffRegister'])->name('staff.register');
        Route::post('/guest/login', [AuthController::class, 'guestLogin'])->name('guest.login');
        Route::post('/guest/register', [AuthController::class, 'guestRegister'])->name('guest.register');

        Route::middleware('api.actor')->group(function () {
            Route::get('/me', [AuthController::class, 'me'])->name('me');
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        });
    });

    Route::get('/restaurants/{slug}', [PublicRestaurantController::class, 'show'])->name('restaurants.show');
    Route::get('/restaurants/{slug}/availability', [PublicRestaurantController::class, 'availability'])->name('restaurants.availability');
    Route::post('/restaurants/{slug}/reservations', [PublicRestaurantController::class, 'storeReservation'])->name('restaurants.reservations.store');
    Route::post('/restaurants/{slug}/order', [PublicRestaurantController::class, 'storeTakeawayOrder'])->name('restaurants.order.store');

    Route::get('/tables/{qrToken}/menu', [PublicRestaurantController::class, 'menu'])->name('tables.menu');
    Route::post('/tables/{qrToken}/calls', [PublicRestaurantController::class, 'storeCall'])->name('tables.calls.store');
    Route::post('/tables/{qrToken}/orders', [PublicRestaurantController::class, 'storeOrder'])->name('tables.orders.store');
    Route::get('/tables/{qrToken}/bill', [PublicRestaurantController::class, 'customerBill'])->name('tables.bill');

    Route::prefix('tables/{qrToken}')->group(function () {
        Route::post('/auth/register', [CustomerAuthController::class, 'register'])->name('tables.auth.register');
        Route::post('/auth/login', [CustomerAuthController::class, 'login'])->name('tables.auth.login');
        Route::middleware('api.actor')->group(function () {
            Route::post('/auth/logout', [CustomerAuthController::class, 'logout'])->name('tables.auth.logout');
            Route::get('/auth/me', [CustomerAuthController::class, 'me'])->name('tables.auth.me');
        });

        Route::get('/products/{productId}/reactions', [CustomerReactionController::class, 'show'])->name('tables.products.reactions');
        Route::middleware('api.actor')->group(function () {
            Route::post('/products/{productId}/like', [CustomerReactionController::class, 'toggleLike'])->name('tables.products.like');
            Route::post('/products/{productId}/favorite', [CustomerReactionController::class, 'toggleFavorite'])->name('tables.products.favorite');
        });

        Route::get('/products/{productId}/reviews', [CustomerReviewController::class, 'index'])->name('tables.products.reviews.index');
        Route::middleware('api.actor')->group(function () {
            Route::post('/products/{productId}/reviews', [CustomerReviewController::class, 'store'])->name('tables.products.reviews.store');
            Route::delete('/products/{productId}/reviews/{reviewId}', [CustomerReviewController::class, 'destroy'])->name('tables.products.reviews.destroy');
        });
    });

    Route::get('/orders/{publicCode}', [PublicRestaurantController::class, 'orderTrack'])->name('orders.track');
    Route::get('/orders/{publicCode}/feed', [PublicRestaurantController::class, 'orderFeed'])->name('orders.feed');
    Route::get('/reservations/{publicCode}', [PublicRestaurantController::class, 'reservationTrack'])->name('reservations.track');
    Route::post('/reservations/{publicCode}/cancel', [PublicRestaurantController::class, 'cancelReservation'])->name('reservations.cancel');

    Route::middleware('api.actor')->prefix('panel')->name('panel.')->group(function () {
        // ── Dashboard ────────────────────────────────────────────────────────
        Route::get('/dashboard', [PanelController::class, 'dashboard'])->middleware('api.permission:dashboard.view')->name('dashboard');

        // ── Siparişler ───────────────────────────────────────────────────────
        Route::get('/orders', [PanelController::class, 'orders'])->middleware('api.permission:orders.view')->name('orders.index');
        Route::get('/orders/{id}', [PanelController::class, 'orderDetail'])->middleware('api.permission:orders.view')->name('orders.show');
        Route::post('/orders/{id}/status', [PanelController::class, 'updateOrderStatus'])->middleware('api.permission:orders.manage')->name('orders.status');
        Route::post('/orders/{id}/confirm', [PanelController::class, 'confirmOrder'])->middleware('api.permission:orders.manage')->name('orders.confirm');

        // ── Menü ─────────────────────────────────────────────────────────────
        Route::get('/menu', [PanelController::class, 'menu'])->middleware('api.permission:menu.view')->name('menu.index');
        Route::middleware('api.permission:menu.manage')->group(function () {
            Route::post('/menu/categories', [MenuApiController::class, 'storeCategory'])->name('menu.categories.store');
            Route::put('/menu/categories/{id}', [MenuApiController::class, 'updateCategory'])->name('menu.categories.update');
            Route::delete('/menu/categories/{id}', [MenuApiController::class, 'destroyCategory'])->name('menu.categories.destroy');
            Route::post('/menu/products', [MenuApiController::class, 'storeProduct'])->name('menu.products.store');
            Route::put('/menu/products/{id}', [MenuApiController::class, 'updateProduct'])->name('menu.products.update');
            Route::delete('/menu/products/{id}', [MenuApiController::class, 'destroyProduct'])->name('menu.products.destroy');
            Route::post('/menu/products/{id}/toggle-stock', [MenuApiController::class, 'toggleStock'])->name('menu.products.toggle-stock');
            Route::post('/menu/products/{id}/toggle-active', [MenuApiController::class, 'toggleActive'])->name('menu.products.toggle-active');
        });

        // ── Masalar ──────────────────────────────────────────────────────────
        Route::get('/tables', [PanelController::class, 'tables'])->middleware('api.permission:tables.view')->name('tables.index');
        Route::middleware('api.permission:tables.manage')->group(function () {
            Route::post('/tables', [TableApiController::class, 'store'])->name('tables.store');
            Route::put('/tables/{id}', [TableApiController::class, 'update'])->name('tables.update');
            Route::delete('/tables/{id}', [TableApiController::class, 'destroy'])->name('tables.destroy');
            Route::post('/tables/{id}/regenerate-qr', [TableApiController::class, 'regenerateQr'])->name('tables.regenerate-qr');
        });

        // ── Personel ─────────────────────────────────────────────────────────
        Route::get('/staff', [PanelController::class, 'staff'])->middleware('api.permission:staff.view')->name('staff.index');
        Route::middleware('api.permission:staff.manage')->group(function () {
            Route::post('/staff', [StaffApiController::class, 'store'])->name('staff.store');
            Route::put('/staff/{id}', [StaffApiController::class, 'update'])->name('staff.update');
            Route::delete('/staff/{id}', [StaffApiController::class, 'destroy'])->name('staff.destroy');
        });

        // ── Rezervasyonlar ────────────────────────────────────────────────────
        Route::get('/reservations', [PanelController::class, 'reservations'])->middleware('api.permission:reservations.view')->name('reservations.index');
        Route::middleware('api.permission:reservations.manage')->group(function () {
            Route::get('/reservations/{id}', [ReservationApiController::class, 'show'])->name('reservations.show');
            Route::post('/reservations/{id}/confirm', [ReservationApiController::class, 'confirm'])->name('reservations.confirm');
            Route::post('/reservations/{id}/cancel', [ReservationApiController::class, 'cancel'])->name('reservations.cancel');
            Route::post('/reservations/{id}/no-show', [ReservationApiController::class, 'noShow'])->name('reservations.no-show');
            Route::post('/reservations/{id}/complete', [ReservationApiController::class, 'complete'])->name('reservations.complete');
        });

        // ── Yorumlar ─────────────────────────────────────────────────────────
        Route::get('/reviews', [PanelController::class, 'reviews'])->middleware('api.permission:reviews.view')->name('reviews.index');
        Route::middleware('api.permission:reviews.manage')->group(function () {
            Route::delete('/reviews/{id}', [ReviewApiController::class, 'destroy'])->name('reviews.destroy');
            Route::post('/reviews/{id}/toggle-visibility', [ReviewApiController::class, 'toggleVisibility'])->name('reviews.toggle-visibility');
        });

        // ── Ayarlar ──────────────────────────────────────────────────────────
        Route::get('/settings', [PanelController::class, 'settings'])->middleware('api.permission:settings.manage')->name('settings.index');
        Route::middleware('api.permission:settings.manage')->group(function () {
            Route::put('/settings', [SettingsApiController::class, 'update'])->name('settings.update');
            Route::put('/settings/password', [SettingsApiController::class, 'updatePassword'])->name('settings.password');
            Route::get('/settings/stock-images', [SettingsApiController::class, 'stockImages'])->name('settings.stock-images');
        });

        // ── Adisyon (Bills) ───────────────────────────────────────────────────
        Route::middleware('api.permission:bills.manage')->group(function () {
            Route::get('/bills', [BillController::class, 'index'])->name('bills.index');
            Route::get('/bills/{tableId}', [BillController::class, 'show'])->name('bills.show');
            Route::post('/bills/{tableId}/payments', [BillController::class, 'addPayment'])->name('bills.payments.store');
            Route::delete('/bills/{tableId}/payments/{paymentId}', [BillController::class, 'cancelPayment'])->name('bills.payments.cancel');
            Route::post('/bills/{tableId}/close', [BillController::class, 'closeBill'])->name('bills.close');
            Route::post('/bills/{tableId}/waiter-order', [BillController::class, 'waiterOrder'])->name('bills.waiter-order');
        });

        // ── KDS (Mutfak Ekranı) ────────────────────────────────────────────────
        Route::middleware('api.permission:orders.manage')->group(function () {
            Route::get('/kds', [KdsController::class, 'index'])->name('kds.index');
            Route::get('/kds/feed', [KdsController::class, 'feed'])->name('kds.feed');
            Route::post('/kds/{orderId}/advance', [KdsController::class, 'advance'])->name('kds.advance');
            Route::post('/kds/{orderId}/cancel', [KdsController::class, 'cancel'])->name('kds.cancel');
        });

        // ── Servis Ekranı ─────────────────────────────────────────────────────
        Route::middleware('api.permission:orders.manage')->group(function () {
            Route::get('/service', [ServiceController::class, 'index'])->name('service.index');
            Route::get('/service/feed', [ServiceController::class, 'feed'])->name('service.feed');
            Route::post('/service/{orderId}/serve', [ServiceController::class, 'serve'])->name('service.serve');
            Route::post('/service/{orderId}/confirm', [ServiceController::class, 'confirm'])->name('service.confirm');
        });

        // ── Çağrılar ─────────────────────────────────────────────────────────
        Route::middleware('api.permission:orders.manage')->group(function () {
            Route::get('/calls', [CallController::class, 'index'])->name('calls.index');
            Route::get('/calls/feed', [CallController::class, 'feed'])->name('calls.feed');
            Route::post('/calls/{callId}/respond', [CallController::class, 'respond'])->name('calls.respond');
            Route::post('/calls/{callId}/close', [CallController::class, 'close'])->name('calls.close');
        });

        // ── Gel-al / Teslimat ────────────────────────────────────────────────
        Route::middleware('api.permission:orders.manage')->group(function () {
            Route::get('/takeaway', [TakeawayApiController::class, 'index'])->name('takeaway.index');
            Route::get('/takeaway/feed', [TakeawayApiController::class, 'feed'])->name('takeaway.feed');
            Route::get('/takeaway/{id}', [TakeawayApiController::class, 'show'])->name('takeaway.show');
            Route::post('/takeaway/{id}/confirm', [TakeawayApiController::class, 'confirm'])->name('takeaway.confirm');
            Route::post('/takeaway/{id}/advance', [TakeawayApiController::class, 'advance'])->name('takeaway.advance');
            Route::post('/takeaway/{id}/cancel', [TakeawayApiController::class, 'cancel'])->name('takeaway.cancel');
        });
    });
});
