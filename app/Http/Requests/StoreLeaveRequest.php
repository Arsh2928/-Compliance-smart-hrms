<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->role === 'employee';
    }

    public function rules()
    {
        return [
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'required|in:casual,sick,earned,unpaid',
            'reason' => 'required|string|min:10|max:500',
        ];
    }
}
