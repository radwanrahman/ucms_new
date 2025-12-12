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

    /**
     * Submit an assignment with file upload
     */
    public function submit($assignmentId, $studentId, $file)
    {
        // Handle file upload
        $targetDir = __DIR__ . "/../public/uploads/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileName = time() . "_" . basename($file["name"]);
        $targetFilePath = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        // Allow certain file formats
        $allowTypes = array('pdf', 'doc', 'docx', 'txt', 'zip', 'jpg', 'png');
        if (in_array($fileType, $allowTypes)) {
            if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
                try {
                    // Check if already submitted
                    $stmt = $this->pdo->prepare("SELECT id FROM submissions WHERE assignment_id = ? AND student_id = ?");
                    $stmt->execute([$assignmentId, $studentId]);
                    if ($stmt->fetch()) {
                        // Update existing submission
                        $stmt = $this->pdo->prepare("UPDATE submissions SET file_path = ?, submitted_at = NOW() WHERE assignment_id = ? AND student_id = ?");
                        $stmt->execute([$fileName, $assignmentId, $studentId]);
                    } else {
                        // Create new submission
                        $stmt = $this->pdo->prepare("INSERT INTO submissions (assignment_id, student_id, file_path) VALUES (?, ?, ?)");
                        $stmt->execute([$assignmentId, $studentId, $fileName]);
                    }
                    return ['success' => true, 'message' => 'Assignment submitted successfully!'];
                } catch (PDOException $e) {
                    return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
                }
            } else {
                return ['success' => false, 'message' => 'Sorry, there was an error uploading your file.'];
            }
        } else {
            return ['success' => false, 'message' => 'Sorry, only PDF, DOC, JPG, PNG & ZIP files are allowed.'];
        }
    }

    /**
     * Get single submission for a student
     */
    public function getSubmission($assignmentId, $studentId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM submissions WHERE assignment_id = ? AND student_id = ?");
        $stmt->execute([$assignmentId, $studentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Grade a submission
     */
    public function grade($submissionId, $grade)
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE submissions SET grade = ? WHERE id = ?");
            $stmt->execute([$grade, $submissionId]);
            return ['success' => true, 'message' => 'Grade updated!'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Get all submissions for an assignment
     */
    public function getSubmissions($assignmentId)
    {
        $stmt = $this->pdo->prepare("
            SELECT s.*, u.name as student_name, u.email 
            FROM submissions s
            JOIN users u ON s.student_id = u.id
            WHERE s.assignment_id = ?
            ORDER BY s.submitted_at DESC
        ");
        $stmt->execute([$assignmentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get student progress stats
     */
    public function getStudentProgress($studentId)
    {
        // Total assignments in enrolled courses
        $stmt = $this->pdo->prepare("
            SELECT COUNT(DISTINCT a.id) 
            FROM assignments a
            JOIN enrollments e ON a.course_id = e.course_id
            WHERE e.student_id = ?
        ");
        $stmt->execute([$studentId]);
        $total = $stmt->fetchColumn();

        // Total submissions
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM submissions WHERE student_id = ?");
        $stmt->execute([$studentId]);
        $submitted = $stmt->fetchColumn();

        return [
            'total' => $total,
            'submitted' => $submitted,
            'percentage' => $total > 0 ? round(($submitted / $total) * 100) : 0
        ];
    }
}
?>