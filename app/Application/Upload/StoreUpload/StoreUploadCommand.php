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

namespace App\Application\Upload\StoreUpload;

final class StoreUploadCommand
{
    public function __construct(
        public readonly string $contents,
        public readonly string $originalName,
        public readonly string $mimeType,
    ) {
    }
}
