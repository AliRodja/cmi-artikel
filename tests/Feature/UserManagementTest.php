<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Dokter Budi',
            'email' => 'budi@cmi.test',
            'role' => User::ROLE_PENULIS,
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ], $overrides);
    }

    public function test_only_admin_can_open_user_management(): void
    {
        $this->actingAs(User::factory()->create())->get(route('users.index'))->assertForbidden();
        $this->actingAs(User::factory()->pembaca()->create())->get(route('users.index'))->assertForbidden();

        $this->actingAs(User::factory()->create())
            ->post(route('users.store'), $this->payload(['role' => User::ROLE_ADMIN]))
            ->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'budi@cmi.test']);

        $this->actingAs($this->admin)->get(route('users.index'))->assertOk();
    }

    public function test_admin_can_search_and_filter_by_role(): void
    {
        User::factory()->create(['name' => 'Siti Penulis', 'email' => 'siti@cmi.test']);
        User::factory()->pembaca()->create(['name' => 'Andi Pembaca', 'email' => 'andi@cmi.test']);

        $this->actingAs($this->admin);

        $this->get(route('users.index', ['search' => 'andi@cmi']))
            ->assertSee('Andi Pembaca')
            ->assertDontSee('Siti Penulis');

        $this->get(route('users.index', ['role' => User::ROLE_PENULIS]))
            ->assertSee('Siti Penulis')
            ->assertDontSee('Andi Pembaca');

        // Search tidak boleh menembus filter peran: (name OR email) AND role.
        $this->get(route('users.index', ['role' => User::ROLE_PENULIS, 'search' => 'andi']))
            ->assertDontSee('Andi Pembaca');
    }

    public function test_admin_can_create_user_with_role_and_user_can_log_in(): void
    {
        $this->actingAs($this->admin)
            ->post(route('users.store'), $this->payload())
            ->assertRedirect(route('users.index'));

        $user = User::where('email', 'budi@cmi.test')->firstOrFail();
        $this->assertSame(User::ROLE_PENULIS, $user->role);
        $this->assertTrue(Hash::check('rahasia123', $user->password), 'Password tersimpan sebagai hash.');

        $this->post('/logout');
        $this->post('/login', ['email' => 'budi@cmi.test', 'password' => 'rahasia123'])
            ->assertRedirect(route('dashboard'));
    }

    public function test_create_user_is_validated(): void
    {
        User::factory()->create(['email' => 'budi@cmi.test']);

        $this->actingAs($this->admin)
            ->post(route('users.store'), $this->payload(['role' => 'superadmin', 'password_confirmation' => 'beda']))
            ->assertSessionHasErrors(['email', 'role', 'password']);

        $this->post(route('users.store'), $this->payload(['email' => 'baru@cmi.test', 'password' => '', 'password_confirmation' => '']))
            ->assertSessionHasErrors('password');
    }

    public function test_admin_can_edit_user_and_empty_password_keeps_old_one(): void
    {
        $user = User::factory()->pembaca()->create();
        $oldHash = $user->password;

        $this->actingAs($this->admin)
            ->put(route('users.update', $user), $this->payload([
                'email' => $user->email,
                'password' => '',
                'password_confirmation' => '',
            ]))
            ->assertRedirect(route('users.index'));

        $user->refresh();
        $this->assertSame('Dokter Budi', $user->name);
        $this->assertSame(User::ROLE_PENULIS, $user->role);
        $this->assertSame($oldHash, $user->password);
    }

    public function test_admin_can_reset_someone_elses_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->admin)->put(route('users.update', $user), $this->payload([
            'password' => 'passwordbaru1',
            'password_confirmation' => 'passwordbaru1',
        ]));

        $this->assertTrue(Hash::check('passwordbaru1', $user->fresh()->password));
    }

    public function test_admin_cannot_change_own_role_or_delete_own_account(): void
    {
        $this->actingAs($this->admin)
            ->put(route('users.update', $this->admin), $this->payload(['email' => $this->admin->email, 'role' => User::ROLE_PEMBACA]))
            ->assertSessionHasErrors('role');
        $this->assertSame(User::ROLE_ADMIN, $this->admin->fresh()->role);

        // Tanpa role (form untuk akun sendiri memang tidak mengirimnya), nama tetap bisa diubah.
        $this->put(route('users.update', $this->admin), [
            'name' => 'Admin Baru', 'email' => $this->admin->email, 'password' => '', 'password_confirmation' => '',
        ])->assertSessionHasNoErrors();
        $this->assertSame('Admin Baru', $this->admin->fresh()->name);

        $this->delete(route('users.destroy', $this->admin))->assertForbidden();
        $this->assertModelExists($this->admin);
    }

    public function test_deleting_user_keeps_articles_without_author_and_removes_comments_bookmarks_notifications(): void
    {
        $penulis = User::factory()->create();
        $article = Article::factory()->for($penulis)->published()->create();
        $comment = Comment::factory()->for($article)->for($penulis)->create();
        $penulis->bookmarks()->attach($article);
        $penulis->notifications()->create([
            'id' => (string) str()->uuid(), 'type' => 'test', 'data' => ['title' => 'x'],
        ]);

        $this->actingAs($this->admin)
            ->delete(route('users.destroy', $penulis))
            ->assertRedirect(route('users.index'));

        $this->assertModelMissing($penulis);
        $this->assertNull($article->fresh()->user_id, 'Artikel tetap ada tanpa penulis.');
        $this->assertModelMissing($comment);
        $this->assertDatabaseCount('bookmarks', 0);
        $this->assertDatabaseCount('notifications', 0);
    }
}
