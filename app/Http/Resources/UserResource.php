<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'username' => $this->username,
            'gender' => $this->gender,
            'user_type' => $this->user_type,
            'status' => $this->status,
            'is_convert' => $this->when($this->isFemale(), $this->is_convert),
            'is_online' => $this->isOnline(),
            'last_online_at' => $this->last_online_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),

            // Profile
            'profile' => new ProfileResource($this->whenLoaded('profile')),

            // Photos
            'primary_photo' => new PhotoResource($this->whenLoaded('primaryPhoto')),
            'photos' => PhotoResource::collection($this->whenLoaded('photos')),

            // Guardian (for females)
            'has_guardian' => $this->when($this->isFemale(), fn() => $this->hasGuardian()),
            'guardian' => new GuardianResource($this->whenLoaded('guardian')),

            // Stats
            'profile_completion' => $this->profile?->profile_completion ?? 0,
        ];
    }
}
