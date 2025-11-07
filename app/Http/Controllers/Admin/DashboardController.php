<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MoodRecord;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Ambil data statistik dari database
        $totalEmployees = User::count();
        $activeToday = MoodRecord::where('created_at', '>=', Carbon::today())->distinct('user_id')->count();
        
        // Ambil data mood breakdown untuk minggu ini (untuk statistik mingguan di dashboard)
        $startDateOfWeek = Carbon::now()->startOfWeek();
        $moodCountsWeek = MoodRecord::select('mood', \DB::raw('count(*) as count'))
            ->where('created_at', '>=', $startDateOfWeek)
            ->groupBy('mood')
            ->get()
            ->pluck('count', 'mood');
        
        $senangCount = $moodCountsWeek['senyum'] ?? 0;
        $sedihCount = $moodCountsWeek['sedih'] ?? 0;
        $netralCount = $moodCountsWeek['netral'] ?? 0;
        $lelahCount = $moodCountsWeek['lelah'] ?? 0;
        $marahCount = $moodCountsWeek['marah'] ?? 0;
        
        $employees = User::select('id', 'name', 'division', 'avatar')->get();
        
        return view('admin.dashboard', [
            'totalEmployees' => $totalEmployees,
            'activeToday' => $activeToday,
            'senangCount' => $senangCount,
            'sedihCount' => $sedihCount,
            'netralCount' => $netralCount,
            'lelahCount' => $lelahCount,
            'marahCount' => $marahCount,
            'employees' => $employees
        ]);
    }
    
    public function moodMonitoring()
    {
        // Ambil semua mood records dengan informasi user
        $moodRecords = MoodRecord::with('user')
            ->latest()
            ->paginate(10);
        
        return view('admin.mood-monitoring', compact('moodRecords'));
    }
    
    public function getChartData()
    {
        // Data untuk grafik tren mood minggu ini - berdasarkan minggu kalender
        $startDate = Carbon::now()->startOfWeek(); // Mulai dari Senin minggu ini
        $endDate = Carbon::now()->endOfWeek(); // Sampai Minggu minggu ini
        
        $moodData = [];
        $dates = [];
        $dayLabels = []; // Store the day labels
        
        // Generate list dates and day labels
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $dates[] = $currentDate->format('Y-m-d');
            // Format date to get day name in Indonesian
            $dayLabels[] = $currentDate->locale('id')->dayName; // This will give us the day name in Indonesian
            $currentDate->addDay();
        }
        
        // Data untuk setiap kategori mood
        $moodCategories = ['senyum', 'sedih', 'netral', 'lelah', 'marah'];
        foreach ($moodCategories as $category) {
            $data = [];
            foreach ($dates as $date) {
                $count = MoodRecord::whereDate('created_at', $date)
                    ->where('mood', $category)
                    ->count();
                $data[] = $count;
            }
            
            // Generate darker color for point
            $pointColor = $this->getDarkerMoodColor($category);
            
            $moodData[] = [
                'label' => $this->getMoodLabel($category),
                'data' => $data,
                'borderColor' => $this->getMoodColor($category),
                'backgroundColor' => $this->getMoodBackgroundColor($category, 0.3), // 30% opacity for area
                'tension' => 0.4, // Membuat garis lebih lengkung
                'pointRadius' => 6, // Memperbesar titik
                'pointBackgroundColor' => $pointColor, // Warna titik sesuai kategori dan lebih gelap
                'pointBorderColor' => $pointColor, // Warna border titik sama dengan warna titik
                'fill' => true // Enable area fill
            ];
        }
        
        // Data untuk grafik divisi
        $divisions = User::whereNotNull('division')->distinct('division')->pluck('division');
        $divisionData = [];
        
        foreach ($divisions as $division) {
            // Ambil total mood per divisi untuk semua kategori
            $moodCounts = MoodRecord::join('users', 'mood_records.user_id', '=', 'users.id')
                ->where('users.division', $division)
                ->select('mood', \DB::raw('count(*) as count'))
                ->groupBy('mood')
                ->get();
                
            // Kategorikan mood untuk ditampilkan di grafik
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
        
        return response()->json([
            'moodTrend' => [
                'labels' => $dayLabels, // Use the correct day names
                'datasets' => $moodData
            ],
            'divisionMood' => [
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
            ]
        ]);
    }
    
    private function getMoodLabel($moodType)
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
    
    private function getMoodColor($moodType)
    {
        $colors = [
            'senyum' => '#28a745', // Hijau
            'sedih' => '#dc3545',  // Merah
            'netral' => '#6c757d', // Abu-abu
            'lelah' => '#ffc107',  // Kuning
            'marah' => '#6f42c1'   // Ungu
        ];
        
        return $colors[$moodType] ?? '#000000';
    }
    
    private function getMoodBackgroundColor($moodType, $opacity = 0.1)
    {
        $colors = [
            'senyum' => [40, 167, 69],  // Hijau
            'sedih' => [220, 53, 69],   // Merah
            'netral' => [108, 117, 125], // Abu-abu
            'lelah' => [255, 193, 7],   // Kuning
            'marah' => [111, 66, 193]   // Ungu
        ];
        
        $color = $colors[$moodType] ?? [0, 0, 0];
        
        return "rgba({$color[0]}, {$color[1]}, {$color[2]}, {$opacity})";
    }
    
    private function getDarkerMoodColor($moodType)
    {
        $colors = [
            'senyum' => [20, 80, 35],   // Hijau lebih gelap
            'sedih' => [150, 35, 45],   // Merah lebih gelap
            'netral' => [70, 75, 80],   // Abu-abu lebih gelap
            'lelah' => [200, 150, 0],   // Kuning lebih gelap
            'marah' => [75, 45, 130]    // Ungu lebih gelap
        ];
        
        $color = $colors[$moodType] ?? [0, 0, 0];
        
        return "rgb({$color[0]}, {$color[1]}, {$color[2]})";
    }
    
    public function getUserDetail($id)
    {
        $user = User::find($id);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ]);
        }
        
        // Ambil semua mood record dari user, urutkan dari terbaru
        $moodRecords = $user->moodRecords()->latest()->get();
        
        return response()->json([
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
        ]);
    }
}