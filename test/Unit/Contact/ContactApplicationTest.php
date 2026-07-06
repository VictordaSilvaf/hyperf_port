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
use App\Application\Contact\ContactCaptchaVerifierInterface;
use App\Application\Contact\GetContactMessage\GetContactMessageHandler;
use App\Application\Contact\GetContactMessage\GetContactMessageQuery;
use App\Application\Contact\ListContactMessages\ListContactMessagesHandler;
use App\Application\Contact\ListContactMessages\ListContactMessagesQuery;
use App\Application\Contact\SubmitContactMessage\SubmitContactMessageCommand;
use App\Application\Contact\SubmitContactMessage\SubmitContactMessageHandler;
use App\Application\Contact\UpdateContactMessageStatus\UpdateContactMessageStatusCommand;
use App\Application\Contact\UpdateContactMessageStatus\UpdateContactMessageStatusHandler;
use App\Domain\Contact\Exception\ContactCaptchaFailedException;
use App\Domain\Contact\Exception\ContactMessageNotFoundException;
use App\Infrastructure\Mail\NoOpContactMessageNotifier;
use App\Infrastructure\Persistence\Contact\InMemoryContactMessageRepository;
use App\Infrastructure\Persistence\Site\InMemorySiteSettingsRepository;
use App\Infrastructure\Security\NoOpContactCaptchaVerifier;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;

class ContactTestConfig implements ConfigInterface
{
    public function get(string $key, mixed $default = null): mixed
    {
        return match ($key) {
            'mail.contact_to' => 'admin@example.com',
            'contact.turnstile.enabled' => false,
            default => $default,
        };
    }

    public function has(string $keys): bool
    {
        return true;
    }

    public function set(string $key, mixed $value): void
    {
    }
}

class ContactTestLogger implements StdoutLoggerInterface
{
    /** @var list<string> */
    public array $logs = [];

    public function emergency($message, array $context = []): void
    {
        $this->logs[] = (string) $message;
    }

    public function alert($message, array $context = []): void
    {
        $this->logs[] = (string) $message;
    }

    public function critical($message, array $context = []): void
    {
        $this->logs[] = (string) $message;
    }

    public function error($message, array $context = []): void
    {
        $this->logs[] = (string) $message;
    }

    public function warning($message, array $context = []): void
    {
        $this->logs[] = (string) $message;
    }

    public function notice($message, array $context = []): void
    {
        $this->logs[] = (string) $message;
    }

    public function info($message, array $context = []): void
    {
        $this->logs[] = (string) $message;
    }

    public function debug($message, array $context = []): void
    {
        $this->logs[] = (string) $message;
    }

    public function log($level, $message, array $context = []): void
    {
        $this->logs[] = (string) $message;
    }
}

class FailingContactCaptchaVerifier implements ContactCaptchaVerifierInterface
{
    public function verify(string $token, ?string $remoteIp): bool
    {
        return false;
    }
}

function contactFixtures(): array
{
    $messages = new InMemoryContactMessageRepository();
    $settings = new InMemorySiteSettingsRepository();
    $notifier = new NoOpContactMessageNotifier();
    $captcha = new NoOpContactCaptchaVerifier();
    $config = new ContactTestConfig();
    $logger = new ContactTestLogger();

    $submit = new SubmitContactMessageHandler($messages, $settings, $notifier, $captcha, $config, $logger);
    $list = new ListContactMessagesHandler($messages);
    $get = new GetContactMessageHandler($messages);
    $update = new UpdateContactMessageStatusHandler($messages);

    return compact('messages', 'settings', 'notifier', 'captcha', 'config', 'logger', 'submit', 'list', 'get', 'update');
}

test('submit contact message persists and notifies', function () {
    $fixtures = contactFixtures();

    $fixtures['submit']->handle(new SubmitContactMessageCommand(
        name: 'Jane Doe',
        email: 'jane@example.com',
        subject: 'Hello',
        body: 'This is a test message body.',
        captchaToken: null,
        ipAddress: '127.0.0.1',
        userAgent: 'Pest',
    ));

    $result = $fixtures['list']->handle(new ListContactMessagesQuery(1, 15, null));

    expect($result['data'])->toHaveCount(1);
    expect($result['data'][0]['name'])->toBe('Jane Doe');
    expect($result['data'][0]['email'])->toBe('jane@example.com');
    expect($result['data'][0]['status'])->toBe('new');
});

test('submit contact message rejects failed captcha', function () {
    $fixtures = contactFixtures();
    $submit = new SubmitContactMessageHandler(
        $fixtures['messages'],
        $fixtures['settings'],
        $fixtures['notifier'],
        new FailingContactCaptchaVerifier(),
        $fixtures['config'],
        $fixtures['logger'],
    );

    $submit->handle(new SubmitContactMessageCommand(
        name: 'Bot',
        email: 'bot@example.com',
        subject: null,
        body: 'Spam message here.',
        captchaToken: 'invalid',
        ipAddress: null,
        userAgent: null,
    ));
})->throws(ContactCaptchaFailedException::class);

test('list contact messages filters by status', function () {
    $fixtures = contactFixtures();

    $fixtures['submit']->handle(new SubmitContactMessageCommand(
        'Alice',
        'alice@example.com',
        null,
        'First message here.',
        null,
        null,
        null,
    ));
    $fixtures['submit']->handle(new SubmitContactMessageCommand(
        'Bob',
        'bob@example.com',
        null,
        'Second message here.',
        null,
        null,
        null,
    ));

    $all = $fixtures['list']->handle(new ListContactMessagesQuery(1, 15, null));
    $newOnly = $fixtures['list']->handle(new ListContactMessagesQuery(1, 15, 'new'));

    expect($all['meta']['total'])->toBe(2);
    expect($newOnly['meta']['total'])->toBe(2);
});

test('get contact message marks new as read', function () {
    $fixtures = contactFixtures();

    $fixtures['submit']->handle(new SubmitContactMessageCommand(
        'Carol',
        'carol@example.com',
        'Subject',
        'Message body content.',
        null,
        null,
        null,
    ));

    $listed = $fixtures['list']->handle(new ListContactMessagesQuery(1, 1, null));
    $id = $listed['data'][0]['id'];

    $detail = $fixtures['get']->handle(new GetContactMessageQuery($id, true));

    expect($detail['data']['status'])->toBe('read');
    expect($detail['data']['body'])->toBe('Message body content.');
});

test('update contact message archives message', function () {
    $fixtures = contactFixtures();

    $fixtures['submit']->handle(new SubmitContactMessageCommand(
        'Dave',
        'dave@example.com',
        null,
        'Archive me please.',
        null,
        null,
        null,
    ));

    $id = $fixtures['list']->handle(new ListContactMessagesQuery(1, 1, null))['data'][0]['id'];

    $updated = $fixtures['update']->handle(new UpdateContactMessageStatusCommand($id, 'archived'));

    expect($updated['data']['status'])->toBe('archived');
});

test('get contact message throws when not found', function () {
    $fixtures = contactFixtures();

    $fixtures['get']->handle(new GetContactMessageQuery('a0000001-0000-4000-8000-000000000099', false));
})->throws(ContactMessageNotFoundException::class);
