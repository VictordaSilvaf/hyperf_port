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

namespace App\Infrastructure\Security;

use App\Application\Contact\ContactCaptchaVerifierInterface;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Guzzle\ClientFactory;
use Throwable;

final class CloudflareTurnstileVerifier implements ContactCaptchaVerifierInterface
{
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function __construct(
        private readonly ClientFactory $clientFactory,
        private readonly ConfigInterface $config,
        private readonly StdoutLoggerInterface $logger,
    ) {
    }

    public function verify(string $token, ?string $remoteIp): bool
    {
        if (! (bool) $this->config->get('contact.turnstile.enabled', false)) {
            return true;
        }

        $secret = (string) $this->config->get('contact.turnstile.secret_key', '');
        if ($secret === '' || trim($token) === '') {
            return false;
        }

        try {
            $client = $this->clientFactory->create(['timeout' => 5]);
            $payload = [
                'secret' => $secret,
                'response' => $token,
            ];
            if ($remoteIp !== null && $remoteIp !== '') {
                $payload['remoteip'] = $remoteIp;
            }

            $response = $client->post(self::VERIFY_URL, [
                'form_params' => $payload,
            ]);

            $body = json_decode((string) $response->getBody(), true);

            return is_array($body) && ($body['success'] ?? false) === true;
        } catch (Throwable $e) {
            $this->logger->error(sprintf('[turnstile] verification failed: %s', $e->getMessage()));

            return false;
        }
    }
}
