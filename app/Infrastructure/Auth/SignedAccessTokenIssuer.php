<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Application\Auth\AccessTokenIssuerInterface;
use App\Domain\User\ValueObject\UserId;
use JsonException;
use function Hyperf\Support\env;

final class SignedAccessTokenIssuer implements AccessTokenIssuerInterface
{
    public function issue(string $userId): string
    {
        UserId::fromString($userId);

        $exp = time() + (int) env('APP_AUTH_TOKEN_TTL', 604800);
        $payload = json_encode(['sub' => $userId, 'exp' => $exp], JSON_THROW_ON_ERROR);
        $payloadB64 = $this->base64UrlEncode($payload);
        $sig = hash_hmac('sha256', $payloadB64, $this->secret(), true);

        return $payloadB64 . '.' . $this->base64UrlEncode($sig);
    }

    public function parseUserId(string $token): ?string
    {
        $parts = explode('.', $token, 2);
        if (count($parts) !== 2) {
            return null;
        }

        [$payloadB64, $sigB64] = $parts;
        $expected = hash_hmac('sha256', $payloadB64, $this->secret(), true);
        $sig = $this->base64UrlDecode($sigB64);
        if ($sig === '' || ! hash_equals($expected, $sig)) {
            return null;
        }

        try {
            $payload = json_decode($this->base64UrlDecode($payloadB64), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        if (! is_array($payload)) {
            return null;
        }

        if (($payload['exp'] ?? 0) < time()) {
            return null;
        }

        $sub = $payload['sub'] ?? '';
        if (! is_string($sub) || $sub === '') {
            return null;
        }

        try {
            UserId::fromString($sub);
        } catch (\InvalidArgumentException) {
            return null;
        }

        return $sub;
    }

    private function secret(): string
    {
        return (string) env('APP_AUTH_SECRET', 'dev-secret-change-me');
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        $padding = strlen($data) % 4;
        if ($padding > 0) {
            $data .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode(strtr($data, '-_', '+/'), true);

        return $decoded === false ? '' : $decoded;
    }
}
