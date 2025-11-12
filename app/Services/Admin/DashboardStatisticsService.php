<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Models\MoodRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Service untuk menangani statistik dashboard admin
 */
class DashboardStatisticsService
{
    /**
     * Mendapatkan statistik dashboard utama
     *
     * @return array
     */
    public function getDashboardStatistics(): array
    {
        $totalEmployees = User::count();
        $activeToday = MoodRecord::where('created_at', '>=', Carbon::today())
            ->distinct('user_id')
            ->count();

        // Ambil data mood breakdown untuk minggu ini
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
     * Mendapatkan data untuk filter mood monitoring
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getMoodMonitoringData(Request $request)
    {
        $division = $request->get('division');
        $mood = $request->get('mood');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = MoodRecord::with('user')->latest();

        if ($division) {
            $query->whereHas('user', function ($q) use ($division) {
                $q->where('division', $division);
            });
        }

        if ($mood) {
            $query->where('mood', $mood);
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return $query->paginate(10);
    }
}

