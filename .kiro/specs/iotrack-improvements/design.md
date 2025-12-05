# Design Document

## Overview

IoTrack is a Laravel-based web application for managing lab visitor check-ins, equipment borrowing, and administrative oversight. This design document outlines improvements to enhance business logic, add new features (Excel export, QR scanning, analytics), improve UI/UX, and refactor code for better maintainability.

### Current Architecture Strengths

- Clean MVC separation with dedicated controllers for different concerns
- Proper use of Eloquent relationships between models
- Database transactions for atomic operations (Tap In with borrowing)
- Row-level locking (`lockForUpdate`) to prevent race conditions on stock updates
- Soft deletes on Items to preserve historical data integrity
- Eager loading to prevent N+1 queries in admin dashboard

### Current Architecture Weaknesses

- Fat controllers with business logic mixed with HTTP concerns
- Tap Out logic allows multiple checkouts for the same visit
- No validation preventing Tap Out without active borrowings
- Authentication routes don't follow Laravel conventions (`/login` instead of `/admin/login`)
- No service layer for complex business operations
- Limited error handling and validation feedback
- No analytics or reporting capabilities
- Basic UI without modern navigation patterns

## Architecture

### Layered Architecture Pattern

```
┌─────────────────────────────────────────┐
│         Presentation Layer              │
│  (Routes, Controllers, Blade Views)     │
└─────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────┐
│         Service Layer (NEW)             │
│  (Business Logic, Validation)           │
└─────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────┐
│         Data Access Layer               │
│  (Models, Eloquent Relationships)       │
└─────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────┐
│         Database Layer                  │
│  (MySQL/SQLite with Migrations)         │
└─────────────────────────────────────────┘
```

### Directory Structure Changes

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── VisitController.php (refactored)
│   │   ├── AdminController.php (refactored)
│   │   ├── ItemController.php
│   │   └── Auth/
│   │       └── AdminAuthController.php (NEW - replaces AuthController)
│   └── Requests/ (NEW)
│       ├── TapInRequest.php
│       └── TapOutRequest.php
├── Services/ (NEW)
│   ├── VisitService.php
│   ├── BorrowingService.php
│   └── AnalyticsService.php
├── Exports/ (NEW)
│   └── VisitsExport.php
└── Models/
    ├── Visit.php (enhanced)
    ├── Borrowing.php
    ├── Item.php
    └── Student.php
```

## Components and Interfaces

### 1. Enhanced Visit Model

**Responsibilities:**
- Represent a lab visit session
- Track Tap Out status to prevent duplicate checkouts
- Provide relationship to borrowings

**New Fields:**
- `tapped_out_at` (timestamp, nullable): Records when Tap Out was completed

**Methods:**
- `isTappedOut()`: Returns boolean indicating if visit has been checked out
- `hasActiveBorrowings()`: Returns boolean indicating if visit has unreturned items
- `borrowings()`: HasMany relationship to Borrowing model

### 2. VisitService (NEW)

**Responsibilities:**
- Encapsulate Tap In business logic
- Encapsulate Tap Out business logic with validation
- Coordinate between Visit and Borrowing operations

**Methods:**
```php
public function tapIn(array $data): Visit
// Validates student, creates visit, handles borrowing if needed
// Returns: Visit instance
// Throws: ValidationException if student not found or stock insufficient

public function tapOut(string $nim): Visit
// Validates active borrowings exist and visit not already tapped out
// Returns stock, marks visit as tapped out
// Returns: Visit instance
// Throws: ValidationException if no active borrowings or already tapped out

public function validateTapOut(string $nim): array
// Checks if tap out is allowed
// Returns: ['allowed' => bool, 'message' => string, 'visits' => Collection]
```

### 3. BorrowingService (NEW)

**Responsibilities:**
- Handle equipment borrowing logic
- Manage stock updates with proper locking
- Process returns with stock restoration

**Methods:**
```php
public function createBorrowing(Visit $visit, int $itemId, int $quantity): Borrowing
// Creates borrowing record and decrements stock atomically
// Returns: Borrowing instance
// Throws: ValidationException if insufficient stock

public function returnBorrowing(Borrowing $borrowing): void
// Marks borrowing as returned and restores stock
// Uses row-level locking to prevent race conditions

