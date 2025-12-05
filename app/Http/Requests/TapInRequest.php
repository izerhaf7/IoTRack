<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request untuk validasi data Tap In.
 * Memvalidasi NIM, tujuan kunjungan, dan data peminjaman.
 */
class TapInRequest extends FormRequest
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
            'purpose'    => 'required|in:belajar,pinjam',
            'item_id'    => 'nullable|required_if:purpose,pinjam|exists:items,id',
            'quantity'   => 'nullable|required_if:purpose,pinjam|integer|min:1',
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
            'purpose.required'    => 'Tujuan kunjungan wajib dipilih.',
            'purpose.in'          => 'Tujuan kunjungan tidak valid.',
            'item_id.required_if' => 'Barang wajib dipilih jika tujuan adalah meminjam.',
            'item_id.exists'      => 'Barang tidak ditemukan.',
            'quantity.required_if'=> 'Jumlah wajib diisi jika tujuan adalah meminjam.',
            'quantity.integer'    => 'Jumlah harus berupa angka.',
            'quantity.min'        => 'Jumlah minimal adalah 1.',
        ];
    }
}
