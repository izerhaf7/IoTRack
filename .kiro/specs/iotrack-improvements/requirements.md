# Requirements Document

## Introduction

IoTrack is a lab visitor and equipment borrowing tracker for an IoT laboratory. The system manages student check-ins (Tap In), check-outs (Tap Out), equipment borrowing, and administrative oversight. This document specifies requirements for improving the existing system with enhanced business logic, new features for data export, QR-based check-in, analytics dashboards, and improved UI/UX.

## Glossary

- **IoTrack System**: The web-based application for tracking lab visitors and equipment borrowing
- **Student**: A lab visitor identified by NIM (student ID number)
- **NIM**: Student identification number (Nomor Induk Mahasiswa)
- **Tap In**: The process where a student checks into the lab
- **Tap Out**: The process where a student checks out of the lab
- **Visit**: A record of a student's lab session from Tap In to Tap Out
- **Borrowing**: A record of equipment borrowed during a visit
- **Active Borrowing**: A borrowing record with status 'dipinjam' (borrowed) that has not been returned
- **Item**: A piece of equipment available for borrowing in the lab
- **Stock**: The quantity of an item available for borrowing
- **Admin User**: An authenticated user with administrative privileges
- **Admin Dashboard**: The protected interface for administrative functions
- **QR Code**: A machine-readable code containing the student's NIM
- **Excel Export**: The process of generating an XLSX file containing visit history data
- **UI Text**: User-facing interface text and messages displayed to students and administrators
- **Bahasa**: Indonesian language (Bahasa Indonesia)
- **Authentication Route**: The URL endpoint used for admin login functionality

## Requirements

### Requirement 1: Tap Out Access Control

**User Story:** As a lab administrator, I want to prevent students without active borrowings from tapping out, so that the system maintains accurate borrowing records and prevents misuse of the Tap Out feature.

#### Acceptance Criteria

1. WHEN a Student enters their NIM on the Tap Out page AND the Student has no Active Borrowings THEN the IoTrack System SHALL reject the Tap Out request
2. WHEN the Tap Out request is rejected due to no Active Borrowings THEN the IoTrack System SHALL display an error message stating "You have no active borrowings to return, Tap Out is not allowed"
3. WHEN a Student has at least one Active Borrowing THEN the IoTrack System SHALL allow the Tap Out process to proceed
4. WHEN the Tap Out request is rejected THEN the IoTrack System SHALL maintain the current state without modifying Visit records or Item stock levels

### Requirement 2: Single Tap Out Per Visit

**User Story:** As a lab administrator, I want each visit session to allow only one Tap Out operation, so that equipment stock is not incorrectly incremented multiple times and visit records remain accurate.

#### Acceptance Criteria

1. WHEN a Visit record is created during Tap In THEN the IoTrack System SHALL initialize the Visit with a null tapped_out_at timestamp
2. WHEN a Student successfully completes Tap Out for a Visit THEN the IoTrack System SHALL record the current timestamp in the tapped_out_at field
3. WHEN a Student attempts Tap Out AND the Visit already has a non-null tapped_out_at timestamp THEN the IoTrack System SHALL reject the Tap Out request
4. WHEN Tap Out is rejected due to duplicate attempt THEN the IoTrack System SHALL display an error message stating "This visit has already been checked out. Multiple Tap Out attempts are not allowed"
5. WHEN a valid Tap Out occurs THEN the IoTrack System SHALL return borrowed Item stock quantities and update Borrowing records within a single database transaction

### Requirement 3: Visit History Excel Export

**User Story:** As a lab administrator, I want to download daily visit history as an Excel file, so that I can maintain offline records and perform external analysis of lab usage.

#### Acceptance Criteria

