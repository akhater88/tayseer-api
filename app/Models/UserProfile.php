<?php

namespace App\Models;

use App\Enums\BeardType;
use App\Enums\BodyType;
use App\Enums\EducationLevel;
use App\Enums\HijabType;
use App\Enums\MaritalStatus;
use App\Enums\PrayerLevel;
use App\Enums\ReligiousLevel;
use App\Enums\SkinColor;
use App\Enums\Smoking;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'date_of_birth',
        'nationality_id',
        'country_id',
        'city_id',
        'marital_status',
        'number_of_children',
        'number_of_wives',
        'height_cm',
        'weight_kg',
        'skin_color',
        'body_type',
        'religious_level',
        'prayer_level',
        'smoking',
        'beard_type',
        'hijab_type',
        'education_level',
        'work_field_id',
        'job_title',
        'about_me',
        'partner_preferences',
        'profile_completion',
    ];

    protected $hidden = [
        'full_name',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'marital_status' => MaritalStatus::class,
            'skin_color' => SkinColor::class,
            'body_type' => BodyType::class,
            'religious_level' => ReligiousLevel::class,
            'prayer_level' => PrayerLevel::class,
            'smoking' => Smoking::class,
            'beard_type' => BeardType::class,
            'hijab_type' => HijabType::class,
            'education_level' => EducationLevel::class,
            'number_of_children' => 'integer',
            'number_of_wives' => 'integer',
            'height_cm' => 'integer',
            'weight_kg' => 'integer',
            'profile_completion' => 'integer',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function nationality(): BelongsTo
    {
        return $this->belongsTo(Nationality::class);
    }

    public function workField(): BelongsTo
    {
        return $this->belongsTo(WorkField::class);
    }

    // ==================== HELPERS ====================

    public function getAge(): int
    {
        return $this->date_of_birth?->age ?? 0;
    }

    public function calculateCompletion(): int
    {
        $fields = [
            'date_of_birth' => 10,
            'nationality_id' => 5,
            'country_id' => 10,
            'city_id' => 10,
            'marital_status' => 10,
            'religious_level' => 10,
            'prayer_level' => 10,
            'about_me' => 15,
            'height_cm' => 5,
            'education_level' => 5,
            'job_title' => 5,
            'partner_preferences' => 5,
        ];

        $completion = 0;
        foreach ($fields as $field => $points) {
            if (!empty($this->$field)) {
                $completion += $points;
            }
        }

        return min(100, $completion);
    }

    public function updateCompletion(): void
    {
        $this->update(['profile_completion' => $this->calculateCompletion()]);
    }
}
