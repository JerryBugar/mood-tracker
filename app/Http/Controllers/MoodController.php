<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MoodQuote;
use Illuminate\Support\Facades\View; // Tambahkan ini
use Hotwired\Turbo\Turbo; // Pastikan ini di-use jika Anda menggunakan package turbo-laravel

class MoodController extends Controller
{
    public function showMoodModal(Request $request)
    {
        // Pastikan user terautentikasi (logika ini tetap sama)
        if (!Auth::check()) {
            // Untuk Turbo Frame, kita bisa return response error atau redirect
            // Tapi karena ini dipicu link, lebih baik handle di middleware/route
            // Jika request adalah Turbo Frame dan user tidak login, mungkin return frame kosong dg pesan error
             return response('<turbo-frame id="mood_modal_content"><div class="alert alert-danger">Anda harus login.</div></turbo-frame>', 401)
                    ->header('Content-Type', 'text/html; turbo-stream-content-type=text/html');
        }

        $user = Auth::user();
        $mood = $request->input('mood', 'netral'); // Default ke netral jika tidak ada

        $moodData = [
            'netral' => ['title' => 'Biasa saja', 'explanation' => 'Kenapa Biasa saja? Coba ceritain...', 'suggestion' => 'Kira-kira apa yang bisa bikin kamu gak Biasa aja?'],
            'senyum' => ['title' => 'Senang', 'explanation' => 'Kenapa kamu merasa senang hari ini? Bagikan ceritanya...', 'suggestion' => 'Apa yang bisa bikin kamu tetap merasa senang?'],
            'sedih' => ['title' => 'Sedih', 'explanation' => 'Kenapa kamu merasa sedih? Ceritakan perasaanmu...', 'suggestion' => 'Apa yang bisa membantumu merasa lebih baik?'],
            'lelah' => ['title' => 'Lelah', 'explanation' => 'Kenapa kamu merasa lelah? Apa penyebabnya?', 'suggestion' => 'Apa yang bisa kamu lakukan untuk mengurangi rasa lelah ini?'],
            'marah' => ['title' => 'Marah', 'explanation' => 'Kenapa kamu merasa marah? Ceritakan penyebabnya...', 'suggestion' => 'Apa yang bisa membantumu meredakan amarah ini?']
        ];

        $data = $moodData[$mood] ?? $moodData['netral'];

        $today = now();
        $formattedDate = $today->locale('id_ID')->translatedFormat('l, j F Y');

        $userAvatar = $user->avatar ?: ''; // Gunakan null coalescing
        $jenisKelamin = $user->jenis_kelamin ?? '';

        $emoticonPaths = [
            'netral' => $jenisKelamin === 'Cewek' ? asset('logo/netral1.png') : asset('logo/netral.png'),
            'senyum' => $jenisKelamin === 'Cewek' ? asset('logo/senyum1.png') : asset('logo/senyum.png'),
            'sedih' => $jenisKelamin === 'Cewek' ? asset('logo/sedih1.png') : asset('logo/sedih.png'),
            'lelah' => $jenisKelamin === 'Cewek' ? asset('logo/lelah1.png') : asset('logo/lelah.png'),
            'marah' => $jenisKelamin === 'Cewek' ? asset('logo/marah1.png') : asset('logo/marah.png'),
        ];
        $emoticonPath = $emoticonPaths[$mood] ?? $emoticonPaths['netral'];

        // Data untuk view
        $viewData = [
            'mood' => $mood, // Kirim mood saat ini
            'title' => $data['title'],
            'explanation' => $data['explanation'],
            'suggestion' => $data['suggestion'],
            'date' => $formattedDate, // Kita mungkin tidak perlu ini di frame, tapi di header modal
            'avatar' => $userAvatar, // idem
            'emoticon' => $emoticonPath,
            // Quote tidak perlu di sini karena sudah ada di container utama
        ];

        // Kembalikan view partial yang berisi turbo-frame
        // Penting: Pastikan view ini hanya berisi frame dan kontennya
        return View::make('components._partials.mood_modal_content', $viewData);
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
        \Illuminate\Support\Facades\Log::info('Mood save request received (via Turbo):', $request->all());

        // Validasi request jika perlu
        $validated = $request->validate([
            'mood' => 'required|string|in:netral,senyum,sedih,lelah,marah',
            'reason' => 'nullable|string',
            'suggestion_action' => 'nullable|string',
        ]);

        // Logika simpan mood ke database...
        // Misalnya:
        // auth()->user()->moods()->create($validated);

        \Illuminate\Support\Facades\Log::info('Mood data validated and ready to save:', $validated);

        // Jika berhasil disimpan, kirim Turbo Stream untuk menghapus frame modal
        // Ini akan secara otomatis menutup modal jika modal memiliki data-turbo-temporary

        // Menggunakan package turbo-laravel (lebih disarankan)
        if (class_exists(\Hotwired\Turbo\Turbo::class)) {
            return response()->turboStreamRemove('mood_modal_content');
        }

        // Atau secara manual jika tidak pakai package
        return response('<turbo-stream action="remove" target="mood_modal_content"></turbo-stream>', 200)
               ->header('Content-Type', Turbo::TURBO_STREAM_CONTENT_TYPE);
    }
}