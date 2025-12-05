<?php

namespace App\Exports;

use App\Models\Visit;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Kelas untuk mengekspor data kunjungan ke format Excel.
 */
class VisitsExport
{
    /**
     * Mengambil koleksi kunjungan hari ini dengan relasi peminjaman.
     */
    public function collection(): Collection
    {
        return Visit::whereDate('created_at', now()->today())
            ->with(['borrowings.item'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Header kolom dalam Bahasa Indonesia.
     */
    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Waktu Masuk',
            'Waktu Keluar',
            'Nama Pengunjung',
            'NIM',
            'Tujuan',
            'Detail Peminjaman',
            'Status',
        ];
    }

    /**
     * Memformat setiap baris kunjungan untuk ekspor.
     */
    public function map($visit, $index): array
    {
        return [
            $index + 1,
            $visit->created_at->format('d/m/Y'),
            $visit->created_at->format('H:i'),
            $visit->tapped_out_at ? $visit->tapped_out_at->format('H:i') : '-',
            $visit->visitor_name,
            $visit->visitor_id,
            $this->formatPurpose($visit->purpose),
            $this->formatBorrowingDetails($visit),
            $this->getStatus($visit),
        ];
    }

    protected function formatPurpose(string $purpose): string
    {
        return match ($purpose) {
            'belajar' => 'Belajar',
            'pinjam' => 'Meminjam',
            default => $purpose,
        };
    }

    protected function formatBorrowingDetails($visit): string
    {
        if ($visit->borrowings->isEmpty()) {
            return '-';
        }

        return $visit->borrowings->map(function ($borrowing) {
            $itemName = $borrowing->item?->name ?? 'Item Dihapus';
            return "{$itemName} ({$borrowing->quantity})";
        })->implode(', ');
    }

    protected function getStatus($visit): string
    {
        $hasActiveBorrowing = $visit->borrowings->contains('status', 'dipinjam');
        
        if ($hasActiveBorrowing) {
            return 'Meminjam';
        } elseif ($visit->tapped_out_at) {
            return 'Selesai';
        }
        return 'Di Lab';
    }

    /**
     * Menghasilkan file Excel.
     */
    public function toExcel(): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Kunjungan');

        // Title
        $sheet->setCellValue('A1', 'LAPORAN KUNJUNGAN LAB IOT');
        $sheet->setCellValue('A2', 'Tanggal: ' . now()->isoFormat('dddd, D MMMM Y'));
        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');
        
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Headers
        $headers = $this->headings();
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '4', $header);
            $col++;
        }

        // Header styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '6366F1']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A4:I4')->applyFromArray($headerStyle);

        // Data
        $visits = $this->collection();
        $row = 5;
        foreach ($visits as $index => $visit) {
            $data = $this->map($visit, $index);
            $col = 'A';
            foreach ($data as $value) {
                $sheet->setCellValue($col . $row, $value);
                $col++;
            }
            $row++;
        }

        // Data styling
        $lastRow = $row - 1;
        if ($lastRow >= 5) {
            $dataStyle = [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ];
            $sheet->getStyle('A5:I' . $lastRow)->applyFromArray($dataStyle);
            
            // Alternate row colors
            for ($i = 5; $i <= $lastRow; $i++) {
                if ($i % 2 == 0) {
                    $sheet->getStyle('A' . $i . ':I' . $i)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('F3F4F6');
                }
            }
        }

        // Auto width
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Summary
        $summaryRow = $lastRow + 2;
        $sheet->setCellValue('A' . $summaryRow, 'Total Kunjungan: ' . $visits->count());
        $sheet->setCellValue('A' . ($summaryRow + 1), 'Belajar: ' . $visits->where('purpose', 'belajar')->count());
        $sheet->setCellValue('A' . ($summaryRow + 2), 'Meminjam: ' . $visits->where('purpose', 'pinjam')->count());

        // Save to temp file
        $filename = $this->filename();
        $tempPath = storage_path('app/temp/' . $filename);
        
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }

    /**
     * Nama file untuk download.
     */
    public function filename(): string
    {
        return 'kunjungan_' . now()->format('Y-m-d') . '.xlsx';
    }
}