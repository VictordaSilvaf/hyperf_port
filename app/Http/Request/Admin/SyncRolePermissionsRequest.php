<?php

declare(strict_types=1);

namespace App\Http\Request\Admin;

use Hyperf\Validation\Request\FormRequest;

class SyncRolePermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'permission_slugs' => 'required|array',
            'permission_slugs.*' => 'required|string|max:128',
        ];
    }
}
