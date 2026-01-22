<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\BeardType;
use App\Enums\BodyType;
use App\Enums\EducationLevel;
use App\Enums\Gender;
use App\Enums\GuardianRelationship;
use App\Enums\HijabType;
use App\Enums\MaritalStatus;
use App\Enums\PrayerLevel;
use App\Enums\ReligiousLevel;
use App\Enums\ReportReason;
use App\Enums\SkinColor;
use App\Enums\Smoking;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\Nationality;
use App\Models\WorkField;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LookupController extends Controller
{
    /**
     * Get all countries
     */
    public function countries(): JsonResponse
    {
        $countries = Country::active()
            ->ordered()
            ->get(['id', 'name_ar', 'name_en', 'code', 'phone_code']);

        return response()->json([
            'success' => true,
            'data' => $countries,
        ]);
    }

    /**
     * Get cities by country
     */
    public function cities(Request $request): JsonResponse
    {
        $query = City::active()->ordered();

        if ($request->has('country_id')) {
            $query->forCountry($request->country_id);
        }

        $cities = $query->get(['id', 'country_id', 'name_ar', 'name_en']);

        return response()->json([
            'success' => true,
            'data' => $cities,
        ]);
    }

    /**
     * Get all nationalities
     */
    public function nationalities(): JsonResponse
    {
        $nationalities = Nationality::active()
            ->ordered()
            ->get(['id', 'name_ar', 'name_en']);

        return response()->json([
            'success' => true,
            'data' => $nationalities,
        ]);
    }

    /**
     * Get all work fields
     */
    public function workFields(): JsonResponse
    {
        $workFields = WorkField::active()
            ->ordered()
            ->get(['id', 'name_ar', 'name_en']);

        return response()->json([
            'success' => true,
            'data' => $workFields,
        ]);
    }

    /**
     * Get all enum values
     */
    public function enums(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'gender' => $this->formatEnum(Gender::cases()),
                'marital_status' => $this->formatEnum(MaritalStatus::cases()),
                'religious_level' => $this->formatEnum(ReligiousLevel::cases()),
                'prayer_level' => $this->formatEnum(PrayerLevel::cases()),
                'smoking' => $this->formatEnum(Smoking::cases()),
                'hijab_type' => $this->formatEnum(HijabType::cases()),
                'beard_type' => $this->formatEnum(BeardType::cases()),
                'education_level' => $this->formatEnum(EducationLevel::cases()),
                'skin_color' => $this->formatEnum(SkinColor::cases()),
                'body_type' => $this->formatEnum(BodyType::cases()),
                'guardian_relationship' => $this->formatEnum(GuardianRelationship::cases()),
                'report_reason' => $this->formatEnum(ReportReason::cases()),
            ],
        ]);
    }

    /**
     * Format enum cases for API response
     */
    private function formatEnum(array $cases): array
    {
        return array_map(function ($case) {
            return [
                'value' => $case->value,
                'label_ar' => $case->labelAr(),
                'label_en' => $case->label(),
            ];
        }, $cases);
    }
}
