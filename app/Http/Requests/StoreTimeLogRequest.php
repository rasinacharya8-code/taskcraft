<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTimeLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'minutes_spent' => ['required', 'integer', 'min:1', 'max:1440'],
            'logged_at'     => ['required', 'date', 'before_or_equal:today'],
            'description'   => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'minutes_spent.max' => 'You cannot log more than 24 hours (1440 minutes) per entry.',
        ];
    }
}
