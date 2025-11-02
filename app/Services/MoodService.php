<?php

namespace App\Services;

use App\Models\MoodRecord;
use App\Models\MoodQuote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * MoodService adalah kelas layanan yang menangani logika bisnis terkait mood.
 * Kelas ini bertugas untuk menyimpan data mood pengguna dan mengambil kutipan motivasi.
 */
class MoodService
{
    /**
     * Menyimpan mood baru ke database.
     *
     * @param array $data Data mood yang akan disimpan
     * @return MoodRecord|null Objek MoodRecord jika berhasil disimpan, null jika gagal
     */
    public function saveMood(array $data): ?MoodRecord
    {
        try {
            $user = Auth::user();
            
            // Validasi data input
            $validatedData = $this->validateMoodData($data);
            
            // Buat record mood baru
            $moodRecord = $user->moodRecords()->create($validatedData);
            
            Log::info('Mood record saved successfully for user: ' . $user->id);
            
            return $moodRecord;
        } catch (\Exception $e) {
            Log::error('Failed to save mood record:', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Mendapatkan kutipan acak untuk user.
     *
     * @return array Array berisi kutipan dan penulisnya
     */
    public function getRandomQuote(): array
    {
        // Gunakan cache untuk menyimpan kutipan acak per user
        $cacheKey = MoodQuote::getRandomQuoteCacheKey(Auth::id());
        $quoteCache = Cache::remember($cacheKey, 3600, function () { // Cache selama 1 jam
            return MoodQuote::random()->first();
        });
        
        if ($quoteCache) {
            return [
                'quote' => $quoteCache->quote,
                'author' => $quoteCache->author
            ];
        } else {
            // Fallback jika tidak ada kutipan di database
            return [
                'quote' => 'Dibalik setiap kesulitan, tersimpan sebuah kesempatan.',
                'author' => 'Albert Einstein'
            ];
        }
    }

    /**
     * Validasi data mood sebelum disimpan.
     *
     * @param array $data Data mood
     * @return array Data yang sudah divalidasi siap untuk disimpan
     * @throws \InvalidArgumentException Jika data tidak valid
     */
    private function validateMoodData(array $data): array
    {
        // Validasi manual sesuai dengan aturan bisnis
        $allowedMoods = ['netral', 'senyum', 'sedih', 'lelah', 'marah'];
        
        if (!isset($data['mood']) || !in_array($data['mood'], $allowedMoods)) {
            throw new \InvalidArgumentException('Mood tidak valid');
        }
        
        // Validasi field opsional
        if (isset($data['reason']) && !is_string($data['reason'])) {
            throw new \InvalidArgumentException('Reason harus berupa string');
        }
        
        if (isset($data['suggestion_action']) && !is_string($data['suggestion_action'])) {
            throw new \InvalidArgumentException('Suggestion action harus berupa string');
        }
        
        // Siapkan data untuk disimpan
        return [
            'mood' => $data['mood'],
            'reason' => $data['reason'] ?? null,
            'suggestion_action' => $data['suggestion_action'] ?? null,
        ];
    }
}