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

class UpdateSiteSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nav' => 'sometimes|nullable|array',
            'footer' => 'sometimes|nullable|array',
            'social' => 'sometimes|nullable|array',
            'branding' => 'sometimes|nullable|array',
            'seo' => 'sometimes|nullable|array',
            'seo.site_name' => 'sometimes|string|min:1|max:200',
            'seo.default_meta_description' => 'sometimes|nullable|string|max:500',
            'seo.default_og_image_id' => 'sometimes|nullable|uuid',
            'seo.twitter_site' => 'sometimes|nullable|string|max:100',
            'seo.google_site_verification' => 'sometimes|nullable|string|max:200',
            'seo.locale' => 'sometimes|nullable|string|max:10',
        ];
    }
}
