<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MatchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_1_id' => $this->user_1_id,
            'user_2_id' => $this->user_2_id,
            'status' => $this->status,
            'matched_at' => $this->matched_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),

            // Include users when loaded
            'user_1' => new UserResource($this->whenLoaded('user1')),
            'user_2' => new UserResource($this->whenLoaded('user2')),

            // Include chat request when loaded
            'chat_request' => new ChatRequestResource($this->whenLoaded('chatRequest')),

            // Computed
            'has_chat_request' => $this->when(
                $this->relationLoaded('chatRequest'),
                fn() => $this->chatRequest !== null
            ),
            'has_approved_chat' => $this->when(
                $this->relationLoaded('chatRequest'),
                fn() => $this->chatRequest?->isApproved() ?? false
            ),
        ];
    }
}
