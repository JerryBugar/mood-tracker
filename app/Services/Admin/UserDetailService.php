<?php

namespace App\Services\Admin;

use App\Models\User;

/**
 * Service untuk menangani detail user
 */
class UserDetailService
{
    /**
     * Mendapatkan detail user beserta mood records
     *
     * @param int $userId
     * @return array|null
     */
    public function getUserDetail(int $userId): ?array
    {
        $user = User::find($userId);

        if (!$user) {
            return null;
        }

        $moodRecords = $user->moodRecords()->latest()->get();

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
            })->toArray()
        ];
    }
}

