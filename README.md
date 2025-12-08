# University Course Management System (UCMS)

## Step 2: Database Schema Setup

This is the second commit of the UCMS project. This step includes:

- Complete database schema with all tables
- Database setup script
- Updated README with database instructions

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
│   └── Auth.php        # Authentication class ⭐ NEW
├── public/
│   └── css/
│       └── style.css   # Basic styles
├── index.php           # Landing page
└── README.md          # This file
```

## Completed Steps

✅ **Step 1:** Project Initialization  
✅ **Step 2:** Database Schema Setup  
✅ **Step 3:** Authentication System