public function returnAllForVisit(Visit $visit): void
// Returns all active borrowings for a visit
// Called during Tap Out process
```

### 4. AnalyticsService (NEW)

**Responsibilities:**
- Aggregate data for dashboard charts
- Calculate visitor statistics
- Generate borrowing frequency reports

**Methods:**
```php
public function getMostBorrowedItems(int $days = 7): Collection
// Returns items with borrowing count, ordered by frequency
// Format: [['item_name' => string, 'borrow_count' => int], ...]

public function getDailyVisitorCounts(int $days = 7): Collection
// Returns unique visitor count per day
// Format: [['date' => string, 'visitor_count' => int], ...]

public function getPurposeDistribution(int $days = 7): array
// Returns percentage breakdown of visit purposes
// Format: ['belajar' => int, 'pinjam' => int]

public function getTodayStats(): array
// Returns today's metrics for dashboard cards
// Format: ['unique_visitors' => int, 'active_borrowings' => int]
```

### 5. VisitsExport (NEW)

**Responsibilities:**
- Generate Excel file from visit data
- Format borrowing details for readability
- Use PhpSpreadsheet for XLSX generation

**Library Choice:** To be confirmed with user
- **Option 1:** maatwebsite/excel (Laravel wrapper for PhpSpreadsheet)
  - Pros: Laravel-friendly API, widely used, good documentation
  - Cons: Additional dependency
- **Option 2:** PhpSpreadsheet directly
  - Pros: More control, no wrapper overhead
  - Cons: More verbose code, steeper learning curve
- **Option 3:** Simple CSV export (no additional library)
  - Pros: No dependencies, built into PHP
  - Cons: No formatting, Excel may not open correctly without proper headers

**Recommendation:** Start with Option 3 (CSV) for simplicity, can upgrade to Option 1 if Excel formatting is needed

**Methods:**
```php
public function collection(): Collection
// Returns today's visits with relationships loaded

public function headings(): array
// Returns Excel column headers in Bahasa

public function map($visit): array
// Formats each visit row with borrowing details
```

### 6. AdminAuthController (NEW)

**Responsibilities:**
- Handle admin authentication following Laravel conventions
- Replace current AuthController with proper route structure

**Routes:**
- `GET /admin/login` - Show login form
- `POST /admin/login` - Process login
- `POST /admin/logout` - Process logout

### 7. Frontend Stack (Current)

**CSS Framework:** Bootstrap 5.3.0 (via CDN)
- Already in use throughout the application
- No changes to CSS framework needed
- Consistent with existing UI patterns

**Icons:** Bootstrap Icons 1.11.0 (via CDN)
- Already in use for UI icons
- Continue using for consistency

**JavaScript:** Vanilla JavaScript
- No jQuery or heavy frameworks
- Keep it simple with native DOM manipulation
- Use Bootstrap's built-in JS components

**Charts Library:** Chart.js (to be added via CDN)
- Lightweight and simple to use
- Works well with Bootstrap
- No build step required
- **Confirmation needed:** Is adding Chart.js via CDN acceptable?

**QR Scanner Library:** html5-qrcode (to be added via CDN)
- Lightweight QR code scanner
- Works in browser without dependencies
- **Confirmation needed:** Is adding html5-qrcode via CDN acceptable?

## Data Models

### Enhanced Visits Table Schema

```sql
CREATE TABLE visits (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    visitor_name VARCHAR(255) NOT NULL,
    visitor_id VARCHAR(255) NOT NULL,
    purpose ENUM('belajar', 'pinjam') NOT NULL,
    tapped_out_at TIMESTAMP NULL,  -- NEW: tracks when tap out occurred
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_visitor_id (visitor_id),
    INDEX idx_created_at (created_at),
    INDEX idx_tapped_out (tapped_out_at)
);
```

### Existing Tables (No Changes)

**Items Table:**
- `id`, `name`, `description`, `image`, `total_stock`, `current_stock`
- `created_at`, `updated_at`, `deleted_at` (soft deletes)

**Borrowings Table:**
- `id`, `visit_id`, `item_id`, `quantity`, `status`, `returned_at`
- `created_at`, `updated_at`
- Foreign keys: `visit_id` → `visits.id`, `item_id` → `items.id`

**Students Table:**
- `id`, `nim`, `name`, `program_studi`, `tahun_masuk`, `angkatan`
- `created_at`, `updated_at`

### Model Relationships

```
Student (1) ──────────── (*) Visit
                            │
                            │ (1)
                            │
                            │ (*)
                         Borrowing
                            │
                            │ (*)
                            │
                            │ (1)
                          Item
