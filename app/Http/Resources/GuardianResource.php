<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuardianResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'relationship' => $this->relationship,
            'status' => $this->status,
            'guardian_name' => $this->guardianUser?->username,
            'registered_at' => $this->registered_at?->toIso8601String(),
        ];
    }
}
