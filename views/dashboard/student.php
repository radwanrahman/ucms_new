<?php
/**
 * Step 7: Student Dashboard
 */
require_once '../../config/db.php';
require_once '../../src/Auth.php';

$auth = new Auth($pdo);
$auth->requireLogin();
$user = $auth->getUser();

if ($user['role'] !== 'student') {
    header("Location: /ucms_new/views/dashboard/" . $user['role'] . ".php");
    exit;
}

// Reuse header
include '../../templates/header.php';

// --- DATA FETCHING (Direct SQL for now) ---
$studentId = $user['id'];

// 1. Fetch Enrolled Courses
$stmtInfo = $pdo->prepare("
    SELECT c.*, u.name as teacher_name, 
           (SELECT COUNT(*) FROM assignments WHERE course_id = c.id) as assignment_count
    FROM courses c
    JOIN enrollments e ON c.id = e.course_id
    JOIN users u ON c.teacher_id = u.id
    WHERE e.student_id = ?
    ORDER BY e.enrolled_at DESC
");
$stmtInfo->execute([$studentId]);
$courses = $stmtInfo->fetchAll(PDO::FETCH_ASSOC);

// 2. Fetch Upcoming Assignments (Pending)
$stmtAssign = $pdo->prepare("
    SELECT a.*, c.title as course_title, c.course_code
    FROM assignments a
    JOIN enrollments e ON a.course_id = e.course_id
    JOIN courses c ON c.id = a.course_id
    LEFT JOIN submissions s ON a.id = s.assignment_id AND s.student_id = ?
    WHERE e.student_id = ? 
    AND s.id IS NULL 
    AND a.due_date >= NOW()
    ORDER BY a.due_date ASC
    LIMIT 5
");
$stmtAssign->execute([$studentId, $studentId]);
$upcomingAssignments = $stmtAssign->fetchAll(PDO::FETCH_ASSOC);

// 3. Fetch Recent Announcements
$stmtAnnounce = $pdo->prepare("
    SELECT an.*, c.title as course_title, u.name as teacher_name
    FROM announcements an
    JOIN enrollments e ON an.course_id = e.course_id
    JOIN courses c ON c.id = an.course_id
    JOIN users u ON an.teacher_id = u.id
    WHERE e.student_id = ?
    ORDER BY an.created_at DESC
    LIMIT 3
");
$stmtAnnounce->execute([$studentId]);
$recentAnnouncements = $stmtAnnounce->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="dashboard-header">
    <div class="welcome-banner">
        <div>
            <h1>Welcome back, <?php echo htmlspecialchars($user['name']); ?>! 👋</h1>
            <p>You have <?php echo count($upcomingAssignments); ?> upcoming assignments this week.</p>
        </div>
        <div class="date-badge">
            <?php echo date('l, F j, Y'); ?>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Main Column: Courses -->
    <div class="main-column">
        <div class="section-header">
            <h2>My Courses</h2>
            <a href="#" class="btn btn-outline btn-sm">Browse All</a>
        </div>

        <?php if (empty($courses)): ?>
            <div class="empty-state">
                <div class="empty-icon">📚</div>
                <h3>No courses enrolled</h3>
                <p>You haven't enrolled in any courses yet.</p>
                <button class="btn btn-primary">Browse Courses</button>
            </div>
        <?php else: ?>
            <div class="courses-grid">
                <?php foreach ($courses as $course): ?>
                <div class="course-card">
                    <div class="course-header">
                        <span class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></span>
                        <div class="course-options">...</div>
                    </div>
                    <h3 class="course-title">
                        <a href="/ucms_new/views/course/view.php?id=<?php echo $course['id']; ?>">
                            <?php echo htmlspecialchars($course['title']); ?>
                        </a>
                    </h3>
                    <p class="course-teacher">👨‍🏫 <?php echo htmlspecialchars($course['teacher_name']); ?></p>
                    <div class="course-footer">
                        <span class="assignment-badge">
                            <?php echo $course['assignment_count']; ?> Assignments
                        </span>
                        <a href="/ucms_new/views/course/view.php?id=<?php echo $course['id']; ?>" class="btn-arrow">→</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="section-header" style="margin-top: 2.5rem;">
            <h2>Recent Announcements</h2>
        </div>
        
        <?php if (empty($recentAnnouncements)): ?>
            <div class="empty-simple">No recent announcements.</div>
        <?php else: ?>
            <div class="announcements-list">
                <?php foreach ($recentAnnouncements as $announcement): ?>
                <div class="announcement-card">
                    <div class="announcement-header">
                        <span class="course-tag"><?php echo htmlspecialchars($announcement['course_title']); ?></span>
                        <span class="date"><?php echo date('M j', strtotime($announcement['created_at'])); ?></span>
                    </div>
                    <p class="announcement-content"><?php echo nl2br(htmlspecialchars(substr($announcement['content'], 0, 150) . (strlen($announcement['content']) > 150 ? '...' : ''))); ?></p>
                    <div class="announcement-author">
                        Posted by <?php echo htmlspecialchars($announcement['teacher_name']); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar: Upcoming & Stats -->
    <div class="sidebar">
        <div class="card">
            <h3>Upcoming Due Dates</h3>
            <?php if (empty($upcomingAssignments)): ?>
                <p class="text-muted">No pending assignments! 🎉</p>
            <?php else: ?>
                <ul class="upcoming-list">
                    <?php foreach ($upcomingAssignments as $assign): ?>
                        <li class="upcoming-item">
                            <div class="upcoming-info">
                                <h4><?php echo htmlspecialchars($assign['title']); ?></h4>
                                <span class="course-name"><?php echo htmlspecialchars($assign['course_code']); ?></span>
                            </div>
                            <div class="due-date <?php echo (strtotime($assign['due_date']) < time() + 86400*2) ? 'urgent' : ''; ?>">
                                <?php echo date('M j', strtotime($assign['due_date'])); ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        
        <div class="card stats-card">
            <h3>Quick Stats</h3>
            <div class="stat-row">
                <span class="stat-label">Enrolled</span>
                <span class="stat-value"><?php echo count($courses); ?></span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Assignments</span>
                <span class="stat-value"><?php echo count($upcomingAssignments); ?></span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Attendance</span>
                <span class="stat-value">--%</span>
            </div>
        </div>
    </div>
</div>

<style>
    /* Dashboard specific styles - ideally move to style.css later */
    .dashboard-header {
        margin-bottom: 2rem;
    }
    .welcome-banner {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        padding: 2rem;
        border-radius: var(--radius);
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        box-shadow: var(--shadow-md);
    }
    .welcome-banner h1 { margin: 0 0 0.5rem 0; font-size: 1.8rem; }
    .welcome-banner p { margin: 0; opacity: 0.9; }
    .date-badge { 
        background: rgba(255,255,255,0.2); 
        padding: 0.5rem 1rem; 
        border-radius: 2rem; 
        font-size: 0.9rem;
        backdrop-filter: blur(5px);
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 2rem;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    .section-header h2 { font-size: 1.5rem; color: var(--text-color); }
    
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
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .course-card:hover { 
        transform: translateY(-3px); 
        box-shadow: var(--shadow-md); 
        border-color: var(--primary-color);
    }
    .course-header { display: flex; justify-content: space-between; margin-bottom: 1rem; }
    .course-code { 
        background: #eff6ff; color: var(--primary-color); 
        padding: 0.25rem 0.75rem; border-radius: 1rem; 
        font-size: 0.8rem; font-weight: 600; 
    }
    .course-title a {
        color: var(--text-color);
        text-decoration: none;
        font-size: 1.25rem;
        font-weight: 600;
        display: block;
        margin-bottom: 0.5rem;
    }
    .course-teacher { font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 1.5rem; }
    .course-footer { 
        display: flex; justify-content: space-between; align-items: center; 
        padding-top: 1rem; border-top: 1px solid var(--border-color); 
    }
    .assignment-badge { font-size: 0.85rem; color: var(--text-secondary); }
    .btn-arrow { color: var(--primary-color); text-decoration: none; font-weight: bold; font-size: 1.2rem; }

    /* Empty States */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: var(--card-bg);
        border-radius: var(--radius);
        border: 2px dashed var(--border-color);
    }
    .empty-icon { font-size: 3rem; margin-bottom: 1rem; }

    /* Sidebar */
    .card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .card h3 { margin-bottom: 1rem; font-size: 1.1rem; }
    
    .upcoming-list { list-style: none; }
    .upcoming-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border-color);
    }
    .upcoming-item:last-child { border-bottom: none; }
    .upcoming-info h4 { font-size: 0.95rem; margin-bottom: 0.2rem; }
    .course-name { font-size: 0.8rem; color: var(--text-secondary); }
    .due-date { 
        font-size: 0.85rem; font-weight: 500; color: var(--text-secondary); 
        background: #f3f4f6; padding: 0.25rem 0.5rem; border-radius: 0.5rem; 
    }
    .due-date.urgent { background: #fee2e2; color: #991b1b; }

    .stat-row { 
        display: flex; justify-content: space-between; 
        padding: 0.75rem 0; border-bottom: 1px solid var(--border-color); 
    }
    .stat-row:last-child { border-bottom: none; }
    .stat-value { font-weight: 600; }

    /* Announcements */
    .announcements-list { display: grid; gap: 1rem; }
    .announcement-card {
        background: var(--card-bg);
        padding: 1.25rem;
        border-radius: var(--radius);
        border: 1px solid var(--border-color);
    }
    .announcement-header { 
        display: flex; justify-content: space-between; 
        margin-bottom: 0.5rem; font-size: 0.85rem; 
    }
    .course-tag { font-weight: 600; color: var(--primary-color); }
    .date { color: var(--text-secondary); }
    .announcement-author { 
        margin-top: 0.75rem; font-size: 0.85rem; 
        color: var(--text-secondary); font-style: italic; 
    }

    @media (max-width: 900px) {
        .dashboard-grid { grid-template-columns: 1fr; }
    }
</style>

<?php include '../../templates/footer.php'; ?>
