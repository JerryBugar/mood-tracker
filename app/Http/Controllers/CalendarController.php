<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\MoodRecord;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        // Middleware sudah memastikan user terautentikasi, tidak perlu pengecekan manual
        $user = Auth::user();

        // Get year and month from request, default to current
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        
        // Validate year and month
        $year = (int) $year;
        $month = (int) $month;
        if ($year < 1970 || $year > 2100) {
            $year = Carbon::now()->year;
        }
        if ($month < 1 || $month > 12) {
            $month = Carbon::now()->month;
        }

        // Get the current date for highlighting
        $currentDate = Carbon::now();
        
        // Create Carbon instance for the selected month
        $selectedMonth = Carbon::create($year, $month, 1);
        
        // Get first day of month and last day of month
        $firstDayOfMonth = $selectedMonth->copy()->startOfMonth();
        $lastDayOfMonth = $selectedMonth->copy()->endOfMonth();
        
        // Get the starting day of the calendar (Sunday of the week that includes the first day)
        $startDay = $firstDayOfMonth->copy()->startOfWeek();
        
        // Get the ending day of the calendar (Saturday of the week that includes the last day)
        $endDay = $lastDayOfMonth->copy()->endOfWeek();
        
        // Get mood records for the selected month
        $moodRecords = $user->moodRecords()
            ->whereBetween('created_at', [$firstDayOfMonth->startOfDay(), $lastDayOfMonth->endOfDay()])
            ->get()
            ->groupBy(function ($record) {
                return $record->created_at->format('Y-m-d');
            });

        // Generate calendar days
        $calendarDays = [];
        $currentDay = $startDay->copy();
        
        while ($currentDay->lte($endDay)) {
            $dateKey = $currentDay->format('Y-m-d');
            $dayRecords = $moodRecords->get($dateKey, collect());
            
            $calendarDays[] = [
                'date' => $currentDay->copy(),
                'records' => $dayRecords,
                'isCurrentMonth' => $currentDay->month === $month,
                'isToday' => $currentDay->format('Y-m-d') === $currentDate->format('Y-m-d'),
            ];
            
            $currentDay->addDay();
        }

        // Prepare month and year navigation
        $previousMonth = $selectedMonth->copy()->subMonth();
        $nextMonth = $selectedMonth->copy()->addMonth();
        
        $monthName = $selectedMonth->locale('id_ID')->translatedFormat('F Y');
        
        // Check if this is a Turbo request
        // We should return the full view for Turbo Drive navigation to work correctly
        // Turbo Drive expects an HTML response to replace the body, not a stream
        // unless we are specifically targeting a frame update from within the calendar page.
        // For navigation from other pages (like Home), we must return the view.
        
        return response(view('calendar.index', [
            'calendarDays' => $calendarDays,
            'monthName' => $monthName,
            'currentYear' => $year,
            'currentMonth' => $month,
            'previousMonth' => $previousMonth,
            'nextMonth' => $nextMonth,
            'currentDate' => $currentDate->format('Y-m-d'),
        ]))->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
           ->header('Pragma', 'no-cache')
           ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }
    
    public function showDay(Request $request, $date)
    {
        // Middleware sudah memastikan user terautentikasi, tidak perlu pengecekan manual
        $user = Auth::user();

        $date = Carbon::createFromFormat('Y-m-d', $date);
        
        // Get records for the specific day
        $records = $user->moodRecords()
            ->whereDate('created_at', $date->format('Y-m-d'))
            ->get();
            
        $moodLabels = [
            'netral' => 'Biasa saja',
            'senyum' => 'Senang',
            'sedih' => 'Sedih',
            'lelah' => 'Lelah',
            'marah' => 'Marah'
        ];
        
        $formattedDate = $date->locale('id_ID')->translatedFormat('l, j F Y');
        
        // Check if this is a Turbo Frame request
        $turboFrame = $request->header('Turbo-Frame');
        if ($turboFrame === 'calendar-day-view') {
            // Return Turbo Frame response
            return view('components._partials.calendar_day_content', [
                'records' => $records,
                'moodLabels' => $moodLabels,
                'formattedDate' => $formattedDate,
                'date' => $date
            ]);
        }
        
        // Check if this is a Turbo Stream request (fallback)
        $acceptHeader = $request->header('Accept', '');
        if (strpos($acceptHeader, 'text/vnd.turbo-stream') !== false) {
            // Return Turbo Stream response
            $dayContent = view('components._partials.calendar_day_content', [
                'records' => $records,
                'moodLabels' => $moodLabels,
                'formattedDate' => $formattedDate,
                'date' => $date
            ])->render();
            
            $streamContent = '<turbo-stream action="replace" target="calendar-day-view">'.PHP_EOL.
                            '<template>'.PHP_EOL.
                            '<div id="calendar-day-view">' . $dayContent . '</div>'.PHP_EOL.
                            '</template>'.PHP_EOL.
                            '</turbo-stream>'.PHP_EOL;
            
            return response($streamContent, 200, ['Content-Type' => 'text/vnd.turbo-stream.html']);
        }
        
        return view('calendar.day', [
            'records' => $records,
            'moodLabels' => $moodLabels,
            'formattedDate' => $formattedDate,
            'date' => $date
        ]);
    }
}