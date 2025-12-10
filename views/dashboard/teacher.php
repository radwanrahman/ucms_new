<?php
/**
 * Step 8: Teacher Dashboard
 */
require_once '../../config/db.php';
require_once '../../src/Auth.php';

$auth = new Auth($pdo);
$auth->requireLogin();
$user = $auth->getUser();

if ($user['role'] !== 'teacher') {
    header("Location: /ucms_new/views/dashboard/" . $user['role'] . ".php");
    exit;
}

// Reuse header
include '../../templates/header.php';

// --- DATA FETCHING ---
$teacherId = $user['id'];

// 1. Fetch Courses Taught by Teacher
$stmtCourses = $pdo->prepare("
    SELECT c.*, 
           (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as student_count,
           (SELECT COUNT(*) FROM assignments WHERE course_id = c.id) as assignment_count
    FROM courses c
    WHERE c.teacher_id = ?
    ORDER BY c.created_at DESC
");
$stmtCourses->execute([$teacherId]);
$courses = $stmtCourses->fetchAll(PDO::FETCH_ASSOC);

// 2. Fetch Recent Submissions (needing grading)
$stmtSubs = $pdo->prepare("
    SELECT s.*, a.title as assignment_title, u.name as student_name, c.course_code
    FROM submissions s
    JOIN assignments a ON s.assignment_id = a.id
    JOIN courses c ON a.course_id = c.id
    JOIN users u ON s.student_id = u.id
    WHERE c.teacher_id = ? AND s.grade IS NULL
    ORDER BY s.submitted_at ASC
    LIMIT 5
");
$stmtSubs->execute([$teacherId]);
$pendingSubmissions = $stmtSubs->fetchAll(PDO::FETCH_ASSOC);

// 3. Simple Stats
$totalStudents = 0;
foreach ($courses as $c) {
    $totalStudents += $c['student_count'];
}
?>

<div class="dashboard-header">
    <div class="welcome-banner teacher-banner">
        <div>
            <h1>Hello, Professor <?php echo htmlspecialchars($user['name']); ?>! 👨‍🏫</h1>
            <p>You have <?php echo count($pendingSubmissions); ?> new submissions to grade.</p>
        </div>
        <div class="header-actions">
            <!-- These will link to future create pages -->
            <a href="/ucms_new/views/course/create.php" class="btn btn-light">+ Create Course</a>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Main Column: Courses Management -->
    <div class="main-column">
        <div class="section-header">
            <h2>Teaching Courses</h2>
            <div class="filter-options">
                <span class="active">Active</span>
                <span>Archived</span>
            </div>
        </div>

        <?php if (empty($courses)): ?>
            <div class="empty-state">
                <div class="empty-icon">📝</div>
                <h3>No courses created yet</h3>
                <p>Start your teaching journey by creating your first course.</p>
                <!-- Placeholder link for now -->
                <button class="btn btn-primary" onclick="alert('Create Course page coming in Step 9!')">Create
                    Course</button>
            </div>
        <?php else: ?>
            <div class="courses-grid">
                <?php foreach ($courses as $course): ?>
                    <div class="course-card teacher-card">
                        <div class="course-header">
                            <span class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></span>
                            <div class="course-menu">⋮</div>
                        </div>
                        <h3 class="course-title">
                            <a href="/ucms_new/views/course/view.php?id=<?php echo $course['id']; ?>">
                                <?php echo htmlspecialchars($course['title']); ?>
                            </a>
                        </h3>

                        <div class="teacher-stats">
                            <div class="t-stat">
                                <span class="t-val"><?php echo $course['student_count']; ?></span>
                                <span class="t-lbl">Students</span>
                            </div>
                            <div class="t-stat">
                                <span class="t-val"><?php echo $course['assignment_count']; ?></span>
                                <span class="t-lbl">Assignments</span>
                            </div>
                        </div>

                        <div class="course-footer">
                            <a href="/ucms_new/views/course/view.php?id=<?php echo $course['id']; ?>" class="btn-text">Manage
                                Course</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar: Grading & Quick Actions -->
    <div class="sidebar">
        <div class="card bg-gradient">
            <h3 style="color:white; margin-bottom:0.5rem;">Overview</h3>
            <div class="stats-overview">
                <div class="stat-box">
                    <span class="val"><?php echo count($courses); ?></span>
                    <span class="lbl">Courses</span>
                </div>
                <div class="stat-box">
                    <span class="val"><?php echo $totalStudents; ?></span>
                    <span class="lbl">Students</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header-flex">
                <h3>Pending Grading</h3>
                <span class="badge-count"><?php echo count($pendingSubmissions); ?></span>
            </div>

            <?php if (empty($pendingSubmissions)): ?>
                <div class="empty-mini">
                    <p>All caught up! ✅</p>
                </div>
            <?php else: ?>
                <ul class="notification-list">
                    <?php foreach ($pendingSubmissions as $sub): ?>
                        <li class="notify-item">
                            <div class="notify-icon">📝</div>
                            <div class="notify-content">
                                <span class="notify-title"><?php echo htmlspecialchars($sub['student_name']); ?></span>
                                <span class="notify-desc">
                                    Submitted <strong><?php echo htmlspecialchars($sub['assignment_title']); ?></strong>
                                    <br>
                                    <span class="course-ref"><?php echo htmlspecialchars($sub['course_code']); ?></span>
                                </span>
                            </div>
                            <!-- Link to grading page (future step) -->
                            <a href="#" class="btn-icon">→</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>Quick Actions</h3>
            <div class="action-list">
                <button class="action-btn" onclick="alert('Announcement feature coming in Step 12!')">
                    <span>📢</span> Post Announcement
                </button>
                <button class="action-btn" onclick="alert('Assignment feature coming in Step 14!')">
                    <span>✍️</span> Create Assignment
                </button>
                <button class="action-btn" onclick="alert('Attendance feature coming in Step 17!')">
                    <span>📅</span> Take Attendance
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Teacher Dashboard Specifics */
    .teacher-banner {
        background: linear-gradient(135deg, #4f46e5, #4338ca);
        /* Indigo theme for teachers */
    }

    .btn-light {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 0.5rem 1rem;
        backdrop-filter: blur(4px);
    }

    .btn-light:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .teacher-card {
        border-top: 4px solid #4f46e5;
    }

    .teacher-stats {
        display: flex;
        gap: 1.5rem;
        margin: 1rem 0;
        padding: 0.75rem 0;
        border-top: 1px solid var(--border-color);
        border-bottom: 1px solid var(--border-color);
    }

    .t-stat {
        display: flex;
        flex-direction: column;
    }

    .t-val {
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--text-color);
    }

    .t-lbl {
        font-size: 0.75rem;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-text {
        color: #4f46e5;
        font-weight: 600;
        text-decoration: none;
        font-size: 0.9rem;
    }

    .btn-text:hover {
        text-decoration: underline;
    }

    /* Stats Box in Sidebar */
    .bg-gradient {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
        border: none;
    }

    .stats-overview {
        display: flex;
        justify-content: space-around;
        text-align: center;
        margin-top: 1rem;
    }

    .stat-box {
        display: flex;
        flex-direction: column;
    }

    .stat-box .val {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 0.25rem;
    }

    .stat-box .lbl {
        font-size: 0.85rem;
        opacity: 0.9;
    }

    /* Notification List */
    .card-header-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .badge-count {
        background: #fee2e2;
        color: #991b1b;
        padding: 0.1rem 0.5rem;
        border-radius: 1rem;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .notification-list {
        list-style: none;
    }

    .notify-item {
        display: flex;
        gap: 0.75rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border-color);
    }

    .notify-item:last-child {
        border-bottom: none;
    }

    .notify-icon {
        font-size: 1.25rem;
    }

    .notify-content {
        flex: 1;
        font-size: 0.9rem;
    }

    .notify-title {
        display: block;
        font-weight: 600;
        color: var(--text-color);
    }

    .notify-desc {
        color: var(--text-secondary);
        font-size: 0.85rem;
    }

    .course-ref {
        background: #eff6ff;
        color: #2563eb;
        padding: 0 4px;
        border-radius: 4px;
        font-size: 0.75rem;
    }

    .btn-icon {
        color: var(--text-secondary);
        text-decoration: none;
    }

    /* Action List */
    .action-list {
        display: grid;
        gap: 0.75rem;
    }

    .action-btn {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        width: 100%;
        padding: 0.75rem;
        background: white;
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        cursor: pointer;
        font-family: var(--font-family);
        font-size: 0.9rem;
        color: var(--text-color);
        transition: all 0.2s;
        text-align: left;
    }

    .action-btn:hover {
        background: var(--hover-bg);
        border-color: #4f46e5;
        color: #4f46e5;
    }

    .action-btn span {
        font-size: 1.2rem;
    }

    /* Reuse some styles from student dashboard if needed, or rely on shared classes */
    .dashboard-header {
        margin-bottom: 2rem;
    }

    .welcome-banner {
        padding: 2rem;
        border-radius: var(--radius);
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: white;
        box-shadow: var(--shadow-md);
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 2rem;
    }

    /* ... repeat necessary layout base styles or ensure they are global ... */
    /* Since we didn't put everything in global CSS yet, I'll allow some duplication here for safety */
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .section-header h2 {
        font-size: 1.5rem;
        margin: 0;
    }

    .courses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
    }

    .course-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        padding: 1.5rem;
        transition: transform 0.2s;
    }

    .course-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
    }

    .course-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .course-code {
        background: #f3f4f6;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-weight: 600;
        font-size: 0.8rem;
    }

    .course-title a {
        color: var(--text-color);
        text-decoration: none;
        font-size: 1.15rem;
        font-weight: 600;
        display: block;
        margin-bottom: 0.5rem;
    }

    .sidebar .card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .sidebar h3 {
        margin-bottom: 1rem;
        font-size: 1.1rem;
    }

    @media (max-width: 900px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }

        .welcome-banner {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }
    }
</style>

<?php include '../../templates/footer.php'; ?>