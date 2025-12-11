<?php
/**
 * Step 11: Course View Page (Stream)
 */
require_once '../../config/db.php';
require_once '../../src/Auth.php';
require_once '../../src/Course.php';

$auth = new Auth($pdo);
$auth->requireLogin();
$user = $auth->getUser();

$courseId = $_GET['id'] ?? null;
if (!$courseId) {
    header("Location: /ucms_new/index.php");
    exit;
}

$courseObj = new Course($pdo);
$course = $courseObj->getById($courseId);

if (!$course) {
    die("Course not found.");
}

// Access Control
$isTeacher = ($course['teacher_id'] == $user['id']);
$isEnrolled = $courseObj->isEnrolled($courseId, $user['id']);

if (!$isTeacher && !$isEnrolled) {
    die("Access Denied: You are not a member of this course.");
}

include '../../templates/header.php';
?>

<div class="course-container">
    <!-- Course Header -->
    <div class="course-hero">
        <div class="course-hero-content">
            <h1><?php echo htmlspecialchars($course['title']); ?></h1>
            <p class="course-section"><?php echo htmlspecialchars($course['description'] ?? ''); ?></p>
            <div class="course-meta">
                <span class="teacher-name">👨‍🏫 <?php echo htmlspecialchars($course['teacher_name']); ?></span>
                <?php if ($isTeacher): ?>
                    <span class="course-code-badge" onclick="copyCode('<?php echo $course['course_code']; ?>')"
                        title="Click to copy">
                        Code: <?php echo htmlspecialchars($course['course_code']); ?> 📋
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="course-nav">
        <a href="view.php?id=<?php echo $courseId; ?>" class="nav-item active">Stream</a>
        <a href="assignments.php?id=<?php echo $courseId; ?>" class="nav-item">Classwork</a>
        <a href="people.php?id=<?php echo $courseId; ?>" class="nav-item">People</a>
        <a href="attendance.php?id=<?php echo $courseId; ?>" class="nav-item">Attendance</a>
    </div>

    <!-- Main Content (Stream) -->
    <div class="stream-layout">
        <div class="stream-sidebar">
            <div class="card">
                <h3>Upcoming</h3>
                <p class="text-muted text-sm">No work due soon</p>
                <a href="assignments.php?id=<?php echo $courseId; ?>" class="link-sm">View all</a>
            </div>
        </div>

        <div class="stream-feed">
            <?php if ($isTeacher): ?>
                <div class="announce-box card">
                    <div class="announce-input" onclick="alert('Announcement creation coming in Step 12!')">
                        Announce something to your class...
                    </div>
                </div>
            <?php endif; ?>

            <!-- Placeholder for Announcements -->
            <div class="stream-msg" style="text-align: center; margin-top: 2rem; color: var(--text-secondary);">
                <p>Welcome to the course stream!</p>
                <p class="text-sm">Announcements will appear here (Step 12).</p>
            </div>
        </div>
    </div>
</div>

<script>
    function copyCode(code) {
        navigator.clipboard.writeText(code).then(() => {
            const badge = document.querySelector('.course-code-badge');
            const original = badge.innerHTML;
            badge.innerHTML = 'Copied! ✅';
            setTimeout(() => badge.innerHTML = original, 2000);
        });
    }
</script>

<style>
    .course-hero {
        background: radial-gradient(circle at top right, #3b82f6, #1d4ed8);
        color: white;
        padding: 2rem 2.5rem;
        border-radius: var(--radius);
        margin-bottom: 0;
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
    }

    .course-hero h1 {
        font-size: 2.2rem;
        margin-bottom: 0.5rem;
    }

    .course-section {
        font-size: 1.1rem;
        opacity: 0.9;
        margin-bottom: 1.5rem;
        max-width: 600px;
    }

    .course-meta {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        font-size: 0.95rem;
    }

    .course-code-badge {
        background: rgba(255, 255, 255, 0.2);
        padding: 0.25rem 0.75rem;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 600;
        transition: background 0.2s;
    }

    .course-code-badge:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .course-nav {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-top: none;
        border-radius: 0 0 var(--radius) var(--radius);
        padding: 0 1.5rem;
        margin-bottom: 2rem;
        display: flex;
        gap: 2rem;
        box-shadow: var(--shadow-sm);
    }

    .nav-item {
        padding: 1rem 0;
        color: var(--text-secondary);
        text-decoration: none;
        font-weight: 500;
        border-bottom: 3px solid transparent;
        transition: color 0.2s;
    }

    .nav-item:hover {
        color: var(--primary-color);
    }

    .nav-item.active {
        color: var(--primary-color);
        border-bottom-color: var(--primary-color);
    }

    .stream-layout {
        display: grid;
        grid-template-columns: 250px 1fr;
        gap: 2rem;
    }

    .card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        padding: 1.25rem;
        border-radius: var(--radius);
    }

    .stream-sidebar .card h3 {
        font-size: 1rem;
        margin-bottom: 0.5rem;
    }

    .text-sm {
        font-size: 0.85rem;
    }

    .link-sm {
        font-size: 0.85rem;
        display: block;
        margin-top: 1rem;
        color: var(--primary-color);
        text-decoration: none;
        text-align: right;
    }

    .announce-box {
        cursor: pointer;
        transition: box-shadow 0.2s;
    }

    .announce-box:hover {
        box-shadow: var(--shadow-md);
    }

    .announce-input {
        color: var(--text-secondary);
        padding: 0.5rem;
    }

    @media (max-width: 800px) {
        .stream-layout {
            grid-template-columns: 1fr;
        }

        .stream-sidebar {
            display: none;
        }
    }
</style>

<?php include '../../templates/footer.php'; ?>