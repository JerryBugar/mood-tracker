<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Models\MoodRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Helpers\TurboStreamHelper;

/**
 * Service untuk menangani tab navigation dashboard admin
 */
class DashboardTabService
{
    /**
     * Mendapatkan data untuk overview tab
     *
     * @return array
     */
    public function getOverviewTabData(): array
    {
        $totalEmployees = User::count();
        $activeToday = MoodRecord::where('created_at', '>=', Carbon::today())
            ->distinct('user_id')
            ->count();

        $startDateOfWeek = Carbon::now()->startOfWeek();
        $moodCountsWeek = MoodRecord::select('mood', DB::raw('count(*) as count'))
            ->where('created_at', '>=', $startDateOfWeek)
            ->groupBy('mood')
            ->get()
            ->pluck('count', 'mood');

        return [
            'totalEmployees' => $totalEmployees,
            'activeToday' => $activeToday,
            'senangCount' => $moodCountsWeek['senyum'] ?? 0,
            'sedihCount' => $moodCountsWeek['sedih'] ?? 0,
            'netralCount' => $moodCountsWeek['netral'] ?? 0,
            'lelahCount' => $moodCountsWeek['lelah'] ?? 0,
            'marahCount' => $moodCountsWeek['marah'] ?? 0,
            'employees' => User::select('id', 'name', 'division', 'avatar')->get()
        ];
    }

    /**
     * Mendapatkan data untuk employees tab
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getEmployeesTabData()
    {
        $employees = User::select('id', 'name', 'division', 'avatar')->get();
        
        // Cek apakah setiap karyawan mengisi mood hari ini
        $today = Carbon::today();
        $employeeIdsWithMoodToday = MoodRecord::whereDate('created_at', $today)
            ->distinct('user_id')
            ->pluck('user_id')
            ->toArray();
        
        // Tambahkan informasi apakah karyawan mengisi mood hari ini
        // Highlight hanya muncul jika mood record hari ini belum direspons oleh admin
        // Juga tambahkan informasi tentang mood record yang belum direspon (semua tanggal)
        $employees->each(function ($employee) use ($employeeIdsWithMoodToday, $today) {
            $hasMoodToday = in_array($employee->id, $employeeIdsWithMoodToday);
            
            if ($hasMoodToday) {
                // Ambil mood record hari ini untuk mendapatkan created_at
                $moodRecord = MoodRecord::where('user_id', $employee->id)
                    ->whereDate('created_at', $today)
                    ->latest()
                    ->first();
                
                // Highlight hanya muncul jika mood record hari ini belum direspons oleh admin
                $employee->has_mood_today = $moodRecord && empty($moodRecord->admin_response);
                $employee->mood_today_date = $moodRecord ? $moodRecord->created_at : $today;
            } else {
                $employee->has_mood_today = false;
            }
            
            // Cek apakah karyawan memiliki mood record yang belum direspon (semua tanggal)
            $unrespondedRecords = MoodRecord::where('user_id', $employee->id)
                ->where(function($query) {
                    $query->whereNull('admin_response')
                          ->orWhere('admin_response', '');
                })
                ->get();
            
            $employee->has_unresponded_mood = $unrespondedRecords->count() > 0;
            $employee->unresponded_count = $unrespondedRecords->count();
        });
        
        return $employees;
    }

    /**
     * Cek apakah request adalah Turbo Frame request
     *
     * @return bool
     */
    public function isTurboFrameRequest(): bool
    {
        $turboFrame = request()->header('Turbo-Frame');
        $acceptHeader = request()->header('Accept', '');
        
        return $turboFrame === 'dashboard_content' || 
               strpos($acceptHeader, 'text/vnd.turbo-stream.html') !== false;
    }

    /**
     * Cek apakah request adalah Turbo Stream request
     *
     * @return bool
     */
    public function isTurboStreamRequest(): bool
    {
        $acceptHeader = request()->header('Accept', '');
        return strpos($acceptHeader, 'text/vnd.turbo-stream.html') !== false;
    }

    /**
     * Membuat Turbo Stream response untuk replace frame
     *
     * @param string $frameId
     * @param string $content
     * @return \Illuminate\Http\Response
     */
    public function createTurboStreamResponse(string $frameId, string $content)
    {
        $streamContent = TurboStreamHelper::replace($frameId, $content);
        return response($streamContent, 200, ['Content-Type' => 'text/vnd.turbo-stream.html']);
    }
}