```

## Correctness Properties


*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Tap Out Validation Based on Active Borrowings

*For any* student NIM, when attempting Tap Out, the system should reject the request if and only if the student has no active borrowings (status = 'dipinjam') associated with their visits.

**Validates: Requirements 1.1, 1.3**

**Rationale:** This property ensures that Tap Out access control is consistently enforced. By testing across all possible student states (with and without active borrowings), we verify that the validation logic correctly distinguishes between valid and invalid Tap Out attempts.

### Property 2: State Preservation on Validation Failure

*For any* rejected Tap Out request, the system state (visit records, borrowing records, item stock levels) should remain identical to the state before the request was made.

**Validates: Requirements 1.4**

**Rationale:** This is an invariant property ensuring that failed operations don't corrupt data. When validation fails, no side effects should occur. This prevents partial updates and maintains data integrity.

### Property 3: Visit Initialization State

*For any* newly created visit during Tap In, the `tapped_out_at` field should be initialized as null.

**Validates: Requirements 2.1**

**Rationale:** This property ensures all visits start in the correct initial state, ready for eventual Tap Out. Testing this across all visit creations (with and without borrowing) verifies consistent initialization.

### Property 4: Tap Out Timestamp Recording

*For any* successful Tap Out operation, the system should record a non-null timestamp in the `tapped_out_at` field of the associated visit record.

**Validates: Requirements 2.2**

**Rationale:** This property verifies that Tap Out completion is properly tracked. The timestamp serves as both an audit trail and a flag for preventing duplicate Tap Outs.

### Property 5: Tap Out Idempotence

*For any* visit that already has a non-null `tapped_out_at` timestamp, attempting Tap Out again should be rejected regardless of other conditions.

**Validates: Requirements 2.3**

**Rationale:** This is an idempotence property - performing Tap Out twice should not be allowed. This prevents double-returning stock and maintains accurate visit history. Testing across all tapped-out visits ensures the check is reliable.

### Property 6: Tap Out Atomicity

*For any* valid Tap Out operation, all associated changes (stock restoration, borrowing status updates, visit timestamp recording) should either all succeed together or all fail together.

**Validates: Requirements 2.5**

**Rationale:** This property tests transaction atomicity. In the event of any failure during Tap Out (database error, constraint violation), no partial changes should persist. This is critical for data consistency.

### Property 7: Excel Export Column Completeness

*For any* visit record exported to Excel, the generated row should contain all required columns: Date, Time, Visitor Name, NIM, Purpose, and Borrowing Details.

**Validates: Requirements 3.2**

**Rationale:** This property ensures export format consistency. By testing across all visit types (with/without borrowings, different purposes), we verify that no data is omitted from the export.

### Property 8: Excel Borrowing Details Formatting

*For any* visit in the Excel export, the Borrowing Details column should display "-" when no borrowings exist, and should display "Item Name (Qty: X)" format when borrowings exist.

**Validates: Requirements 3.3, 3.4**

**Rationale:** This property ensures consistent formatting of borrowing information. Testing across visits with 0, 1, and multiple borrowings verifies that the formatting logic handles all cases correctly.

### Property 9: QR Code to Form Field Mapping

*For any* valid QR code payload containing a NIM, scanning should populate the NIM input field with the exact decoded value.

**Validates: Requirements 4.4**

**Rationale:** This is a round-trip property for QR decoding. The scanned value should match the encoded value exactly, with no truncation or corruption. Testing with various NIM formats ensures robust decoding.

### Property 10: QR and Manual Input Equivalence

*For any* NIM value, submitting the Tap In form should produce identical results whether the NIM was entered manually or via QR scan.

**Validates: Requirements 4.5**

**Rationale:** This property ensures that QR scanning is truly just an input method, not a different code path. The same validation and processing should occur regardless of how the NIM was provided.

### Property 11: Borrowing Frequency Aggregation Accuracy

*For any* set of borrowing records within a time period, the most-borrowed-items chart data should show each item's name paired with the correct count of how many times it was borrowed.

**Validates: Requirements 5.2**

**Rationale:** This property verifies the aggregation logic for analytics. By testing with random borrowing data, we ensure the counting and grouping logic is correct and handles edge cases like items with zero borrows or ties in frequency.

### Property 12: Chart Data Format Consistency

*For any* analytics chart (borrowing frequency, daily visitors, purpose distribution), the data structure should match the expected format for the charting library (labels array and values array).

**Validates: Requirements 5.3, 6.3, 7.3**

**Rationale:** This property ensures that analytics data is always in the correct format for rendering. Testing across different data scenarios (empty, single item, multiple items) verifies that the formatting logic doesn't break with edge cases.

### Property 13: Daily Visitor Count Uniqueness

*For any* date within the analytics period, the visitor count should equal the number of distinct NIMs that have visit records on that date, not the total number of visits.

**Validates: Requirements 6.2**

**Rationale:** This property ensures we're counting unique visitors, not visits. A student who taps in multiple times per day should only count once. Testing with various visit patterns verifies the DISTINCT logic works correctly.

### Property 14: Purpose Distribution Percentage Accuracy

*For any* set of visit records, the purpose distribution percentages should sum to 100% and each category's percentage should equal (category_count / total_count) * 100.

**Validates: Requirements 7.2, 7.3**

**Rationale:** This property verifies the percentage calculation math. Testing with various distributions (50/50, 90/10, 100/0) ensures the calculation handles all cases including division by zero.

### Property 15: Authentication Redirect Consistency

*For any* protected admin route, accessing it without authentication should redirect to "/admin/login" regardless of which specific admin page was requested.

**Validates: Requirements 11.3**

**Rationale:** This property ensures authentication middleware is consistently applied. Testing across all admin routes verifies that no protected endpoints are accidentally exposed.

### Property 16: Transaction Atomicity Under Failure

*For any* multi-table database operation (Tap In with borrowing, Tap Out with returns), if any part of the operation fails, no changes should be committed to any table.

**Validates: Requirements 12.2**

**Rationale:** This property tests that database transactions properly roll back on failure. By simulating failures at different points in the operation, we verify that atomicity is maintained and no orphaned records are created.

### Property 17: Concurrent Stock Update Integrity

*For any* two concurrent borrowing operations on the same item, the final stock level should equal the initial stock minus the sum of both quantities, with no lost updates.

**Validates: Requirements 12.3**

**Rationale:** This property tests race condition prevention. Without proper locking, concurrent updates can result in lost decrements. Testing with simulated concurrent access verifies that row-level locking prevents this.

### Property 18: Error Message Localization

*For any* validation error or system error displayed to users, the message text should be in Bahasa Indonesia.

**Validates: Requirements 10.4**

**Rationale:** This property ensures consistent localization of error messages. Testing across all error conditions verifies that no English error messages leak through to the user interface.

## Error Handling

### Validation Errors

**Tap In Validation:**
- Student NIM not found in database → "NIM tidak terdaftar di data mahasiswa"
- Insufficient stock for borrowing → "Stok barang [nama] hanya tersisa [jumlah] unit"
- Invalid quantity (negative, zero, non-integer) → "Jumlah peminjaman harus berupa angka positif"

**Tap Out Validation:**
- No active borrowings → "Anda tidak memiliki peminjaman aktif untuk dikembalikan, Tap Out tidak diizinkan"
- Visit already tapped out → "Kunjungan ini sudah di-checkout. Tap Out berulang tidak diizinkan"
- NIM not found in visits → "Data kunjungan tidak ditemukan untuk NIM tersebut"

**Admin Operations:**
- Cannot delete visit with active borrowings → "Tidak dapat menghapus riwayat kunjungan yang masih memiliki peminjaman aktif"
- Item not found → "Barang tidak ditemukan"
- Unauthorized access → Redirect to "/admin/login"

### Database Errors

**Transaction Rollback:**
- All multi-table operations wrapped in `DB::transaction()`
- On any exception, entire transaction rolls back
- User sees generic error: "Terjadi kesalahan sistem. Silakan coba lagi"
- Detailed error logged for debugging

**Concurrency Conflicts:**
- Use `lockForUpdate()` on items during stock changes
- If lock timeout occurs, retry once before failing
- User sees: "Sistem sedang sibuk. Silakan coba lagi"

**Foreign Key Violations:**
- Prevented by application logic (check before delete)
- If occurs, log error and show: "Operasi tidak dapat dilakukan karena data terkait dengan record lain"

### External Service Errors

**QR Scanner:**
- Camera permission denied → Show message: "Akses kamera ditolak. Silakan masukkan NIM secara manual"
- QR decode fails → Show message: "QR code tidak valid. Silakan coba lagi atau masukkan NIM secara manual"
- Camera not available → Hide QR button, show only manual input

**Excel Export:**
- No data to export → Generate empty file with headers only
- File generation fails → Show message: "Gagal membuat file Excel. Silakan coba lagi"
- Memory limit exceeded → Implement chunking or pagination for large datasets

## Testing Strategy

### Unit Testing Approach

**Purpose:** Verify specific examples, integration points, and edge cases

**Framework:** PHPUnit (Laravel's default testing framework)

**Coverage Areas:**
- Model methods (`isTappedOut()`, `hasActiveBorrowings()`)
- Service class methods with specific inputs
- Controller responses for known scenarios
- Validation rules with edge case inputs
- Excel export formatting with sample data

**Example Unit Tests:**
```php
// Test that new visits have null tapped_out_at
public function test_new_visit_has_null_tapped_out_at()

