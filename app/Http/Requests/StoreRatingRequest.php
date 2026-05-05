<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRatingRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && in_array(auth()->user()->role, ['admin', 'hr']);
    }

    public function rules()
    {
        return [
            'categories' => 'required|array',
            'categories.work_quality' => 'required|numeric|min:1|max:5',
            'categories.punctuality' => 'required|numeric|min:1|max:5',
            'categories.teamwork' => 'required|numeric|min:1|max:5',
            'categories.task_completion' => 'required|numeric|min:1|max:5',
            'categories.discipline' => 'required|numeric|min:1|max:5',
            'feedback' => 'nullable|string|max:1000'
        ];
    }
}
