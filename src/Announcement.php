<?php
/**
 * Announcement Class
 * Step 12: Handle course announcements
 */

class Announcement
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Create a new announcement
     */
    public function create($courseId, $teacherId, $content)
    {
        if (empty($content)) {
            return ['success' => false, 'message' => 'Content cannot be empty.'];
        }

        try {
            $stmt = $this->pdo->prepare("INSERT INTO announcements (course_id, teacher_id, content) VALUES (?, ?, ?)");
            $stmt->execute([$courseId, $teacherId, $content]);
            return ['success' => true, 'message' => 'Announcement posted successfully!'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Get all announcements for a course
     */
    public function getByCourse($courseId)
    {
        $stmt = $this->pdo->prepare("
            SELECT a.*, u.name as teacher_name 
            FROM announcements a
            JOIN users u ON a.teacher_id = u.id
            WHERE a.course_id = ?
            ORDER BY a.created_at DESC
        ");
        $stmt->execute([$courseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>