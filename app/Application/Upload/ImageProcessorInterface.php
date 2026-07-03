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

namespace App\Application\Upload;

use App\Application\Upload\ProcessUploadImage\ProcessedImageResult;

interface ImageProcessorInterface
{
    public function supports(string $mimeType): bool;

    public function process(string $contents, string $mimeType): ProcessedImageResult;
}
