<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MoodQuote;

class MoodController extends Controller
{
    public function showMoodModal(Request $request)
    {
        // Pastikan user terautentikasi
        if (!Auth::check()) {
            return response()->json([
                'error' => 'User not authenticated',
                'message' => 'Anda harus login terlebih dahulu'
            ], 401);
        }

        // Ambil user yang terautentikasi
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'error' => 'User data not available',
                'message' => 'Data pengguna tidak ditemukan'
            ], 401);
        }

        $mood = $request->input('mood');
        
        $moodData = [
            'netral' => [
                'title' => 'Biasa saja',
                'explanation' => 'Kenapa Biasa saja? Coba ceritain...',
                'suggestion' => 'Kira-kira apa yang bisa bikin kamu gak Biasa aja?'
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
        
        $userAvatar = $user->avatar ? $user->avatar : '';
        $jenisKelamin = $user->jenis_kelamin ?? '';
        
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
        
        // Ambil kutipan acak dari cache
        $quoteCacheKey = \App\Models\MoodQuote::getRandomQuoteCacheKey(Auth::id()); // Cache per user dengan versi
        $randomQuote = cache()->remember($quoteCacheKey, 3600, function () { // Cache selama 1 jam
            return MoodQuote::inRandomOrder()->first();
        });
        
        $quoteText = $randomQuote ? $randomQuote->quote : 'Dibalik setiap kesulitan, tersimpan sebuah kesempatan.';
        $quoteAuthor = $randomQuote ? $randomQuote->author : 'Albert Einstein';

        return response()->json([
            'title' => $data['title'],
            'explanation' => $data['explanation'],
            'suggestion' => $data['suggestion'],
            'date' => $formattedDate,
            'avatar' => $userAvatar,
            'emoticon' => $emoticonPath,
            'quote' => $quoteText,
            'author' => $quoteAuthor
        ])->header('Content-Type', 'application/json');
    }
    
    public function getRandomQuote()
    {
        // Gunakan cache untuk menyimpan kutipan acak per user
        $cacheKey = \App\Models\MoodQuote::getRandomQuoteCacheKey(Auth::id());
        $quoteCache = cache()->remember($cacheKey, 3600, function () { // Cache selama 1 jam
            return MoodQuote::inRandomOrder()->first();
        });
        
        if ($quoteCache) {
            return response()->json([
                'quote' => $quoteCache->quote,
                'author' => $quoteCache->author
            ]);
        } else {
            // Fallback jika tidak ada kutipan di database
            return response()->json([
                'quote' => 'Dibalik setiap kesulitan, tersimpan sebuah kesempatan.',
                'author' => 'Albert Einstein'
            ]);
        }
    }
    
    public function saveMood(Request $request)
    {
        // Logika untuk menyimpan mood ke database akan ditambahkan di sini
        return response()->json(['success' => true, 'message' => 'Mood berhasil disimpan']);
    }
}