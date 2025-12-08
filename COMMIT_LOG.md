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

---

## Commit 3: Authentication System ✅

**Date:** Step 3  
**Description:** User authentication system with login, register, and session management

### Changes:
- Created `src/Auth.php` class with complete authentication functionality
- Added user registration method with validation
- Added user login method with password verification
- Added session management (login, logout, check status)
- Added password hashing for security
- Added email validation
- Added role validation (student/teacher)
- Added requireLogin() method for protected pages
- Added getUser() method to get current user data

### Files Created:
- `src/Auth.php` - Complete authentication class

### Commit Message:
```
feat: add authentication system with Auth class
```

---

## Commit 4: Login Page ✅

**Date:** Step 4  
**Description:** User login interface

### Changes:
- Created `views/auth/login.php` with login form
- Integrated with Auth class for authentication
- Added form validation and error handling
- Redirects logged-in users away from login page
- Added link to register page
- Modern responsive design

### Files Created:
- `views/auth/login.php` - Login page

### Commit Message:
```
feat: add login page
```

---

## Commit 5: Register Page ✅

**Date:** Step 5  
**Description:** User registration interface

### Changes:
- Created `views/auth/register.php` with registration form
- Added form fields: name, email, password, role selection
- Integrated with Auth class for user registration
- Added success/error message handling
- Shows success alert with redirect to login page
- Added link to login page for existing users

### Files Created:
- `views/auth/register.php` - User registration page

### Commit Message:
```
feat: add register page
```

---

## Commit 6: Dashboard Structure ✅

**Date:** Step 6  
**Description:** Basic dashboard structure with templates and navigation

### Changes:
- Created `templates/header.php` with navigation bar
- Created `templates/footer.php` with footer
- Added `logout.php` for user logout functionality
- Updated `index.php` to use templates
- Added navbar styles to CSS (logo, nav-links, user-badge)
- Added footer styles to CSS
- Created `views/dashboard/` folder structure
- Updated main-content styling

### Files Created:
- `templates/header.php` - Header template with navbar
- `templates/footer.php` - Footer template
- `logout.php` - Logout functionality

### Files Modified:
- `index.php` - Updated to use templates and Auth class
- `public/css/style.css` - Added navbar and footer styles

### Features:
- ✅ Navigation bar with user info
- ✅ User badge showing role
- ✅ Logout functionality
- ✅ Responsive navbar design
- ✅ Footer with copyright
- ✅ Template system for reusable components

### Commit Message:
```
feat: add dashboard structure with templates and logout
```

---

## Future Commits Preview:

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

