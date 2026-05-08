<?php

use Illuminate\Support\Facades\Route;

Route::prefix('cekirdex')->name('cekirdex.')->group(function () {
    require __DIR__.'/cekirdex.php';
});
