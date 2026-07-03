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

namespace App\Domain\Site\Entity;

use App\Domain\Site\ValueObject\SiteSeoDefaults;
use DateTimeImmutable;

final class SiteSettings
{
    public const SINGLETON_ID = '00000000-0000-4000-8000-000000000001';

    /**
     * @param null|array<string, mixed> $nav
     * @param null|array<string, mixed> $footer
     * @param null|array<string, mixed> $social
     * @param null|array<string, mixed> $branding
     */
    private function __construct(
        private readonly string $id,
        private readonly ?array $nav,
        private readonly ?array $footer,
        private readonly ?array $social,
        private readonly ?array $branding,
        private readonly SiteSeoDefaults $seo,
        private readonly ?DateTimeImmutable $updatedAt,
    ) {
    }

    /**
     * @param array{
     *   nav?: null|array<string, mixed>,
     *   footer?: null|array<string, mixed>,
     *   social?: null|array<string, mixed>,
     *   branding?: null|array<string, mixed>,
     *   seo?: null|array<string, mixed>,
     *   updated_at?: null|DateTimeImmutable,
     * } $data
     */
    public static function restore(array $data): self
    {
        return new self(
            self::SINGLETON_ID,
            $data['nav'] ?? null,
            $data['footer'] ?? null,
            $data['social'] ?? null,
            $data['branding'] ?? null,
            SiteSeoDefaults::fromArray($data['seo'] ?? null),
            $data['updated_at'] ?? null,
        );
    }

    /** @param array<string, mixed> $changes */
    public function replace(array $changes): self
    {
        return self::restore([
            'nav' => array_key_exists('nav', $changes) ? $changes['nav'] : $this->nav,
            'footer' => array_key_exists('footer', $changes) ? $changes['footer'] : $this->footer,
            'social' => array_key_exists('social', $changes) ? $changes['social'] : $this->social,
            'branding' => array_key_exists('branding', $changes) ? $changes['branding'] : $this->branding,
            'seo' => array_key_exists('seo', $changes)
                ? ($changes['seo'] instanceof SiteSeoDefaults ? $changes['seo']->toArray() : $changes['seo'])
                : $this->seo->toArray(),
            'updated_at' => $changes['updated_at'] ?? new DateTimeImmutable(),
        ]);
    }

    public function id(): string
    {
        return $this->id;
    }

    /** @return null|array<string, mixed> */
    public function nav(): ?array
    {
        return $this->nav;
    }

    /** @return null|array<string, mixed> */
    public function footer(): ?array
    {
        return $this->footer;
    }

    /** @return null|array<string, mixed> */
    public function social(): ?array
    {
        return $this->social;
    }

    /** @return null|array<string, mixed> */
    public function branding(): ?array
    {
        return $this->branding;
    }

    public function seo(): SiteSeoDefaults
    {
        return $this->seo;
    }

    public function updatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
