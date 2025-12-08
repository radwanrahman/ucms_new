# University Course Management System (UCMS)

## Steps Overview

- **Step 1:** Project initialization (structure, landing page, config)
- **Step 2:** Database schema (all tables)
- **Step 3:** Authentication system (Auth.php)
- **Step 4:** Login page (UI + Auth integration)

## Database Schema

The database includes the following tables:
- `users` - User accounts (students and teachers)
- `courses` - Course information
- `enrollments` - Student course enrollments
- `assignments` - Course assignments
- `submissions` - Assignment submissions
- `announcements` - Course announcements
- `attendance` - Attendance records

## Setup Instructions

1. Create database `ucms` in MySQL
2. Run the SQL script: `database.sql`
3. Update database credentials in `config/db.php`
4. Access the project at `http://localhost/ucms_new/`

## Project Structure

```
ucms_new/
├── config/
│   └── db.php          # Database configuration
├── database.sql        # Database schema
├── src/
│   └── Auth.php        # Authentication class
├── templates/
│   ├── header.php      # Header template
│   └── footer.php      # Footer template
├── public/
│   └── css/
│       └── style.css   # Basic styles
├── views/
│   ├── auth/
│   │   ├── login.php   # Login page
│   │   └── register.php # Register page
│   └── dashboard/      # Dashboard pages (to be added)
├── logout.php          # Logout functionality
├── index.php           # Landing page
└── README.md          # This file
```

## Completed Steps

✅ **Step 1:** Project Initialization  
✅ **Step 2:** Database Schema Setup  
✅ **Step 3:** Authentication System  
✅ **Step 4:** Login Page  
✅ **Step 5:** Register Page  
✅ **Step 6:** Dashboard Structure
