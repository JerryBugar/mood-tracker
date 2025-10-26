<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MoodQuote;
use App\Models\MoodRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View; // Tambahkan ini
// use Hotwired\Turbo\Turbo; // Tidak perlu jika membuat stream manual

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
        Log::info('Mood save request received (via Turbo):', $request->all());

        $validated = $request->validate([
            'mood' => 'required|string|in:netral,senyum,sedih,lelah,marah',
            'reason' => 'nullable|string',
            'suggestion_action' => 'nullable|string',
        ]);

        $moodLabels = [
            'netral' => 'Biasa saja',
            'senyum' => 'Senang',
            'sedih' => 'Sedih',
            'lelah' => 'Lelah',
            'marah' => 'Marah'
        ];

        try {
            $user = auth()->user();
            
            // Hitung jumlah record SEBELUM menyimpan
            $recordCountBeforeSave = $user->moodRecords()->count();
            
            // 1. SIMPAN RECORD BARU
            $moodRecord = $user->moodRecords()->create($validated);
            Log::info('Mood record saved successfully for user: ' . $user->id);
            
            // Cek apakah ini adalah record pertama
            $isFirstRecord = $recordCountBeforeSave === 0;

            // 2. AMBIL DATA HALAMAN PERTAMA (SETELAH DISIMPAN)
            // Ini adalah *satu-satunya* query paginasi yang kita perlukan.
            // Dia akan tahu kapan harus `hasPages()` (saat record > 5)
            $records = $user->moodRecords()->latest()->paginate(5);
            
            // 3. RENDER KONTEN LIST BARU
            $recordsContent = '';
            if ($records->count() > 0) {
                foreach ($records as $record) {
                    $recordsContent .= view('components._partials.mood_record_item', [
                        'record' => $record,
                        'moodLabels' => $moodLabels,
                        'user' => $user
                    ])->render();
                }
            }
            
            // 4. BUAT STREAM UNTUK MENGGANTI LIST (SELALU REPLACE)
            // Kita selalu 'replace' list untuk memastikan halaman 1 (terbaru) ditampilkan
            $updateListStream = '<turbo-stream action="replace" target="record_container_list">'.PHP_EOL.
                              '<template>'.PHP_EOL.
                              '<div id="record_container_list">' . $recordsContent . '</div>'.PHP_EOL.
                              '</template>'.PHP_EOL.
                              '</turbo-stream>'.PHP_EOL;
            
            // 5. BUAT STREAM UNTUK MENGGANTI PAGINASI (SELALU REPLACE)
            // View ini akan otomatis menampilkan/menyembunyikan paginasi
            $updatePaginationStream = '<turbo-stream action="replace" target="pagination-container">'.PHP_EOL.
                                    '<template>'.PHP_EOL.
                                    // Bungkus dengan div target
                                    '<div id="pagination-container">' . 
                                       view('components._partials.pagination', ['records' => $records])->render().
                                    '</div>' . PHP_EOL.
                                    '</template>'.PHP_EOL.
                                    '</turbo-stream>'.PHP_EOL;

            // 6. BUAT STREAM UNTUK HAPUS PESAN "BELUM ADA CATATAN" (JIKA PERLU)
            $removeMessageStream = '';
            if ($isFirstRecord) {
                $removeMessageStream = '<turbo-stream action="remove" target="no-records-message">'.PHP_EOL.
                                      '<template></template>'.PHP_EOL.
                                      '</turbo-stream>'.PHP_EOL;
            }
            
            // 7. BUAT STREAM UNTUK MENGGANTI KONTEN MODAL (PESAN SUKSES)
            $replaceStream = '<turbo-stream action="replace" target="mood_modal_content">'.PHP_EOL.
                            '<template>'.PHP_EOL.
                            // Pastikan Anda punya view ini
                            view('components._partials.mood_modal_success')->render().PHP_EOL. 
                            '</template>'.PHP_EOL.
                            '</turbo-stream>'.PHP_EOL;
            
            // 8. GABUNGKAN SEMUA STREAM
            $streamContent = $removeMessageStream . $updateListStream . $updatePaginationStream . $replaceStream;
            
            return response($streamContent, 200, ['Content-Type' => 'text/vnd.turbo-stream.html']);

        } catch (\Exception $e) {
            Log::error('Failed to save mood record:', ['error' => $e->getMessage()]);
            // Fallback jika terjadi error
            return view('components._partials.mood_modal_success');
        }
    }
}