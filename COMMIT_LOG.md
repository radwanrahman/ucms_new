# Commit History - UCMS Project

## Commit 1: Project Initialization ✅

**Date:** Step 1  
**Description:** Initial project setup with basic structure

### Changes:
- Created project folder structure
- Added database configuration (`config/db.php`)
- Created landing page (`index.php`)
- Added basic CSS styling (`public/css/style.css`)
- Added README.md with project documentation

### Commit Message:
```
feat: initial project setup with landing page and database config
```

---

## Commit 2: Database Schema Setup ✅

**Date:** Step 2  
**Description:** Complete database schema with all required tables

### Changes:
- Created `database.sql` with complete schema
- Added all database tables:
  - users (students and teachers)
  - courses
  - enrollments
  - assignments
  - submissions
  - announcements
  - attendance
- Updated landing page with database connection test
- Added foreign key constraints
- Added unique constraints where needed

### Files Created:
- `database.sql` - Complete database schema

### Files Modified:
- `index.php` - Added database connection status indicator

### Commit Message:
```
feat: add database schema with all tables
```

### Database Tables:
1. **users** - User accounts with roles (student/teacher)
2. **courses** - Course information
3. **enrollments** - Student course enrollments
4. **assignments** - Course assignments
5. **submissions** - Assignment submissions with grades
6. **announcements** - Course announcements
7. **attendance** - Attendance tracking

---

## Future Commits Preview:

### Commit 3: Authentication System
- Auth class with login/register functionality

### Commit 4: Login Page
- User login interface

### Commit 5: Register Page
- User registration interface

### Commit 6: Dashboard Structure
- Basic dashboard layout

### Commit 7: Student Dashboard
- Student-specific dashboard

### Commit 8: Teacher Dashboard
- Teacher-specific dashboard

### Commit 9: Course Management
- Course creation and listing

### Commit 10: Course Enrollment
- Student course enrollment system

### Commit 11: Course View Page
- Individual course detail page

### Commit 12: Announcements
- Announcement posting and viewing

### Commit 13: Assignments System
- Assignment creation and management

### Commit 14: Assignment Submissions
- Student assignment submission

### Commit 15: Grading System
- Teacher grading functionality

### Commit 16: Attendance System
- Attendance tracking

### Commit 17: People/Users Page
- Course participants listing

### Commit 18: Profile Management
- User profile updates

### Commit 19: Notifications
- System notifications

### Commit 20: Final Polish
- Bug fixes, improvements, documentation

