<?php

namespace App\Services;

use App\Models\Concern;
use App\Models\DocumentRequest;
use App\Models\User;
use App\Notifications\NewConcernNotification;
use App\Notifications\NewRequestNotification;

class NotificationService
{
    public static function notifyPersonnelOfNewRequest(DocumentRequest $documentRequest): void
    {
        $personnelUsers = User::where('role', 'personnel')
            ->where('is_active', true)
            ->get();

        foreach ($personnelUsers as $personnel) {
            $personnel->notify(new NewRequestNotification($documentRequest));
        }
    }

    public static function notifyPersonnelOfNewConcern(Concern $concern): void
    {
        $staffUsers = User::whereIn('role', ['personnel', 'admin'])
            ->where('is_active', true)
            ->get();

        foreach ($staffUsers as $staff) {
            $staff->notify(new NewConcernNotification($concern));
        }
    }
}