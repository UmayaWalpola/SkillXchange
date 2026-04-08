<?php
/**
 * FeedbackReport Model
 * Handles database operations for reporting abusive/fake feedback
 */
class FeedbackReport extends Database {

    /**
     * Create a new feedback report
     * 
     * @param array $data - Report data (feedback_id, reporter_id, reason, details)
     * @return int|false - Report ID on success, false on failure
     */
    public function createReport($data) {
        // Check if user already reported this feedback
        if ($this->hasUserReported($data['feedback_id'], $data['reporter_id'])) {
            return false; // Prevent duplicate reports
        }

        $conn = $this->connect();

        $sql = "INSERT INTO feedback_reports (feedback_id, reporter_id, reason, details, created_at) 
                VALUES (:feedback_id, :reporter_id, :reason, :details, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':feedback_id', $data['feedback_id']);
        $stmt->bindValue(':reporter_id', $data['reporter_id']);
        $stmt->bindValue(':reason', $data['reason']);
        $stmt->bindValue(':details', $data['details'] ?? '');
        
        if ($stmt->execute()) {
            // Increment report count on user_feedback
            $this->incrementReportCount($data['feedback_id']);

            $reportId = (int)$conn->lastInsertId();
            if ($reportId <= 0) {
                $idStmt = $conn->query("SELECT LAST_INSERT_ID() AS id");
                $idRow = $idStmt ? $idStmt->fetch(PDO::FETCH_ASSOC) : null;
                $reportId = isset($idRow['id']) ? (int)$idRow['id'] : 0;
            }

            if ($reportId <= 0) {
                $fallbackSql = "SELECT id
                                FROM feedback_reports
                                WHERE feedback_id = :feedback_id
                                  AND reporter_id = :reporter_id
                                ORDER BY id DESC
                                LIMIT 1";
                $fallbackStmt = $conn->prepare($fallbackSql);
                $fallbackStmt->bindValue(':feedback_id', $data['feedback_id']);
                $fallbackStmt->bindValue(':reporter_id', $data['reporter_id']);
                $fallbackStmt->execute();
                $fallbackRow = $fallbackStmt->fetch(PDO::FETCH_ASSOC);
                $reportId = isset($fallbackRow['id']) ? (int)$fallbackRow['id'] : 0;
            }

            return $reportId > 0 ? $reportId : false;
        }
        
        return false;
    }

