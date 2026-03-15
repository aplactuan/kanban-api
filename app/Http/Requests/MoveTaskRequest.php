<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MoveTaskRequest extends FormRequest
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
            'column_id' => ['required', 'integer', 'exists:columns,id'],
            'position' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'column_id.required' => 'Target column is required.',
            'column_id.integer' => 'Target column must be a valid integer.',
            'column_id.exists' => 'Target column does not exist.',
            'position.required' => 'Task position is required.',
            'position.integer' => 'Task position must be a valid integer.',
            'position.min' => 'Task position must be at least 1.',
        ];
    }
}
