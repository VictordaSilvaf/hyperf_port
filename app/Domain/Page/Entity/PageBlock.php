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

namespace App\Domain\Page\Entity;

use App\Domain\Page\ValueObject\PageBlockId;
use App\Domain\Page\ValueObject\PageId;
use InvalidArgumentException;

final class PageBlock
{
    /**
     * @param array<string, mixed> $payload
     * @param null|array<string, mixed> $settings
     */
    private function __construct(
        private readonly PageBlockId $id,
        private readonly PageId $pageId,
        private readonly string $type,
        private readonly int $sortOrder,
        private readonly array $payload,
        private readonly ?array $settings,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @param null|array<string, mixed> $settings
     */
    public static function create(
        PageId $pageId,
        string $type,
        int $sortOrder,
        array $payload,
        ?array $settings = null,
    ): self {
        $type = trim($type);
        if ($type === '') {
            throw new InvalidArgumentException('Block type cannot be empty.');
        }

        return new self(
            PageBlockId::generate(),
            $pageId,
            $type,
            $sortOrder,
            $payload,
            $settings,
        );
    }

    /**
     * @param array{
     *   id: PageBlockId,
     *   page_id: PageId,
     *   type: string,
     *   sort_order: int,
     *   payload: array<string, mixed>,
     *   settings: null|array<string, mixed>,
     * } $data
     */
    public static function restore(array $data): self
    {
        return new self(
            $data['id'],
            $data['page_id'],
            $data['type'],
            $data['sort_order'],
            $data['payload'],
            $data['settings'],
        );
    }

    public function id(): PageBlockId
    {
        return $this->id;
    }

    public function pageId(): PageId
    {
        return $this->pageId;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }

    /** @return array<string, mixed> */
    public function payload(): array
    {
        return $this->payload;
    }

    /** @return null|array<string, mixed> */
    public function settings(): ?array
    {
        return $this->settings;
    }
}
