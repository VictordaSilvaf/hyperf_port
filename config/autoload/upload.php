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
use function Hyperf\Support\env;

return [
    'max_width' => (int) env('UPLOAD_IMAGE_MAX_WIDTH', 2048),
    'max_height' => (int) env('UPLOAD_IMAGE_MAX_HEIGHT', 2048),
    'jpeg_quality' => (int) env('UPLOAD_JPEG_QUALITY', 85),
    'webp_quality' => (int) env('UPLOAD_WEBP_QUALITY', 82),
    'thumbnail_width' => (int) env('UPLOAD_THUMB_WIDTH', 400),
    'thumbnail_height' => (int) env('UPLOAD_THUMB_HEIGHT', 400),
    'queue_processing' => filter_var(env('UPLOAD_QUEUE_PROCESSING', true), FILTER_VALIDATE_BOOLEAN),
];
