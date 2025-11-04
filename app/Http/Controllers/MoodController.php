<?php

namespace App\Http\Controllers;

use App\Helpers\TurboStreamHelper;
use App\Http\Requests\StoreMoodRequest;
use App\Services\MoodService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Models\MoodQuote;
use App\Models\MoodRecord;

class MoodController extends Controller
{
    protected MoodService $moodService;

    public function __construct(MoodService $moodService)
    {
        $this->moodService = $moodService;
    }

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
        $quoteData = $this->moodService->getRandomQuote();
        
        return response()->json($quoteData);
    }
    
    public function saveMood(StoreMoodRequest $request)
    {
        $moodRecord = $this->moodService->saveMood($request->validated());
        
        if (!$moodRecord) {
            // Jika service mengembalikan null, kemungkinan karena pengguna sudah menyimpan mood hari ini
            // Tampilkan pesan bahwa sudah menyimpan mood hari ini
            $replaceStream = TurboStreamHelper::replace('mood_modal_content', 
                view('components._partials.mood_modal_duplicate')->render()
            );
            
            return response($replaceStream, 200, ['Content-Type' => 'text/vnd.turbo-stream.html']);
        }

        $moodLabels = [
            'netral' => 'Biasa saja',
            'senyum' => 'Senang',
            'sedih' => 'Sedih',
            'lelah' => 'Lelah',
            'marah' => 'Marah'
        ];

        $user = auth()->user();
        
        // Hitung jumlah record SEBELUM menyimpan
        $recordCountBeforeSave = $user->moodRecords()->count();
        
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
        $updateListStream = TurboStreamHelper::replace('record_container_list', $recordsContent);
        
        // 5. BUAT STREAM UNTUK MENGGANTI PAGINASI (SELALU REPLACE)
        // View ini akan otomatis menampilkan/menyembunyikan paginasi
        $updatePaginationStream = TurboStreamHelper::replace('pagination-container', 
            view('components._partials.pagination', ['records' => $records])->render()
        );

        // 6. BUAT STREAM UNTUK HAPUS PESAN "BELUM ADA CATATAN" (JIKA PERLU)
        $removeMessageStream = '';
        $isFirstRecord = $recordCountBeforeSave === 0;
        if ($isFirstRecord) {
            $removeMessageStream = TurboStreamHelper::remove('no-records-message');
        }
        
        // 7. BUAT STREAM UNTUK MENGGANTI KONTEN MODAL (PESAN SUKSES)
        $replaceStream = TurboStreamHelper::replace('mood_modal_content', 
            view('components._partials.mood_modal_success')->render()
        );
        
        // 8. GABUNGKAN SEMUA STREAM
        $streamContent = TurboStreamHelper::combine([
            $removeMessageStream,
            $updateListStream,
            $updatePaginationStream,
            $replaceStream
        ]);
        
        return response($streamContent, 200, ['Content-Type' => 'text/vnd.turbo-stream.html']);
    }
}