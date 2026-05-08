<?php

namespace App\Http\Controllers\Cekirdex;

use App\Cekirdex\Models\CekirdexContact;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ContactController extends Controller
{
    public function submit(Request $request)
    {
        // Basit rate limit: aynı IP dakikada en fazla 3 kez
        $key = 'cekirdex-contact:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()->withErrors(['message' => 'Çok hızlı gönderiyorsunuz. Lütfen biraz sonra tekrar deneyin.'])
                ->withInput();
        }
        RateLimiter::hit($key, 60);

        $data = $request->validate([
            'name'            => 'required|string|max:120',
            'email'           => 'required|email|max:160',
            'phone'           => 'nullable|string|max:32',
            'restaurant_name' => 'nullable|string|max:160',
            'city'            => 'nullable|string|max:80',
            'subject'         => 'nullable|string|max:200',
            'message'         => 'required|string|min:10|max:5000',
            'website'         => 'nullable|max:0', // honeypot — boş olmalı
        ], [], [
            'name'    => 'isim',
            'email'   => 'e-posta',
            'message' => 'mesaj',
        ]);

        // Honeypot dolduysa sessizce başarı dön
        if (!empty($request->input('website'))) {
            return back()->with('success', 'Mesajınız alındı. En kısa sürede dönüş yapacağız.');
        }

        unset($data['website']);
        $data['source']     = 'cekirdex_landing';
        $data['ip_address'] = $request->ip();
        $data['user_agent'] = substr((string) $request->userAgent(), 0, 500);

        CekirdexContact::create($data);

        return back()->with('success', 'Mesajınız bize ulaştı. En kısa sürede dönüş yapacağız.');
    }
}
