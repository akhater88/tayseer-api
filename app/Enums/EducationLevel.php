<?php

namespace App\Enums;

enum EducationLevel: string
{
    case HighSchool = 'high_school';
    case Diploma = 'diploma';
    case Bachelors = 'bachelors';
    case Masters = 'masters';
    case Phd = 'phd';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::HighSchool => __('enums.education_level.high_school'),
            self::Diploma => __('enums.education_level.diploma'),
            self::Bachelors => __('enums.education_level.bachelors'),
            self::Masters => __('enums.education_level.masters'),
            self::Phd => __('enums.education_level.phd'),
            self::Other => __('enums.education_level.other'),
        };
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::HighSchool => 'ثانوية',
            self::Diploma => 'دبلوم',
            self::Bachelors => 'بكالوريوس',
            self::Masters => 'ماجستير',
            self::Phd => 'دكتوراه',
            self::Other => 'أخرى',
        };
    }

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
