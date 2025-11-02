<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MoodRecord;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Mood\MoodRecordController;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Kita gunakan MoodRecordController untuk menangani logika data dan Turbo Streams
        $moodRecordController = new MoodRecordController(app(\App\Services\MoodService::class));
        
        // Jika permintaan datang dari Turbo Stream (misalnya dari link pagination)
        $acceptHeader = $request->header('Accept', '');
        if (strpos($acceptHeader, 'text/vnd.turbo-stream') !== false) {
            return $moodRecordController->index($request);
        }

        // Untuk permintaan biasa, kita tetap kembalikan view dengan data
        $moodLabels = [
            'netral' => 'Biasa saja',
            'senyum' => 'Senang',
            'sedih' => 'Sedih',
            'lelah' => 'Lelah',
            'marah' => 'Marah'
        ];

        $records = collect(); // Default empty collection
        
        if (Auth::check()) {
            // Ambil records untuk user yang sedang login, 5 record per halaman
            $records = Auth::user()->moodRecords()
                ->latest()
                ->paginate(5); // 5 record per halaman
        }

        return view('home-page.index', [
            'records' => $records,
            'moodLabels' => $moodLabels
        ]);
    }
    
    // Kita bisa hapus method pagination karena sekarang menggunakan MoodRecordController
    // Tapi kita tetap pertahankan untuk kompatibilitas
    public function pagination(Request $request)
    {
        $moodRecordController = new MoodRecordController(app(\App\Services\MoodService::class));
        return $moodRecordController->pagination($request);
    }
}