1. WHEN an Admin User clicks the export button on the Admin Dashboard THEN the IoTrack System SHALL generate an XLSX file containing today's visit records
2. WHEN generating the Excel file THEN the IoTrack System SHALL include columns for Date, Time, Visitor Name, NIM, Purpose, and Borrowing Details
3. WHEN a Visit has no associated Borrowings THEN the IoTrack System SHALL display a dash character in the Borrowing Details column
4. WHEN a Visit has associated Borrowings THEN the IoTrack System SHALL display item names and quantities in the Borrowing Details column
5. WHEN the Excel file is generated THEN the IoTrack System SHALL trigger a browser download with filename format "visits_YYYY-MM-DD.xlsx"
6. WHEN generating the Excel file THEN the IoTrack System SHALL use a dedicated export class to separate concerns from the controller

### Requirement 4: QR Code Based Tap In

**User Story:** As a student, I want to scan a QR code containing my NIM to check in, so that I can enter the lab quickly without manually typing my student ID.

#### Acceptance Criteria

1. WHEN a Student accesses the Tap In page THEN the IoTrack System SHALL display both a manual NIM input field and a QR scan button
2. WHEN a Student clicks the QR scan button THEN the IoTrack System SHALL request camera access permission from the browser
3. WHEN camera access is granted THEN the IoTrack System SHALL display a camera preview for QR code scanning
4. WHEN a QR code containing a NIM is successfully scanned THEN the IoTrack System SHALL populate the NIM input field with the decoded value
5. WHEN the NIM field is populated via QR scan THEN the IoTrack System SHALL allow the Student to proceed with the normal Tap In form submission
6. WHEN camera access is denied THEN the IoTrack System SHALL display an informative message and allow manual NIM entry

### Requirement 5: Most Borrowed Items Analytics

**User Story:** As a lab administrator, I want to view a chart showing the most frequently borrowed items, so that I can understand equipment demand and make informed inventory decisions.

#### Acceptance Criteria

1. WHEN an Admin User views the Admin Dashboard THEN the IoTrack System SHALL display a bar chart showing the most frequently borrowed items
2. WHEN generating the chart data THEN the IoTrack System SHALL aggregate Borrowing records by Item for a configurable time period
3. WHEN displaying the chart THEN the IoTrack System SHALL show Item names on one axis and borrowing frequency count on the other axis
4. WHEN no Borrowing records exist for the time period THEN the IoTrack System SHALL display an empty chart with an informative message
5. WHEN the chart is rendered THEN the IoTrack System SHALL use a JavaScript charting library compatible with the existing Bootstrap UI

### Requirement 6: Daily Visitor Analytics

**User Story:** As a lab administrator, I want to view a time series chart of daily visitor counts, so that I can identify usage patterns and peak periods for the lab.

#### Acceptance Criteria

1. WHEN an Admin User views the Admin Dashboard THEN the IoTrack System SHALL display a line chart showing daily unique visitor counts
2. WHEN generating the chart data THEN the IoTrack System SHALL count unique Students per day based on Visit records for the last 7 days
3. WHEN displaying the chart THEN the IoTrack System SHALL show dates on the X-axis and visitor count on the Y-axis
4. WHEN a date has no Visit records THEN the IoTrack System SHALL display zero for that date
5. WHEN the chart is rendered THEN the IoTrack System SHALL use the same JavaScript charting library as other dashboard charts

### Requirement 7: Purpose Distribution Analytics

**User Story:** As a lab administrator, I want to view the proportion of study-only versus borrowing visits, so that I can understand how students are using the lab facilities.

#### Acceptance Criteria

1. WHEN an Admin User views the Admin Dashboard THEN the IoTrack System SHALL display a pie chart showing the distribution of visit purposes
2. WHEN generating the chart data THEN the IoTrack System SHALL count Visit records grouped by purpose field for a configurable time period
3. WHEN displaying the chart THEN the IoTrack System SHALL show purpose categories with their respective percentages
4. WHEN no Visit records exist for the time period THEN the IoTrack System SHALL display an empty chart with an informative message

### Requirement 8: Admin Dashboard Navigation

**User Story:** As a lab administrator, I want a left sidebar navigation menu, so that I can easily access different administrative sections without cluttering the main content area.

#### Acceptance Criteria

