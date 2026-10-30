<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_response_has_security_headers_including_404(): void
    {
        foreach ([$this->get(route('home')), $this->get('/halaman-yang-tidak-ada')] as $response) {
            $response->assertHeader('X-Frame-Options', 'SAMEORIGIN')
                ->assertHeader('X-Content-Type-Options', 'nosniff')
                ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
                ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        }
    }

    public function test_hsts_is_only_sent_over_https(): void
    {
        $this->get('http://localhost/')->assertHeaderMissing('Strict-Transport-Security');
        $this->get('https://localhost/')->assertHeader('Strict-Transport-Security', 'max-age=31536000');
    }

    public function test_session_on_other_device_is_logged_out_after_password_changes(): void
    {
        $user = User::factory()->pembaca()->create();

        $this->actingAs($user)->get(route('profile.edit'))->assertOk();

        // Password diganti di tempat lain (perangkat lain, reset lewat email, atau oleh admin).
        $user->forceFill(['password' => 'password-baru-123'])->save();

        $this->get(route('profile.edit'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_user_who_changes_own_password_stays_logged_in(): void
    {
        $user = User::factory()->pembaca()->create();

        $this->actingAs($user)->get(route('profile.edit'))->assertOk();

        $this->put(route('profile.password'), [
            'current_password' => 'password',
            'password' => 'password-baru-123',
            'password_confirmation' => 'password-baru-123',
        ])->assertSessionHasNoErrors();

        $this->get(route('profile.edit'))->assertOk();
        $this->assertAuthenticatedAs($user);
    }

    public function test_changing_only_name_does_not_log_out(): void
    {
        $user = User::factory()->pembaca()->create();

        $this->actingAs($user)->get(route('profile.edit'))->assertOk();
        $user->update(['name' => 'Nama Baru']);

        $this->get(route('profile.edit'))->assertOk();
        $this->assertAuthenticatedAs($user);
    }
}