// Test error message for duplicate tap out
public function test_duplicate_tap_out_shows_correct_error()

// Test Excel export with no borrowings shows dash
public function test_excel_export_formats_empty_borrowings_as_dash()

// Test analytics with empty data returns empty array
public function test_analytics_handles_no_data_gracefully()
```

### Property-Based Testing Approach

**Purpose:** Verify universal properties across all valid inputs

**Framework:** We will use **PHPUnit with data providers** for property-based testing
- **Rationale:** PHPUnit is already included in Laravel by default (no new dependencies)
- Data providers allow testing with multiple input variations
- Familiar syntax consistent with existing Laravel testing patterns
- No additional learning curve or setup required

**Configuration:** Each property test will use data providers with multiple test cases covering edge cases and random scenarios

**Test Tagging:** Each property-based test will include a comment with this format:
```php
// Feature: iotrack-improvements, Property 1: Tap Out Validation Based on Active Borrowings
```

**Coverage Areas:**
- Tap Out validation across all student/visit states
- State preservation on validation failures
- Transaction atomicity with simulated failures
- Concurrent access to shared resources
- Data aggregation accuracy with varied datasets
- Formatting consistency across all data types

**Example Property Tests:**
```php
// Feature: iotrack-improvements, Property 1: Tap Out Validation Based on Active Borrowings
/**
 * @dataProvider tapOutValidationProvider
 */
