<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MoodRecord;

class HomeController extends Controller
{
    public function index()
    {
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
}