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

namespace App\Domain\Page\Block;

use App\Domain\Page\Exception\InvalidBlockPayloadException;

final class EmbedBlockValidator implements BlockValidatorInterface
{
    use BlockValidationHelpers;

    private const ALLOWED_PROVIDERS = ['youtube', 'github', 'codepen', 'generic'];

    public function type(): string
    {
        return 'embed';
    }

    public function label(): string
    {
        return 'Embed';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'required' => ['provider', 'url'],
            'properties' => [
                'provider' => ['type' => 'string', 'enum' => self::ALLOWED_PROVIDERS],
                'url' => ['type' => 'string', 'format' => 'uri'],
                'aspect_ratio' => ['type' => 'string', 'enum' => ['16:9', '4:3', '1:1']],
            ],
        ];
    }

    public function validate(array $payload): void
    {
        $this->requireEnum($payload, 'provider', self::ALLOWED_PROVIDERS, $this->type());
        $url = $this->requireNonEmptyString($payload, 'url', $this->type());
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw InvalidBlockPayloadException::forType($this->type(), 'url must be a valid URL.');
        }
        $this->optionalEnum($payload, 'aspect_ratio', ['16:9', '4:3', '1:1'], '16:9');
    }
}
