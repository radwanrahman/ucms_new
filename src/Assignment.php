<?php
/**
 * Assignment Class
 * Step 13: Manage course assignments
 */

class Assignment
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Create a new assignment
     */
    public function create($courseId, $title, $description, $dueDate)
    {
        if (empty($title) || empty($dueDate)) {
            return ['success' => false, 'message' => 'Title and Due Date are required.'];
        }

        try {
            $stmt = $this->pdo->prepare("INSERT INTO assignments (course_id, title, description, due_date) VALUES (?, ?, ?, ?)");
            $stmt->execute([$courseId, $title, $description, $dueDate]);
            return ['success' => true, 'message' => 'Assignment created successfully!'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Get all assignments for a course (with specific student submission status if studentId provided)
     */
    public function getByCourse($courseId, $studentId = null)
    {
        $sql = "SELECT a.*";

        if ($studentId) {
            // Include submission status if viewing as student
            $sql .= ", s.id as submission_id, s.grade, s.submitted_at 
                     FROM assignments a 
                     LEFT JOIN submissions s ON a.id = s.assignment_id AND s.student_id = ?";
        } else {
            $sql .= " FROM assignments a";
        }

        $sql .= " WHERE a.course_id = ? ORDER BY a.due_date ASC";

        $stmt = $this->pdo->prepare($sql);

        if ($studentId) {
            $stmt->execute([$studentId, $courseId]);
        } else {
            $stmt->execute([$courseId]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get single assignment details
     */
    public function getById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM assignments WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>