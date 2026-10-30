<?php

namespace App\Support;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/*
 * Simpan gambar upload setelah digambar ulang dengan GD (bukan menyalin file aslinya).
 *
 * - Metadata EXIF ikut terbuang: foto ponsel biasanya menyimpan lokasi GPS, waktu, dan jenis perangkat, yang tidak
 *   boleh ikut terunduh publik.
 * - Yang disimpan hanya pikselnya, jadi file "gambar" yang disisipi data/kode lain ikut ternetralkan.
 * - Tag Orientation diterapkan dulu ke piksel; tanpa itu foto ponsel yang dipotret tegak bisa tampil miring.
 */
class ImageUpload
{
    private const EXTENSIONS = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];

    public static function store(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        $path = $file->getRealPath();
        $type = @getimagesize($path)[2] ?? null;
        $image = isset(self::EXTENSIONS[$type]) ? @imagecreatefromstring((string) file_get_contents($path)) : false;

        if (! $image) {
            throw ValidationException::withMessages(['image' => __('Gambar tidak dapat diproses. Coba file JPG, PNG, atau WebP lain.')]);
        }

        if ($type === IMAGETYPE_JPEG) {
            $image = self::applyOrientation($image, $path);
        }

        // Pertahankan transparansi PNG/WebP.
        imagealphablending($image, false);
        imagesavealpha($image, true);

        ob_start();
        match ($type) {
            IMAGETYPE_JPEG => imagejpeg($image, null, 85),
            IMAGETYPE_PNG => imagepng($image, null, 6),
            IMAGETYPE_WEBP => imagewebp($image, null, 85),
        };
        $contents = (string) ob_get_clean();

        $name = $directory.'/'.Str::random(40).'.'.self::EXTENSIONS[$type];
        Storage::disk($disk)->put($name, $contents);

        return $name;
    }

    /** Putar/balik piksel sesuai tag EXIF Orientation (1–8). imagerotate() berputar berlawanan arah jarum jam. */
    private static function applyOrientation(GdImage $image, string $path): GdImage
    {
        // Tanpa ekstensi exif (sering belum aktif di Windows) metadata tetap terbuang; hanya koreksi orientasi dilewati.
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $orientation = (int) (@exif_read_data($path)['Orientation'] ?? 1);

        $rotate = [3 => 180, 4 => 180, 5 => 270, 6 => 270, 7 => 90, 8 => 90][$orientation] ?? 0;
        if ($rotate !== 0) {
            $image = imagerotate($image, $rotate, 0);
        }
        if (in_array($orientation, [2, 4, 5, 7], true)) {
            imageflip($image, IMG_FLIP_HORIZONTAL);
        }

        return $image;
    }
}
