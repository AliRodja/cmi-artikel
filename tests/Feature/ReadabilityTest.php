<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

/** Penyesuaian untuk pembaca usia matang: tanggal pasti, pop-up gagal tidak hilang sendiri, tombol WA kontras. */
class ReadabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_date_is_relative_only_within_a_day_otherwise_exact(): void
    {
        Carbon::setTestNow('2026-10-28 15:00:00');

        $recent = Blade::render('<x-date :value="$value" />', ['value' => now()->subHours(3)]);
        $old = Blade::render('<x-date :value="$value" />', ['value' => Carbon::parse('2026-10-01 08:30')]);

        $this->assertStringContainsString('3 jam yang lalu', $recent);
        $this->assertStringContainsString('01 Okt 2026', $old);
        $this->assertStringContainsString('title="Kamis, 01 Oktober 2026, 08:30"', $old);
        $this->assertStringContainsString('datetime="2026-10-01T08:30:00', $old);
    }

    public function test_failure_popup_stays_until_closed_but_success_popup_has_timer(): void
    {
        $this->actingAs(User::factory()->create())
            ->from(route('articles.create'))
            ->followingRedirects()
            ->post(route('articles.store'), ['title' => ''])
            ->assertSee('data-toast', false)
            ->assertDontSee('data-toast-timer', false);

        $this->followingRedirects()
            ->post(route('logout'))
            ->assertSee('data-toast-timer', false)
            ->assertSee('[--toast-duration:8s]', false);
    }

    public function test_whatsapp_button_uses_high_contrast_green(): void
    {
        $this->get(route('home', ['search' => 'apa saja']))
            ->assertSee('bg-[#25D366]', false);
    }
}
