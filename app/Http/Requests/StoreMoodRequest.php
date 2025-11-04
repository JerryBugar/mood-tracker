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
            'reason' => 'required|string|min:1',
            'suggestion_action' => 'required|string|min:1',
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
            'reason.required' => 'Alasan harus diisi.',
            'reason.string' => 'Alasan harus berupa teks.',
            'reason.min' => 'Alasan harus diisi dengan teks yang valid.',
            'suggestion_action.required' => 'Saran tindakan harus diisi.',
            'suggestion_action.string' => 'Saran tindakan harus berupa teks.',
            'suggestion_action.min' => 'Saran tindakan harus diisi dengan teks yang valid.',
        ];
    }
}