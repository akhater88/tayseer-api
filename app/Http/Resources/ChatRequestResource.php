<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'match_id' => $this->match_id,
            'requester_id' => $this->requester_id,
            'receiver_id' => $this->receiver_id,
            'status' => $this->status->value,
            'status_label' => $this->status->labelAr(),

            // Guardian info
            'guardian_id' => $this->guardian_id,
            'guardian_reviewed_at' => $this->guardian_reviewed_at?->toIso8601String(),
            'guardian_decision' => $this->guardian_decision,
            'guardian_rejection_reason' => $this->guardian_rejection_reason,

            // Firebase
            'firebase_conversation_id' => $this->firebase_conversation_id,

            // Timestamps
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),

            // Include relations when loaded
            'requester' => new UserResource($this->whenLoaded('requester')),
            'receiver' => new UserResource($this->whenLoaded('receiver')),
            'guardian' => new UserResource($this->whenLoaded('guardian')),
            'match' => new MatchResource($this->whenLoaded('match')),

            // Computed
            'is_pending_female' => $this->isPendingFemale(),
            'is_pending_guardian' => $this->isPendingGuardian(),
            'is_approved' => $this->isApproved(),
            'is_rejected' => $this->isRejected(),
        ];
    }
}
