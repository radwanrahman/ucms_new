<?php
/**
 * Step 10: Course Enrollment Handler
 */
require_once '../../config/db.php';
require_once '../../src/Auth.php';
require_once '../../src/Course.php';

$auth = new Auth($pdo);
$auth->requireLogin();
$user = $auth->getUser();

if ($user['role'] !== 'student') {
    die('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['course_code'] ?? '');

    if (empty($code)) {
        header("Location: /ucms_new/views/dashboard/student.php?error=" . urlencode("Please enter a course code."));
        exit;
    }

    $courseObj = new Course($pdo);
    $course = $courseObj->getByCode($code);

    if (!$course) {
        header("Location: /ucms_new/views/dashboard/student.php?error=" . urlencode("Invalid course code."));
        exit;
    }

    $result = $courseObj->enroll($course['id'], $user['id']);

    if ($result['success']) {
        header("Location: /ucms_new/views/dashboard/student.php?success=" . urlencode("Successfully joined " . $course['title']));
    } else {
        header("Location: /ucms_new/views/dashboard/student.php?error=" . urlencode($result['message']));
    }
    exit;
}
?>