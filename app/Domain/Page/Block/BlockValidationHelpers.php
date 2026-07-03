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
use App\Domain\Project\ValueObject\ProjectId;
use App\Domain\Upload\ValueObject\UploadId;

trait BlockValidationHelpers
{
    protected function requireNonEmptyString(array $payload, string $key, string $type): string
    {
        if (! isset($payload[$key]) || ! is_string($payload[$key]) || trim($payload[$key]) === '') {
            throw InvalidBlockPayloadException::forType($type, sprintf('%s is required.', $key));
        }

        return trim($payload[$key]);
    }

    protected function optionalString(array $payload, string $key): ?string
    {
        if (! isset($payload[$key]) || ! is_string($payload[$key])) {
            return null;
        }
        $trimmed = trim($payload[$key]);

        return $trimmed === '' ? null : $trimmed;
    }

    protected function requireUuid(array $payload, string $key, string $type): string
    {
        $value = $this->requireNonEmptyString($payload, $key, $type);
        UploadId::fromString($value);

        return $value;
    }

    protected function optionalUuid(array $payload, string $key): ?string
    {
        $value = $this->optionalString($payload, $key);
        if ($value === null) {
            return null;
        }
        UploadId::fromString($value);

        return $value;
    }

    /** @return list<string> */
    protected function requireUuidList(array $payload, string $key, string $type): array
    {
        if (! isset($payload[$key]) || ! is_array($payload[$key]) || $payload[$key] === []) {
            throw InvalidBlockPayloadException::forType($type, sprintf('%s must be a non-empty array.', $key));
        }
        $ids = [];
        foreach ($payload[$key] as $item) {
            if (! is_string($item) || trim($item) === '') {
                throw InvalidBlockPayloadException::forType($type, sprintf('%s must contain valid UUIDs.', $key));
            }
            UploadId::fromString(trim($item));
            $ids[] = trim($item);
        }

        return $ids;
    }

    /** @return list<string> */
    protected function optionalProjectIdList(array $payload, string $key): array
    {
        if (! isset($payload[$key]) || ! is_array($payload[$key])) {
            return [];
        }
        $ids = [];
        foreach ($payload[$key] as $item) {
            if (! is_string($item) || trim($item) === '') {
                continue;
            }
            ProjectId::fromString(trim($item));
            $ids[] = trim($item);
        }

        return $ids;
    }

    protected function requireEnum(array $payload, string $key, array $allowed, string $type): string
    {
        $value = $this->requireNonEmptyString($payload, $key, $type);
        if (! in_array($value, $allowed, true)) {
            throw InvalidBlockPayloadException::forType($type, sprintf('Invalid %s value.', $key));
        }

        return $value;
    }

    protected function optionalEnum(array $payload, string $key, array $allowed, ?string $default = null): ?string
    {
        $value = $this->optionalString($payload, $key);
        if ($value === null) {
            return $default;
        }
        if (! in_array($value, $allowed, true)) {
            throw InvalidBlockPayloadException::forType($this->type(), sprintf('Invalid %s value.', $key));
        }

        return $value;
    }
}
