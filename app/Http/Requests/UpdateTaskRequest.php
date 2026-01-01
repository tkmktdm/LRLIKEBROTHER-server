<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
        return [
            'title' => ["required", "string", "max:255"],
            'notes' => ["nullable", "string", "max:255"],
            'status' => ["nullable", "integer", "min:0"],
            'score' => ["nullable", "integer", "min:0", "max:100"],
            'sort_order' => ["nullable", "integer", "min:0"],
            'priority' => ["nullable", "integer", "min:0"],
            'start_date' => ["nullable", "datetime"],
            'end_date' => ["nullable", "datetime"],
            'target_date' => ["nullable", "datetime"],
            'category_id' => [
                "required",
                Rule::exists("categories", "id")->where(function ($query) {
                    $query->where("user_id", auth()->id());
                }),
            ],
        ];
    }
}
