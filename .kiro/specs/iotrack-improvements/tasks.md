# Implementation Plan

- [x] 1. Database Schema Updates









  - Add `tapped_out_at` column to visits table via migration
  - Add indexes for performance optimization
  - _Requirements: 2.1, 2.2, 2.3_

- [x] 2. Create Service Layer Foundation

  - [x] 2.1 Create VisitService class with basic structure


    - Implement `tapIn()` method to handle visit creation and borrowing
    - Implement `tapOut()` method with validation logic
    - Implement `validateTapOut()` helper method
    - _Requirements: 1.1, 1.3, 1.4, 2.1, 2.2, 2.3, 2.5, 12.1_
  

  - [x] 2.2 Create BorrowingService class
    - Implement `createBorrowing()` method with stock management
    - Implement `returnBorrowing()` method with stock restoration
    - Implement `returnAllForVisit()` method for Tap Out process
    - _Requirements: 2.5, 12.2, 12.3_
  
  - [x] 2.3 Create AnalyticsService class
    - Implement `getMostBorrowedItems()` method
    - Implement `getDailyVisitorCounts()` method
    - Implement `getPurposeDistribution()` method
    - Implement `getTodayStats()` method
    - _Requirements: 5.2, 6.2, 7.2_


- [x] 2.4 Write property tests for service layer


  - **Property 1: Tap Out Validation Based on Active Borrowings**
  - **Validates: Requirements 1.1, 1.3**
  - **Property 2: State Preservation on Validation Failure**
  - **Validates: Requirements 1.4**
  - **Property 3: Visit Initialization State**
  - **Validates: Requirements 2.1**
  - **Property 4: Tap Out Timestamp Recording**
  - **Validates: Requirements 2.2**
  - **Property 5: Tap Out Idempotence**
  - **Validates: Requirements 2.3**
  - **Property 6: Tap Out Atomicity**
  - **Validates: Requirements 2.5**

- [ ] 3. Refactor Authentication to Follow Laravel Conventions
  - [x] 3.1 Create AdminAuthController in Auth namespace

    - Move login logic from AuthController
    - Update routes to use `/admin/login` and `/admin/logout`
    - Update middleware redirect path
    - _Requirements: 11.1, 11.2, 11.3_
  

  - [x] 3.2 Update authentication views








    - Move login view to `resources/views/admin/auth/login.blade.php`
    - Update form action URLs
    - Ensure all text is in Bahasa Indonesia
    - _Requirements: 10.3, 11.1_

  
  - [x] 3.3 Update route definitions




    - Group admin routes with proper prefix and middleware
    - Remove old AuthController routes
    - Test authentication flow
    - _Requirements: 11.1, 11.2, 11.3, 11.4_


- [x] 3.4 Write tests for authentication






  - **Property 15: Authentication Redirect Consistency**
  - **Validates: Requirements 11.3**


- [x] 4. Implement Enhanced Tap Out Logic





  - [x] 4.1 Update Visit model






    - Add `isTappedOut()` method
    - Add `hasActiveBorrowings()` method
    - Update fillable array to include `tapped_out_at`
    - _Requirements: 2.1, 2.2, 2.3_
  


  - [x] 4.2 Refactor VisitController to use VisitService





    - Update `tapOutProcess()` to use `VisitService::tapOut()`
    - Add validation for no active borrowings
    - Add validation for duplicate Tap Out
    - Update error messages in Bahasa Indonesia
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 2.3, 2.4, 2.5, 10.4_
  

  - [x] 4.3 Update Tap Out view with error handling






    - Display validation errors clearly
    - Ensure all text is in Bahasa Indonesia
    - _Requirements: 1.2, 2.4, 10.2_


- [x] 4.4 Write tests for Tap Out logic






  - Unit test for "no active borrowings" error message
  - Unit test for "already tapped out" error message
  - **Property 16: Transaction Atomicity Under Failure**
  - **Validates: Requirements 12.2**
  - **Property 17: Concurrent Stock Update Integrity**
  - **Validates: Requirements 12.3**

