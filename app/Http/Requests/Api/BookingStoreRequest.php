<?php

namespace App\Http\Requests\Api;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class BookingStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'slots' => ['required', 'array', 'min:1'],
            'slots.*.start_time' => ['required', 'date', 'before:slots.*.end_time'],
            'slots.*.end_time' => ['required', 'date', 'after:slots.*.start_time'],
        ];
    }
}
