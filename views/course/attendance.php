<?php
/**
 * Step 17: Attendance Page
 */
require_once '../../config/db.php';
require_once '../../src/Auth.php';
require_once '../../src/Course.php';
require_once '../../src/Attendance.php';

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

$attendObj = new Attendance($pdo);
$date = $_GET['date'] ?? date('Y-m-d');
$message = '';

// Handle Attendance Submission (Teacher)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_attendance']) && $isTeacher) {
    $date = $_POST['date'];
    $updates = $_POST['status'] ?? []; // Array [student_id => status]

    $count = 0;
    foreach ($updates as $sId => $status) {
        $attendObj->mark($courseId, $sId, $date, $status);
        $count++;
    }
    $message = "Saved attendance for $count students on $date.";
}

// Data Fetching
if ($isTeacher) {
    $students = $courseObj->getEnrolledStudents($courseId);
    $existing = $attendObj->getByDate($courseId, $date); // Returns array keyed by student_id
} else {
    $myStats = $attendObj->calculatePercentage($courseId, $user['id']);
    $myHistory = $attendObj->getByStudent($courseId, $user['id']);
}

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
        <a href="people.php?id=<?php echo $courseId; ?>" class="nav-item">People</a>
        <a href="attendance.php?id=<?php echo $courseId; ?>" class="nav-item active">Attendance</a>
    </div>

    <div class="content-wrapper">
        <?php if ($message): ?>
            <div class="alert alert-success"
                style="margin-bottom: 1.5rem; background: #dcfce7; color: #166534; padding: 1rem; border-radius: var(--radius);">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($isTeacher): ?>
            <div class="attendance-toolbar card">
                <form method="GET" class="date-selector">
                    <input type="hidden" name="id" value="<?php echo $courseId; ?>">
                    <label>Select Date:</label>
                    <input type="date" name="date" value="<?php echo $date; ?>" class="input-date"
                        onchange="this.form.submit()">
                </form>
                <div class="toolbar-info">
                    Marking for: <strong><?php echo date('l, M j, Y', strtotime($date)); ?></strong>
                </div>
            </div>

            <form method="POST" class="attendance-sheet card">
                <input type="hidden" name="save_attendance" value="1">
                <input type="hidden" name="date" value="<?php echo $date; ?>">

                <table class="table-full">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Status.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="2" class="text-center">No students enrolled yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $student):
                                $sId = $student['id'];
                                $currentStatus = $existing[$sId]['status'] ?? '';
                                ?>
                                <tr>
                                    <td>
                                        <div class="student-row">
                                            <div class="avatar-sm"><?php echo strtoupper(substr($student['name'], 0, 1)); ?></div>
                                            <div>
                                                <div class="student-name"><?php echo htmlspecialchars($student['name']); ?></div>
                                                <div class="student-email"><?php echo htmlspecialchars($student['email']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="status-options">
                                            <label class="status-label present">
                                                <input type="radio" name="status[<?php echo $sId; ?>]" value="present" <?php echo ($currentStatus == 'present' || !$currentStatus) ? 'checked' : ''; ?>>
                                                Present
                                            </label>
                                            <label class="status-label absent">
                                                <input type="radio" name="status[<?php echo $sId; ?>]" value="absent" <?php echo ($currentStatus == 'absent') ? 'checked' : ''; ?>>
                                                Absent
                                            </label>
                                            <label class="status-label late">
                                                <input type="radio" name="status[<?php echo $sId; ?>]" value="late" <?php echo ($currentStatus == 'late') ? 'checked' : ''; ?>>
                                                Late
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?php if (!empty($students)): ?>
                    <div class="sheet-actions">
                        <button type="submit" class="btn btn-primary">Save Attendance</button>
                    </div>
                <?php endif; ?>
            </form>

        <?php else: ?>
            <!-- Student View -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Average Attendance</h3>
                    <div class="stat-circle" style="--val: <?php echo $myStats; ?>">
                        <svg viewBox="0 0 36 36" class="circular-chart">
                            <path class="circle-bg"
                                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="circle" stroke-dasharray="<?php echo $myStats; ?>, 100"
                                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <text x="18" y="20.35" class="percentage"><?php echo $myStats; ?>%</text>
                        </svg>
                    </div>
                </div>

                <div class="history-card">
                    <h3>History</h3>
                    <?php if (empty($myHistory)): ?>
                        <p class="text-muted">No attendance records yet.</p>
                    <?php else: ?>
                        <table class="table-simple">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($myHistory as $rec): ?>
                                    <tr>
                                        <td><?php echo date('M j, Y', strtotime($rec['date'])); ?></td>
                                        <td>
                                            <span class="badge <?php echo $rec['status']; ?>">
                                                <?php echo ucfirst($rec['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    /* Reuse Hero & Nav from view.php */
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

    /* Teacher Styles */
    .attendance-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        background: var(--card-bg);
        border-radius: var(--radius);
        border: 1px solid var(--border-color);
    }

    .date-selector {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .input-date {
        padding: 0.5rem;
        border: 1px solid var(--border-color);
        border-radius: 4px;
    }

    .attendance-sheet {
        background: var(--card-bg);
        border-radius: var(--radius);
        border: 1px solid var(--border-color);
        overflow: hidden;
    }

    .table-full {
        width: 100%;
        border-collapse: collapse;
    }

    .table-full th,
    .table-full td {
        padding: 1rem 1.5rem;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
    }

    .table-full th {
        background: #f9fafb;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .student-row {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .avatar-sm {
        width: 32px;
        height: 32px;
        background: var(--primary-color);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
    }

    .student-name {
        font-weight: 500;
    }

    .student-email {
        font-size: 0.85rem;
        color: var(--text-secondary);
    }

    .status-options {
        display: flex;
        gap: 1.5rem;
    }

    .status-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        font-size: 0.95rem;
    }

    .status-label.present {
        color: #166534;
    }

    .status-label.absent {
        color: #991b1b;
    }

    .status-label.late {
        color: #b45309;
    }

    .sheet-actions {
        padding: 1.5rem;
        text-align: right;
        background: #f9fafb;
    }

    /* Student Styles */
    .stats-grid {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 2rem;
    }

    .stat-card,
    .history-card {
        background: var(--card-bg);
        padding: 1.5rem;
        border-radius: var(--radius);
        border: 1px solid var(--border-color);
    }

    .stat-circle {
        width: 150px;
        margin: 1rem auto;
    }

    .circular-chart {
        display: block;
        margin: 10px auto;
        max-width: 80%;
        max-height: 250px;
    }

    .circle-bg {
        fill: none;
        stroke: #eee;
        stroke-width: 3.8;
    }

    .circle {
        fill: none;
        stroke-width: 2.8;
        stroke-linecap: round;
        animation: progress 1s ease-out forwards;
        stroke: var(--primary-color);
    }

    .percentage {
        fill: var(--text-color);
        font-family: sans-serif;
        font-weight: bold;
        font-size: 0.5em;
        text-anchor: middle;
    }

    .table-simple {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }

    .table-simple th,
    .table-simple td {
        padding: 0.75rem;
        text-align: left;
        border-bottom: 1px solid #f3f4f6;
    }

    .badge {
        padding: 0.2rem 0.6rem;
        border-radius: 1rem;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .badge.present {
        background: #dcfce7;
        color: #166534;
    }

    .badge.absent {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge.late {
        background: #ffedd5;
        color: #9a3412;
    }

    @media (max-width: 800px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .status-options {
            gap: 0.75rem;
        }
    }
</style>

<?php include '../../templates/footer.php'; ?>