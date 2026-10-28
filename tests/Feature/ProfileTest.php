<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_role_can_open_own_account_page_but_guest_cannot(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));

        foreach ([User::factory()->pembaca()->create(), User::factory()->create(), User::factory()->admin()->create()] as $user) {
            $this->actingAs($user)->get(route('profile.edit'))->assertOk()->assertSee($user->email);
        }
    }

    public function test_navbar_links_own_account_for_everyone_and_user_management_only_for_admin(): void
    {
        $this->actingAs(User::factory()->pembaca()->create())
            ->get(route('home'))
            ->assertSee(route('profile.edit'))
            ->assertDontSee(route('users.index'));

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('home'))
            ->assertSee(route('profile.edit'))
            ->assertSee(route('users.index'));
    }

    public function test_user_can_update_own_name_and_email_but_not_role(): void
    {
        $user = User::factory()->pembaca()->create();
        $taken = User::factory()->create(['email' => 'dipakai@cmi.test']);

        $this->actingAs($user)
            ->patch(route('profile.update'), ['name' => 'Nama Baru', 'email' => $taken->email])
            ->assertSessionHasErrors('email');

        $this->patch(route('profile.update'), ['name' => 'Nama Baru', 'email' => 'baru@cmi.test', 'role' => User::ROLE_ADMIN])
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame('baru@cmi.test', $user->email);
        $this->assertSame(User::ROLE_PEMBACA, $user->role, 'Peran tidak bisa diubah lewat Kelola Akun.');
    }

    public function test_password_change_requires_correct_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('profile.password'), [
                'current_password' => 'salah-total',
                'password' => 'passwordbaru1',
                'password_confirmation' => 'passwordbaru1',
            ])
            ->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('password', $user->fresh()->password));

        $this->put(route('profile.password'), [
            'current_password' => 'password',
            'password' => 'passwordbaru1',
            'password_confirmation' => 'passwordbaru1',
        ])->assertSessionHas('success', 'Password Anda berhasil diganti.');

        $this->assertTrue(Hash::check('passwordbaru1', $user->fresh()->password));
    }

    public function test_new_password_must_differ_and_be_confirmed(): void
    {
        $this->actingAs(User::factory()->create())
            ->put(route('profile.password'), [
                'current_password' => 'password',
                'password' => 'password',
                'password_confirmation' => 'beda',
            ])
            ->assertSessionHasErrors('password');
    }
}
