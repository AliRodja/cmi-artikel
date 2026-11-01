<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use App\Support\ImageUpload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Gambar artikel yang diunggah selalu dikompres ke WebP dan diperkecil bila terlalu lebar. */
class ImageCompressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    private function upload(UploadedFile $image): string
    {
        $this->actingAs(User::factory()->create())->post(route('articles.store'), [
            'title' => 'Gambar Jantung',
            'content' => '<div>Isi artikel.</div>',
            'status' => Article::STATUS_DRAFT,
            'image' => $image,
        ])->assertSessionHasNoErrors();

        return Article::firstOrFail()->image;
    }

    public function test_uploaded_jpg_is_stored_as_webp(): void
    {
        $path = $this->upload(UploadedFile::fake()->image('jantung.jpg', 300, 200));

        $this->assertStringEndsWith('.webp', $path);
        $stored = Storage::disk('public')->get($path);
        $this->assertSame(IMAGETYPE_WEBP, getimagesizefromstring($stored)[2]);
        $this->assertSame([300, 200], array_slice(getimagesizefromstring($stored), 0, 2));
    }

    public function test_wide_image_is_scaled_down_keeping_its_proportion(): void
    {
        $path = $this->upload(UploadedFile::fake()->image('lebar.jpg', 4000, 2500));

        [$width, $height] = getimagesizefromstring(Storage::disk('public')->get($path));
        $this->assertSame([ImageUpload::MAX_WIDTH, 1000], [$width, $height]);
    }

    public function test_webp_result_is_smaller_than_uncompressed_png(): void
    {
        // Foto berderau (noise) acak: PNG tanpa kompresi lossy jauh lebih besar dari WebP.
        $gd = imagecreatetruecolor(800, 500);
        for ($y = 0; $y < 500; $y += 2) {
            for ($x = 0; $x < 800; $x += 2) {
                imagefilledrectangle($gd, $x, $y, $x + 1, $y + 1, imagecolorallocate($gd, ($x * 7) % 256, ($y * 3) % 256, ($x + $y) % 256));
            }
        }
        ob_start();
        imagepng($gd, null, 0);
        $png = (string) ob_get_clean();

        $path = $this->upload(UploadedFile::fake()->createWithContent('foto.png', $png));

        $this->assertStringEndsWith('.webp', $path);
        $this->assertLessThan(strlen($png) / 5, strlen(Storage::disk('public')->get($path)));
    }

    public function test_png_transparency_survives_conversion(): void
    {
        // PNG berpalet (8-bit) dengan latar transparan, mis. logo.
        $gd = imagecreate(20, 20);
        imagecolortransparent($gd, imagecolorallocate($gd, 255, 255, 255));
        imagefilledrectangle($gd, 5, 5, 14, 14, imagecolorallocate($gd, 6, 106, 201));
        ob_start();
        imagepng($gd);
        $png = (string) ob_get_clean();

        $path = $this->upload(UploadedFile::fake()->createWithContent('logo.png', $png));

        $webp = imagecreatefromstring(Storage::disk('public')->get($path));
        $this->assertSame(127, imagecolorsforindex($webp, imagecolorat($webp, 0, 0))['alpha']);
        $this->assertSame(0, imagecolorsforindex($webp, imagecolorat($webp, 10, 10))['alpha']);
    }
}