- [x] 5. Implement Excel/CSV Export Feature





  - [x] 5.1 Decide on export format and create export class


    - Create `VisitsExport` class (CSV or Excel based on user preference)
    - Implement `collection()` method to fetch today's visits
    - Implement `headings()` method with Bahasa Indonesia headers
    - Implement `map()` method to format rows with borrowing details
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6_
  
  - [x] 5.2 Add export route and controller method







    - Add `GET /admin/visits/export` route
    - Implement `AdminController::exportVisits()` method
    - Generate filename with current date
    - Return download response
    - _Requirements: 3.1, 3.5_
  


  - [x] 5.3 Add export button to admin dashboard





    - Add button in "Today's Visits" card header
    - Style with Bootstrap classes
    - Use Bahasa Indonesia text
    - _Requirements: 3.1, 10.3_


- [x] 5.4 Write tests for export feature






  - Unit test for export with no borrowings (dash character)
  - Unit test for export with borrowings (item names and quantities)
  - Unit test for filename format
  - **Property 7: Excel Export Column Completeness**
  - **Validates: Requirements 3.2**
  - **Property 8: Excel Borrowing Details Formatting**
  - **Validates: Requirements 3.3, 3.4**

- [x] 6. Checkpoint - Ensure all tests pass





  - Ensure all tests pass, ask the user if questions arise.


- [x] 7. Implement QR Code Scanning for Tap In











  - [x] 7.1 Add html5-qrcode library to Tap In view










    - Include library via CDN in `visit/form.blade.php`
    - Add QR scan button next to NIM input
    - Add camera preview container (hidden by default)
    - _Requirements: 4.1, 4.2_
  





  - [x] 7.2 Implement QR scanning JavaScript



    - Initialize QR scanner on button click
    - Request camera permission
    - Display camera preview
    - Handle successful scan (populate NIM field)
    - Handle errors (permission denied, decode failure)
    - Display error messages in Bahasa Indonesia
    - _Requirements: 4.2, 4.3, 4.4, 4.6, 10.1_
  




  - [x] 7.3 Test QR scanning flow




    - Verify NIM field is populated correctly
    - Verify form submission works with QR-populated NIM
    - Verify manual entry still works
    - Verify error handling for denied permissions
    - _Requirements: 4.4, 4.5_




- [x] 7.4 Write tests for QR functionality





  - Unit test for QR scan button presence
  - Unit test for camera permission request
  - **Property 9: QR Code to Form Field Mapping**
  - **Validates: Requirements 4.4**
  - **Property 10: QR and Manual Input Equivalence**
  - **Validates: Requirements 4.5**


- [x] 8. Implement Analytics Charts










  - [x] 8.1 Add Chart.js library to admin dashboard





    - Include Chart.js via CDN in admin layout
    - Create canvas elements for three charts
    - _Requirements: 5.1, 6.1, 7.1_
  

  - [x] 8.2 Implement most borrowed items chart







    - Add controller method to fetch chart data using AnalyticsService
    - Pass data to view
    - Initialize Chart.js bar chart
    - Configure chart with Bahasa Indonesia labels
    - Handle empty data case
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 10.3_

  

  - [x] 8.3 Implement daily visitors chart






    - Add controller method to fetch chart data using AnalyticsService
    - Pass data to view
    - Initialize Chart.js line chart
    - Configure chart with Bahasa Indonesia labels
    - Handle dates with no visits (show zero)

    - _Requirements: 6.1, 6.2, 6.3, 6.4, 10.3_
  

  - [x] 8.4 Implement purpose distribution chart






    - Add controller method to fetch chart data using AnalyticsService
    - Pass data to view
    - Initialize Chart.js pie chart
    - Configure chart with Bahasa Indonesia labels

    - Handle empty data case
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 10.3_


- [x] 8.5 Write tests for analytics






  - **Property 11: Borrowing Frequency Aggregation Accuracy**
  - **Validates: Requirements 5.2**
  - **Property 12: Chart Data Format Consistency**
  - **Validates: Requirements 5.3, 6.3, 7.3**
  - **Property 13: Daily Visitor Count Uniqueness**
  - **Validates: Requirements 6.2**
  - **Property 14: Purpose Distribution Percentage Accuracy**
  - **Validates: Requirements 7.2, 7.3**


