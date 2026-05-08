<?php

/**
 * PHP built-in server router (php artisan serve).
 *
 * Varsayılan Laravel server.php, dizin yollarında file_exists true döndüğü için
 * GET /cekirdex rotası, public/cekirdex/ klasörüyle çakışıp Laravel'e hiç düşmüyordu.
 * Yalnızca gerçek dosyalar doğrudan sunulur.
 */
$publicPath = getcwd();

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

if ($uri !== '/' && file_exists($publicPath.$uri) && is_file($publicPath.$uri)) {
    return false;
}

require_once $publicPath.'/index.php';
