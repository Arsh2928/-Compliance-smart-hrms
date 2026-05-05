<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreComplaintRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->role === 'employee';
    }

    public function rules()
    {
        return [
            'title' => 'required|string|min:5|max:255',
            'description' => 'required|string|min:20',
            'is_anonymous' => 'nullable|boolean'
        ];
    }
}
