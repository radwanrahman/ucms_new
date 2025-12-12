<?php
/**
 * Step 15: Assignment Details Page
 */
require_once '../../config/db.php';
require_once '../../src/Auth.php';
require_once '../../src/Course.php';
require_once '../../src/Assignment.php';

$auth = new Auth($pdo);
$auth->requireLogin();
$user = $auth->getUser();

$assignmentId = $_GET['id'] ?? null;
if (!$assignmentId) {
    header("Location: /ucms_new/index.php");
    exit;
}

$assignObj = new Assignment($pdo);
$assignment = $assignObj->getById($assignmentId);

if (!$assignment) {
    die("Assignment not found.");
}

$courseObj = new Course($pdo);
// Only fetch course title for breadcrumb
$course = $courseObj->getById($assignment['course_id']);

// Access Control
$isTeacher = ($course['teacher_id'] == $user['id']);
$isEnrolled = $courseObj->isEnrolled($assignment['course_id'], $user['id']);

if (!$isTeacher && !$isEnrolled) {
    die("Access Denied.");
}

// Handle Student Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_work']) && !$isTeacher) {
    $link = $_POST['file_link'] ?? ''; // Simulating file upload with a link or text

    $result = $assignObj->submit($assignmentId, $user['id'], $link);
    if ($result['success']) {
        header("Location: assignment_details.php?id=$assignmentId&success=submitted");
        exit;
    }
}

// Handle Teacher Grading
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['grade_submission']) && $isTeacher) {
    $subId = $_POST['submission_id'];
    $grade = $_POST['grade'];

    $result = $assignObj->grade($subId, $grade);
    if ($result['success']) {
        header("Location: assignment_details.php?id=$assignmentId&success=graded");
        exit;
    }
}

// Fetch Submission Status
$submission = null;
$submissions = [];

if ($isTeacher) {
    $submissions = $assignObj->getSubmissions($assignmentId);
} else {
    // Check if student has submitted. Re-using getByCourse logic logic or simple query?
    // Let's use a quick inline query or a method if we had one. 
    // We can use the simple query here for now as getSubmissions is for ALL students.
    // Actually, let's reuse getByCourse logic but targeted.
    // Or just query directly for this student.
    $stmt = $pdo->prepare("SELECT * FROM submissions WHERE assignment_id = ? AND student_id = ?");
    $stmt->execute([$assignmentId, $user['id']]);
    $submission = $stmt->fetch(PDO::FETCH_ASSOC);
}

include '../../templates/header.php';
?>

<div class="container" style="max-width: 900px;">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="assignments.php?id=<?php echo $assignment['course_id']; ?>">← Back to Classwork</a>
    </div>

    <div class="details-layout">
        <!-- Left Column: Assignment Details -->
        <div class="details-main">
            <div class="details-header">
                <div class="icon-circle-lg">📋</div>
                <div>
                    <h1><?php echo htmlspecialchars($assignment['title']); ?></h1>
                    <div class="meta-row">
                        <span><?php echo htmlspecialchars($course['teacher_name']); ?></span>
                        <span class="dot">•</span>
                        <span>Due <?php echo date('M j, Y - g:i A', strtotime($assignment['due_date'])); ?></span>
                    </div>
                </div>
            </div>

            <div class="description-box">
                <?php echo nl2br(htmlspecialchars($assignment['description'])); ?>
            </div>

            <?php if ($isTeacher): ?>
                <div class="submissions-section">
                    <h3>Student Submissions</h3>
                    <?php if (empty($submissions)): ?>
                        <p class="text-muted">No submissions yet.</p>
                    <?php else: ?>
                        <div class="submission-list">
                            <?php foreach ($submissions as $sub): ?>
                                <div class="sub-item">
                                    <div class="sub-info">
                                        <span class="student-name"><?php echo htmlspecialchars($sub['student_name']); ?></span>
                                        <span
                                            class="sub-date"><?php echo date('M j, g:i A', strtotime($sub['submitted_at'])); ?></span>
                                        <div class="sub-link-preview">
                                            <a href="<?php echo htmlspecialchars($sub['file_path']); ?>" target="_blank">View
                                                Work</a>
                                        </div>
                                    </div>
                                    <form method="POST" class="grade-form">
                                        <input type="hidden" name="grade_submission" value="1">
                                        <input type="hidden" name="submission_id" value="<?php echo $sub['id']; ?>">
                                        <input type="number" name="grade" placeholder="/100" value="<?php echo $sub['grade']; ?>"
                                            min="0" max="100" class="grade-input">
                                        <button type="submit" class="btn-icon-save">💾</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column: Submission Box (Student Only) -->
        <?php if (!$isTeacher): ?>
            <div class="details-sidebar">
                <div class="card submission-card">
                    <div class="card-header">
                        <h3>Your Work</h3>
                        <span class="status-badge <?php echo $submission ? 'submitted' : 'assigned'; ?>">
                            <?php echo $submission ? ($submission['grade'] ? 'Graded' : 'Turned In') : 'Assigned'; ?>
                        </span>
                    </div>

                    <?php if ($submission): ?>
                        <div class="submitted-view">
                            <p>Submitted <?php echo date('M j, g:i A', strtotime($submission['submitted_at'])); ?></p>
                            <?php if ($submission['file_path']): ?>
                                <a href="<?php echo htmlspecialchars($submission['file_path']); ?>" target="_blank"
                                    class="file-attachment">
                                    📎 View Attached Work
                                </a>
                            <?php endif; ?>

                            <?php if ($submission['grade']): ?>
                                <div class="grade-display">
                                    <span class="grade-val"><?php echo $submission['grade']; ?></span>
                                    <span class="grade-max">/100</span>
                                </div>
                            <?php else: ?>
                                <button class="btn btn-outline btn-full" onclick="toggleForm()">Unsubmit / Edit</button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Submission Form (Hidden if graded) -->
                    <?php if (!$submission || ($submission && !$submission['grade'])): ?>
                        <form method="POST" id="submit-form" class="<?php echo $submission ? 'hidden' : ''; ?>">
                            <input type="hidden" name="submit_work" value="1">
                            <div class="upload-area">
                                <!-- Simulating file upload -->
                                <label>Attach Link or Text</label>
                                <input type="text" name="file_link" placeholder="http://google.docs/..." required
                                    class="input-full" value="<?php echo $submission['file_path'] ?? ''; ?>">
                            </div>
                            <button type="submit" class="btn btn-primary btn-full">
                                <?php echo $submission ? 'Resubmit' : 'Turn In'; ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function toggleForm() {
        document.querySelector('.submitted-view').classList.toggle('hidden');
        document.getElementById('submit-form').classList.toggle('hidden');
    }

    // Success Alerts
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success') === 'submitted') {
        Swal.fire({ icon: 'success', title: 'Turned In!', timer: 1500, showConfirmButton: false });
        window.history.replaceState({}, document.title, window.location.pathname + '?id=<?php echo $assignmentId; ?>');
    } else if (urlParams.get('success') === 'graded') {
        const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
        Toast.fire({ icon: 'success', title: 'Grade saved' });
        window.history.replaceState({}, document.title, window.location.pathname + '?id=<?php echo $assignmentId; ?>');
    }
