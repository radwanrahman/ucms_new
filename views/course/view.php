<?php
/**
 * Step 11: Course View Page (Stream)
 */
require_once '../../config/db.php';
require_once '../../src/Auth.php';
require_once '../../src/Course.php';
require_once '../../src/Announcement.php';

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

// Handle Announcement Submission
$announceObj = new Announcement($pdo);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content']) && $isTeacher) {
    $result = $announceObj->create($courseId, $user['id'], $_POST['content']);
    if ($result['success']) {
        // Redirect to avoid resubmission
        header("Location: view.php?id=$courseId&success=posted");
        exit;
    } else {
        $error = $result['message'];
    }
}

$announcements = $announceObj->getByCourse($courseId);

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
                    <?php if (isset($_GET['posting'])): ?>
                        <form method="POST">
                            <textarea name="content" class="announce-area" placeholder="Announce something to your class..."
                                required autofocus></textarea>
                            <div class="announce-actions">
                                <a href="view.php?id=<?php echo $courseId; ?>" class="btn btn-outline btn-sm">Cancel</a>
                                <button type="submit" class="btn btn-primary btn-sm">Post</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="announce-input"
                            onclick="window.location.href='view.php?id=<?php echo $courseId; ?>&posting=1'">
                            <div class="avatar-circle">
                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                            </div>
                            <span class="placeholder-text">Announce something to your class...</span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($announcements)): ?>
                <div class="stream-msg" style="text-align: center; margin-top: 2rem; color: var(--text-secondary);">
                    <p>Welcome to the course stream!</p>
                    <p class="text-sm">No announcements yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($announcements as $announce): ?>
                    <div class="post-card card">
                        <div class="post-header">
                            <div class="avatar-circle">
                                <?php echo strtoupper(substr($announce['teacher_name'], 0, 1)); ?>
                            </div>
                            <div class="post-meta">
                                <span class="author-name"><?php echo htmlspecialchars($announce['teacher_name']); ?></span>
                                <span class="post-date"><?php echo date('M j', strtotime($announce['created_at'])); ?></span>
                            </div>
                        </div>
                        <div class="post-content">
                            <?php echo nl2br(htmlspecialchars($announce['content'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
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
    // Remove 'success' query param cleanly
    if (window.location.search.includes('success=posted')) {
        window.history.replaceState({}, document.title, window.location.pathname + '?id=<?php echo $courseId; ?>');
        Swal.fire({
            icon: 'success',
            title: 'Posted!',
            text: 'Announcement added successfully.',
            timer: 1500,
            showConfirmButton: false
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
        transition: box-shadow 0.2s;
        margin-bottom: 2rem;
    }

    .announce-box:hover {
        box-shadow: var(--shadow-md);
    }

    .announce-input {
        color: var(--text-secondary);
        padding: 0.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        cursor: pointer;
    }

    .placeholder-text {
        opacity: 0.8;
        font-size: 0.95rem;
    }

    .avatar-circle {
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

    .announce-area {
        width: 100%;
        border: none;
        background: #f9fafb;
        padding: 1rem;
        border-radius: var(--radius);
        resize: vertical;
        min-height: 100px;
        margin-bottom: 1rem;
        font-family: inherit;
    }

    .announce-area:focus {
        outline: 1px solid var(--primary-color);
        background: white;
    }

    .announce-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
    }

    .post-card {
        margin-bottom: 1.5rem;
        padding: 0 !important;
        overflow: hidden;
    }

    .post-header {
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        border-bottom: 1px solid #f3f4f6;
    }

    .post-meta {
        display: flex;
        flex-direction: column;
    }

    .author-name {
        font-weight: 600;
        font-size: 0.95rem;
    }

    .post-date {
        font-size: 0.8rem;
        color: var(--text-secondary);
    }

    .post-content {
        padding: 1.25rem;
        line-height: 1.6;
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