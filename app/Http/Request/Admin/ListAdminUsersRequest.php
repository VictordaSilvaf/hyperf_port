<?php

declare(strict_types=1);

namespace App\Http\Request\Admin;

use Hyperf\Validation\Request\FormRequest;

class ListAdminUsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'search' => 'nullable|string|max:255',
        ];
    }
}
