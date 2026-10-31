<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Tombol kontak melayang di kanan bawah: Konsultasi Gratis (WhatsApp) + telepon klinik. */
class FloatingContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_page_shows_floating_whatsapp_and_phone_buttons(): void
    {
        config(['clinic.phone' => '(022) 253 1000', 'clinic.phone_international' => '+62222531000']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Konsultasi Gratis')
            ->assertSee(config('clinic.whatsapp_consultation_url'))
            ->assertSee('Telepon klinik')
            ->assertSee('href="tel:+62222531000"', false);
    }

    public function test_phone_button_is_hidden_when_clinic_phone_is_empty(): void
    {
        config(['clinic.phone' => null, 'clinic.phone_international' => null]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Konsultasi Gratis')
            ->assertDontSee('Telepon klinik');
    }

    public function test_floating_buttons_are_hidden_in_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Konsultasi Gratis')
            ->assertDontSee('Telepon klinik');
    }
}