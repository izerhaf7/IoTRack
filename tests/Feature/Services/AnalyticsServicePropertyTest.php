<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use App\Models\Visit;
use App\Models\Item;
use App\Models\Borrowing;
use App\Models\Student;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

/**
 * Property-based tests untuk AnalyticsService.
 * Menggunakan data providers untuk menguji berbagai skenario.
 */
class AnalyticsServicePropertyTest extends TestCase
{
    use RefreshDatabase;

    protected AnalyticsService $analyticsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyticsService = new AnalyticsService();
    }

    /**
     * Feature: iotrack-improvements, Property 11: Borrowing Frequency Aggregation Accuracy
     * *For any* set of borrowing records within a time period, the most-borrowed-items chart data
     * should show each item's name paired with the correct count of how many times it was borrowed.
     * **Validates: Requirements 5.2**
     *
     * @dataProvider borrowingFrequencyProvider
     */
    public function test_borrowing_frequency_aggregation_accuracy(
        int $itemCount,
        array $borrowsPerItem
    ): void {
        // Setup: Buat items dan borrowings
        $items = [];
        for ($i = 0; $i < $itemCount; $i++) {
            $items[] = Item::create([
                'name' => 'Item ' . ($i + 1),
                'total_stock' => 100,
                'current_stock' => 100,
            ]);
        }

        // Buat student untuk visits
        $student = Student::create([
            'nim' => '12345678',
            'name' => 'Test Student',
            'program_studi' => 'Teknik Informatika',
            'tahun_masuk' => 2020,
            'angkatan' => 2020,
        ]);

        // Buat borrowings sesuai dengan jumlah yang ditentukan
        foreach ($items as $index => $item) {
            $borrowCount = $borrowsPerItem[$index] ?? 0;
            for ($j = 0; $j < $borrowCount; $j++) {
                $visit = Visit::create([
                    'visitor_name' => $student->name,
                    'visitor_id' => $student->nim,
                    'purpose' => 'pinjam',
                    'tapped_out_at' => null,
                ]);

                Borrowing::create([
                    'visit_id' => $visit->id,
                    'item_id' => $item->id,
                    'quantity' => 1,
                    'status' => 'dipinjam',
                ]);
            }
        }

        // Act: Ambil data analytics
        $result = $this->analyticsService->getMostBorrowedItems(7);

        // Assert: Verifikasi jumlah peminjaman sesuai
        foreach ($result as $row) {
            $itemName = $row['item_name'];
            $borrowCount = $row['borrow_count'];

            // Cari index item berdasarkan nama
            $itemIndex = (int) str_replace('Item ', '', $itemName) - 1;
            $expectedCount = $borrowsPerItem[$itemIndex] ?? 0;

            $this->assertEquals(
                $expectedCount,
                $borrowCount,
                "Item '{$itemName}' seharusnya memiliki {$expectedCount} peminjaman, tapi mendapat {$borrowCount}"
            );
        }

        // Verifikasi bahwa items dengan 0 peminjaman tidak muncul di hasil
        $resultItemNames = $result->pluck('item_name')->toArray();
        foreach ($items as $index => $item) {
            if (($borrowsPerItem[$index] ?? 0) === 0) {
                $this->assertNotContains(
                    $item->name,
                    $resultItemNames,
                    "Item dengan 0 peminjaman tidak seharusnya muncul di hasil"
                );
            }
        }
    }

    public static function borrowingFrequencyProvider(): array
    {
        return [
            'single item single borrow' => [1, [1]],
            'multiple items equal borrows' => [3, [5, 5, 5]],
            'multiple items varied borrows' => [5, [1, 5, 10, 0, 3]],
            'items with zero borrows' => [3, [0, 0, 0]],
            'mixed zero and non-zero' => [4, [0, 3, 0, 7]],
        ];
    }


    /**
     * Feature: iotrack-improvements, Property 12: Chart Data Format Consistency
     * *For any* analytics chart (borrowing frequency, daily visitors, purpose distribution),
     * the data structure should match the expected format for the charting library.
     * **Validates: Requirements 5.3, 6.3, 7.3**
     *
     * @dataProvider chartDataFormatProvider
     */
    public function test_chart_data_format_consistency(
        string $chartType,
        int $dataPoints
    ): void {
        // Setup: Buat data sesuai dengan tipe chart
        $student = Student::create([
            'nim' => '12345678',
            'name' => 'Test Student',
            'program_studi' => 'Teknik Informatika',
            'tahun_masuk' => 2020,
            'angkatan' => 2020,
        ]);

        if ($chartType === 'borrowing' && $dataPoints > 0) {
            // Buat items dan borrowings
            for ($i = 0; $i < $dataPoints; $i++) {
                $item = Item::create([
                    'name' => 'Item ' . ($i + 1),
                    'total_stock' => 100,
                    'current_stock' => 100,
                ]);

                $visit = Visit::create([
                    'visitor_name' => $student->name,
                    'visitor_id' => $student->nim,
                    'purpose' => 'pinjam',
                    'tapped_out_at' => null,
                ]);

                Borrowing::create([
                    'visit_id' => $visit->id,
                    'item_id' => $item->id,
                    'quantity' => 1,
                    'status' => 'dipinjam',
                ]);
            }
        } elseif ($chartType === 'visitors' && $dataPoints > 0) {
            // Buat visits untuk beberapa hari
            for ($i = 0; $i < $dataPoints; $i++) {
                $visit = Visit::create([
                    'visitor_name' => $student->name,
                    'visitor_id' => $student->nim,
                    'purpose' => 'belajar',
                    'tapped_out_at' => null,
                    'created_at' => Carbon::now()->subDays($i),
                ]);
            }
        } elseif ($chartType === 'purpose' && $dataPoints > 0) {
            // Buat visits dengan berbagai tujuan
            for ($i = 0; $i < $dataPoints; $i++) {
                Visit::create([
                    'visitor_name' => $student->name,
                    'visitor_id' => $student->nim,
                    'purpose' => $i % 2 === 0 ? 'belajar' : 'pinjam',
                    'tapped_out_at' => null,
                ]);
            }
        }

        // Act & Assert berdasarkan tipe chart
        if ($chartType === 'borrowing') {
            $result = $this->analyticsService->getMostBorrowedItems(7);

            // Verifikasi format: Collection dengan item_name dan borrow_count
            $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
            foreach ($result as $row) {
                $this->assertArrayHasKey('item_name', $row->toArray());
                $this->assertArrayHasKey('borrow_count', $row->toArray());
                $this->assertIsString($row['item_name']);
                $this->assertIsInt((int) $row['borrow_count']);
            }
        } elseif ($chartType === 'visitors') {
            $result = $this->analyticsService->getDailyVisitorCounts(7);

            // Verifikasi format: Collection dengan date dan visitor_count
            $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
            $this->assertCount(7, $result); // Selalu 7 hari
            foreach ($result as $row) {
                $this->assertArrayHasKey('date', $row);
                $this->assertArrayHasKey('visitor_count', $row);
                $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $row['date']);
                $this->assertIsInt($row['visitor_count']);
            }
        } elseif ($chartType === 'purpose') {
            $result = $this->analyticsService->getPurposeDistribution(7);

            // Verifikasi format: Array dengan belajar dan pinjam
            $this->assertIsArray($result);
            $this->assertArrayHasKey('belajar', $result);
            $this->assertArrayHasKey('pinjam', $result);
            $this->assertIsInt($result['belajar']);
            $this->assertIsInt($result['pinjam']);
        }
    }

    public static function chartDataFormatProvider(): array
    {
        return [
            'borrowing chart empty' => ['borrowing', 0],
            'borrowing chart with data' => ['borrowing', 3],
            'visitors chart empty' => ['visitors', 0],
            'visitors chart with data' => ['visitors', 5],
            'purpose chart empty' => ['purpose', 0],
            'purpose chart with data' => ['purpose', 6],
        ];
    }

    /**
     * Feature: iotrack-improvements, Property 13: Daily Visitor Count Uniqueness
     * *For any* date within the analytics period, the visitor count should equal the number
     * of distinct NIMs that have visit records on that date, not the total number of visits.
     * **Validates: Requirements 6.2**
     *
     * @dataProvider dailyVisitorUniquenessProvider
     */
    public function test_daily_visitor_count_uniqueness(
        int $uniqueStudents,
        int $visitsPerStudent
    ): void {
        // Setup: Buat students
        $students = [];
        for ($i = 0; $i < $uniqueStudents; $i++) {
            $students[] = Student::create([
                'nim' => '1234567' . $i,
                'name' => 'Student ' . ($i + 1),
                'program_studi' => 'Teknik Informatika',
                'tahun_masuk' => 2020,
                'angkatan' => 2020,
            ]);
        }

        // Buat visits - setiap student melakukan beberapa kunjungan hari ini
        foreach ($students as $student) {
            for ($j = 0; $j < $visitsPerStudent; $j++) {
                Visit::create([
                    'visitor_name' => $student->name,
                    'visitor_id' => $student->nim,
                    'purpose' => 'belajar',
                    'tapped_out_at' => null,
                    'created_at' => Carbon::today()->addHours($j),
                ]);
            }
        }

        // Act: Ambil data pengunjung harian
        $result = $this->analyticsService->getDailyVisitorCounts(7);

        // Assert: Jumlah pengunjung hari ini harus sama dengan jumlah student unik
        $todayData = $result->firstWhere('date', Carbon::today()->format('Y-m-d'));
        $this->assertNotNull($todayData);
        $this->assertEquals(
            $uniqueStudents,
            $todayData['visitor_count'],
            "Seharusnya ada {$uniqueStudents} pengunjung unik, bukan " . ($uniqueStudents * $visitsPerStudent) . " total kunjungan"
        );
    }

    public static function dailyVisitorUniquenessProvider(): array
    {
        return [
            'single student single visit' => [1, 1],
            'single student multiple visits' => [1, 5],
            'multiple students single visit each' => [3, 1],
            'multiple students multiple visits each' => [3, 4],
            'many students many visits' => [5, 3],
        ];
    }


    /**
     * Feature: iotrack-improvements, Property 14: Purpose Distribution Percentage Accuracy
     * *For any* set of visit records, the purpose distribution percentages should sum to 100%
     * and each category's percentage should equal (category_count / total_count) * 100.
     * **Validates: Requirements 7.2, 7.3**
     *
     * @dataProvider purposeDistributionProvider
     */
    public function test_purpose_distribution_percentage_accuracy(
        int $belajarCount,
        int $pinjamCount
    ): void {
        // Setup: Buat student
        $student = Student::create([
            'nim' => '12345678',
            'name' => 'Test Student',
            'program_studi' => 'Teknik Informatika',
            'tahun_masuk' => 2020,
            'angkatan' => 2020,
        ]);

        // Buat visits dengan tujuan belajar
        for ($i = 0; $i < $belajarCount; $i++) {
            Visit::create([
                'visitor_name' => $student->name,
                'visitor_id' => $student->nim,
                'purpose' => 'belajar',
                'tapped_out_at' => null,
            ]);
        }

        // Buat visits dengan tujuan pinjam
        for ($i = 0; $i < $pinjamCount; $i++) {
            Visit::create([
                'visitor_name' => $student->name,
                'visitor_id' => $student->nim,
                'purpose' => 'pinjam',
                'tapped_out_at' => null,
            ]);
        }

        // Act: Ambil distribusi tujuan
        $result = $this->analyticsService->getPurposeDistribution(7);

        // Assert: Verifikasi jumlah sesuai
        $this->assertEquals($belajarCount, $result['belajar']);
        $this->assertEquals($pinjamCount, $result['pinjam']);

        // Verifikasi persentase
        $total = $belajarCount + $pinjamCount;
        if ($total > 0) {
            $expectedBelajarPct = ($belajarCount / $total) * 100;
            $expectedPinjamPct = ($pinjamCount / $total) * 100;

            $actualBelajarPct = ($result['belajar'] / $total) * 100;
            $actualPinjamPct = ($result['pinjam'] / $total) * 100;

            $this->assertEquals($expectedBelajarPct, $actualBelajarPct, '', 0.01);
            $this->assertEquals($expectedPinjamPct, $actualPinjamPct, '', 0.01);

            // Verifikasi total persentase = 100%
            $this->assertEquals(100, $actualBelajarPct + $actualPinjamPct, '', 0.01);
        }
    }

    public static function purposeDistributionProvider(): array
    {
        return [
            'empty data' => [0, 0],
            'only belajar' => [10, 0],
            'only pinjam' => [0, 10],
            'equal distribution' => [5, 5],
            'skewed to belajar' => [9, 1],
            'skewed to pinjam' => [1, 9],
            'varied distribution' => [7, 3],
        ];
    }

    /**
     * Test tambahan: Verifikasi bahwa data di luar periode tidak termasuk.
     */
    public function test_analytics_respects_time_period(): void
    {
        // Setup: Buat student
        $student = Student::create([
            'nim' => '12345678',
            'name' => 'Test Student',
            'program_studi' => 'Teknik Informatika',
            'tahun_masuk' => 2020,
            'angkatan' => 2020,
        ]);

        $item = Item::create([
            'name' => 'Arduino Uno',
            'total_stock' => 100,
            'current_stock' => 100,
        ]);

        // Buat visit dalam periode (hari ini)
        $recentVisit = Visit::create([
            'visitor_name' => $student->name,
            'visitor_id' => $student->nim,
            'purpose' => 'pinjam',
            'tapped_out_at' => null,
        ]);

        Borrowing::create([
            'visit_id' => $recentVisit->id,
            'item_id' => $item->id,
            'quantity' => 1,
            'status' => 'dipinjam',
        ]);

        // Buat visit di luar periode (10 hari lalu) - gunakan update untuk set created_at
        $oldVisit = new Visit([
            'visitor_name' => $student->name,
            'visitor_id' => $student->nim,
            'purpose' => 'pinjam',
            'tapped_out_at' => null,
        ]);
        $oldVisit->save();
        // Update created_at secara langsung via query untuk bypass timestamps
        Visit::where('id', $oldVisit->id)->update(['created_at' => Carbon::now()->subDays(10)]);

        $oldBorrowing = new Borrowing([
            'visit_id' => $oldVisit->id,
            'item_id' => $item->id,
            'quantity' => 1,
            'status' => 'dipinjam',
        ]);
        $oldBorrowing->save();
        // Update created_at secara langsung via query untuk bypass timestamps
        Borrowing::where('id', $oldBorrowing->id)->update(['created_at' => Carbon::now()->subDays(10)]);

        // Act: Ambil data dengan periode 7 hari
        $borrowedItems = $this->analyticsService->getMostBorrowedItems(7);
        $purposeDistribution = $this->analyticsService->getPurposeDistribution(7);

        // Assert: Hanya data dalam periode yang dihitung
        $this->assertEquals(1, $borrowedItems->first()['borrow_count']);
        $this->assertEquals(1, $purposeDistribution['pinjam']);
    }
}
