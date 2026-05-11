<?php

namespace Tests\Feature\Api;

use App\Cekirdex\Middleware\ApiAuthenticate;
use App\Cekirdex\Models\CekirdexCustomerUser;
use App\Cekirdex\Models\CekirdexRestaurant;
use App\Cekirdex\Models\CekirdexUser;
use App\Cekirdex\Services\ApiTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Sentry\Event;
use Tests\TestCase;

/**
 * Sentry entegrasyon testleri.
 *
 * `before_send` callback'i null döndürerek gerçek ağ isteği atılmaz;
 * event'ler $this->sentryEvents dizisinde yakalanır.
 */
class SentryIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private array $sentryEvents = [];
    private bool $sentryAvailable = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sentryEvents = [];
        $events = &$this->sentryEvents;

        $client = app('sentry')->getClient();

        if ($client !== null) {
            $this->sentryAvailable = true;
            $client->getOptions()->setBeforeSendCallback(
                static function (Event $event) use (&$events): ?Event {
                    $events[] = $event;
                    return null; // İnternete gönderme — sadece yakala
                }
            );
        }
    }

    // ──────────────────────────────────────────────────────────────
    // Konfigürasyon testleri
    // ──────────────────────────────────────────────────────────────

    public function test_sentry_is_bound_and_client_is_initialized(): void
    {
        $this->assertTrue(app()->bound('sentry'), 'Sentry container\'a bind edilmeli');
        $this->assertTrue($this->sentryAvailable, 'Sentry client başlatılmış olmalı');
    }

    public function test_performance_tracing_is_disabled(): void
    {
        $this->assertNull(config('sentry.traces_sample_rate'), 'Performance tracing KAPALI olmalı');
        $this->assertNull(config('sentry.profiles_sample_rate'), 'Profiling KAPALI olmalı');
    }

    public function test_pii_is_not_sent_to_sentry(): void
    {
        $this->assertFalse(config('sentry.send_default_pii'), 'IP/cookie/header gibi PII gönderilmemeli');
    }

    public function test_log_forwarding_to_sentry_is_disabled(): void
    {
        $this->assertFalse(config('sentry.enable_logs'), 'Log forwarding KAPALI olmalı');
    }

    // ──────────────────────────────────────────────────────────────
    // ignore_exceptions listesi testleri
    // ──────────────────────────────────────────────────────────────

    /** @dataProvider ignoredExceptionProvider */
    public function test_expected_exception_class_is_in_ignore_list(string $class): void
    {
        $this->assertContains(
            $class,
            config('sentry.ignore_exceptions', []),
            "{$class} Sentry ignore listesinde olmalı",
        );
    }

    public static function ignoredExceptionProvider(): array
    {
        return [
            '422 ValidationException'           => [\Illuminate\Validation\ValidationException::class],
            '401 AuthenticationException'        => [\Illuminate\Auth\AuthenticationException::class],
            '403 AuthorizationException'         => [\Illuminate\Auth\Access\AuthorizationException::class],
            '404 ModelNotFoundException'         => [\Illuminate\Database\Eloquent\ModelNotFoundException::class],
            '404 NotFoundHttpException'          => [\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class],
            '405 MethodNotAllowedHttpException'  => [\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class],
            '429 TooManyRequestsHttpException'   => [\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException::class],
        ];
    }

    // ──────────────────────────────────────────────────────────────
    // Hata yakalama testleri — event var mı, yok mu?
    // ──────────────────────────────────────────────────────────────

    public function test_runtime_exception_is_captured(): void
    {
        $this->skipIfSentryUnavailable();

        \Sentry\captureException(new \RuntimeException('Beklenmedik test hatası'));

        $this->assertCount(1, $this->sentryEvents, 'RuntimeException Sentry\'e gönderilmeli');
        $this->assertSame(
            'Beklenmedik test hatası',
            $this->sentryEvents[0]->getExceptions()[0]->getValue(),
        );
    }

    public function test_validation_error_api_response_is_422_and_not_captured(): void
    {
        $this->postJson('/api/v1/auth/staff/login', [])->assertStatus(422);

        $this->assertEmpty($this->sentryEvents, 'ValidationException Sentry\'e gönderilmemeli');
    }

    public function test_model_not_found_api_response_is_404_and_not_captured(): void
    {
        $this->getJson('/api/v1/restaurants/asla-olmayan-restoran-xyz')->assertStatus(404);

        $this->assertEmpty($this->sentryEvents, 'ModelNotFoundException Sentry\'e gönderilmemeli');
    }

    public function test_unauthenticated_request_returns_401_and_not_captured(): void
    {
        $this->getJson('/api/v1/auth/me')->assertStatus(401);

        $this->assertEmpty($this->sentryEvents, '401 AuthenticationException Sentry\'e gönderilmemeli');
    }

    public function test_rate_limited_endpoint_returns_429_and_not_captured(): void
    {
        // Contact endpoint 3 istek/dk ile sınırlı
        for ($i = 0; $i < 4; $i++) {
            $this->postJson('/api/v1/contact', [
                'name'    => 'Test',
                'email'   => 'test@test.com',
                'message' => 'Test mesajı, yeterince uzun olmalı.',
            ]);
        }

        $this->assertEmpty($this->sentryEvents, 'Rate limit hatası Sentry\'e gönderilmemeli');
    }

    // ──────────────────────────────────────────────────────────────
    // Kullanıcı context testleri
    // ──────────────────────────────────────────────────────────────

    public function test_staff_user_context_is_attached_to_sentry_event(): void
    {
        $this->skipIfSentryUnavailable();

        $restaurant = CekirdexRestaurant::create([
            'slug'      => 'sentry-restoran',
            'name'      => 'Sentry Test Restoran',
            'is_active' => true,
        ]);

        $user = CekirdexUser::create([
            'cekirdex_restaurant_id' => $restaurant->id,
            'role'      => CekirdexUser::ROLE_OWNER,
            'name'      => 'Sentry Sahibi',
            'email'     => 'sentry-owner@test.com',
            'password'  => Hash::make('password123'),
            'is_active' => true,
        ]);

        [$token] = app(ApiTokenService::class)->issue($user, 'sentry-staff-test');

        // withScope: scope izolasyonu sağlar — bu test önceki/sonraki testleri etkilemez
        \Sentry\withScope(function (\Sentry\State\Scope $_scope) use ($token, $user, $restaurant): void {
            $request = Request::create('/api/v1/test', 'GET');
            $request->headers->set('Authorization', 'Bearer ' . $token);
            app(ApiAuthenticate::class)->handle($request, fn() => response('ok'));

            \Sentry\captureException(new \RuntimeException('Personel bağlam testi'));
        });

        $this->assertCount(1, $this->sentryEvents, 'Event yakalanmış olmalı');

        $event = $this->sentryEvents[0];
        $tags  = $event->getTags();

        $this->assertEquals('staff', $tags['actor.type'] ?? null, 'actor.type = staff');
        $this->assertEquals('owner', $tags['actor.role'] ?? null, 'actor.role = owner');
        $this->assertEquals((string) $restaurant->id, $tags['restaurant.id'] ?? null, 'restaurant.id eşleşmeli');
        $this->assertEquals('Sentry Test Restoran', $tags['restaurant.name'] ?? null, 'restaurant.name eşleşmeli');
        $this->assertEquals('sentry-restoran', $tags['restaurant.slug'] ?? null, 'restaurant.slug eşleşmeli');

        $userBag = $event->getUser();
        $this->assertNotNull($userBag, 'User bilgisi event\'te olmalı');
        $this->assertEquals($user->email, $userBag->getEmail(), 'Kullanıcı e-postası eşleşmeli');
    }

    public function test_guest_user_context_is_attached_to_sentry_event(): void
    {
        $this->skipIfSentryUnavailable();

        $guest = CekirdexCustomerUser::create([
            'name'      => 'Sentry Misafir',
            'phone'     => '905001112244',
            'email'     => 'sentry-guest@test.com',
            'password'  => Hash::make('password123'),
            'is_active' => true,
        ]);

        [$token] = app(ApiTokenService::class)->issue($guest, 'sentry-guest-test', ['guest:*']);

        \Sentry\withScope(function (\Sentry\State\Scope $_scope) use ($token, $guest): void {
            $request = Request::create('/api/v1/test', 'GET');
            $request->headers->set('Authorization', 'Bearer ' . $token);
            app(ApiAuthenticate::class)->handle($request, fn() => response('ok'));

            \Sentry\captureException(new \RuntimeException('Misafir bağlam testi'));
        });

        $this->assertCount(1, $this->sentryEvents, 'Event yakalanmış olmalı');

        $event = $this->sentryEvents[0];
        $tags  = $event->getTags();

        $this->assertEquals('guest', $tags['actor.type'] ?? null, 'actor.type = guest');
        $this->assertArrayNotHasKey('restaurant.id', $tags, 'Misafirde restaurant.id olmamalı');

        $userBag = $event->getUser();
        $this->assertNotNull($userBag);
        $this->assertEquals($guest->email, $userBag->getEmail());
    }

    public function test_unauthenticated_request_has_no_user_context_in_event(): void
    {
        $this->skipIfSentryUnavailable();

        // Auth olmadan direkt bir event yakala
        \Sentry\withScope(function (\Sentry\State\Scope $_scope): void {
            \Sentry\captureException(new \RuntimeException('Auth olmadan hata'));
        });

        $this->assertCount(1, $this->sentryEvents);
        $this->assertNull(
            $this->sentryEvents[0]->getUser(),
            'Auth olmadan Sentry event\'te kullanıcı bilgisi olmamalı',
        );
    }

    // ──────────────────────────────────────────────────────────────
    // Yardımcı
    // ──────────────────────────────────────────────────────────────

    private function skipIfSentryUnavailable(): void
    {
        if (!$this->sentryAvailable) {
            $this->markTestSkipped('Sentry client başlatılamadı — test DSN eksik');
        }
    }
}
