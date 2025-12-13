<?php
/**
 * Course Class
 * Step 9: Encapsulate course management logic
 */

class Course
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Create a new course
     */
    public function create($title, $description, $teacherId)
    {
        if (empty($title) || empty($teacherId)) {
            return ['success' => false, 'message' => 'Title and Teacher ID are required.'];
        }

        $code = $this->generateUniqueCode();

        try {
            $stmt = $this->pdo->prepare("INSERT INTO courses (title, description, course_code, teacher_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$title, $description, $code, $teacherId]);

            return [
                'success' => true,
                'message' => 'Course created successfully!',
                'course_id' => $this->pdo->lastInsertId(),
                'course_code' => $code
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error creating course: ' . $e->getMessage()];
        }
    }

    /**
     * Get details of a single course by ID
     */
    public function getById($id)
    {
        $stmt = $this->pdo->prepare("SELECT c.*, u.name as teacher_name FROM courses c JOIN users u ON c.teacher_id = u.id WHERE c.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get details of a single course by Code
     */
    public function getByCode($code)
    {
        $stmt = $this->pdo->prepare("SELECT c.*, u.name as teacher_name FROM courses c JOIN users u ON c.teacher_id = u.id WHERE c.course_code = ?");
        $stmt->execute([$code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all courses created by a specific teacher
     */
    public function getByTeacher($teacherId)
    {
        $stmt = $this->pdo->prepare("
            SELECT c.*, 
                   (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as student_count
            FROM courses c 
            WHERE c.teacher_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all courses a student is enrolled in
     */
    public function getByStudent($studentId)
    {
        $stmt = $this->pdo->prepare("
            SELECT c.*, u.name as teacher_name 
            FROM courses c
            JOIN enrollments e ON c.id = e.course_id
            JOIN users u ON c.teacher_id = u.id
            WHERE e.student_id = ?
            ORDER BY e.enrolled_at DESC
        ");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if a student is enrolled in a specific course
     */
    public function isEnrolled($courseId, $studentId)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM enrollments WHERE course_id = ? AND student_id = ?");
        $stmt->execute([$courseId, $studentId]);
        return (bool) $stmt->fetch();
    }

    /**
     * Enroll a student in a course
     */
    public function enroll($courseId, $studentId)
    {
        // Check if already enrolled
        if ($this->isEnrolled($courseId, $studentId)) {
            return ['success' => false, 'message' => 'Already enrolled in this course.'];
        }

        try {
            $stmt = $this->pdo->prepare("INSERT INTO enrollments (course_id, student_id) VALUES (?, ?)");
            $stmt->execute([$courseId, $studentId]);
            return ['success' => true, 'message' => 'Successfully enrolled!'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Enrollment failed: ' . $e->getMessage()];
        }
    }

    /**
     * Get all students enrolled in a course
     */
    public function getEnrolledStudents($courseId)
    {
        $stmt = $this->pdo->prepare("
            SELECT u.id, u.name, u.email, e.enrolled_at 
            FROM users u
            JOIN enrollments e ON u.id = e.student_id
            WHERE e.course_id = ?
            ORDER BY u.name ASC
        ");
        $stmt->execute([$courseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generate unique 6-character course code
     */
    private function generateUniqueCode()
    {
        do {
            $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
            $stmt = $this->pdo->prepare("SELECT id FROM courses WHERE course_code = ?");
            $stmt->execute([$code]);
        } while ($stmt->fetch());

        return $code;
    }
}
?>