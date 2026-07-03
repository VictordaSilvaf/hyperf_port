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

namespace App\Application\Health\GetHealth;

final readonly class GetHealthQuery
{
    public const MODE_LIVE = 'live';

    public const MODE_READY = 'ready';

    public function __construct(
        public string $mode,
    ) {
    }
}
