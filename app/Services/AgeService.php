<?php

namespace App\Services;

use Carbon\Carbon;

class AgeService
{
    public function computeAge(int $year, int $month, int $day): int
    {
        $birthdate = Carbon::createFromDate($year, $month, $day);

        return (int) $birthdate->age;
    }

    public function isSeniorCitizen(int $age): bool
    {
        return $age >= 60;
    }

    public function getCategory(int $age, ?string $gender = null, ?string $disabilityProof = null): string
    {
        if ($this->isSeniorCitizen($age)) {
            return 'senior';
        }

        if ($disabilityProof) {
            return 'pwd';
        }

        return 'regular';
    }
}
