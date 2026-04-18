<?php

namespace App\Http\Requests\Board\Member;

use App\Enums\BoardRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InviteMemberRequest extends FormRequest
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
            'email' => ['required', 'string', 'email', 'max:255'],
            'role' => ['sometimes', 'string', Rule::in([BoardRole::Admin->value, BoardRole::Member->value])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'An email address is required to invite a member.',
            'email.email' => 'The email must be a valid email address.',
            'role.in' => 'The role must be admin or member.',
        ];
    }
}
