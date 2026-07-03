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

namespace App\Presentation\Http\Requests\Admin;

use Hyperf\Validation\Request\FormRequest;

class CreateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return self::validationRules();
    }

    /**
     * @return array<string, string>
     */
    public static function validationRules(): array
    {
        return [
            'title' => 'required|string|min:2|max:200',
            'slug' => 'nullable|string|min:2|max:200|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'description' => 'nullable|string|max:5000',
            'content' => 'nullable|string|max:100000',
            'repository_url' => 'nullable|url|max:500',
            'demo_url' => 'nullable|url|max:500',
            'thumbnail' => 'nullable|string|max:500',
            'cover' => 'nullable|string|max:500',
            'status' => 'nullable|in:draft,published,archived',
            'featured' => 'nullable|boolean',
            'categories' => 'nullable|array',
            'categories.*' => 'uuid',
            'technologies' => 'nullable|array',
            'technologies.*' => 'uuid',
            'tags' => 'nullable|array',
            'tags.*' => 'uuid',
        ];
    }
}
