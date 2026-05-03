<?php

declare(strict_types=1);

namespace App\Http\Request\Admin;

use Hyperf\Validation\Request\FormRequest;

class CreateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|min:2|max:120',
            'slug' => 'required|string|regex:/^[a-z0-9_-]{1,64}$/|max:64',
        ];
    }
}
