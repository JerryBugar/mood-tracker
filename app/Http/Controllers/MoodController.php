<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MoodController extends Controller
{
    public function showMoodModal(Request $request)
    {
        $mood = $request->input('mood');
        
        $moodData = [
            'netral' => [
                'title' => 'Netral atau biasa saja',
                'explanation' => 'Kenapa biasa saja? Coba ceritain...',
                'suggestion' => 'Kira-kira apa yang bisa bikin kamu gak biasa aja?'
            ],
            'senyum' => [
                'title' => 'Senang',
                'explanation' => 'Kenapa kamu merasa senang hari ini? Bagikan ceritanya...',
                'suggestion' => 'Apa yang bisa bikin kamu tetap merasa senang?'
            ],
            'sedih' => [
                'title' => 'Sedih',
                'explanation' => 'Kenapa kamu merasa sedih? Ceritakan perasaanmu...',
                'suggestion' => 'Apa yang bisa membantumu merasa lebih baik?'
            ],
            'lelah' => [
                'title' => 'Lelah',
                'explanation' => 'Kenapa kamu merasa lelah? Apa penyebabnya?',
                'suggestion' => 'Apa yang bisa kamu lakukan untuk mengurangi rasa lelah ini?'
            ],
            'marah' => [
                'title' => 'Marah',
                'explanation' => 'Kenapa kamu merasa marah? Ceritakan penyebabnya...',
                'suggestion' => 'Apa yang bisa membantumu meredakan amarah ini?'
            ]
        ];
        
        $data = $moodData[$mood] ?? $moodData['netral'];
        
        $today = now();
        $formattedDate = $today->locale('id_ID')->translatedFormat('l, j F Y');
        
        $userAvatar = Auth::check() && Auth::user()->avatar ? Auth::user()->avatar : '';
        
        $jenisKelamin = Auth::check() ? Auth::user()->jenis_kelamin : '';
        
        $emoticonPath = '';
        switch($mood) {
            case 'netral':
                $emoticonPath = $jenisKelamin === 'Cewek' ? asset('logo/netral1.png') : asset('logo/netral.png');
                break;
            case 'senyum':
                $emoticonPath = $jenisKelamin === 'Cewek' ? asset('logo/senyum1.png') : asset('logo/senyum.png');
                break;
            case 'sedih':
                $emoticonPath = $jenisKelamin === 'Cewek' ? asset('logo/sedih1.png') : asset('logo/sedih.png');
                break;
            case 'lelah':
                $emoticonPath = $jenisKelamin === 'Cewek' ? asset('logo/lelah1.png') : asset('logo/lelah.png');
                break;
            case 'marah':
                $emoticonPath = $jenisKelamin === 'Cewek' ? asset('logo/marah1.png') : asset('logo/marah.png');
                break;
        }
        
        return response()->json([
            'title' => $data['title'],
            'explanation' => $data['explanation'],
            'suggestion' => $data['suggestion'],
            'date' => $formattedDate,
            'avatar' => $userAvatar,
            'emoticon' => $emoticonPath
        ])->header('Content-Type', 'application/json');
    }
    
    public function saveMood(Request $request)
    {
        // Logika untuk menyimpan mood ke database akan ditambahkan di sini
        return response()->json(['success' => true, 'message' => 'Mood berhasil disimpan']);
    }
}