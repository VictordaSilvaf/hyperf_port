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

namespace App\Domain\Page\ValueObject;

use App\Domain\Upload\ValueObject\UploadId;
use InvalidArgumentException;

final class PageSeo
{
    private const ROBOTS = ['index,follow', 'noindex,nofollow', 'noindex,follow'];

    private const TWITTER_CARDS = ['summary', 'summary_large_image'];

    private function __construct(
        private readonly ?string $metaTitle,
        private readonly ?string $metaDescription,
        private readonly ?string $ogTitle,
        private readonly ?string $ogDescription,
        private readonly ?string $ogImageId,
        private readonly ?string $canonicalUrl,
        private readonly string $robots,
        private readonly string $twitterCard,
    ) {
    }

    /**
     * @param null|array<string, mixed> $data
     */
    public static function fromArray(?array $data): ?self
    {
        if ($data === null || $data === []) {
            return null;
        }

        $metaTitle = self::nullableString($data['meta_title'] ?? null);
        $metaDescription = self::nullableString($data['meta_description'] ?? null);
        $ogTitle = self::nullableString($data['og_title'] ?? null);
        $ogDescription = self::nullableString($data['og_description'] ?? null);
        $ogImageId = self::nullableString($data['og_image_id'] ?? null);
        $canonicalUrl = self::nullableString($data['canonical_url'] ?? null);

        if ($metaTitle !== null && mb_strlen($metaTitle) > 70) {
            throw new InvalidArgumentException('meta_title must be at most 70 characters.');
        }
        if ($metaDescription !== null && mb_strlen($metaDescription) > 160) {
            throw new InvalidArgumentException('meta_description must be at most 160 characters.');
        }
        if ($ogTitle !== null && mb_strlen($ogTitle) > 70) {
            throw new InvalidArgumentException('og_title must be at most 70 characters.');
        }
        if ($ogDescription !== null && mb_strlen($ogDescription) > 200) {
            throw new InvalidArgumentException('og_description must be at most 200 characters.');
        }
        if ($ogImageId !== null) {
            UploadId::fromString($ogImageId);
        }
        if ($canonicalUrl !== null && filter_var($canonicalUrl, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('canonical_url must be a valid URL.');
        }

        $robots = self::nullableString($data['robots'] ?? null) ?? 'index,follow';
        if (! in_array($robots, self::ROBOTS, true)) {
            throw new InvalidArgumentException('Invalid robots value.');
        }

        $twitterCard = self::nullableString($data['twitter_card'] ?? null) ?? 'summary_large_image';
        if (! in_array($twitterCard, self::TWITTER_CARDS, true)) {
            throw new InvalidArgumentException('Invalid twitter_card value.');
        }

        return new self(
            $metaTitle,
            $metaDescription,
            $ogTitle,
            $ogDescription,
            $ogImageId,
            $canonicalUrl,
            $robots,
            $twitterCard,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'meta_title' => $this->metaTitle,
            'meta_description' => $this->metaDescription,
            'og_title' => $this->ogTitle,
            'og_description' => $this->ogDescription,
            'og_image_id' => $this->ogImageId,
            'canonical_url' => $this->canonicalUrl,
            'robots' => $this->robots,
            'twitter_card' => $this->twitterCard,
        ], static fn ($v) => $v !== null);
    }

    public function metaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function metaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function ogTitle(): ?string
    {
        return $this->ogTitle;
    }

    public function ogDescription(): ?string
    {
        return $this->ogDescription;
    }

    public function ogImageId(): ?string
    {
        return $this->ogImageId;
    }

    public function canonicalUrl(): ?string
    {
        return $this->canonicalUrl;
    }

    public function robots(): string
    {
        return $this->robots;
    }

    public function twitterCard(): string
    {
        return $this->twitterCard;
    }

    private static function nullableString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
