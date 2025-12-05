<?php

namespace Tests\Feature\Exports;

use Tests\TestCase;
use App\Models\Visit;
use App\Models\Item;
use App\Models\Borrowing;
use App\Models\Student;
use App\Exports\VisitsExport;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Property-based tests untuk VisitsExport.
 * Menggunakan data providers untuk menguji berbagai skenario ekspor.
 */
class VisitsExportPropertyTest extends TestCase
{
    use RefreshDatabase;

    protected VisitsExport $export;

    protected function setUp(): void
    {
        parent::setUp();
        $this->export = new VisitsExport();
    }

    /**
     * Feature: iotrack-improvements, Property 7: Excel Export Column Completeness
     * *For any* visit record exported, the generated row should contain all required columns:
     * Date, Time, Visitor Name, NIM, Purpose, and Borrowing Details.
     * **Validates: Requirements 3.2**
     *
     * @dataProvider visitDataProvider
     */
    public function test_export_column_completeness(
        string $purpose,
        bool $hasBorrowings,
        int $borrowingCount
    ): void {
        // Setup: Buat mahasiswa dan item
        $student = Student::create([
            'nim' => '12345678',
            'name' => 'Test Student',
            'program_studi' => 'Teknik Informatika',
            'tahun_masuk' => 2020,
            'angkatan' => 2020,
        ]);

        $item = Item::create([
            'name' => 'Arduino Uno',
            'total_stock' => 10,
            'current_stock' => 10,
        ]);

        // Buat kunjungan hari ini
        $visit = Visit::create([
            'visitor_name' => $student->name,
            'visitor_id' => $student->nim,
            'purpose' => $purpose,
            'tapped_out_at' => null,
            'created_at' => now(),
        ]);

        // Buat peminjaman jika diperlukan
        if ($hasBorrowings) {
            for ($i = 0; $i < $borrowingCount; $i++) {
                Borrowing::create([
                    'visit_id' => $visit->id,
                    'item_id' => $item->id,
                    'quantity' => 1,
                    'status' => 'dipinjam',
                ]);
            }
        }

        // Act: Map visit ke row
        $row = $this->export->map($visit->fresh()->load('borrowings.item'));

        // Assert: Row harus memiliki 6 kolom
        $this->assertCount(6, $row);

        // Assert: Setiap kolom harus terisi (tidak null atau empty string)
        $this->assertNotEmpty($row[0], 'Tanggal harus terisi');
        $this->assertNotEmpty($row[1], 'Waktu harus terisi');
        $this->assertNotEmpty($row[2], 'Nama Pengunjung harus terisi');
        $this->assertNotEmpty($row[3], 'NIM harus terisi');
        $this->assertNotEmpty($row[4], 'Tujuan harus terisi');
        $this->assertNotEmpty($row[5], 'Detail Peminjaman harus terisi');

        // Assert: Format tanggal dan waktu benar
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $row[0], 'Format tanggal harus YYYY-MM-DD');
        $this->assertMatchesRegularExpression('/^\d{2}:\d{2}$/', $row[1], 'Format waktu harus HH:MM');
    }

    public static function visitDataProvider(): array
    {
        return [
            'belajar tanpa peminjaman' => ['belajar', false, 0],
            'pinjam dengan 1 peminjaman' => ['pinjam', true, 1],
            'pinjam dengan 3 peminjaman' => ['pinjam', true, 3],
        ];
    }

    /**
     * Feature: iotrack-improvements, Property 8: Excel Borrowing Details Formatting
     * *For any* visit in the export, the Borrowing Details column should display "-" when no borrowings exist,
     * and should display "Item Name (Qty: X)" format when borrowings exist.
     * **Validates: Requirements 3.3, 3.4**
     *
     * @dataProvider borrowingDetailsProvider
     */
    public function test_borrowing_details_formatting(
        bool $hasBorrowings,
        int $borrowingCount,
        string $expectedPattern
    ): void {
        // Setup: Buat mahasiswa dan item
        $student = Student::create([
            'nim' => '12345678',
            'name' => 'Test Student',
            'program_studi' => 'Teknik Informatika',
            'tahun_masuk' => 2020,
            'angkatan' => 2020,
        ]);

        $item = Item::create([
            'name' => 'Arduino Uno',
            'total_stock' => 10,
            'current_stock' => 10,
        ]);

        // Buat kunjungan hari ini
        $visit = Visit::create([
            'visitor_name' => $student->name,
            'visitor_id' => $student->nim,
            'purpose' => $hasBorrowings ? 'pinjam' : 'belajar',
            'tapped_out_at' => null,
            'created_at' => now(),
        ]);

        // Buat peminjaman jika diperlukan
        if ($hasBorrowings) {
            for ($i = 0; $i < $borrowingCount; $i++) {
                Borrowing::create([
                    'visit_id' => $visit->id,
                    'item_id' => $item->id,
                    'quantity' => $i + 1,
                    'status' => 'dipinjam',
                ]);
            }
        }

        // Act: Map visit ke row
        $row = $this->export->map($visit->fresh()->load('borrowings.item'));
        $borrowingDetails = $row[5];

        // Assert: Format sesuai ekspektasi
        $this->assertMatchesRegularExpression($expectedPattern, $borrowingDetails);
    }

    public static function borrowingDetailsProvider(): array
    {
        return [
            'tanpa peminjaman harus dash' => [false, 0, '/^-$/'],
            'dengan 1 peminjaman harus format Qty' => [true, 1, '/^.+ \(Qty: \d+\)$/'],
            'dengan multiple peminjaman harus dipisah koma' => [true, 2, '/^.+ \(Qty: \d+\), .+ \(Qty: \d+\)$/'],
        ];
    }

    /**
     * Unit test: Export dengan tidak ada peminjaman menampilkan dash.
     */
    public function test_export_with_no_borrowings_shows_dash(): void
    {
        // Setup
        $student = Student::create([
            'nim' => '12345678',
            'name' => 'Test Student',
            'program_studi' => 'Teknik Informatika',
            'tahun_masuk' => 2020,
            'angkatan' => 2020,
        ]);

        $visit = Visit::create([
            'visitor_name' => $student->name,
            'visitor_id' => $student->nim,
            'purpose' => 'belajar',
            'tapped_out_at' => null,
            'created_at' => now(),
        ]);

        // Act
        $row = $this->export->map($visit->fresh()->load('borrowings.item'));

        // Assert
        $this->assertEquals('-', $row[5]);
    }

    /**
     * Unit test: Export dengan peminjaman menampilkan nama item dan quantity.
     */
    public function test_export_with_borrowings_shows_item_names_and_quantities(): void
    {
        // Setup
        $student = Student::create([
            'nim' => '12345678',
            'name' => 'Test Student',
            'program_studi' => 'Teknik Informatika',
            'tahun_masuk' => 2020,
            'angkatan' => 2020,
        ]);

        $item = Item::create([
            'name' => 'Arduino Uno',
            'total_stock' => 10,
            'current_stock' => 10,
        ]);

        $visit = Visit::create([
            'visitor_name' => $student->name,
            'visitor_id' => $student->nim,
            'purpose' => 'pinjam',
            'tapped_out_at' => null,
            'created_at' => now(),
        ]);

        Borrowing::create([
            'visit_id' => $visit->id,
            'item_id' => $item->id,
            'quantity' => 3,
            'status' => 'dipinjam',
        ]);

        // Act
        $row = $this->export->map($visit->fresh()->load('borrowings.item'));

        // Assert
        $this->assertEquals('Arduino Uno (Qty: 3)', $row[5]);
    }

    /**
     * Unit test: Filename format dengan tanggal.
     */
    public function test_filename_format(): void
    {
        // Act
        $filename = $this->export->filename();

        // Assert
        $expectedFilename = 'visits_' . now()->format('Y-m-d') . '.csv';
        $this->assertEquals($expectedFilename, $filename);
        $this->assertMatchesRegularExpression('/^visits_\d{4}-\d{2}-\d{2}\.csv$/', $filename);
    }

    /**
     * Unit test: Headings dalam Bahasa Indonesia.
     */
    public function test_headings_in_bahasa_indonesia(): void
    {
        // Act
        $headings = $this->export->headings();

        // Assert
        $expectedHeadings = [
            'Tanggal',
            'Waktu',
            'Nama Pengunjung',
            'NIM',
            'Tujuan',
            'Detail Peminjaman',
        ];
        $this->assertEquals($expectedHeadings, $headings);
    }

    /**
     * Unit test: CSV output berisi header dan data.
     */
    public function test_csv_output_contains_header_and_data(): void
    {
        // Setup
        $student = Student::create([
            'nim' => '12345678',
            'name' => 'Test Student',
            'program_studi' => 'Teknik Informatika',
            'tahun_masuk' => 2020,
            'angkatan' => 2020,
        ]);

        Visit::create([
            'visitor_name' => $student->name,
            'visitor_id' => $student->nim,
            'purpose' => 'belajar',
            'tapped_out_at' => null,
            'created_at' => now(),
        ]);

        // Act
        $csv = $this->export->toCsv();

        // Assert: CSV berisi header
        $this->assertStringContainsString('Tanggal', $csv);
        $this->assertStringContainsString('Waktu', $csv);
        $this->assertStringContainsString('Nama Pengunjung', $csv);
        $this->assertStringContainsString('NIM', $csv);
        $this->assertStringContainsString('Tujuan', $csv);
        $this->assertStringContainsString('Detail Peminjaman', $csv);

        // Assert: CSV berisi data
        $this->assertStringContainsString('Test Student', $csv);
        $this->assertStringContainsString('12345678', $csv);
    }

    /**
     * Unit test: Collection hanya mengambil kunjungan hari ini.
     */
    public function test_collection_only_fetches_todays_visits(): void
    {
        // Setup
        $student = Student::create([
            'nim' => '12345678',
            'name' => 'Test Student',
            'program_studi' => 'Teknik Informatika',
            'tahun_masuk' => 2020,
            'angkatan' => 2020,
        ]);

        // Kunjungan hari ini
        $todayVisit = Visit::create([
            'visitor_name' => $student->name,
            'visitor_id' => $student->nim,
            'purpose' => 'belajar',
            'tapped_out_at' => null,
        ]);

        // Kunjungan kemarin (tidak boleh diambil) - update created_at langsung di database
        $yesterdayVisit = Visit::create([
            'visitor_name' => 'Yesterday Student',
            'visitor_id' => '87654321',
            'purpose' => 'belajar',
            'tapped_out_at' => null,
        ]);
        // Update created_at menggunakan query builder untuk bypass model timestamps
        Visit::where('id', $yesterdayVisit->id)->update(['created_at' => now()->subDay()]);

        // Act
        $collection = $this->export->collection();

        // Assert
        $this->assertCount(1, $collection);
        $this->assertEquals('Test Student', $collection->first()->visitor_name);
    }
}