    /**
     * Check if user has already reported this feedback
     * 
     * @param int $feedbackId
     * @param int $reporterId
     * @return bool
     */
    public function hasUserReported($feedbackId, $reporterId) {
        $sql = "SELECT COUNT(*) as count FROM feedback_reports 
                WHERE feedback_id = :feedback_id AND reporter_id = :reporter_id";
        
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':feedback_id', $feedbackId);
        $stmt->bindValue(':reporter_id', $reporterId);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    /**
     * Increment report count on user_feedback table
     * 
     * @param int $feedbackId
     * @return bool
     */
    private function incrementReportCount($feedbackId) {
        $sql = "UPDATE user_feedback 
                SET report_count = report_count + 1 
                WHERE id = :feedback_id";
        
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':feedback_id', $feedbackId);
        return $stmt->execute();
    }

    /**
     * Get all reports with filtering (for admin)
     * 
     * @param array $filters - Filters: status, limit, offset
     * @return array - Array with 'items' and 'total_count'
     */
    public function getReports($filters = []) {
        $status = $filters['status'] ?? null;
        $limit = $filters['limit'] ?? 20;
        $offset = $filters['offset'] ?? 0;

        // Build WHERE clause
        $where = "WHERE 1=1";
        if ($status && $status !== 'all') {
            $where .= " AND fr.status = :status";
        }

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM feedback_reports fr $where";
        $countStmt = $this->connect()->prepare($countSql);
        if ($status && $status !== 'all') {
            $countStmt->bindValue(':status', $status);
        }
        $countStmt->execute();
        $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Get reports with full details
        $sql = "SELECT 
                    fr.*,
                    reporter.username as reporter_name,
                    reporter.email as reporter_email,
                    reviewer.username as reviewer_name,
                    
                    uf.user_id as feedback_user_id,
                    uf.rating as feedback_rating,
                    uf.comment as feedback_comment,
                    uf.created_at as feedback_created_at,
                    uf.tags as feedback_tags,
                    
                    feedback_user.username as feedback_user_name,
                    feedback_reviewer.username as feedback_reviewer_name,
                    
                    p.name as project_name
                FROM feedback_reports fr
                LEFT JOIN users reporter ON fr.reporter_id = reporter.id
                LEFT JOIN users reviewer ON fr.reviewed_by = reviewer.id
                LEFT JOIN user_feedback uf ON fr.feedback_id = uf.id
                LEFT JOIN users feedback_user ON uf.user_id = feedback_user.id
                LEFT JOIN users feedback_reviewer ON uf.reviewer_id = feedback_reviewer.id
                LEFT JOIN projects p ON uf.project_id = p.id
                $where
                ORDER BY fr.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->connect()->prepare($sql);
        if ($status && $status !== 'all') {
            $stmt->bindValue(':status', $status);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total_count' => $totalCount
        ];
    }

    /**
     * Get report statistics (for admin dashboard)
     * 
     * @return array - Counts by status
     */
    public function getReportStats() {
        $sql = "SELECT 
                    status,
                    COUNT(*) as count
                FROM feedback_reports
                GROUP BY status";
        
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute();
        
        $stats = [
            'pending' => 0,
            'reviewed' => 0,
            'dismissed' => 0,
            'action_taken' => 0,
            'total' => 0
        ];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stats[$row['status']] = (int)$row['count'];
            $stats['total'] += (int)$row['count'];
        }
        
        return $stats;
    }

    /**
     * Update report status (admin action)
     * 
     * @param int $reportId
     * @param string $status - pending, reviewed, dismissed, action_taken
     * @param int $adminId - User ID of admin reviewing
     * @param string $adminNotes - Optional admin notes
     * @return bool
     */
    public function updateReportStatus($reportId, $status, $adminId, $adminNotes = '') {
        $sql = "UPDATE feedback_reports 
                SET status = :status,
                    reviewed_by = :admin_id,
                    reviewed_at = NOW(),
                    admin_notes = :admin_notes
                WHERE id = :report_id";
        
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':report_id', $reportId);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':admin_id', $adminId);
        $stmt->bindValue(':admin_notes', $adminNotes);
        
        return $stmt->execute();
    }

    /**
     * Get single report by ID
     * 
     * @param int $reportId
     * @return array|false
     */
    public function getReportById($reportId) {
        $sql = "SELECT 
                    fr.*,
                    reporter.username as reporter_name,
                    reporter.email as reporter_email,
                    
                    uf.user_id as feedback_user_id,
                    uf.reviewer_id as feedback_reviewer_id,
                    uf.rating as feedback_rating,
                    uf.comment as feedback_comment,
                    uf.created_at as feedback_created_at,
                    uf.tags as feedback_tags,
                    
                    feedback_user.username as feedback_user_name,
                    feedback_reviewer.username as feedback_reviewer_name,
                    
                    p.name as project_name
                FROM feedback_reports fr
                LEFT JOIN users reporter ON fr.reporter_id = reporter.id
                LEFT JOIN user_feedback uf ON fr.feedback_id = uf.id
                LEFT JOIN users feedback_user ON uf.user_id = feedback_user.id
                LEFT JOIN users feedback_reviewer ON uf.reviewer_id = feedback_reviewer.id
                LEFT JOIN projects p ON uf.project_id = p.id
                WHERE fr.id = :report_id";

        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':report_id', $reportId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Delete a feedback report
     * 
     * @param int $reportId
     * @return bool
     */
    public function deleteReport($reportId) {
        $sql = "DELETE FROM feedback_reports WHERE id = :report_id";
        
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':report_id', $reportId);
        
        return $stmt->execute();
    }

    /**
     * Get number of reports for a specific feedback
     * 
     * @param int $feedbackId
     * @return int
     */
    public function getReportCountForFeedback($feedbackId) {
        $sql = "SELECT COUNT(*) as count FROM feedback_reports 
                WHERE feedback_id = :feedback_id";
        
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':feedback_id', $feedbackId);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['count'];
    }
}
