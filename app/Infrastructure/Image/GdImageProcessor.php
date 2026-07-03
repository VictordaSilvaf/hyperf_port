<?php

declare(strict_types=1);
/**
 * Hyperf API — DDD / Hexagonal
 *
 * @link     https://github.com/VictordaSilvaf/hyperf_port
 * @document https://github.com/VictordaSilvaf/hyperf_port/doc
 * @contact  victordasilvafernandes@gmail.com
 * @see      https://github.com/VictordaSilvaf/hyperf_port.git
 */

namespace App\Infrastructure\Image;

use App\Application\Upload\ImageProcessorInterface;
use App\Application\Upload\ProcessUploadImage\ProcessedImageResult;
use GdImage;
use RuntimeException;

use function Hyperf\Config\config;

final class GdImageProcessor implements ImageProcessorInterface
{
    private const SUPPORTED = [
        'image/jpeg' => IMAGETYPE_JPEG,
        'image/png' => IMAGETYPE_PNG,
        'image/webp' => IMAGETYPE_WEBP,
        'image/gif' => IMAGETYPE_GIF,
    ];

    public function supports(string $mimeType): bool
    {
        return isset(self::SUPPORTED[strtolower($mimeType)]) && extension_loaded('gd');
    }

    public function process(string $contents, string $mimeType): ProcessedImageResult
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('GD extension is required for image processing.');
        }

        $mimeType = strtolower($mimeType);
        if (! isset(self::SUPPORTED[$mimeType])) {
            throw new RuntimeException('Unsupported image mime type: ' . $mimeType);
        }

        $source = @imagecreatefromstring($contents);
        if ($source === false) {
            throw new RuntimeException('Unable to decode image.');
        }

        try {
            $maxWidth = (int) config('upload.max_width', 2048);
            $maxHeight = (int) config('upload.max_height', 2048);
            $jpegQuality = (int) config('upload.jpeg_quality', 85);
            $webpQuality = (int) config('upload.webp_quality', 82);
            $thumbWidth = (int) config('upload.thumbnail_width', 400);
            $thumbHeight = (int) config('upload.thumbnail_height', 400);

            $source = $this->fixOrientation($source, $contents);
            $source = $this->resizeDown($source, $maxWidth, $maxHeight);

            $width = imagesx($source);
            $height = imagesy($source);

            $optimizedMime = $mimeType === 'image/png' ? 'image/png' : 'image/jpeg';
            $optimizedContents = $this->encode($source, $optimizedMime, $jpegQuality, $webpQuality);
            $webpContents = $this->encodeWebp($source, $webpQuality);
            $thumbnailContents = $this->encodeWebp(
                $this->coverThumbnail($source, $thumbWidth, $thumbHeight),
                $webpQuality,
            );

            return new ProcessedImageResult(
                $optimizedContents,
                $optimizedMime,
                $webpContents,
                $thumbnailContents,
                $width,
                $height,
            );
        } finally {
            imagedestroy($source);
        }
    }

    private function fixOrientation(GdImage $image, string $contents): GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $stream = fopen('php://memory', 'rb+');
        if ($stream === false) {
            return $image;
        }

        fwrite($stream, $contents);
        rewind($stream);
        $exif = @exif_read_data($stream, 'IFD0');
        fclose($stream);

        if (! is_array($exif) || ! isset($exif['Orientation'])) {
            return $image;
        }

        return match ((int) $exif['Orientation']) {
            3 => imagerotate($image, 180, 0) ?: $image,
            6 => imagerotate($image, -90, 0) ?: $image,
            8 => imagerotate($image, 90, 0) ?: $image,
            default => $image,
        };
    }

    private function resizeDown(GdImage $image, int $maxWidth, int $maxHeight): GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return $image;
        }

        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $targetWidth = max(1, (int) round($width * $ratio));
        $targetHeight = max(1, (int) round($height * $ratio));

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        if ($canvas === false) {
            return $image;
        }

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        imagedestroy($image);

        return $canvas;
    }

    private function coverThumbnail(GdImage $image, int $targetWidth, int $targetHeight): GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $ratio = max($targetWidth / $width, $targetHeight / $height);
        $scaledWidth = (int) round($width * $ratio);
        $scaledHeight = (int) round($height * $ratio);

        $scaled = imagecreatetruecolor($scaledWidth, $scaledHeight);
        if ($scaled === false) {
            return $image;
        }

        imagecopyresampled($scaled, $image, 0, 0, 0, 0, $scaledWidth, $scaledHeight, $width, $height);

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        if ($canvas === false) {
            imagedestroy($scaled);

            return $image;
        }

        $offsetX = (int) max(0, ($scaledWidth - $targetWidth) / 2);
        $offsetY = (int) max(0, ($scaledHeight - $targetHeight) / 2);
        imagecopy($canvas, $scaled, 0, 0, $offsetX, $offsetY, $targetWidth, $targetHeight);
        imagedestroy($scaled);

        return $canvas;
    }

    private function encode(GdImage $image, string $mimeType, int $jpegQuality, int $webpQuality): string
    {
        ob_start();
        if ($mimeType === 'image/png') {
            imagepng($image, null, 6);
        } elseif ($mimeType === 'image/webp') {
            imagewebp($image, null, $webpQuality);
        } else {
            imagejpeg($image, null, $jpegQuality);
        }
        $contents = ob_get_clean();

        if ($contents === false || $contents === '') {
            throw new RuntimeException('Failed to encode optimized image.');
        }

        return $contents;
    }

    private function encodeWebp(GdImage $image, int $quality): string
    {
        if (! function_exists('imagewebp')) {
            throw new RuntimeException('WebP support is not available in GD.');
        }

        ob_start();
        imagewebp($image, null, $quality);
        $contents = ob_get_clean();

        if ($contents === false || $contents === '') {
            throw new RuntimeException('Failed to encode WebP image.');
        }

        return $contents;
    }
}