public function test_tap_out_validation_based_on_active_borrowings($hasBorrowings, $shouldAllow)
{
    // Setup: create visit with or without active borrowings
    // Act: attempt tap out
    // Assert: rejection matches expected behavior
}

public function tapOutValidationProvider()
{
    return [
        'no borrowings should reject' => [false, false],
        'with active borrowings should allow' => [true, true],
        'with returned borrowings should reject' => ['returned', false],
    ];
}

// Feature: iotrack-improvements, Property 11: Borrowing Frequency Aggregation Accuracy
/**
 * @dataProvider borrowingFrequencyProvider
 */
public function test_borrowing_frequency_aggregation_accuracy($itemCount, $borrowsPerItem)
{
    // Setup: create borrowing records
    // Act: get analytics data
    // Assert: counts match expected values
}

public function borrowingFrequencyProvider()
{
    return [
        'single item single borrow' => [1, 1],
        'multiple items equal borrows' => [3, 5],
        'many items varied borrows' => [10, [1, 5, 10, 0, 3, 7, 2, 8, 4, 6]],
    ];
}
```

### Integration Testing

**Purpose:** Verify end-to-end flows work correctly

**Coverage Areas:**
- Complete Tap In → Tap Out flow
- Admin login → dashboard → operations flow
- Excel export download flow
- QR scan → form submit flow

**Example Integration Tests:**
```php
// Test complete borrowing and return flow
public function test_student_can_borrow_and_return_item()

// Test admin can export visits
public function test_admin_can_download_excel_export()

