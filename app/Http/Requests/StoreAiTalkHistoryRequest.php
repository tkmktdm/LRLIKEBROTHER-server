<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAiTalkHistoryRequest extends FormRequest
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
            'message' => ["required", 'string', "max:1000"],
            'emotion_data' => ['string', "nullable"],
            'select_speaker' => ["required", 'integer'],
            // 'user_id' => 'integer',
            // 'ai_agent_id' => 'integer',
            // 'task_id' => 'integer',
            // 'category_id' => 'integer',
        ];
    }
}
