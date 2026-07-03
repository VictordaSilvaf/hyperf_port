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

class PatchPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|min:2|max:200',
            'slug' => 'sometimes|string|min:2|max:200|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'layout' => 'sometimes|in:default,full-width,landing',
            'is_home' => 'sometimes|boolean',
            'status' => 'sometimes|in:draft,published,archived',
            'published_at' => 'sometimes|nullable|date',
            'order' => 'sometimes|integer|min:0',
            'seo' => 'sometimes|nullable|array',
            'seo.meta_title' => 'sometimes|nullable|string|max:70',
            'seo.meta_description' => 'sometimes|nullable|string|max:160',
            'seo.og_title' => 'sometimes|nullable|string|max:70',
            'seo.og_description' => 'sometimes|nullable|string|max:200',
            'seo.og_image_id' => 'sometimes|nullable|uuid',
            'seo.canonical_url' => 'sometimes|nullable|url|max:500',
            'seo.robots' => 'sometimes|nullable|in:index,follow,noindex,nofollow,noindex,follow',
            'seo.twitter_card' => 'sometimes|nullable|in:summary,summary_large_image',
        ];
    }
}
