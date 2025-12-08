# UCMS Project Structure - Following Original UCMS

This document shows how `ucms_new` follows the original `UCMS` project structure.

## Original UCMS Structure:
```
UCMS/
├── config/
│   └── db.php
├── src/
│   ├── Auth.php
│   ├── Course.php
│   ├── Assignment.php
│   ├── Announcement.php
│   └── Attendance.php
├── views/
│   ├── auth/
│   │   ├── login.php
│   │   └── register.php
│   ├── dashboard/
│   │   ├── student.php
│   │   └── teacher.php
│   └── course/
│       ├── view.php
│       ├── assignments.php
│       ├── assignment_details.php
│       ├── attendance.php
│       └── people.php
├── templates/
│   ├── header.php
│   └── footer.php
├── public/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   └── uploads/
├── database.sql
├── index.php
├── logout.php
└── README.md
```

## ucms_new Structure (Step-by-Step):
```
ucms_new/
├── config/
│   └── db.php          ✅ Step 1
├── src/
│   └── Auth.php        ✅ Step 3 (matches UCMS exactly)
├── database.sql        ✅ Step 2
├── public/
│   └── css/
│       └── style.css   ✅ Step 1
├── index.php           ✅ Step 1
└── README.md           ✅ Step 1
```

## Implementation Plan (Following UCMS):

### ✅ Completed:
- **Step 1:** Project structure + landing page
- **Step 2:** Database schema (matches UCMS exactly)
- **Step 3:** Auth.php class (matches UCMS exactly)

### 📋 Next Steps (Following UCMS Order):
- **Step 4:** Login page (`views/auth/login.php`)
- **Step 5:** Register page (`views/auth/register.php`)
- **Step 6:** Header/Footer templates (`templates/header.php`, `templates/footer.php`)
- **Step 7:** Student dashboard (`views/dashboard/student.php`)
- **Step 8:** Teacher dashboard (`views/dashboard/teacher.php`)
- **Step 9:** Course class (`src/Course.php`)
- **Step 10:** Course enrollment functionality
- **Step 11:** Course view page (`views/course/view.php`)
- **Step 12:** Announcement class (`src/Announcement.php`)
- **Step 13:** Assignment class (`src/Assignment.php`)
- **Step 14:** Assignments page (`views/course/assignments.php`)
- **Step 15:** Assignment details page
- **Step 16:** Attendance class (`src/Attendance.php`)
- **Step 17:** Attendance page (`views/course/attendance.php`)
- **Step 18:** People page (`views/course/people.php`)
- **Step 19:** Logout functionality (`logout.php`)
- **Step 20:** Final polish and improvements

## Key Differences:
- **Paths:** `/UCMS/` → `/ucms_new/` (updated for new project)
- **Structure:** Same folder structure
- **Code:** Same logic and functionality
- **Design:** Same CSS and styling system

## Verification:
✅ Auth.php matches UCMS structure exactly
✅ Database schema matches UCMS exactly
✅ Folder structure follows UCMS pattern
✅ All paths updated to `/ucms_new/`