// Test authentication protects admin routes
public function test_unauthenticated_user_redirected_to_login()
```

### Testing Best Practices

1. **Test Database:** Use SQLite in-memory database for fast test execution
2. **Database Transactions:** Wrap each test in transaction that rolls back
3. **Factories:** Use Laravel factories to generate test data
4. **Mocking:** Minimize mocking; test real implementations when possible
5. **Assertions:** Use specific assertions (`assertDatabaseHas`, `assertRedirect`) over generic ones
6. **Test Isolation:** Each test should be independent and not rely on other tests
7. **Descriptive Names:** Test names should clearly describe what is being tested

## Implementation Phases

### Phase 1: Foundation Refactoring
- Create service classes (VisitService, BorrowingService)
- Move business logic from controllers to services
- Add Form Request classes for validation
- Refactor authentication routes to follow Laravel conventions

### Phase 2: Core Business Logic Improvements
- Add `tapped_out_at` column to visits table
- Implement Tap Out validation (no active borrowings check)
- Implement single Tap Out per visit enforcement
- Update Tap Out process to use new validation

### Phase 3: Excel Export Feature
- Install maatwebsite/excel package
- Create VisitsExport class
- Add export route and controller method
- Add export button to admin dashboard

### Phase 4: QR Code Feature
- Add QR scanner library (html5-qrcode) to frontend
- Update Tap In view with QR scan button
- Implement camera access and QR decode logic
- Test QR → form population flow

### Phase 5: Analytics Dashboard
- Create AnalyticsService with aggregation methods
- Add Chart.js library to frontend
- Implement most-borrowed-items chart
- Implement daily-visitors chart
- Implement purpose-distribution chart
- Add charts to admin dashboard

### Phase 6: UI/UX Improvements
- Create sidebar navigation component
- Reorganize admin dashboard layout
- Add metric cards at top of dashboard
- Implement responsive design for mobile
- Ensure all UI text is in Bahasa Indonesia

### Phase 7: Testing
- Write unit tests for all service methods
- Write property-based tests for all correctness properties
- Write integration tests for end-to-end flows
- Achieve minimum 80% code coverage

## Security Considerations

### Authentication & Authorization
- Admin routes protected by `auth` middleware
- Session-based authentication using Laravel's built-in system
- CSRF protection on all POST/PUT/DELETE routes
- Password hashing using bcrypt (Laravel default)

### Input Validation
- All user inputs validated using Form Requests or validation rules
- SQL injection prevented by Eloquent ORM parameterized queries
- XSS prevention through Blade's automatic escaping
- File upload validation (if images are uploaded for items)

### Data Integrity
- Foreign key constraints in database
- Database transactions for multi-table operations
- Row-level locking for concurrent access to shared resources
- Soft deletes on items to preserve referential integrity

### Privacy
- Student data (NIM, name) stored securely
- No sensitive data logged in plain text
- Admin access logs for audit trail
- Excel exports only accessible to authenticated admins

## Performance Considerations

### Database Optimization
- Indexes on frequently queried columns (`visitor_id`, `created_at`, `status`)
- Eager loading to prevent N+1 queries (`with(['borrowings.item'])`)
- Query result caching for analytics (cache for 5 minutes)
- Pagination for large result sets (visit history, borrowing history)

### Frontend Optimization
- Chart.js loaded only on admin dashboard (not on public pages)
- QR scanner library loaded only on Tap In page
- Lazy loading for images in item list
- Minified and bundled assets (Vite)

### Scalability Considerations
- Excel export chunking for large datasets (>1000 records)
- Background job queue for heavy operations (if needed in future)
- Database connection pooling (Laravel default)
- Session storage in database or Redis (for multiple servers)

## Deployment Considerations

### Environment Configuration
- Separate `.env` files for development, staging, production
- Database credentials stored in environment variables
- Debug mode disabled in production
- Error logging to file or external service (e.g., Sentry)

### Database Migrations
- All schema changes via migrations (version controlled)
- Rollback plan for each migration
- Seed data for students table (CSV import)
- Admin user seeded in production

### Asset Compilation
- Run `npm run build` before deployment
- Versioned assets for cache busting
- CDN for static assets (optional)

### Monitoring
- Application logs monitored for errors
- Database query performance monitoring
- Disk space monitoring (for uploaded images, logs)
- Uptime monitoring for public endpoints

## Future Enhancements

### Potential Features (Out of Scope for Current Design)
- Email notifications for overdue returns
- Equipment reservation system
- Student dashboard to view borrowing history
- Mobile app for Tap In/Out
- Integration with university student database API
- Multi-lab support for different IoT lab locations
- Equipment maintenance tracking
- Barcode scanning for items (in addition to QR for students)
- Real-time dashboard updates using WebSockets
- Advanced analytics (peak hours, popular items by program_studi)

### Technical Debt to Address
- Add comprehensive API documentation
- Implement automated backup system
- Add end-to-end testing with browser automation
- Implement rate limiting for public endpoints
- Add multi-language support (currently Bahasa only)
- Migrate to TypeScript for frontend code
- Implement proper logging strategy with log levels
- Add health check endpoint for monitoring