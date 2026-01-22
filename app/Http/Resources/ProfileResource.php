<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'age' => $this->getAge(),
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            
            // Location
            'nationality' => [
                'id' => $this->nationality_id,
                'name' => $this->nationality?->name,
            ],
            'country' => [
                'id' => $this->country_id,
                'name' => $this->country?->name,
            ],
            'city' => [
                'id' => $this->city_id,
                'name' => $this->city?->name,
            ],

            // Personal
            'marital_status' => $this->marital_status,
            'number_of_children' => $this->number_of_children,
            'number_of_wives' => $this->when($this->user->isMale(), $this->number_of_wives),

            // Physical
            'height_cm' => $this->height_cm,
            'weight_kg' => $this->weight_kg,
            'skin_color' => $this->skin_color,
            'body_type' => $this->body_type,

            // Religious
            'religious_level' => $this->religious_level,
            'prayer_level' => $this->prayer_level,
            'smoking' => $this->smoking,
            'beard_type' => $this->when($this->user->isMale(), $this->beard_type),
            'hijab_type' => $this->when($this->user->isFemale(), $this->hijab_type),

            // Education & Work
            'education_level' => $this->education_level,
            'work_field' => [
                'id' => $this->work_field_id,
                'name' => $this->workField?->name,
            ],
            'job_title' => $this->job_title,

            // Bio
            'about_me' => $this->about_me,
            'partner_preferences' => $this->partner_preferences,

            // Completion
            'profile_completion' => $this->profile_completion,
        ];
    }
}
