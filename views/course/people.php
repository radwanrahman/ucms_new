<?php
/**
 * Step 18: People Page
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
    die("Access Denied.");
}

$students = $courseObj->getEnrolledStudents($courseId);
$studentCount = count($students);

include '../../templates/header.php';
?>

<div class="course-container">
    <!-- Header Reuse -->
    <div class="course-hero">
        <div class="course-hero-content">
            <h1><?php echo htmlspecialchars($course['title']); ?></h1>
            <p class="course-section"><?php echo htmlspecialchars($course['description'] ?? ''); ?></p>
            <div class="course-meta">
                <span class="teacher-name">👨‍🏫 <?php echo htmlspecialchars($course['teacher_name']); ?></span>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="course-nav">
        <a href="view.php?id=<?php echo $courseId; ?>" class="nav-item">Stream</a>
        <a href="assignments.php?id=<?php echo $courseId; ?>" class="nav-item">Classwork</a>
        <a href="people.php?id=<?php echo $courseId; ?>" class="nav-item active">People</a>
        <a href="attendance.php?id=<?php echo $courseId; ?>" class="nav-item">Attendance</a>
    </div>

    <div class="content-wrapper" style="max-width: 800px;">

        <!-- Teachers Section -->
        <div class="people-section">
            <h2 class="section-title">Teachers</h2>
            <div class="person-row">
                <div class="avatar-md teacher-avatar">
                    <?php echo strtoupper(substr($course['teacher_name'], 0, 1)); ?>
                </div>
                <div class="person-info">
                    <div class="person-name"><?php echo htmlspecialchars($course['teacher_name']); ?></div>
                    <div class="person-role">Owner</div>
                </div>
            </div>
        </div>

        <!-- Students Section -->
        <div class="people-section">
            <div class="section-header">
                <h2 class="section-title">Students</h2>
                <span class="student-count"><?php echo $studentCount; ?> students</span>
            </div>

            <?php if (empty($students)): ?>
                <div class="empty-list">No students enrolled yet.</div>
            <?php else: ?>
                <?php foreach ($students as $student): ?>
                    <div class="person-row border-top">
                        <div class="avatar-md">
                            <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                        </div>
                        <div class="person-info">
                            <div class="person-name"><?php echo htmlspecialchars($student['name']); ?></div>
                            <?php if ($isTeacher): ?>
                                <div class="person-email"><?php echo htmlspecialchars($student['email']); ?></div>
                            <?php endif; ?>
                        </div>
                        <?php if ($isTeacher): ?>
                            <div class="person-actions">
                                <!-- Placeholder for future actions like remove -->
                                <span title="Actions" style="color:var(--text-secondary); cursor:pointer;">⋮</span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</div>

<style>
    /* Reuse Hero & Nav */
    .course-hero {
        background: radial-gradient(circle at top right, #3b82f6, #1d4ed8);
        color: white;
        padding: 2rem 2.5rem;
        border-radius: var(--radius) var(--radius) 0 0;
    }

    .course-hero h1 {
        font-size: 2.2rem;
        margin-bottom: 0.5rem;
    }

    .course-section {
        margin-bottom: 1.5rem;
        opacity: 0.9;
    }

    .course-nav {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-top: none;
        borderRadius: 0 0 var(--radius) var(--radius);
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

    .content-wrapper {
        margin: 0 auto;
    }

    .people-section {
        background: var(--card-bg);
        border-radius: var(--radius);
        border: 1px solid var(--border-color);
        padding: 0;
        margin-bottom: 2rem;
        overflow: hidden;
    }

    .section-title {
        font-size: 1.5rem;
        color: var(--primary-color);
        margin: 0;
        padding: 1.5rem;
        padding-bottom: 1rem;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        padding-right: 1.5rem;
        border-bottom: 2px solid var(--primary-color);
        margin: 0 1.5rem;
        margin-bottom: 0.5rem;
    }

    .section-header .section-title {
        padding: 1.5rem 0 1rem 0;
        border: none;
    }

    .student-count {
        color: var(--primary-color);
        font-weight: 600;
        font-size: 0.9rem;
    }

    .person-row {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.5rem;
    }

    .person-row.border-top {
        border-top: 1px solid var(--border-color);
    }

    .person-row:hover {
        background-color: #f9fafb;
    }

    .avatar-md {
        width: 40px;
        height: 40px;
        background: var(--primary-color);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }

    .teacher-avatar {
        background: #ea580c;
        /* Distinct color for teacher */
    }

    .person-info {
        flex: 1;
    }

    .person-name {
        font-weight: 500;
        font-size: 1rem;
    }

    .person-role,
    .person-email {
        font-size: 0.85rem;
        color: var(--text-secondary);
    }

    .empty-list {
        padding: 2rem;
        text-align: center;
        color: var(--text-secondary);
        font-style: italic;
    }
</style>

<?php include '../../templates/footer.php'; ?>