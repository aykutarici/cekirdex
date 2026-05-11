<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\Panel\PanelController;
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
    Route::get('/tables/{qrToken}/menu', [PublicRestaurantController::class, 'menu'])->name('tables.menu');
    Route::post('/tables/{qrToken}/calls', [PublicRestaurantController::class, 'storeCall'])->name('tables.calls.store');
    Route::post('/tables/{qrToken}/orders', [PublicRestaurantController::class, 'storeOrder'])->name('tables.orders.store');
    Route::get('/orders/{publicCode}', [PublicRestaurantController::class, 'orderTrack'])->name('orders.track');
    Route::get('/reservations/{publicCode}', [PublicRestaurantController::class, 'reservationTrack'])->name('reservations.track');
    Route::post('/reservations/{publicCode}/cancel', [PublicRestaurantController::class, 'cancelReservation'])->name('reservations.cancel');

    Route::middleware('api.actor')->prefix('panel')->name('panel.')->group(function () {
        Route::get('/dashboard', [PanelController::class, 'dashboard'])->middleware('api.permission:dashboard.view')->name('dashboard');
        Route::get('/orders', [PanelController::class, 'orders'])->middleware('api.permission:orders.view')->name('orders.index');
        Route::get('/menu', [PanelController::class, 'menu'])->middleware('api.permission:menu.view')->name('menu.index');
        Route::get('/tables', [PanelController::class, 'tables'])->middleware('api.permission:tables.view')->name('tables.index');
        Route::get('/staff', [PanelController::class, 'staff'])->middleware('api.permission:staff.view')->name('staff.index');
        Route::get('/reservations', [PanelController::class, 'reservations'])->middleware('api.permission:reservations.view')->name('reservations.index');
        Route::get('/reviews', [PanelController::class, 'reviews'])->middleware('api.permission:reviews.view')->name('reviews.index');
        Route::get('/settings', [PanelController::class, 'settings'])->middleware('api.permission:settings.manage')->name('settings.index');
    });
});
