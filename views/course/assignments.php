<?php
/**
 * Step 14: Assignments Page (Classwork)
 */
require_once '../../config/db.php';
require_once '../../src/Auth.php';
require_once '../../src/Course.php';
require_once '../../src/Assignment.php';

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

// Handle Assignment Creation
$assignObj = new Assignment($pdo);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_assignment']) && $isTeacher) {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $due = $_POST['due_date'];

    $result = $assignObj->create($courseId, $title, $desc, $due);
    if ($result['success']) {
        header("Location: assignments.php?id=$courseId&success=created");
        exit;
    } else {
        $error = $result['message'];
    }
}

// Fetch Assignments
// If student, pass ID to get status
$assignments = $assignObj->getByCourse($courseId, $isTeacher ? null : $user['id']);

include '../../templates/header.php';
?>

<div class="course-container">
    <!-- Reuse Course Header (Simplified for sub-pages or same as view.php?) -->
    <!-- Let's keep it consistent with view.php -->
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
        <a href="assignments.php?id=<?php echo $courseId; ?>" class="nav-item active">Classwork</a>
        <a href="people.php?id=<?php echo $courseId; ?>" class="nav-item">People</a>
        <a href="attendance.php?id=<?php echo $courseId; ?>" class="nav-item">Attendance</a>
    </div>

    <!-- Main Content -->
    <div class="content-wrapper">
        <div class="header-flex">
            <h2>Classwork</h2>
            <?php if ($isTeacher): ?>
                <button onclick="openCreateModal()" class="btn btn-primary">+ Create Assignment</button>
            <?php endif; ?>
        </div>

        <?php if (empty($assignments)): ?>
            <div class="empty-state">
                <div class="empty-icon">📝</div>
                <h3>No assignments yet</h3>
                <?php if ($isTeacher): ?>
                    <p>Create your first assignment to get started.</p>
                    <button onclick="openCreateModal()" class="btn btn-outline">Create Assignment</button>
                <?php else: ?>
                    <p>Woohoo! No work due right now.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="assignment-list">
                <?php foreach ($assignments as $a): ?>
                    <!-- Wrap in link to details page -->
                    <a href="assignment_details.php?id=<?php echo $a['id']; ?>" class="assignment-item">
                        <div class="assign-icon">
                            <div class="icon-circle">📋</div>
                        </div>
                        <div class="assign-info">
                            <h3 class="assign-title"><?php echo htmlspecialchars($a['title']); ?></h3>
                            <div class="assign-meta">
                                <span>Due <?php echo date('M j, Y - g:i A', strtotime($a['due_date'])); ?></span>
                                <?php if (!$isTeacher): ?>
                                    <?php if ($a['grade']): ?>
                                        <span class="status-badge graded">Graded: <?php echo $a['grade']; ?>/100</span>
                                    <?php elseif ($a['submission_id']): ?>
                                        <span class="status-badge submitted">Turned In</span>
                                    <?php elseif (strtotime($a['due_date']) < time()): ?>
                                        <span class="status-badge missing">Missing</span>
                                    <?php else: ?>
                                        <span class="status-badge assigned">Assigned</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Create Assignment Modal Template (Hidden) -->
<script>
    function openCreateModal() {
        Swal.fire({
            title: 'Create Assignment',
            html: `
            <form id="create-form" method="POST">
                <input type="hidden" name="create_assignment" value="1">
                <div class="form-group" style="text-align:left">
                    <label>Title</label>
                    <input type="text" name="title" class="swal2-input" placeholder="Assignment Title" required style="margin: 0.5rem 0 1rem; width: 100%;">
                </div>
                <div class="form-group" style="text-align:left">
                    <label>Description</label>
                    <textarea name="description" class="swal2-textarea" placeholder="Instructions..." style="margin: 0.5rem 0 1rem; width: 100%;"></textarea>
                </div>
                <div class="form-group" style="text-align:left">
                    <label>Due Date</label>
                    <input type="datetime-local" name="due_date" class="swal2-input" required style="margin: 0.5rem 0 1rem; width: 100%;">
                </div>
            </form>
        `,
            showCancelButton: true,
            confirmButtonText: 'Assign',
            confirmButtonColor: '#2563eb',
            preConfirm: () => {
                const form = document.getElementById('create-form');
                if (!form.title.value || !form.due_date.value) {
                    Swal.showValidationMessage('Title and Due Date are required');
                    return false;
                }
                form.submit();
            }
        });
    }

    // Remove success param
    if (window.location.search.includes('success=created')) {
        window.history.replaceState({}, document.title, window.location.pathname + '?id=<?php echo $courseId; ?>');
        Swal.fire({
            icon: 'success',
            title: 'Created!',
            text: 'Assignment posted successfully.',
            timer: 1500,
            showConfirmButton: false
        });
    }
</script>

<style>
    /* Reuse Hero & Nav Styles from view.php (ideally in style.css or included) */
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

    .content-wrapper {
        max-width: 900px;
        margin: 0 auto;
    }

    .header-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .assignment-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .assignment-item {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        background: var(--card-bg);
        padding: 1.25rem;
        border-radius: var(--radius);
        border: 1px solid var(--border-color);
        text-decoration: none;
        color: var(--text-color);
        transition: all 0.2s;
    }

    .assignment-item:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
        border-color: var(--primary-color);
    }

    .icon-circle {
        width: 48px;
        height: 48px;
        background: #eff6ff;
        color: var(--primary-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .assign-info {
        flex: 1;
    }

    .assign-title {
        font-size: 1.1rem;
        margin-bottom: 0.25rem;
        font-weight: 600;
    }

    .assign-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.9rem;
        color: var(--text-secondary);
    }

    .status-badge {
        padding: 0.2rem 0.6rem;
        border-radius: 1rem;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .status-badge.assigned {
        background: #f3f4f6;
        color: var(--text-secondary);
    }

    .status-badge.submitted {
        background: #dcfce7;
        color: #166534;
    }

    .status-badge.missing {
        background: #fee2e2;
        color: #991b1b;
    }

    .status-badge.graded {
        background: #dbeafe;
        color: #1e40af;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: var(--card-bg);
        border-radius: var(--radius);
        border: 2px dashed var(--border-color);
    }

    .empty-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
    }
</style>

<?php include '../../templates/footer.php'; ?>