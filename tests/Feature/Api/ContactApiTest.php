<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_stores_message(): void
    {
        $response = $this->postJson('/api/v1/contact', [
            'name'    => 'Ali Veli',
            'email'   => 'ali@example.com',
            'message' => 'Demo talep ediyorum, lütfen geri dönün.',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message']);

        $this->assertDatabaseHas('cekirdex_contacts', [
            'name'  => 'Ali Veli',
            'email' => 'ali@example.com',
        ]);
    }

    public function test_contact_form_silently_accepts_honeypot_filled(): void
    {
        $response = $this->postJson('/api/v1/contact', [
            'name'    => 'Ali Veli',
            'email'   => 'ali@example.com',
            'message' => 'Demo talep.',
            'website' => 'http://spam.example.com',
        ]);

        // website dolu olduğunda 422 (max:0 validasyon kuralı)
        $response->assertStatus(422);
    }

    public function test_contact_form_requires_name_email_message(): void
    {
        $this->postJson('/api/v1/contact', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'message']);
    }

    public function test_contact_form_requires_minimum_message_length(): void
    {
        $this->postJson('/api/v1/contact', [
            'name'    => 'Ali Veli',
            'email'   => 'ali@example.com',
            'message' => 'Kısa',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }

    public function test_contact_form_requires_valid_email(): void
    {
        $this->postJson('/api/v1/contact', [
            'name'    => 'Ali Veli',
            'email'   => 'gecersiz-email',
            'message' => 'Geçerli bir mesaj metni buraya yazılır.',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
