<?php

declare(strict_types=1);

namespace App\Http\Request\Admin;

use Hyperf\Validation\Request\FormRequest;

class SyncUserRolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role_slugs' => 'required|array',
            'role_slugs.*' => 'required|string|max:64',
        ];
    }
}
