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

namespace App\Application\Upload\ProcessUploadImage;

final class ProcessedImageResult
{
    public function __construct(
        public readonly string $optimizedContents,
        public readonly string $optimizedMimeType,
        public readonly string $webpContents,
        public readonly string $thumbnailContents,
        public readonly int $width,
        public readonly int $height,
    ) {
    }
}
