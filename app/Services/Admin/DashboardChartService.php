<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Models\MoodRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Service untuk menangani data chart dashboard admin
 */
class DashboardChartService
{
    /**
     * Mendapatkan data untuk chart mood trend minggu ini
     *
     * @return array
     */
    public function getMoodTrendData(): array
    {
        $startDate = Carbon::now()->startOfWeek();
        $endDate = Carbon::now()->endOfWeek();

        $dates = [];
        $dayLabels = [];

        // Generate list dates and day labels
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $dates[] = $currentDate->format('Y-m-d');
            $dayLabels[] = $currentDate->locale('id')->dayName;
            $currentDate->addDay();
        }

        $moodData = [];
        $moodCategories = ['senyum', 'sedih', 'netral', 'lelah', 'marah'];

        foreach ($moodCategories as $category) {
            $data = [];
            foreach ($dates as $date) {
                $count = MoodRecord::whereDate('created_at', $date)
                    ->where('mood', $category)
                    ->count();
                $data[] = $count;
            }

            $pointColor = $this->getDarkerMoodColor($category);

            $moodData[] = [
                'label' => $this->getMoodLabel($category),
                'data' => $data,
                'borderColor' => $this->getMoodColor($category),
                'backgroundColor' => $this->getMoodBackgroundColor($category, 0.3),
                'tension' => 0.4,
                'pointRadius' => 6,
                'pointBackgroundColor' => $pointColor,
                'pointBorderColor' => $pointColor,
                'fill' => true
            ];
        }

        return [
            'labels' => $dayLabels,
            'datasets' => $moodData
        ];
    }

    /**
     * Mendapatkan data untuk chart mood per divisi
     *
     * @return array
     */
    public function getDivisionMoodData(): array
    {
        $divisions = User::whereNotNull('division')->distinct('division')->pluck('division');
        $divisionData = [];

        foreach ($divisions as $division) {
            $moodCounts = MoodRecord::join('users', 'mood_records.user_id', '=', 'users.id')
                ->where('users.division', $division)
                ->select('mood', DB::raw('count(*) as count'))
                ->groupBy('mood')
                ->get();

            $divisionMoodData = [
                'label' => $division,
                'senyum' => $moodCounts->firstWhere('mood', 'senyum')['count'] ?? 0,
                'sedih' => $moodCounts->firstWhere('mood', 'sedih')['count'] ?? 0,
                'netral' => $moodCounts->firstWhere('mood', 'netral')['count'] ?? 0,
                'lelah' => $moodCounts->firstWhere('mood', 'lelah')['count'] ?? 0,
                'marah' => $moodCounts->firstWhere('mood', 'marah')['count'] ?? 0,
            ];

            $divisionData[] = $divisionMoodData;
        }

        return [
            'labels' => $divisions->toArray(),
            'datasets' => [
                [
                    'label' => 'Senang',
                    'data' => $divisions->map(function ($division) use ($divisionData) {
                        $item = collect($divisionData)->firstWhere('label', $division);
                        return $item ? $item['senyum'] : 0;
                    })->toArray(),
                    'backgroundColor' => 'rgba(40, 167, 69, 0.7)'
                ],
                [
                    'label' => 'Sedih',
                    'data' => $divisions->map(function ($division) use ($divisionData) {
                        $item = collect($divisionData)->firstWhere('label', $division);
                        return $item ? $item['sedih'] : 0;
                    })->toArray(),
                    'backgroundColor' => 'rgba(220, 53, 69, 0.7)'
                ],
                [
                    'label' => 'Biasa Saja',
                    'data' => $divisions->map(function ($division) use ($divisionData) {
                        $item = collect($divisionData)->firstWhere('label', $division);
                        return $item ? $item['netral'] : 0;
                    })->toArray(),
                    'backgroundColor' => 'rgba(108, 117, 125, 0.7)'
                ],
                [
                    'label' => 'Lelah',
                    'data' => $divisions->map(function ($division) use ($divisionData) {
                        $item = collect($divisionData)->firstWhere('label', $division);
                        return $item ? $item['lelah'] : 0;
                    })->toArray(),
                    'backgroundColor' => 'rgba(255, 193, 7, 0.7)'
                ],
                [
                    'label' => 'Marah',
                    'data' => $divisions->map(function ($division) use ($divisionData) {
                        $item = collect($divisionData)->firstWhere('label', $division);
                        return $item ? $item['marah'] : 0;
                    })->toArray(),
                    'backgroundColor' => 'rgba(111, 66, 193, 0.7)'
                ]
            ]
        ];
    }

    /**
     * Mendapatkan semua data chart untuk dashboard
     *
     * @return array
     */
    public function getAllChartData(): array
    {
        return [
            'moodTrend' => $this->getMoodTrendData(),
            'divisionMood' => $this->getDivisionMoodData()
        ];
    }

    /**
     * Mendapatkan label mood dalam bahasa Indonesia
     *
     * @param string $moodType
     * @return string
     */
    public function getMoodLabel(string $moodType): string
    {
        $labels = [
            'senyum' => 'Senang',
            'sedih' => 'Sedih',
            'netral' => 'Biasa Saja',
            'lelah' => 'Lelah',
            'marah' => 'Marah'
        ];

        return $labels[$moodType] ?? $moodType;
    }

    /**
     * Mendapatkan warna border untuk mood
     *
     * @param string $moodType
     * @return string
     */
    public function getMoodColor(string $moodType): string
    {
        $colors = [
            'senyum' => '#28a745',
            'sedih' => '#dc3545',
            'netral' => '#6c757d',
            'lelah' => '#ffc107',
            'marah' => '#6f42c1'
        ];

        return $colors[$moodType] ?? '#000000';
    }

    /**
     * Mendapatkan warna background untuk mood dengan opacity
     *
     * @param string $moodType
     * @param float $opacity
     * @return string
     */
    public function getMoodBackgroundColor(string $moodType, float $opacity = 0.1): string
    {
        $colors = [
            'senyum' => [40, 167, 69],
            'sedih' => [220, 53, 69],
            'netral' => [108, 117, 125],
            'lelah' => [255, 193, 7],
            'marah' => [111, 66, 193]
        ];

        $color = $colors[$moodType] ?? [0, 0, 0];

        return "rgba({$color[0]}, {$color[1]}, {$color[2]}, {$opacity})";
    }

    /**
     * Mendapatkan warna lebih gelap untuk mood
     *
     * @param string $moodType
     * @return string
     */
    public function getDarkerMoodColor(string $moodType): string
    {
        $colors = [
            'senyum' => [20, 80, 35],
            'sedih' => [150, 35, 45],
            'netral' => [70, 75, 80],
            'lelah' => [200, 150, 0],
            'marah' => [75, 45, 130]
        ];

        $color = $colors[$moodType] ?? [0, 0, 0];

        return "rgb({$color[0]}, {$color[1]}, {$color[2]})";
    }
}

