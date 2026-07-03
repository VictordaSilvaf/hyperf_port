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

class CreatePageRequest extends FormRequest
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
            'layout' => 'nullable|in:default,full-width,landing',
            'is_home' => 'nullable|boolean',
            'status' => 'nullable|in:draft,published,archived',
            'seo' => 'nullable|array',
            'seo.meta_title' => 'nullable|string|max:70',
            'seo.meta_description' => 'nullable|string|max:160',
            'seo.og_title' => 'nullable|string|max:70',
            'seo.og_description' => 'nullable|string|max:200',
            'seo.og_image_id' => 'nullable|uuid',
            'seo.canonical_url' => 'nullable|url|max:500',
            'seo.robots' => 'nullable|in:index,follow,noindex,nofollow,noindex,follow',
            'seo.twitter_card' => 'nullable|in:summary,summary_large_image',
        ];
    }
}
