<?php
/**
 * Step 9: Create Course Page
 */
require_once '../../config/db.php';
require_once '../../src/Auth.php';
require_once '../../src/Course.php';

$auth = new Auth($pdo);
$auth->requireLogin();
$user = $auth->getUser();

if ($user['role'] !== 'teacher') {
    die("Access Denied");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];

    $courseObj = new Course($pdo);
    $result = $courseObj->create($title, $description, $user['id']);

    if ($result['success']) {
        header("Location: /ucms_new/views/dashboard/teacher.php?success=course_created");
        exit;
    } else {
        $error = $result['message'];
    }
}

include '../../templates/header.php';
?>

<div class="container" style="max-width: 600px; margin-top: 4rem;">
    <div class="card">
        <h2 style="margin-bottom: 1.5rem; text-align: center;">Create New Course</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"
                style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Course Title</label>
                <input type="text" name="title" required placeholder="e.g., Advanced Physics"
                    style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; margin-top: 0.5rem; margin-bottom: 1rem;">
            </div>

            <div class="form-group">
                <label>Description (Optional)</label>
                <textarea name="description" rows="4" placeholder="Course details..."
                    style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; margin-top: 0.5rem; margin-bottom: 1.5rem; font-family: inherit;"></textarea>
            </div>

            <div style="display: flex; gap: 1rem;">
                <a href="/ucms_new/views/dashboard/teacher.php" class="btn btn-outline"
                    style="flex: 1; text-align: center;">Cancel</a>
                <button type="submit" class="btn btn-primary" style="flex: 1;">Create Course</button>
            </div>
        </form>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>