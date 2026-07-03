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

class SyncPageBlocksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'blocks' => 'required|array',
            'blocks.*.type' => 'required|string|in:hero,markdown,image,gallery,featured_projects,project_list,tech_stack,cta,embed,spacer',
            'blocks.*.payload' => 'required|array',
            'blocks.*.settings' => 'nullable|array',
        ];
    }
}
