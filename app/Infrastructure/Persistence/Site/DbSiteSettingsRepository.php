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

namespace App\Infrastructure\Persistence\Site;

use App\Domain\Site\Entity\SiteSettings;
use App\Domain\Site\Repository\SiteSettingsRepositoryInterface;
use DateTimeImmutable;
use Hyperf\DbConnection\Db;

final class DbSiteSettingsRepository implements SiteSettingsRepositoryInterface
{
    private const TABLE = 'site_settings';

    public function get(): SiteSettings
    {
        $row = Db::table(self::TABLE)->where('id', SiteSettings::SINGLETON_ID)->first();
        if ($row === null) {
            return SiteSettings::restore([]);
        }

        return $this->toDomain((array) $row);
    }

    public function save(SiteSettings $settings): void
    {
        $row = $this->toRow($settings);
        $exists = Db::table(self::TABLE)->where('id', $row['id'])->exists();
        if ($exists) {
            Db::table(self::TABLE)->where('id', $row['id'])->update($row);
        } else {
            Db::table(self::TABLE)->insert($row);
        }
    }

    /**
     * @param array<string, mixed> $row
     */
    private function toDomain(array $row): SiteSettings
    {
        $updatedAt = null;
        if (! empty($row['updated_at'])) {
            $updatedAt = new DateTimeImmutable((string) $row['updated_at']);
        }

        return SiteSettings::restore([
            'nav' => $this->decodeJson($row['nav'] ?? null),
            'footer' => $this->decodeJson($row['footer'] ?? null),
            'social' => $this->decodeJson($row['social'] ?? null),
            'branding' => $this->decodeJson($row['branding'] ?? null),
            'seo' => $this->decodeJson($row['seo'] ?? null),
            'contact' => $this->decodeJson($row['contact'] ?? null),
            'updated_at' => $updatedAt,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function toRow(SiteSettings $settings): array
    {
        return [
            'id' => $settings->id(),
            'nav' => $this->encodeJson($settings->nav()),
            'footer' => $this->encodeJson($settings->footer()),
            'social' => $this->encodeJson($settings->social()),
            'branding' => $this->encodeJson($settings->branding()),
            'seo' => $this->encodeJson($settings->seo()->toArray()),
            'contact' => $this->encodeJson($settings->contact()->toArray()),
            'updated_at' => ($settings->updatedAt() ?? new DateTimeImmutable())->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @return null|array<string, mixed>
     */
    private function decodeJson(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @param null|array<string, mixed> $value
     */
    private function encodeJson(?array $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return json_encode($value, JSON_THROW_ON_ERROR);
    }
}
