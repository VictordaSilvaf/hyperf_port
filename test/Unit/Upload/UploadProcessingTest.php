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
use App\Application\Shared\PublicContentCacheInvalidatorInterface;
use App\Application\Storage\ObjectStorageInterface;
use App\Application\Upload\ImageProcessorInterface;
use App\Application\Upload\ProcessUploadImage\ProcessedImageResult;
use App\Application\Upload\ProcessUploadImage\ProcessUploadImageHandler;
use App\Application\Upload\StoreUpload\StoreUploadCommand;
use App\Application\Upload\StoreUpload\StoreUploadHandler;
use App\Domain\Upload\ValueObject\UploadId;
use App\Domain\Upload\ValueObject\UploadProcessingStatus;
use App\Infrastructure\Image\GdImageProcessor;
use App\Infrastructure\Persistence\Upload\InMemoryUploadRepository;
use App\Infrastructure\Queue\SyncUploadJobDispatcher;
use Psr\Log\LoggerInterface;

final class InMemoryObjectStorage implements ObjectStorageInterface
{
    /** @var array<string, string> */
    private array $files = [];

    public function write(string $path, string $contents): void
    {
        $this->files[$path] = $contents;
    }

    public function read(string $path): string
    {
        return $this->files[$path] ?? '';
    }

    public function delete(string $path): void
    {
        unset($this->files[$path]);
    }

    public function exists(string $path): bool
    {
        return isset($this->files[$path]);
    }

    public function publicUrl(string $path): ?string
    {
        return 'https://cdn.test/' . ltrim($path, '/');
    }
}

final class FakeImageProcessor implements ImageProcessorInterface
{
    public function supports(string $mimeType): bool
    {
        return str_starts_with($mimeType, 'image/');
    }

    public function process(string $contents, string $mimeType): ProcessedImageResult
    {
        return new ProcessedImageResult(
            'optimized-' . $contents,
            'image/jpeg',
            'webp-' . $contents,
            'thumb-' . $contents,
            800,
            600,
        );
    }
}

function uploadProcessingFixtures(): array
{
    $uploads = new InMemoryUploadRepository();
    $storage = new InMemoryObjectStorage();
    $processor = new FakeImageProcessor();
    $logger = new class implements LoggerInterface {
        public function emergency($message, array $context = []): void
        {
        }

        public function alert($message, array $context = []): void
        {
        }

        public function critical($message, array $context = []): void
        {
        }

        public function error($message, array $context = []): void
        {
        }

        public function warning($message, array $context = []): void
        {
        }

        public function notice($message, array $context = []): void
        {
        }

        public function info($message, array $context = []): void
        {
        }

        public function debug($message, array $context = []): void
        {
        }

        public function log($level, $message, array $context = []): void
        {
        }
    };

    $cacheInvalidator = new class implements PublicContentCacheInvalidatorInterface {
        public function invalidatePages(): void
        {
        }

        public function invalidateSite(): void
        {
        }

        public function invalidateProjects(): void
        {
        }
    };

    $process = new ProcessUploadImageHandler($uploads, $storage, $processor, $logger, $cacheInvalidator);
    $dispatcher = new SyncUploadJobDispatcher($process);
    $store = new StoreUploadHandler($uploads, $storage, $dispatcher);

    return compact('uploads', 'storage', 'process', 'store');
}

test('store upload enqueues image processing and saves variants', function () {
    $fixtures = uploadProcessingFixtures();

    $result = $fixtures['store']->handle(new StoreUploadCommand(
        'raw-image-bytes',
        'photo.jpg',
        'image/jpeg',
    ));

    expect($result['processing_status'])->toBe(UploadProcessingStatus::Completed->value);

    $upload = $fixtures['uploads']->findById(UploadId::fromString($result['id']));
    expect($upload?->processingStatus())->toBe(UploadProcessingStatus::Completed);
    expect($upload?->webpPath())->toEndWith('.webp');
    expect($upload?->thumbnailPath())->toEndWith('_thumb.webp');
    expect($upload?->width())->toBe(800);
    expect($upload?->displayUrl())->toContain('.webp');
    expect($fixtures['storage']->exists((string) $upload?->webpPath()))->toBeTrue();
});

test('non image upload skips processing pipeline', function () {
    $fixtures = uploadProcessingFixtures();

    $result = $fixtures['store']->handle(new StoreUploadCommand(
        '%PDF-1.4',
        'doc.pdf',
        'application/pdf',
    ));

    expect($result['processing_status'])->toBe(UploadProcessingStatus::Skipped->value);
});

test('gd image processor encodes webp when extension is available', function () {
    if (! extension_loaded('gd') || ! function_exists('imagewebp')) {
        expect(true)->toBeTrue();

        return;
    }

    $image = imagecreatetruecolor(1200, 800);
    ob_start();
    imagejpeg($image, null, 90);
    $contents = ob_get_clean() ?: '';
    imagedestroy($image);

    $processor = new GdImageProcessor();
    $result = $processor->process($contents, 'image/jpeg');

    expect($result->width)->toBeLessThanOrEqual(1200);
    expect($result->webpContents)->not->toBe('');
    expect($result->thumbnailContents)->not->toBe('');
})->group('gd');
