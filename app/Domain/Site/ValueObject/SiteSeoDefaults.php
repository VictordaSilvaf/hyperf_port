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

namespace App\Domain\Site\ValueObject;

use App\Domain\Upload\ValueObject\UploadId;
use InvalidArgumentException;

final class SiteSeoDefaults
{
    private function __construct(
        private readonly string $siteName,
        private readonly ?string $defaultMetaDescription,
        private readonly ?string $defaultOgImageId,
        private readonly ?string $twitterSite,
        private readonly ?string $googleSiteVerification,
        private readonly string $locale,
    ) {
    }

    /**
     * @param null|array<string, mixed> $data
     */
    public static function fromArray(?array $data): self
    {
        $data = $data ?? [];

        $siteName = trim((string) ($data['site_name'] ?? 'Victor Dev'));
        if ($siteName === '') {
            throw new InvalidArgumentException('site_name cannot be empty.');
        }

        $defaultMetaDescription = self::nullableString($data['default_meta_description'] ?? null);
        $defaultOgImageId = self::nullableString($data['default_og_image_id'] ?? null);
        if ($defaultOgImageId !== null) {
            UploadId::fromString($defaultOgImageId);
        }

        return new self(
            $siteName,
            $defaultMetaDescription,
            $defaultOgImageId,
            self::nullableString($data['twitter_site'] ?? null),
            self::nullableString($data['google_site_verification'] ?? null),
            self::nullableString($data['locale'] ?? null) ?? 'pt_BR',
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'site_name' => $this->siteName,
            'default_meta_description' => $this->defaultMetaDescription,
            'default_og_image_id' => $this->defaultOgImageId,
            'twitter_site' => $this->twitterSite,
            'google_site_verification' => $this->googleSiteVerification,
            'locale' => $this->locale,
        ], static fn ($v) => $v !== null);
    }

    public function siteName(): string
    {
        return $this->siteName;
    }

    public function defaultMetaDescription(): ?string
    {
        return $this->defaultMetaDescription;
    }

    public function defaultOgImageId(): ?string
    {
        return $this->defaultOgImageId;
    }

    public function twitterSite(): ?string
    {
        return $this->twitterSite;
    }

    public function googleSiteVerification(): ?string
    {
        return $this->googleSiteVerification;
    }

    public function locale(): string
    {
        return $this->locale;
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