1. WHEN an Admin User accesses any admin page THEN the IoTrack System SHALL display a left-aligned sidebar with navigation menu items
2. WHEN the sidebar is displayed THEN the IoTrack System SHALL include menu items for Dashboard, Visits, Items, Borrowings, and Reports
3. WHEN an Admin User clicks a sidebar menu item THEN the IoTrack System SHALL navigate to the corresponding admin section
4. WHEN viewing on a mobile device THEN the IoTrack System SHALL collapse the sidebar and provide a toggle button for menu access
5. WHEN the sidebar is rendered THEN the IoTrack System SHALL highlight the currently active menu item

### Requirement 9: Dashboard Layout Organization

**User Story:** As a lab administrator, I want the dashboard to display metrics, charts, and tables in a clear hierarchical layout, so that I can quickly understand lab status and activity.

#### Acceptance Criteria

1. WHEN an Admin User views the Admin Dashboard THEN the IoTrack System SHALL display metric cards at the top showing active borrowings count and today's unique visitor count
2. WHEN the dashboard is rendered THEN the IoTrack System SHALL display analytics charts in a dedicated section below the metric cards
3. WHEN the dashboard is rendered THEN the IoTrack System SHALL display data tables for active borrowings and today's visits below the charts section
4. WHEN viewing on a mobile device THEN the IoTrack System SHALL stack dashboard sections vertically with appropriate spacing
5. WHEN the dashboard loads THEN the IoTrack System SHALL maintain consistent spacing, alignment, and visual hierarchy across all sections

### Requirement 10: User Interface Localization

**User Story:** As a student or lab administrator in Indonesia, I want all user interface text to be in Bahasa Indonesia, so that I can easily understand and use the system in my native language.

#### Acceptance Criteria

1. WHEN a Student accesses the Tap In page THEN the IoTrack System SHALL display all labels, buttons, and messages in Bahasa Indonesia
2. WHEN a Student accesses the Tap Out page THEN the IoTrack System SHALL display all labels, buttons, and messages in Bahasa Indonesia
3. WHEN an Admin User accesses the Admin Dashboard THEN the IoTrack System SHALL display all navigation items, headings, and interface elements in Bahasa Indonesia
4. WHEN the IoTrack System displays error messages or validation feedback THEN the IoTrack System SHALL present the messages in Bahasa Indonesia
5. WHEN code comments are written THEN the IoTrack System SHALL use Bahasa Indonesia for code comments to maintain consistency with the existing codebase

### Requirement 11: Authentication Route Best Practices

**User Story:** As a developer maintaining the system, I want admin authentication routes to follow Laravel conventions, so that the application structure is predictable and follows framework best practices.

#### Acceptance Criteria

1. WHEN implementing admin authentication THEN the IoTrack System SHALL use the route prefix "/admin/login" for the login page
2. WHEN implementing admin authentication THEN the IoTrack System SHALL use the route prefix "/admin/logout" for the logout action
3. WHEN an unauthenticated user attempts to access admin routes THEN the IoTrack System SHALL redirect to "/admin/login"
4. WHEN admin authentication routes are defined THEN the IoTrack System SHALL group them using Laravel route middleware and prefixes
5. WHEN the admin login form is submitted THEN the IoTrack System SHALL use Laravel's built-in authentication mechanisms rather than custom implementations

### Requirement 12: Code Quality and Architecture

**User Story:** As a developer maintaining the system, I want controllers to delegate business logic to service classes, so that the codebase remains maintainable and testable.

#### Acceptance Criteria

1. WHEN implementing new features THEN the IoTrack System SHALL use dedicated service classes for complex business logic
2. WHEN handling database operations that modify multiple tables THEN the IoTrack System SHALL use database transactions to ensure atomicity
3. WHEN concurrent users access shared resources THEN the IoTrack System SHALL use row-level locking to prevent race conditions
4. WHEN validating user input THEN the IoTrack System SHALL use Laravel Form Request classes or explicit validation rules
5. WHEN establishing relationships between models THEN the IoTrack System SHALL use Eloquent relationships rather than raw queries