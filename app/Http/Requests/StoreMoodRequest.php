<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMoodRequest extends FormRequest
{
    /**
     * Tentukan apakah user diizinkan untuk membuat request ini.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Aturan validasi yang berlaku untuk request ini.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'mood' => 'required|string|in:netral,senyum,sedih,lelah,marah',
            'reason' => 'nullable|string',
            'suggestion_action' => 'nullable|string',
        ];
    }

    /**
     * Pesan kesalahan kustom untuk validasi.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'mood.required' => 'Mood harus dipilih.',
            'mood.string' => 'Mood harus berupa teks.',
            'mood.in' => 'Mood yang dipilih tidak valid.',
            'reason.string' => 'Alasan harus berupa teks.',
            'suggestion_action.string' => 'Saran tindakan harus berupa teks.',
        ];
    }
}