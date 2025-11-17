<?php

class SkillMatch extends Database {

    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /**
     * Get users who want to LEARN skills that the current user TEACHES
     * These are people the current user can teach
     */
    public function getTeachMatches($userId) {
        $this->db->query("
            SELECT DISTINCT
                u.id,
                u.username,
                u.email,
                u.profile_picture,
                us_learner.skill_name,
                us_learner.proficiency_level AS learner_level,
                us_teacher.proficiency_level AS teacher_level
            FROM users u
            INNER JOIN user_skills us_learner 
                ON u.id = us_learner.user_id
                AND us_learner.skill_type = 'learn'
            INNER JOIN user_skills us_teacher
                ON us_teacher.user_id = :current_user_id
                AND us_teacher.skill_type = 'teach'
                AND us_teacher.skill_name = us_learner.skill_name
            WHERE u.id != :current_user_id
            AND u.id NOT IN (
                SELECT receiver_id FROM exchanges 
                WHERE sender_id = :current_user_id AND status IN ('accepted', 'pending')
                UNION
                SELECT sender_id FROM exchanges 
                WHERE receiver_id = :current_user_id AND status IN ('accepted', 'pending')
            )
            ORDER BY 
                CASE us_learner.proficiency_level
                    WHEN 'beginner' THEN 1
                    WHEN 'intermediate' THEN 2
                    WHEN 'advanced' THEN 3
                END ASC
        ");
        $this->db->bind(':current_user_id', $userId);
        $results = $this->db->resultSet();

        $matches = [];
        foreach ($results as $row) {
            $displayName = $row->username ?? '';
            $matches[] = [
                'id' => $row->id,
                'name' => $displayName,
                'email' => $row->email,
                'avatar' => $row->profile_picture ?? strtoupper(substr($displayName, 0, 2)),
                'skill' => 'Wants to learn ' . ucwords(str_replace('-', ' ', $row->skill_name ?? '')),
                'learner_level' => ucfirst($row->learner_level ?? ''),
                'teacher_level' => ucfirst($row->teacher_level ?? '')
            ];
        }

        return $matches;
    }

    /**
     * Get users who TEACH skills that the current user wants to LEARN
     * These are people the current user can learn from
     */
    public function getLearnMatches($userId) {
        $this->db->query("
            SELECT DISTINCT
                u.id,
                u.username,
                u.email,
                u.profile_picture,
                us_teacher.skill_name,
                us_teacher.proficiency_level AS teacher_level,
                us_learner.proficiency_level AS learner_level
            FROM users u
            INNER JOIN user_skills us_teacher
                ON u.id = us_teacher.user_id
                AND us_teacher.skill_type = 'teach'
            INNER JOIN user_skills us_learner
                ON us_learner.user_id = :current_user_id
                AND us_learner.skill_type = 'learn'
                AND us_learner.skill_name = us_teacher.skill_name
            WHERE u.id != :current_user_id
            AND u.id NOT IN (
                SELECT receiver_id FROM exchanges 
                WHERE sender_id = :current_user_id AND status IN ('accepted', 'pending')
                UNION
                SELECT sender_id FROM exchanges 
                WHERE receiver_id = :current_user_id AND status IN ('accepted', 'pending')
            )
            ORDER BY 
                CASE us_teacher.proficiency_level
                    WHEN 'advanced' THEN 1
                    WHEN 'intermediate' THEN 2
                    WHEN 'beginner' THEN 3
                END ASC
        ");
        $this->db->bind(':current_user_id', $userId);
        $results = $this->db->resultSet();

        $matches = [];
        foreach ($results as $row) {
            $displayName = $row->username ?? '';
            $matches[] = [
                'id' => $row->id,
                'name' => $displayName,
                'email' => $row->email,
                'avatar' => $row->profile_picture ?? strtoupper(substr($displayName, 0, 2)),
                'skill' => 'Teaches ' . ucwords(str_replace('-', ' ', $row->skill_name ?? '')),
                'teacher_level' => ucfirst($row->teacher_level ?? ''),
                'learner_level' => ucfirst($row->learner_level ?? '')
            ];
        }

        return $matches;
    }

    /**
     * Get mutual matches (users where both can teach AND learn from each other)
     * This is the most valuable type of match
     */
    public function getMutualMatches($userId) {
        $this->db->query("
            SELECT DISTINCT
                u.id,
                u.username,
                u.email,
                u.profile_picture,
                teach_match.skill_name AS you_teach,
                teach_match.proficiency_level AS you_teach_level,
                learn_match.skill_name AS you_learn,
                learn_match.proficiency_level AS they_teach_level
            FROM users u
            INNER JOIN user_skills teach_match
                ON teach_match.user_id = :current_user_id
                AND teach_match.skill_type = 'teach'
            INNER JOIN user_skills their_learn
                ON their_learn.user_id = u.id
                AND their_learn.skill_type = 'learn'
                AND their_learn.skill_name = teach_match.skill_name
            INNER JOIN user_skills learn_match
                ON learn_match.user_id = :current_user_id
                AND learn_match.skill_type = 'learn'
            INNER JOIN user_skills their_teach
                ON their_teach.user_id = u.id
                AND their_teach.skill_type = 'teach'
                AND their_teach.skill_name = learn_match.skill_name
            WHERE u.id != :current_user_id
            AND u.id NOT IN (
                SELECT receiver_id FROM exchanges 
                WHERE sender_id = :current_user_id AND status IN ('accepted', 'pending')
                UNION
                SELECT sender_id FROM exchanges 
                WHERE receiver_id = :current_user_id AND status IN ('accepted', 'pending')
            )
        ");
        $this->db->bind(':current_user_id', $userId);
        $results = $this->db->resultSet();

        $matches = [];
        foreach ($results as $row) {
            $displayName = $row->username ?? '';
            $matches[] = [
                'id' => $row->id,
                'name' => $displayName,
                'email' => $row->email,
                'avatar' => $row->profile_picture ?? strtoupper(substr($displayName, 0, 2)),
                'you_teach' => ucwords(str_replace('-', ' ', $row->you_teach ?? '')),
                'you_learn' => ucwords(str_replace('-', ' ', $row->you_learn ?? '')),
                'mutual' => true
            ];
        }

        return $matches;
    }

    /**
     * Get match statistics for the current user
     */
    public function getMatchStats($userId) {
        // Count teach matches
        $this->db->query("
            SELECT COUNT(DISTINCT u.id) AS count
            FROM users u
            INNER JOIN user_skills us_learner 
                ON u.id = us_learner.user_id
                AND us_learner.skill_type = 'learn'
            INNER JOIN user_skills us_teacher
                ON us_teacher.user_id = :user_id
                AND us_teacher.skill_type = 'teach'
                AND us_teacher.skill_name = us_learner.skill_name
            WHERE u.id != :user_id
        ");
        $this->db->bind(':user_id', $userId);
        $teachCount = $this->db->single()->count ?? 0;

        // Count learn matches
        $this->db->query("
            SELECT COUNT(DISTINCT u.id) AS count
            FROM users u
            INNER JOIN user_skills us_teacher
                ON u.id = us_teacher.user_id
                AND us_teacher.skill_type = 'teach'
            INNER JOIN user_skills us_learner
                ON us_learner.user_id = :user_id
                AND us_learner.skill_type = 'learn'
                AND us_learner.skill_name = us_teacher.skill_name
            WHERE u.id != :user_id
        ");
        $this->db->bind(':user_id', $userId);
        $learnCount = $this->db->single()->count ?? 0;

        return [
            'teach_matches' => $teachCount,
            'learn_matches' => $learnCount,
            'total_matches' => $teachCount + $learnCount
        ];
    }

    /**
     * Search matches by skill name
     */
    public function searchMatchesBySkill($userId, $skillName, $matchType = 'all') {
        if ($matchType === 'teach') {
            return $this->searchTeachMatches($userId, $skillName);
        } elseif ($matchType === 'learn') {
            return $this->searchLearnMatches($userId, $skillName);
        } else {
            return array_merge(
                $this->searchTeachMatches($userId, $skillName),
                $this->searchLearnMatches($userId, $skillName)
            );
        }
    }

    private function searchTeachMatches($userId, $skillName) {
        $this->db->query("
            SELECT DISTINCT
                u.id,
                u.username,
                us_learner.skill_name
            FROM users u
            INNER JOIN user_skills us_learner
                ON u.id = us_learner.user_id
                AND us_learner.skill_type = 'learn'
                AND us_learner.skill_name LIKE :skill_name
            INNER JOIN user_skills us_teacher
                ON us_teacher.user_id = :user_id
                AND us_teacher.skill_type = 'teach'
                AND us_teacher.skill_name = us_learner.skill_name
            WHERE u.id != :user_id
        ");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':skill_name', '%' . $skillName . '%');

        return $this->db->resultSet();
    }

    private function searchLearnMatches($userId, $skillName) {
        $this->db->query("
            SELECT DISTINCT
                u.id,
                u.username,
                us_teacher.skill_name
            FROM users u
            INNER JOIN user_skills us_teacher
                ON u.id = us_teacher.user_id
                AND us_teacher.skill_type = 'teach'
                AND us_teacher.skill_name LIKE :skill_name
            INNER JOIN user_skills us_learner
                ON us_learner.user_id = :user_id
                AND us_learner.skill_type = 'learn'
                AND us_learner.skill_name = us_teacher.skill_name
            WHERE u.id != :user_id
        ");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':skill_name', '%' . $skillName . '%');

        return $this->db->resultSet();
    }
}
