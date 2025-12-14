# University Course Management System (UCMS)

A comprehensive Course Management System built with native PHP, MySQL, and modern CSS.

## Features

- **Authentication**: Secure Login & Registration (Student/Teacher roles)
- **Dashboard**: Role-based dashboards for Students and Teachers
- **Course Management**: Create, Join (via code), and Archive courses
- **Stream**: Real-time announcements and class discussions
- **Classwork**: Assignment creation, file submission, and grading
- **People**: View teachers and classmates
- **Attendance**: Track student attendance per course
- **Responsive Design**: Modern, clean UI that works on all devices

## Setup Instructions

1. **Database Setup**:
   - Create a MySQL database named `ucms`
   - Import the `database.sql` file provided in the root directory
   
2. **Configuration**:
   - Open `config/db.php` and update your database credentials if necessary

3. **Running the App**:
   - Run via XAMPP/Apache
   - Access at: `http://localhost/ucms_new/`

## Project Structure

```
ucms_new/
├── config/             # Database connection
├── public/             # CSS, JS, Uploads
├── src/                # Core Classes (Auth, Course, Assignment, etc.)
├── templates/          # Header, Footer, Layouts
├── views/              # Page Views
│   ├── auth/           # Login, Register
│   ├── course/         # Course Stream, Classwork, People, Attendance
│   └── dashboard/      # Student & Teacher Dashboards
├── index.php           # Landing Page
└── database.sql        # Database Schema
```

## Implementation Status

✅ **Step 1-3:** Project Setup, Database, Auth Class  
✅ **Step 4-5:** Login & Register Pages  
✅ **Step 6-8:** Dashboard & Role-based Access  
✅ **Step 9-11:** Course Creation, Enrollment, Stream View  
✅ **Step 12-15:** Announcements, Assignments, Submissions, Grading  
✅ **Step 16-17:** Attendance System  
✅ **Step 18-20:** People Page, Logout, Final Polish  

## Usage

- **Teacher**: Create courses, share the 6-character code with students, post announcements, create assignments, grade submissions, and take attendance.
- **Student**: Join courses using a code, view announcements, submit assignments (file upload), and view grades.