</script>

<style>
    .breadcrumb {
        margin-bottom: 2rem;
    }

    .breadcrumb a {
        color: var(--text-secondary);
        text-decoration: none;
        font-weight: 500;
    }

    .details-layout {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 2rem;
    }

    .details-header {
        display: flex;
        gap: 1rem;
        align-items: flex-start;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 1.5rem;
    }

    .icon-circle-lg {
        width: 56px;
        height: 56px;
        background: var(--primary-color);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
    }

    .details-header h1 {
        margin: 0 0 0.5rem 0;
        font-size: 2rem;
        color: var(--primary-color);
    }

    .meta-row {
        color: var(--text-secondary);
        font-size: 0.95rem;
    }

    .dot {
        margin: 0 0.5rem;
    }

    .description-box {
        font-size: 1.05rem;
        line-height: 1.7;
        color: var(--text-color);
        margin-bottom: 3rem;
    }

    /* Teacher Grading */
    .submissions-section {
        margin-top: 2rem;
    }

    .sub-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        border-bottom: 1px solid var(--border-color);
    }

    .sub-info {
        display: flex;
        flex-direction: column;
    }

    .student-name {
        font-weight: 600;
    }

    .sub-date {
        font-size: 0.85rem;
        color: var(--text-secondary);
    }

    .sub-link-preview a {
        font-size: 0.85rem;
        color: var(--primary-color);
    }

    .grade-form {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .grade-input {
        width: 60px;
        padding: 0.25rem;
        border: 1px solid var(--border-color);
        border-radius: 4px;
    }

    .btn-icon-save {
        border: none;
        background: none;
        cursor: pointer;
        font-size: 1.2rem;
    }

    /* Student Sidebar */
    .submission-card {
        background: white;
        padding: 1.5rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-color);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .card-header h3 {
        margin: 0;
        font-size: 1.2rem;
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.85rem;
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

    .input-full {
        width: 100%;
        padding: 0.75rem;
        margin: 0.5rem 0 1rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
    }

    .btn-full {
        width: 100%;
        padding: 0.75rem;
    }

    .hidden {
        display: none;
    }

    .submitted-view {
        text-align: center;
    }

    .file-attachment {
        display: block;
        margin: 1rem 0;
        color: var(--primary-color);
        text-decoration: none;
        border: 1px solid var(--border-color);
        padding: 0.5rem;
        border-radius: var(--radius);
    }

    .grade-display {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-top: 1rem;
    }

    .grade-max {
        font-size: 1rem;
        color: var(--text-secondary);
        font-weight: 400;
    }

    @media (max-width: 800px) {
        .details-layout {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php include '../../templates/footer.php'; ?>