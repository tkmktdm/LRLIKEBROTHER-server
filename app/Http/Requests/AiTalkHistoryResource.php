<?php

namespace App\Http\Requests;

use Illuminate\Http\Resources\Json\JsonResource;

class AiTalkHistoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "message" => $this->messages,
        ];
    }
}
