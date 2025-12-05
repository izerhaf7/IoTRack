<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request untuk validasi data Tap Out.
 * Memvalidasi NIM pengunjung yang akan melakukan Tap Out.
 */
class TapOutRequest extends FormRequest
{
    /**
     * Menentukan apakah pengguna diizinkan membuat request ini.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Mendapatkan aturan validasi untuk request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'visitor_id' => 'required|string',
        ];
    }

    /**
     * Mendapatkan pesan error kustom untuk validasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'visitor_id.required' => 'NIM wajib diisi.',
        ];
    }
}
