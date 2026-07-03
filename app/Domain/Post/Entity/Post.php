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

namespace App\Domain\Post\Entity;

use App\Domain\Post\ValueObject\PostId;
use App\Domain\Post\ValueObject\PostStatus;
use App\Domain\Project\ValueObject\ProjectId;
use InvalidArgumentException;

final class Post
{
    private function __construct(
        private readonly PostId $id,
        private readonly ProjectId $projectId,
        private readonly string $title,
        private readonly string $body,
        private readonly PostStatus $status,
    ) {
    }

    public static function create(
        ProjectId $projectId,
        string $title,
        string $body,
    ): self {
        $trimmedTitle = trim($title);
        if ($trimmedTitle === '') {
            throw new InvalidArgumentException('Post title cannot be empty.');
        }

        return new self(
            PostId::generate(),
            $projectId,
            $trimmedTitle,
            trim($body),
            PostStatus::Draft,
        );
    }

    public static function restore(
        PostId $id,
        ProjectId $projectId,
        string $title,
        string $body,
        PostStatus $status,
    ): self {
        return new self($id, $projectId, $title, $body, $status);
    }

    public function update(string $title, string $body): self
    {
        $trimmedTitle = trim($title);
        if ($trimmedTitle === '') {
            throw new InvalidArgumentException('Post title cannot be empty.');
        }

        return new self($this->id, $this->projectId, $trimmedTitle, trim($body), $this->status);
    }

    public function publish(): self
    {
        if ($this->status === PostStatus::Published) {
            return $this;
        }

        return new self($this->id, $this->projectId, $this->title, $this->body, PostStatus::Published);
    }

    public function id(): PostId
    {
        return $this->id;
    }

    public function projectId(): ProjectId
    {
        return $this->projectId;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function status(): PostStatus
    {
        return $this->status;
    }
}
