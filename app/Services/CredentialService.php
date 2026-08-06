<?php

namespace App\Services;

use App\Models\Resident;
use App\Models\User;
use Illuminate\Support\Str;

class CredentialService
{
    public function generateUsername(Resident $resident): string
    {
        $base = strtolower(
            substr($resident->first_name, 0, 1).
            $resident->last_name
        );

        $base = preg_replace('/[^a-z0-9]/', '', $base);
        $username = $base;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base.$counter;
            $counter++;
        }

        return $username;
    }

    public function generatePassword(): string
    {
        return Str::random(10);
    }

    public function generateTrackingNumber(): string
    {
        $letters = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $length = strlen($letters) - 1;

        do {
            $trackingNumber = 'EC-'
                .$letters[random_int(0, $length)]
                .$letters[random_int(0, $length)]
                .$letters[random_int(0, $length)]
                .'-'
                .random_int(0, 9)
                .random_int(0, 9)
                .random_int(0, 9);
        } while (User::where('tracking_number', $trackingNumber)->exists());

        return $trackingNumber;
    }

    public function generatePin(): string
    {
        return (string) random_int(100000, 999999);
    }
}
