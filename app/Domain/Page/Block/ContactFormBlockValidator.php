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

final class ContactFormBlockValidator implements BlockValidatorInterface
{
    use BlockValidationHelpers;

    public function type(): string
    {
        return 'contact_form';
    }

    public function label(): string
    {
        return 'Contact Form';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'required' => ['submit_label'],
            'properties' => [
                'title' => ['type' => 'string', 'maxLength' => 200],
                'subtitle' => ['type' => 'string', 'maxLength' => 500],
                'submit_label' => ['type' => 'string', 'maxLength' => 100],
                'success_message' => ['type' => 'string', 'maxLength' => 500],
                'show_subject' => ['type' => 'boolean'],
            ],
        ];
    }

    public function validate(array $payload): void
    {
        $this->requireNonEmptyString($payload, 'submit_label', $this->type());
        $this->optionalString($payload, 'title');
        $this->optionalString($payload, 'subtitle');
        $this->optionalString($payload, 'success_message');
        if (isset($payload['show_subject']) && ! is_bool($payload['show_subject'])) {
            throw InvalidBlockPayloadException::forType($this->type(), 'show_subject must be a boolean.');
        }
    }
}
