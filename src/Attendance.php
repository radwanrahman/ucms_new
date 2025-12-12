<?php
/**
 * Attendance Class
 * Step 16: Manage student attendance
 */

class Attendance
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Mark attendance for a student on a specific date
     */
    public function mark($courseId, $studentId, $date, $status)
    {
        // Status enum: 'present', 'absent', 'late'
        if (!in_array($status, ['present', 'absent', 'late'])) {
            return ['success' => false, 'message' => 'Invalid status.'];
        }

        try {
            // Check if record exists
            $stmt = $this->pdo->prepare("SELECT id FROM attendance WHERE course_id = ? AND student_id = ? AND date = ?");
            $stmt->execute([$courseId, $studentId, $date]);

            if ($stmt->fetch()) {
                // Update
                $stmt = $this->pdo->prepare("UPDATE attendance SET status = ? WHERE course_id = ? AND student_id = ? AND date = ?");
                $stmt->execute([$status, $courseId, $studentId, $date]);
            } else {
                // Insert
                $stmt = $this->pdo->prepare("INSERT INTO attendance (course_id, student_id, date, status) VALUES (?, ?, ?, ?)");
                $stmt->execute([$courseId, $studentId, $date, $status]);
            }

            return ['success' => true, 'message' => 'Attendance saved.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Get attendance records for a specific date (for Teacher view)
     */
    public function getByDate($courseId, $date)
    {
        $stmt = $this->pdo->prepare("
            SELECT a.*, u.name as student_name, u.email
            FROM attendance a
            JOIN users u ON a.student_id = u.id
            WHERE a.course_id = ? AND a.date = ?
        ");
        $stmt->execute([$courseId, $date]);

        // Key records by student_id for easy lookup
        $records = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $records[$row['student_id']] = $row;
        }
        return $records;
    }

    /**
     * Get attendance history for a student (for Student view)
     */
    public function getByStudent($courseId, $studentId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM attendance WHERE course_id = ? AND student_id = ? ORDER BY date DESC");
        $stmt->execute([$courseId, $studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calculate attendance percentage for a student
     */
    public function calculatePercentage($courseId, $studentId)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM attendance WHERE course_id = ? AND student_id = ?");
        $stmt->execute([$courseId, $studentId]);
        $total = $stmt->fetch()['total'];

        if ($total == 0)
            return 0;

        $stmt = $this->pdo->prepare("SELECT COUNT(*) as present FROM attendance WHERE course_id = ? AND student_id = ? AND (status = 'present' OR status = 'late')");
        $stmt->execute([$courseId, $studentId]);
        $present = $stmt->fetch()['present'];

        return round(($present / $total) * 100);
    }
}
?>