- [x] 9. Implement Admin Dashboard UI Improvements






  - [x] 9.1 Create sidebar navigation component








    - Create `resources/views/admin/partials/sidebar.blade.php`
    - Add menu items: Dashboard, Visits, Items, Borrowings, Reports
    - Highlight active menu item
    - Make responsive (collapsible on mobile)
    - Use Bahasa Indonesia for all menu text
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5, 10.3_
  


  - [x] 9.2 Create admin layout with sidebar










    - Create `resources/views/admin/layouts/app.blade.php`
    - Include sidebar partial
    - Add main content area
    - Add mobile toggle button
    - Ensure consistent spacing and styling
    - _Requirements: 8.1, 8.4, 9.5_
  



  - [x] 9.3 Reorganize dashboard layout




    - Add metric cards at top (active borrowings, unique visitors)
    - Add charts section below metrics
    - Add tables section below charts
    - Ensure responsive stacking on mobile
    - Use Bahasa Indonesia for all labels
    - _Requirements: 9.1, 9.2, 9.3, 9.4, 10.3_
  

  - [x] 9.4 Update all admin views to use new layout






    - Update dashboard view
    - Update items views
    - Ensure consistent navigation across all pages
    - _Requirements: 8.1, 8.2_



- [x] 9.5 Write tests for UI components










  - Unit test for sidebar menu items presence
  - Unit test for active menu highlighting
  - Unit test for dashboard layout structure
  - Unit test for responsive behavior




- [x] 10. Localization Review











  - [x] 10.1 Review all user-facing text





    - Audit Tap In page for Bahasa Indonesia
    - Audit Tap Out page for Bahasa Indonesia
    - Audit admin dashboard for Bahasa Indonesia
    - Audit error messages for Bahasa Indonesia
    - _Requirements: 10.1, 10.2, 10.3, 10.4_

  




  - [x] 10.2 Update any English text to Bahasa Indonesia




    - Update button labels
    - Update form labels
    - Update validation messages
    - Update success/error messages
    - _Requirements: 10.1, 10.2, 10.3, 10.4_



- [x] 10.3 Write tests for localization







  - **Property 18: Error Message Localization**
  - **Validates: Requirements 10.4**

- [x] 11. Code Quality and Refactoring














  - [x] 11.1 Review and refactor VisitController















    - Move business logic to VisitService
    - Keep controller thin (HTTP concerns only)
    - Add proper validation using Form Requests
    - _Requirements: 12.1, 12.4_
  




  - [x] 11.2 Review and refactor AdminController







    - Move analytics logic to AnalyticsService
    - Keep controller thin
    - Ensure proper error handling
    - _Requirements: 12.1_
  




  - [x] 11.3 Review all database queries


    - Ensure Eloquent relationships are used
    - Add eager loading where needed
    - Add indexes for frequently queried columns
    - _Requirements: 12.5_
  


  - [x] 11.4 Add comprehensive code comments



    - Comment all service methods explaining purpose
    - Comment complex business logic
    - Use Bahasa Indonesia for code comments (consistent with existing codebase)
    - Ensure comments are accurate and helpful
    - _Requirements: 10.5_






- [x] 11.5 Write integration tests



  - Test complete Tap In → Tap Out flow
  - Test admin login → dashboard → operations flow
  - Test Excel export download flow
  - Test QR scan → form submit flow



- [x] 12. Final Checkpoint - Ensure all tests pass






  - Ensure all tests pass, ask the user if questions arise.


- [x] 13. Documentation and Cleanup



  - [x] 13.1 Update README with new features

    - Document QR scanning feature
    - Document Excel export feature
    - Document analytics charts
    - Document new Tap Out rules
  


  - [x] 13.2 Clean up unused code

    - Remove old AuthController if fully replaced
    - Remove any commented-out code
    - Remove unused imports
  


  - [x] 13.3 Final code review


    - Verify all requirements are met
    - Verify all UI text is in Bahasa Indonesia
    - Verify all code comments are in Bahasa Indonesia
    - Verify consistent code style