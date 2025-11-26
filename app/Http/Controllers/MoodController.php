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
            'netral' => ['title' => 'Biasa saja', 'explanation' => 'Ada hal yang lagi kamu pikirin belakangan ini? Kadang ngobrol dikit bisa bantu juga loh', 'suggestion' => 'Kira-kira apa ya yang bisa bikin kamu tambah semangat dikit?'],
            'senyum' => ['title' => 'Senang', 'explanation' => 'Lagi happy banget nih! Cerita dong, apa sih yang bikin hari kamu secerah ini?', 'suggestion' => 'Menurut kamu, apa yang paling ampuh buat jaga mood biar tetap bagus?'],
            'sedih' => ['title' => 'Sedih', 'explanation' => 'Lagi ada perasaan yang mengganggu di hati, ya? Gak usah ditahan sendiri, pelan-pelan aja diceritain', 'suggestion' => 'Kalau boleh tahu, hal kecil apa yang bisa bantu nyembuhin sedih kamu sekarang?'],
            'lelah' => ['title' => 'Lelah', 'explanation' => 'Lagi drop energinya, ya? Kalau mau, ceritakan sedikit aja, biar lebih lega', 'suggestion' => 'Apa ya yang bisa bantu kamu balik semangat pelan-pelan?'],
            'marah' => ['title' => 'Marah', 'explanation' => 'Lagi agak down ya? Cerita dikit dong, siapa tahu bisa bantu nyemangatin', 'suggestion' => 'Kalau lagi kayak gini, apa sih yang bisa bantu kamu merasa lebih baik lagi?']
        ];

        $data = $moodData[$mood] ?? $moodData['netral'];

        $today = now();
        $formattedDate = $today->locale('id_ID')->translatedFormat('l, j F Y');

        $userAvatar = $user->avatar ?: ''; // Gunakan null coalescing
        $jenisKelamin = $user->jenis_kelamin ?? '';

        // Tentukan apakah user berjenis kelamin perempuan
        $isFemale = $jenisKelamin === 'Perempuan' || $jenisKelamin === 'Cewek';

        $emoticonPaths = [
            'netral' => $isFemale ? asset('logo/netral1.png') : asset('logo/netral.png'),
            'senyum' => $isFemale ? asset('logo/senyum1.png') : asset('logo/senyum.png'),
            'sedih' => $isFemale ? asset('logo/sedih1.png') : asset('logo/sedih.png'),
            'lelah' => $isFemale ? asset('logo/lelah1.png') : asset('logo/lelah.png'),
            'marah' => $isFemale ? asset('logo/marah1.png') : asset('logo/marah.png'),
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

        // 4. BUAT KONTEN UNTUK UPDATE KALENDER (menambah emotikon di kotak kalender hari ini)
        $today = now()->format('Y-m-d');
        $jenisKelamin = $user->jenis_kelamin ?? '';
        $isFemale = $jenisKelamin === 'Perempuan' || $jenisKelamin === 'Cewek';

        $emoticonPaths = [
            'netral' => $isFemale ? asset('logo/netral1.png') : asset('logo/netral.png'),
            'senyum' => $isFemale ? asset('logo/senyum1.png') : asset('logo/senyum.png'),
            'sedih' => $isFemale ? asset('logo/sedih1.png') : asset('logo/sedih.png'),
            'lelah' => $isFemale ? asset('logo/lelah1.png') : asset('logo/lelah.png'),
            'marah' => $isFemale ? asset('logo/marah1.png') : asset('logo/marah.png'),
        ];

        $emoticonPath = $emoticonPaths[$moodRecord->mood] ?? $emoticonPaths['netral'];
        $tooltipText = $moodRecord->reason ?? 'Mood: ' . ($moodLabels[$moodRecord->mood] ?? $moodRecord->mood);
        if ($moodRecord->admin_response) {
            $tooltipText .= ' - Direspons oleh Admin/HRD';
        }

        $calendarDayKey = 'day_'.$today; // Tidak perlu mengganti tanda hubung lagi karena sekarang formatnya langsung Y-m-d
        $calendarEmoticonHtml = '<div class="mood-emoticon-wrapper">'.
                                    '<img src="'.$emoticonPath.'" '.
                                         'alt="'.$moodRecord->mood.'" '.
                                         'class="mood-emoticon '.$moodRecord->mood.'" '.
                                         'data-bs-toggle="tooltip" '.
                                         'title="'.$tooltipText.'" '.
                                         'onclick="showDayRecords(\''.$today.'\')">'.
                                    ($moodRecord->admin_response ?
                                        '<span class="admin-response-indicator-calendar" '.
                                              'data-bs-toggle="tooltip" '.
                                              'title="Direspons oleh Admin/HRD">'.
                                            '<i class="bi bi-check-circle-fill"></i>'.
                                        '</span>' : '').
                                '</div>';

        // Update untuk memperbarui emotikon di hari ini menggunakan ID unik div dalam kotak kalender
        // Kita tambahkan ID ke div .day-records juga agar bisa ditargetkan
        $updateCalendarStream = TurboStreamHelper::append('day_'.$today.'_records', $calendarEmoticonHtml);

        // 5. BUAT STREAM UNTUK MENGGANTI LIST (SELALU REPLACE)
        // Kita selalu 'replace' list untuk memastikan halaman 1 (terbaru) ditampilkan
        $updateListStream = TurboStreamHelper::replace('record_container_list', $recordsContent);

        // 6. BUAT STREAM UNTUK MENGGANTI PAGINASI (SELALU REPLACE)
        // View ini akan otomatis menampilkan/menyembunyikan paginasi
        $updatePaginationStream = TurboStreamHelper::replace('pagination-container',
            view('components._partials.pagination', ['records' => $records])->render()
        );

        // 7. BUAT STREAM UNTUK HAPUS PESAN "BELUM ADA CATATAN" (JIKA PERLU)
        $removeMessageStream = '';
        $isFirstRecord = $recordCountBeforeSave === 0;
        if ($isFirstRecord) {
            $removeMessageStream = TurboStreamHelper::remove('no-records-message');
        }

        // 8. BUAT STREAM UNTUK MENGGANTI KONTEN MODAL (PESAN SUKSES)
        $replaceStream = TurboStreamHelper::replace('mood_modal_content',
            view('components._partials.mood_modal_success')->render()
        );

        // 9. GABUNGKAN SEMUA STREAM
        $streamContent = TurboStreamHelper::combine([
            $removeMessageStream,
            $updateListStream,
            $updatePaginationStream,
            $updateCalendarStream,
            $replaceStream
        ]);
        
        // Prevent caching of Turbo Stream response to ensure real-time updates
        return response($streamContent, 200, [
            'Content-Type' => 'text/vnd.turbo-stream.html',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Sat, 01 Jan 2000 00:00:00 GMT'
        ]);
    }

    public function edit(MoodRecord $moodRecord)
    {
        // Pastikan user yang login adalah pemilik record
        if ($moodRecord->user_id !== Auth::id()) {
            abort(403);
        }

        $moodData = [
            'netral' => ['title' => 'Biasa saja', 'explanation' => 'Ada hal yang lagi kamu pikirin belakangan ini? Kadang ngobrol dikit bisa bantu juga loh', 'suggestion' => 'Kira-kira apa ya yang bisa bikin kamu tambah semangat dikit?'],
            'senyum' => ['title' => 'Senang', 'explanation' => 'Lagi happy banget nih! Cerita dong, apa sih yang bikin hari kamu secerah ini?', 'suggestion' => 'Menurut kamu, apa yang paling ampuh buat jaga mood biar tetap bagus?'],
            'sedih' => ['title' => 'Sedih', 'explanation' => 'Lagi ada perasaan yang mengganggu di hati, ya? Gak usah ditahan sendiri, pelan-pelan aja diceritain', 'suggestion' => 'Kalau boleh tahu, hal kecil apa yang bisa bantu nyembuhin sedih kamu sekarang?'],
            'lelah' => ['title' => 'Lelah', 'explanation' => 'Lagi drop energinya, ya? Kalau mau, ceritakan sedikit aja, biar lebih lega', 'suggestion' => 'Apa ya yang bisa bantu kamu balik semangat pelan-pelan?'],
            'marah' => ['title' => 'Marah', 'explanation' => 'Lagi agak down ya? Cerita dikit dong, siapa tahu bisa bantu nyemangatin', 'suggestion' => 'Kalau lagi kayak gini, apa sih yang bisa bantu kamu merasa lebih baik lagi?']
        ];

        $user = Auth::user();
        $jenisKelamin = $user->jenis_kelamin ?? '';
        $isFemale = $jenisKelamin === 'Perempuan' || $jenisKelamin === 'Cewek';

        $emoticonPaths = [
            'netral' => $isFemale ? asset('logo/netral1.png') : asset('logo/netral.png'),
            'senyum' => $isFemale ? asset('logo/senyum1.png') : asset('logo/senyum.png'),
            'sedih' => $isFemale ? asset('logo/sedih1.png') : asset('logo/sedih.png'),
            'lelah' => $isFemale ? asset('logo/lelah1.png') : asset('logo/lelah.png'),
            'marah' => $isFemale ? asset('logo/marah1.png') : asset('logo/marah.png'),
        ];

        return view('components._partials.edit_mood_modal', [
            'record' => $moodRecord,
            'moodData' => $moodData,
            'emoticonPaths' => $emoticonPaths
        ]);
    }

    public function update(StoreMoodRequest $request, MoodRecord $moodRecord)
    {
        // Pastikan user yang login adalah pemilik record
        if ($moodRecord->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validated();
        
        // Update record
        $moodRecord->update([
            'mood' => $validated['mood'],
            'reason' => $validated['reason'],
            'suggestion_action' => $validated['suggestion_action']
        ]);

        $moodLabels = [
            'netral' => 'Biasa saja',
            'senyum' => 'Senang',
            'sedih' => 'Sedih',
            'lelah' => 'Lelah',
            'marah' => 'Marah'
        ];

        $user = Auth::user();

        // 1. UPDATE ITEM DI LIST (REPLACE)
        // Kita render ulang item yang diupdate saja
        $updatedItemHtml = view('components.mood-record-item', [
            'record' => $moodRecord,
            'user' => $user
        ])->render();

        $updateListStream = TurboStreamHelper::replace('mood_record_' . $moodRecord->id, $updatedItemHtml);

        // 2. UPDATE KALENDER
        // Kita refresh seluruh konten hari itu agar urutan dan data sesuai
        $date = $moodRecord->date_recorded->format('Y-m-d');
        $dayRecords = $user->moodRecords()->whereDate('date_recorded', $date)->get();
        
        $calendarHtml = '';
        $jenisKelamin = $user->jenis_kelamin ?? '';
        $isFemale = $jenisKelamin === 'Perempuan' || $jenisKelamin === 'Cewek';

        $emoticonPaths = [
            'netral' => $isFemale ? asset('logo/netral1.png') : asset('logo/netral.png'),
            'senyum' => $isFemale ? asset('logo/senyum1.png') : asset('logo/senyum.png'),
            'sedih' => $isFemale ? asset('logo/sedih1.png') : asset('logo/sedih.png'),
            'lelah' => $isFemale ? asset('logo/lelah1.png') : asset('logo/lelah.png'),
            'marah' => $isFemale ? asset('logo/marah1.png') : asset('logo/marah.png'),
        ];

        foreach ($dayRecords as $dayRecord) {
            $emoticonPath = $emoticonPaths[$dayRecord->mood] ?? $emoticonPaths['netral'];
            $tooltipText = $dayRecord->reason ?? 'Mood: ' . ($moodLabels[$dayRecord->mood] ?? $dayRecord->mood);
            if ($dayRecord->admin_response) {
                $tooltipText .= ' - Direspons oleh Admin/HRD';
            }
            
            $calendarHtml .= '<div class="mood-emoticon-wrapper">'.
                                '<img src="'.$emoticonPath.'"'.
                                     ' alt="'.$dayRecord->mood.'"'.
                                     ' class="mood-emoticon '.$dayRecord->mood.'"'.
                                     ' data-bs-toggle="tooltip"'.
                                     ' title="'.$tooltipText.'"'.
                                     ' onclick="showDayRecords(\''.$date.'\')">'.
                                ($dayRecord->admin_response ?
                                    '<span class="admin-response-indicator-calendar" '.
                                          'data-bs-toggle="tooltip" '.
                                          'title="Direspons oleh Admin/HRD">'.
                                        '<i class="bi bi-check-circle-fill"></i>'.
                                    '</span>' : '').
                            '</div>';
        }
        
        $updateCalendarStream = TurboStreamHelper::replace('day_'.$date.'_records', $calendarHtml);

        // 3. TUTUP MODAL / GANTI DENGAN PESAN SUKSES
        $successModalStream = TurboStreamHelper::replace('mood_modal_content', 
            view('components._partials.mood_modal_success_edit')->render());

        // Combine all streams and add cache‑control headers
        $streamContent = TurboStreamHelper::combine([
            $updateListStream,
            $updateCalendarStream,
            $successModalStream,
        ]);

        return response($streamContent, 200, [
            'Content-Type' => 'text/vnd.turbo-stream.html',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Sat, 01 Jan 2000 00:00:00 GMT',
        ]);
    }
}