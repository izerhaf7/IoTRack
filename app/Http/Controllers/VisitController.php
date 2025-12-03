<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use App\Models\Item;
use App\Models\Borrowing;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisitController extends Controller
{
    // 1. Form Tap In
    public function create()
    {
        $items = Item::where('current_stock', '>', 0)->get();

        return view('visit.form', compact('items'));
    }

    // 2. Proses Tap In
    public function store(Request $request)
    {
        $request->validate([
            'visitor_id' => 'required',                     // NIM saja
            'purpose'    => 'required|in:belajar,pinjam',
            'item_id'    => 'nullable|required_if:purpose,pinjam',
            'quantity'   => 'nullable|required_if:purpose,pinjam|integer|min:1',
        ]);

        // Cari mahasiswa berdasarkan NIM
        $student = Student::where('nim', $request->visitor_id)->first();

        if (!$student) {
            return back()
                ->withInput()
                ->with('error', 'NIM tidak terdaftar di data mahasiswa.');
        }

        // Cek stok kalau meminjam
        if ($request->purpose === 'pinjam') {
            $item = Item::findOrFail($request->item_id);

            if ($item->current_stock < $request->quantity) {
                return back()
                    ->withInput()
                    ->with('error', 'Stok barang '.$item->name.' hanya tersisa '.$item->current_stock.' unit.');
            }
        }

        // Simpan visit + borrowing di dalam transaksi
        DB::transaction(function () use ($request, $student, &$visit) {

            // Simpan data kunjungan
            $visit = Visit::create([
                'visitor_name' => $student->name,   // otomatis dari tabel students
                'visitor_id'   => $student->nim,
                'purpose'      => $request->purpose,
            ]);

            if ($request->purpose === 'pinjam') {
                $item = Item::lockForUpdate()->findOrFail($request->item_id);

                Borrowing::create([
                    'visit_id' => $visit->id,
                    'item_id'  => $item->id,
                    'quantity' => $request->quantity,
                    'status'   => 'dipinjam',
                ]);

                $item->decrement('current_stock', $request->quantity);
            }
        });

        // Setelah sukses, arahkan ke halaman "Selamat Datang"
        return redirect()->route('visit.welcome', $visit->id);
    }

    // 3. Halaman sambutan setelah Tap In
    public function welcome(Visit $visit)
    {
        return view('visit.welcome', compact('visit'));
    }

    // 4. Form Tap Out tetap seperti punyamu (NIM saja)
    public function tapOutForm()
    {
        return view('visit.tap-out');
    }

    // 5. Proses Tap Out -> redirect ke halaman ucapan penutup
    public function tapOutProcess(Request $request)
    {
        $request->validate([
            'visitor_id' => 'required'
        ]);

        $visits = Visit::where('visitor_id', $request->visitor_id)->get();

        if ($visits->isEmpty()) {
            return back()->with('error', 'Data kunjungan tidak ditemukan untuk NIM tersebut.');
        }

        DB::transaction(function () use ($visits) {
            foreach ($visits as $visit) {
                $borrowings = Borrowing::where('visit_id', $visit->id)->get();

                foreach ($borrowings as $borrow) {
                    $item = $borrow->item;

                    if ($item) {
                        $newStock = $item->current_stock + $borrow->quantity;
                        $item->current_stock = min($newStock, $item->total_stock);
                        $item->save();
                    }

                    // JANGAN delete; cukup update status agar histori tetap ada
                    $borrow->update([
                        'status'      => 'dikembalikan',
                        'returned_at' => now(),
                    ]);
                }
            }
        });

        return redirect()->route('visit.goodbye', ['visitor_id' => $request->visitor_id]);
    }

    // 6. Halaman ucapan setelah Tap Out
    public function goodbye($visitor_id)
    {
        $latestVisit = Visit::where('visitor_id', $visitor_id)->latest()->first();

        $name = $latestVisit?->visitor_name ?? 'Pengunjung';

        return view('visit.goodbye', compact('name'));
    }
}
