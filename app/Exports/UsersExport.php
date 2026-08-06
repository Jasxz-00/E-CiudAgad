<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $users;

    public function __construct($users)
    {
        $this->users = $users;
    }

    public function collection()
    {
        return $this->users;
    }

    public function headings(): array
    {
        return [
            'Username',
            'Name',
            'Email',
            'Role',
            'Status',
            'Category',
            'Created At',
        ];
    }

    public function map($user): array
    {
        $name = $user->resident ? $user->resident->full_name : '-';
        $category = $user->resident ? ucfirst($user->resident->category) : '-';

        return [
            $user->username ?? '-',
            $name,
            $user->email,
            ucfirst($user->role),
            $user->is_active ? 'Active' : 'Inactive',
            $category,
            $user->created_at->format('Y-m-d H:i'),
        ];
    }
}
