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

namespace App\Presentation\Http\Requests\Public;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Validation\Request\FormRequest;

class SubmitContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:200',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:300',
            'message' => 'required|string|min:10|max:5000',
            'website' => 'prohibited',
        ];

        $config = $this->container->get(ConfigInterface::class);
        if ((bool) $config->get('contact.turnstile.enabled', false)) {
            $rules['cf_turnstile_response'] = 'required|string|min:1';
        } else {
            $rules['cf_turnstile_response'] = 'nullable|string';
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'name' => 'name',
            'email' => 'email',
            'subject' => 'subject',
            'message' => 'message',
            'cf_turnstile_response' => 'captcha',
        ];
    }
}
