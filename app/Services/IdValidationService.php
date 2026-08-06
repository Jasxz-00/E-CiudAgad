<?php

namespace App\Services;

class IdValidationService
{
    protected array $patterns = [
        'phil_id' => '/^\d{4}-\d{4}-\d{4}-\d{4}$/',
        'passport' => '/^[A-Z]\d{7}[A-Z]$|^[A-Z]{2}\d{7}$/',
        'umid' => '/^\d{4}-\d{7}-\d$/',
        'philhealth' => '/^\d{2}-\d{9}-\d$/',
        'drivers_license' => '/^[A-Z]\d{2}-\d{2}-\d{6}$/',
        'prc_id' => '/^\d{7}$/',
        'postal_id' => '/^\d{4}-\d{4}-\d{4}$/',
        'sss_id' => '/^\d{2}-\d{7}-\d$/',
        'tin_id' => '/^\d{3}-\d{3}-\d{3}-\d{3,4}$/',
        'ibp_id' => '/^\d{5}$/',
        'owwa_ofw_id' => '/^\d{10}$/',
        'barangay_id' => null,
        'school_id' => null,
    ];

    public function validate(string $idType, string $idNumber): bool
    {
        $pattern = $this->patterns[$idType] ?? null;

        if ($pattern === null) {
            return true;
        }

        return (bool) preg_match($pattern, strtoupper(trim($idNumber)));
    }

    public function getPattern(string $idType): ?string
    {
        return $this->patterns[$idType] ?? null;
    }

    public function getSupportedTypes(): array
    {
        return array_keys($this->patterns);
    }

    public function getPatternDescription(string $idType): ?string
    {
        $descriptions = [
            'phil_id' => '1234-5678-9012-3456',
            'passport' => 'P1234567A or AA1234567',
            'umid' => '0033-1234567-8',
            'philhealth' => '11-201534404-7',
            'drivers_license' => 'D01-12-345678',
            'prc_id' => '0123456',
            'postal_id' => '1234-5678-9012',
            'sss_id' => '34-1234567-8',
            'tin_id' => '123-456-789-000',
            'ibp_id' => '12345',
            'owwa_ofw_id' => '1234567890',
            'barangay_id' => null,
            'school_id' => null,
        ];

        return $descriptions[$idType] ?? null;
    }
}
