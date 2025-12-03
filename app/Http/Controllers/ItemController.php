<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // WAJIB DITAMBAHKAN

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::all();
        return view('items.index', compact('items'));
    }

    // SIMPAN BARANG BARU (+Gambar)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'total_stock' => 'required|integer|min:1',
            // Validasi gambar: harus gambar (jpeg,png,jpg,gif) dan maks 2MB
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // 1. Handle Upload Gambar
        if ($request->hasFile('image')) {
            // Simpan ke folder 'public/items'. Akan menghasilkan path unik.
            $imagePath = $request->file('image')->store('items', 'public');
        }

        // 2. Simpan ke Database
        Item::create([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $imagePath, // Simpan path gambarnya
            'total_stock' => $request->total_stock,
            'current_stock' => $request->total_stock,
        ]);

        return back()->with('success', 'Barang berhasil ditambahkan');
    }

    // UPDATE BARANG (+Ganti Gambar)
    public function update(Request $request, Item $item)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'total_stock' => 'required|integer|min:1',
            'current_stock' => 'required|integer',
            // Gambar di sini 'nullable' (boleh kosong jika tidak ingin ganti gambar)
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $dataToUpdate = $request->all();

        // Cek jika user mengupload gambar baru
        if ($request->hasFile('image')) {
            // 1. Hapus gambar lama jika ada
            if ($item->image && Storage::disk('public')->exists($item->image)) {
                Storage::disk('public')->delete($item->image);
            }

            // 2. Simpan gambar baru
            $newImagePath = $request->file('image')->store('items', 'public');
            $dataToUpdate['image'] = $newImagePath;
        }

        $item->update($dataToUpdate);

        return back()->with('success', 'Data barang diperbarui');
    }

    // HAPUS BARANG (+Hapus Gambar Fisik)
    public function destroy(Item $item)
    {
        // Hapus file gambar dari penyimpanan jika ada
    if ($item->image && Storage::disk('public')->exists($item->image)) {
        Storage::disk('public')->delete($item->image);
    }
        
        // Hapus data dari database
        $item->delete();
        return back()->with('success', 'Barang berhasil dihapus');
    }
}