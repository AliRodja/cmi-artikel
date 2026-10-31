<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Cloudinary\Api\ApiResponse;
use Cloudinary\Api\Exception\GeneralError;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Cloudinary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/** Gambar artikel di Cloudinary. Tanpa internet: SDK diganti tiruan (mock) lewat service container. */
class CloudinaryImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Akun palsu: alamat gambar dihitung SDK tanpa menghubungi Cloudinary.
        config([
            'filesystems.article_images' => 'cloudinary',
            'services.cloudinary.url' => 'cloudinary://kunci:rahasia@cloud-uji',
            'services.cloudinary.folder' => 'cmi-artikel',
        ]);
        Storage::fake('public');
    }

    /** Ganti SDK Cloudinary dengan tiruan yang hanya punya uploadApi(). */
    private function fakeUploadApi(): MockInterface
    {
        $uploadApi = Mockery::mock(UploadApi::class);
        $this->mock(Cloudinary::class)->shouldReceive('uploadApi')->andReturn($uploadApi);

        return $uploadApi;
    }

    private function storeArticleWithImage(): TestResponse
    {
        return $this->actingAs(User::factory()->create())->post(route('articles.store'), [
            'title' => 'Mengenal Detak Jantung',
            'content' => '<div>Isi artikel.</div>',
            'status' => Article::STATUS_DRAFT,
            'image' => UploadedFile::fake()->image('jantung.jpg', 40, 20),
        ]);
    }

    public function test_uploaded_image_goes_to_cloudinary_folder_not_local_disk(): void
    {
        $this->fakeUploadApi()->shouldReceive('upload')->once()
            ->withArgs(fn ($file, $options) => str_starts_with($file, 'data:image/jpeg;base64,')
                && $options['folder'] === 'cmi-artikel/articles')
            ->andReturn(new ApiResponse(['public_id' => 'cmi-artikel/articles/abc123'], []));

        $this->storeArticleWithImage()->assertSessionHasNoErrors();

        $this->assertSame('cloudinary:cmi-artikel/articles/abc123', Article::firstOrFail()->image);
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    public function test_failed_upload_shows_form_error_instead_of_server_error(): void
    {
        $this->fakeUploadApi()->shouldReceive('upload')->andThrow(new GeneralError('Koneksi gagal'));

        $this->storeArticleWithImage()
            ->assertRedirect()
            ->assertSessionHasErrors(['image' => 'Gambar gagal diunggah. Periksa koneksi internet lalu coba lagi.']);

        $this->assertSame(0, Article::count());
    }

    public function test_image_url_uses_cloudinary_cdn_with_automatic_format_and_quality(): void
    {
        $cloud = Article::factory()->make(['image' => 'cloudinary:cmi-artikel/articles/abc123']);
        $local = Article::factory()->make(['image' => 'articles/lama.webp']);

        $this->assertStringStartsWith('https://res.cloudinary.com/cloud-uji/image/upload/f_auto/q_auto/', $cloud->image_url);
        $this->assertStringContainsString('cmi-artikel/articles/abc123', $cloud->image_url);
        // Gambar lama (sebelum Cloudinary) tetap dari storage sendiri.
        $this->assertSame(asset('storage/articles/lama.webp'), $local->image_url);
    }

    public function test_deleting_article_removes_its_image_from_cloudinary(): void
    {
        $article = Article::factory()->draft()->create(['image' => 'cloudinary:cmi-artikel/articles/lama']);

        $this->fakeUploadApi()->shouldReceive('destroy')->once()->with('cmi-artikel/articles/lama');

        $this->actingAs($article->user)->delete(route('articles.destroy', $article))->assertRedirect();

        $this->assertModelMissing($article);
    }

    public function test_command_moves_old_local_images_to_cloudinary_without_touching_updated_at(): void
    {
        Storage::disk('public')->put('articles/lama.jpg', UploadedFile::fake()->image('lama.jpg', 20, 10)->get());
        $old = Article::factory()->published()->create(['image' => 'articles/lama.jpg', 'updated_at' => '2026-10-01 08:00:00']);
        $missing = Article::factory()->published()->create(['image' => 'articles/hilang.jpg']);
        $done = Article::factory()->published()->create(['image' => 'cloudinary:cmi-artikel/articles/sudah']);

        $this->fakeUploadApi()->shouldReceive('upload')->once()
            ->withArgs(fn ($file, $options) => str_starts_with($file, 'data:image/jpeg;base64,')
                && $options['folder'] === 'cmi-artikel/articles')
            ->andReturn(new ApiResponse(['public_id' => 'cmi-artikel/articles/baru'], []));

        $this->artisan('images:to-cloudinary')
            ->expectsOutputToContain('file articles/hilang.jpg tidak ditemukan')
            ->expectsOutputToContain('Selesai: 1 dari 2 gambar dipindah')
            ->assertExitCode(0);

        $this->assertSame('cloudinary:cmi-artikel/articles/baru', $old->fresh()->image);
        $this->assertEquals('2026-10-01 08:00:00', $old->fresh()->updated_at);
        $this->assertSame('articles/hilang.jpg', $missing->fresh()->image);
        $this->assertSame('cloudinary:cmi-artikel/articles/sudah', $done->fresh()->image);
        // File lokal tetap ada sebagai cadangan.
        Storage::disk('public')->assertExists('articles/lama.jpg');
    }

    public function test_command_dry_run_changes_nothing(): void
    {
        Storage::disk('public')->put('articles/lama.jpg', UploadedFile::fake()->image('lama.jpg')->get());
        $article = Article::factory()->published()->create(['image' => 'articles/lama.jpg']);

        $this->fakeUploadApi()->shouldNotReceive('upload');

        $this->artisan('images:to-cloudinary --dry-run')
            ->expectsOutputToContain('Akan dipindah: #'.$article->id)
            ->assertExitCode(0);

        $this->assertSame('articles/lama.jpg', $article->fresh()->image);
    }
}