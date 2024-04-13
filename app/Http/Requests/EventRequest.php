<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class EventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    private static $rules = [
        'events.location' => ['location' => 'required|string|min:3'],
        'events.dates' => [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ],
        'events.upload' => [
            'roster_file' => 'required|file|mimes:html,txt,pdf'
        ]
    ];
    
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return self::$rules[$this->route()->getName()];
    }
}
