<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/dataMHSTekom.csv');

        if (!file_exists($path)) {
            $this->command->warn("File students.csv tidak ditemukan di database/data");
            return;
        }

         if (($handle = fopen($path, 'r')) !== false) {

            // Lewati baris header
            fgetcsv($handle);

            while (($row = fgetcsv($handle, 1000, ';')) !== false) {
                // Kalau file kamu pakai titik koma (;) ganti di atas jadi ';'

                // Skip baris kosong atau baris yang tidak punya NIM & Nama
                if (count($row) < 3) {
                    continue;
                }

                $no         = $row[0] ?? null;
                $nim        = trim($row[1] ?? '');
                $name       = trim($row[2] ?? '');
                $prodi      = $row[3] ?? null;
                $tahunMasuk = $row[4] ?? null;
                $angkatan   = $row[5] ?? null;

                if ($nim === '' || $name === '') {
                    // kalau NIM atau Nama kosong, skip saja
                    continue;
                }

                Student::updateOrCreate(
                    ['nim' => $nim],
                    [
                        'name'         => $name,
                        'program_studi'=> $prodi,
                        'tahun_masuk'  => $tahunMasuk ?: null,
                        'angkatan'     => $angkatan ?: null,
                    ]
                );
            }

            fclose($handle);
        }
    }
}