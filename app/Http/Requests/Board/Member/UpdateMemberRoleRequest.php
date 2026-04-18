<?php

namespace App\Http\Requests\Board\Member;

use App\Enums\BoardRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role' => ['required', 'string', Rule::in([BoardRole::Admin->value, BoardRole::Member->value])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'role.required' => 'A role is required.',
            'role.in' => 'The role must be admin or member.',
        ];
    }
}
