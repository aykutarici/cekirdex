<?php

namespace App\Http\Controllers\Api\V1;

use App\Cekirdex\Models\CekirdexContact;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class ContactController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $key = 'cekirdex-api-contact:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            throw ValidationException::withMessages([
                'message' => 'Çok hızlı gönderiyorsunuz. Lütfen biraz sonra tekrar deneyin.',
            ]);
        }

        RateLimiter::hit($key, 60);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:32'],
            'restaurant_name' => ['nullable', 'string', 'max:160'],
            'city' => ['nullable', 'string', 'max:80'],
            'subject' => ['nullable', 'string', 'max:200'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'website' => ['nullable', 'max:0'],
        ]);

        if (!empty($request->input('website'))) {
            return response()->json(['message' => 'Mesajınız alındı.'], 202);
        }

        unset($data['website']);

        CekirdexContact::create($data + [
            'source' => 'cekirdex_api',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        return response()->json([
            'message' => 'Mesajınız bize ulaştı. En kısa sürede dönüş yapacağız.',
        ], 201);
    }
}
