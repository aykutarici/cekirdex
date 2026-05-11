<?php

/**
 * Sentry — yalnızca hata izleme.
 * Performance tracing, profiling ve log forwarding kasıtlı olarak kapalıdır.
 * Sadece uygulama seviyesi beklenmedik hatalar (5xx) Sentry'e gönderilir.
 */
return [

    'dsn' => env('SENTRY_LARAVEL_DSN', env('SENTRY_DSN')),

    // git commit hash'i ile release takibi (opsiyonel)
    'release' => env('SENTRY_RELEASE'),

    // APP_ENV otomatik olarak environment olarak kullanılır (local / production)
    'environment' => env('SENTRY_ENVIRONMENT'),

    // Hata örnekleme oranı — tüm hatalar gönderilsin
    'sample_rate' => 1.0,

    // Performance tracing KAPALI — sadece hata izleme istiyoruz
    'traces_sample_rate' => null,
    'profiles_sample_rate' => null,

    // Sentry Logs özelliği KAPALI — log kanalı olarak kullanmıyoruz
    'enable_logs' => false,

    // Kişisel veri (IP, cookie, request body) GÖNDERILMESIN — GDPR
    'send_default_pii' => false,

    // Org ID trace devam kontrolü için (Sentry org ID buraya yazılabilir)
    'org_id' => env('SENTRY_ORG_ID') === null ? null : (int) env('SENTRY_ORG_ID'),

    // ─────────────────────────────────────────────────────────────
    // Beklenen / gürültülü hataları filtrele — Sentry'e GİTMESİN
    // ─────────────────────────────────────────────────────────────
    'ignore_exceptions' => [
        // 422 — Kullanıcı girdisi hatası (form validation)
        \Illuminate\Validation\ValidationException::class,
        // 401 — Token yok / geçersiz
        \Illuminate\Auth\AuthenticationException::class,
        // 403 — Yetki yok
        \Illuminate\Auth\Access\AuthorizationException::class,
        // 404 — Kayıt bulunamadı (Eloquent)
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        // 404 — Route bulunamadı
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
        // 405 — Method not allowed
        \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class,
        // 429 — Rate limit aşıldı
        \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException::class,
    ],

    'ignore_transactions' => [
        '/up',      // Laravel health check
        '/api/health',
    ],

    // ─────────────────────────────────────────────────────────────
    // Breadcrumbs — hata bağlamı için faydalı, bant genişliği az
    // ─────────────────────────────────────────────────────────────
    'breadcrumbs' => [
        'logs'               => true,
        'cache'              => false,  // çok gürültülü
        'livewire'           => false,
        'sql_queries'        => true,   // hata anındaki sorguları göster
        'sql_bindings'       => false,  // parametre değerlerini gönderme (PII riski)
        'queue_info'         => true,
        'command_info'       => false,
        'http_client_requests' => true,
        'notifications'      => false,
    ],

    // ─────────────────────────────────────────────────────────────
    // Tracing — tamamen kapalı
    // ─────────────────────────────────────────────────────────────
    'tracing' => [
        'queue_job_transactions'  => false,
        'queue_jobs'              => false,
        'sql_queries'             => false,
        'sql_bindings'            => false,
        'sql_origin'              => false,
        'sql_origin_threshold_ms' => 100,
        'views'                   => false,
        'livewire'                => false,
        'http_client_requests'    => false,
        'cache'                   => false,
        'redis_commands'          => false,
        'redis_origin'            => false,
        'notifications'           => false,
        'missing_routes'          => false,
        'continue_after_response' => false,
        'default_integrations'    => false,
    ],

];
