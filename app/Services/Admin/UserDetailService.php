<?php

namespace App\Services\Admin;

use App\Models\User;
use Carbon\Carbon;

/**
 * Service untuk menangani detail user
 */
class UserDetailService
{
    /**
     * Mendapatkan detail user beserta mood records dengan filter
     *
     * @param int $userId
     * @param string|null $filterType Filter type: 'day', 'month', 'year', atau null untuk semua
     * @param string|null $filterValue Nilai filter (format: YYYY-MM-DD untuk day, YYYY-MM untuk month, YYYY untuk year)
     * @return array|null
     */
    public function getUserDetail(int $userId, ?string $filterType = null, ?string $filterValue = null): ?array
    {
        $user = User::find($userId);

        if (!$user) {
            return null;
        }

        $query = $user->moodRecords()->latest();

        // Apply filter jika ada
        if ($filterType && $filterValue) {
            switch ($filterType) {
                case 'day':
                    // Filter berdasarkan hari tertentu
                    $query->whereDate('created_at', $filterValue);
                    break;
                case 'month':
                    // Filter berdasarkan bulan tertentu (format: YYYY-MM)
                    $startDate = Carbon::createFromFormat('Y-m', $filterValue)->startOfMonth();
                    $endDate = Carbon::createFromFormat('Y-m', $filterValue)->endOfMonth();
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                    break;
                case 'year':
                    // Filter berdasarkan tahun tertentu
                    $startDate = Carbon::createFromFormat('Y', $filterValue)->startOfYear();
                    $endDate = Carbon::createFromFormat('Y', $filterValue)->endOfYear();
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                    break;
            }
        }

        $moodRecords = $query->get();

        return [
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'division' => $user->division,
                'jenis_kelamin' => $user->jenis_kelamin,
                'avatar' => $user->avatar
            ],
            'moodRecords' => $moodRecords->map(function($record) {
                return [
                    'id' => $record->id,
                    'mood' => $record->mood,
                    'reason' => $record->reason,
                    'action_suggestion' => $record->suggestion_action,
                    'created_at' => $record->created_at
                ];
            })->toArray(),
            'filterType' => $filterType,
            'filterValue' => $filterValue
        ];
    }
}

