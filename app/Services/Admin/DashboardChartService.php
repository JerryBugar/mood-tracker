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
     * Mendapatkan data untuk chart mood trend dengan filter
     *
     * @param string|null $filterType 'day', 'month', 'year', atau null untuk minggu ini
     * @param string|null $filterValue Nilai filter (Y-m-d untuk day, Y-m untuk month, Y untuk year)
     * @return array
     */
    public function getMoodTrendDataFiltered(?string $filterType = null, ?string $filterValue = null): array
    {
        // Jika tidak ada filter, gunakan default (minggu ini)
        if (!$filterType || !$filterValue) {
            return $this->getMoodTrendData();
        }

        $dates = [];
        $labels = [];
        $moodCategories = ['senyum', 'sedih', 'netral', 'lelah', 'marah'];

        // Tentukan range tanggal berdasarkan filter type
        switch ($filterType) {
            case 'day':
                // Filter per hari tertentu - tampilkan jam per jam (0-23)
                $date = Carbon::parse($filterValue);
                for ($hour = 0; $hour < 24; $hour++) {
                    $dates[] = [
                        'date' => $date->format('Y-m-d'),
                        'hour' => $hour
                    ];
                    $labels[] = sprintf('%02d:00', $hour);
                }
                break;

            case 'month':
                // Filter per bulan - tampilkan per hari dalam bulan tersebut
                $monthStart = Carbon::parse($filterValue . '-01');
                $monthEnd = $monthStart->copy()->endOfMonth();
                
                $currentDate = clone $monthStart;
                while ($currentDate <= $monthEnd) {
                    $dates[] = $currentDate->format('Y-m-d');
                    $labels[] = $currentDate->format('d');
                    $currentDate->addDay();
                }
                break;

            case 'year':
                // Filter per tahun - tampilkan per bulan dalam tahun tersebut
                for ($month = 1; $month <= 12; $month++) {
                    $dates[] = sprintf('%s-%02d', $filterValue, $month);
                    $labels[] = Carbon::create($filterValue, $month, 1)->locale('id')->monthName;
                }
                break;

            default:
                return $this->getMoodTrendData();
        }

        // Generate mood data
        $moodData = [];
        foreach ($moodCategories as $category) {
            $data = [];
            
            foreach ($dates as $dateInfo) {
                if ($filterType === 'day') {
                    // Query by hour
                    $count = MoodRecord::whereDate('created_at', $dateInfo['date'])
                        ->whereRaw('HOUR(created_at) = ?', [$dateInfo['hour']])
                        ->where('mood', $category)
                        ->count();
                } elseif ($filterType === 'month') {
                    // Query by date
                    $count = MoodRecord::whereDate('created_at', $dateInfo)
                        ->where('mood', $category)
                        ->count();
                } else { // year
                    // Query by month
                    $count = MoodRecord::where('created_at', 'like', $dateInfo . '%')
                        ->where('mood', $category)
                        ->count();
                }
                
                $data[] = $count;
            }

            $pointColor = $this->getDarkerMoodColor($category);

            $moodData[] = [
                'label' => $this->getMoodLabel($category),
                'data' => $data,
                'borderColor' => $this->getMoodColor($category),
                'backgroundColor' => $this->getMoodBackgroundColor($category, 0.3),
                'tension' => 0.4,
                'pointRadius' => $filterType === 'day' ? 4 : 6,
                'pointBackgroundColor' => $pointColor,
                'pointBorderColor' => $pointColor,
                'fill' => true
            ];
        }

        return [
            'labels' => $labels,
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
     * Mendapatkan data untuk chart mood per divisi dengan filter
     *
     * @param string|null $filterType 'day', 'month', 'year', atau null untuk semua data
     * @param string|null $filterValue Nilai filter (Y-m-d untuk day, Y-m untuk month, Y untuk year)
     * @return array
     */
    public function getDivisionMoodDataFiltered(?string $filterType = null, ?string $filterValue = null): array
    {
        // Jika tidak ada filter, gunakan default (semua data)
        if (!$filterType || !$filterValue) {
            return $this->getDivisionMoodData();
        }

        $divisions = User::whereNotNull('division')->distinct('division')->pluck('division');
        $divisionData = [];

        foreach ($divisions as $division) {
            // Build query dengan filter
            $query = MoodRecord::join('users', 'mood_records.user_id', '=', 'users.id')
                ->where('users.division', $division);

            // Apply filter berdasarkan type
            switch ($filterType) {
                case 'day':
                    $query->whereDate('mood_records.created_at', $filterValue);
                    break;
                case 'month':
                    $query->where('mood_records.created_at', 'like', $filterValue . '%');
                    break;
                case 'year':
                    $query->whereYear('mood_records.created_at', $filterValue);
                    break;
            }

            $moodCounts = $query
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
     * @param string|null $filterType 'day', 'month', 'year', atau null
     * @param string|null $filterValue Nilai filter
     * @param string|null $chartType 'mood' atau 'division' atau null untuk semua
     * @return array
     */
    public function getAllChartData(?string $filterType = null, ?string $filterValue = null, ?string $chartType = null): array
    {
        $result = [];

        // Jika chartType spesifik diminta
        if ($chartType === 'mood') {
            $result['moodTrend'] = $this->getMoodTrendDataFiltered($filterType, $filterValue);
        } elseif ($chartType === 'division') {
            $result['divisionMood'] = $this->getDivisionMoodDataFiltered($filterType, $filterValue);
        } else {
            // Return both charts
            $result['moodTrend'] = $this->getMoodTrendDataFiltered($filterType, $filterValue);
            $result['divisionMood'] = $this->getDivisionMoodDataFiltered($filterType, $filterValue);
        }

        return $result;
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

