<?php

class SkillMatch extends Database {

    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /**
     * CODECHECK GUIDE: Main matching entry point.
     * This builds three groups for the matches page:
     * mutual = both users can teach each other, multi = 2+ one-way skill matches, single = 1 skill match.
     *
     * Get ALL matches with simplified tier categorization
     * Returns array grouped by: mutual, multi, single
     */
    public function getAllMatchesWithScores($userId) {
        $this->db->query("
            SELECT 
                u.id,
                u.username,
                u.email,
                u.profile_picture,
                -- Skills I can teach them
                GROUP_CONCAT(DISTINCT 
                    CASE 
                        WHEN my_teach.skill_name IS NOT NULL 
                        THEN CONCAT(my_teach.skill_name, ':', my_teach.proficiency_level, ':', their_learn.proficiency_level)
                    END
                ) AS i_teach_them,
                -- Skills they can teach me
                GROUP_CONCAT(DISTINCT 
                    CASE 
                        WHEN their_teach.skill_name IS NOT NULL 
                        THEN CONCAT(their_teach.skill_name, ':', their_teach.proficiency_level, ':', my_learn.proficiency_level)
                    END
                ) AS they_teach_me,
                -- Check connection status
                CASE 
                    WHEN EXISTS (
                        SELECT 1 FROM exchanges 
                        WHERE ((requester_id = :current_user_id AND receiver_id = u.id)
                            OR (requester_id = u.id AND receiver_id = :current_user_id))
                        AND status = 'pending'
                    ) THEN 'pending'
                    WHEN EXISTS (
                        SELECT 1 FROM exchanges 
                        WHERE ((requester_id = :current_user_id AND receiver_id = u.id)
                            OR (requester_id = u.id AND receiver_id = :current_user_id))
                        AND status = 'active'
                    ) THEN 'connected'
                    ELSE 'available'
                END AS connection_status
            FROM users u
            -- Skills I teach that they want to learn
            LEFT JOIN user_skills their_learn 
                ON their_learn.user_id = u.id 
                AND their_learn.skill_type = 'learn'
            LEFT JOIN user_skills my_teach
                ON my_teach.user_id = :current_user_id
                AND my_teach.skill_type = 'teach'
                AND my_teach.skill_name = their_learn.skill_name
            -- Skills they teach that I want to learn
            LEFT JOIN user_skills my_learn
                ON my_learn.user_id = :current_user_id
                AND my_learn.skill_type = 'learn'
            LEFT JOIN user_skills their_teach
                ON their_teach.user_id = u.id
                AND their_teach.skill_type = 'teach'
                AND their_teach.skill_name = my_learn.skill_name
            WHERE u.id != :current_user_id
            GROUP BY u.id, u.username, u.email, u.profile_picture
            HAVING i_teach_them IS NOT NULL OR they_teach_me IS NOT NULL
        ");
        
        $this->db->bind(':current_user_id', $userId);
        $results = $this->db->resultSet();

        // Process raw SQL rows into cards the view can render, then put each one into a tier.
        $matches = [
            'mutual' => [],
            'multi' => [],
            'single' => []
        ];

        foreach ($results as $row) {
            $match = $this->processMatch($row);
            
            // Add connection status
            $match['connection_status'] = $row->connection_status ?? 'available';
            
            // Categorize match into tier
            $tier = $this->categorizeMatch($match);
            $matches[$tier][] = $match;
        }

        return $matches;
    }

    /**
     * CODECHECK GUIDE: Raw SQL strings become clean arrays here.
     * If a task asks to show "common skill count" or proficiency labels, this is a good place to add it.
     *
     * Process raw database row into structured match data
     */
    private function processMatch($row) {
        $match = [
            'id' => $row->id,
            'name' => $row->username ?? '',
            'email' => $row->email,
            'avatar' => $row->profile_picture ?? strtoupper(substr($row->username ?? 'U', 0, 2)),
            'i_teach' => [],
            'they_teach' => [],
            'is_mutual' => false,
            'total_skills' => 0
        ];

        // Parse skills I can teach them
        if (isset($row->i_teach_them) && $row->i_teach_them !== null && $row->i_teach_them !== '') {
            $skills = array_filter(explode(',', trim($row->i_teach_them, ',')));
            foreach ($skills as $skill) {
                $skill = trim($skill);
                if (empty($skill)) continue;
                $parts = explode(':', $skill);
                if (count($parts) >= 3) {
                    $match['i_teach'][] = [
                        'name' => $parts[0],
                        'display_name' => ucwords(str_replace('-', ' ', $parts[0])),
                        'my_level' => ucfirst($parts[1]),
                        'their_level' => ucfirst($parts[2])
                    ];
                }
            }
        }

        // Parse skills they can teach me
        if (isset($row->they_teach_me) && $row->they_teach_me !== null && $row->they_teach_me !== '') {
            $skills = array_filter(explode(',', trim($row->they_teach_me, ',')));
            foreach ($skills as $skill) {
                $skill = trim($skill);
                if (empty($skill)) continue;
                $parts = explode(':', $skill);
                if (count($parts) >= 3) {
                    $match['they_teach'][] = [
                        'name' => $parts[0],
                        'display_name' => ucwords(str_replace('-', ' ', $parts[0])),
                        'their_level' => ucfirst($parts[1]),
                        'my_level' => ucfirst($parts[2])
                    ];
                }
            }
        }

        // These two values drive categorization and ranking in the matches UI.
        $match['is_mutual'] = !empty($match['i_teach']) && !empty($match['they_teach']);
        $match['total_skills'] = count($match['i_teach']) + count($match['they_teach']);

        return $match;
    }

    /**
     * CODECHECK GUIDE: Ranking category decision.
     * For tasks like "prioritize mutual matches" or "add top match tag", start from this logic.
     *
     * Categorize match into tiers: mutual, multi, or single
     * MUTUAL = Both can teach AND learn from each other
     * MULTI = 2+ skills matched (but not mutual)
     * SINGLE = 1 skill matched
     */
    private function categorizeMatch($match) {
        $is_mutual = $match['is_mutual'];
        $skill_count = $match['total_skills'];
        
        // MUTUAL MATCHES - Both can teach and learn from each other (BEST!)
        if ($is_mutual) {
            return 'mutual';
        }
        
        // MULTI-SKILL MATCHES - 2+ skills matched
        if ($skill_count >= 2) {
            return 'multi';
        }
        
        // SINGLE SKILL MATCHES - 1 skill matched
        return 'single';
    }

    /**
     * CODECHECK GUIDE: Filter dropdown data source.
     * The matches page uses this to let users filter by skills they teach or want to learn.
     *
     * Get user's skills for filter dropdown
     */
    public function getUserSkillsForFilter($userId) {
        $this->db->query("
            SELECT DISTINCT skill_name, skill_type
            FROM user_skills
            WHERE user_id = :user_id
            ORDER BY skill_type DESC, skill_name ASC
        ");
        
        $this->db->bind(':user_id', $userId);
        $results = $this->db->resultSet();

        $skills = ['teaches' => [], 'learns' => []];
        
        foreach ($results as $skill) {
            $displayName = ucwords(str_replace('-', ' ', $skill->skill_name));
            if ($skill->skill_type === 'teach') {
                $skills['teaches'][] = ['name' => $skill->skill_name, 'display' => $displayName];
            } else {
                $skills['learns'][] = ['name' => $skill->skill_name, 'display' => $displayName];
            }
        }

        return $skills;
    }

    /**
     * CODECHECK GUIDE: One-way match where current user is the teacher.
     * A proficiency-level task usually changes the ORDER BY or SELECT fields in this query.
     *
     * Get users who want to LEARN skills that current user TEACHES
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
                us_teacher.proficiency_level AS teacher_level,
                CASE 
                    WHEN EXISTS (
                        SELECT 1 FROM exchanges 
                        WHERE ((requester_id = :current_user_id AND receiver_id = u.id)
                            OR (requester_id = u.id AND receiver_id = :current_user_id))
                        AND status = 'pending'
                    ) THEN 'pending'
                    WHEN EXISTS (
                        SELECT 1 FROM exchanges 
                        WHERE ((requester_id = :current_user_id AND receiver_id = u.id)
                            OR (requester_id = u.id AND receiver_id = :current_user_id))
                        AND status = 'active'
                    ) THEN 'connected'
                    ELSE 'available'
                END AS connection_status
            FROM users u
            INNER JOIN user_skills us_learner 
                ON u.id = us_learner.user_id
                AND us_learner.skill_type = 'learn'
            INNER JOIN user_skills us_teacher
                ON us_teacher.user_id = :current_user_id
                AND us_teacher.skill_type = 'teach'
                AND us_teacher.skill_name = us_learner.skill_name
            WHERE u.id != :current_user_id
            ORDER BY 
                connection_status ASC,
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
            $rawSkillName = $row->skill_name ?? '';
            $matches[] = [
                'id' => $row->id,
                'name' => $displayName,
                'email' => $row->email,
                'avatar' => $row->profile_picture ?? strtoupper(substr($displayName, 0, 2)),
                'skill' => 'Wants to learn ' . ucwords(str_replace('-', ' ', $rawSkillName)),
                'skill_name' => $rawSkillName,
                'learner_level' => ucfirst($row->learner_level ?? ''),
                'teacher_level' => ucfirst($row->teacher_level ?? ''),
                'connection_status' => $row->connection_status ?? 'available'
            ];
        }

        return $matches;
    }

    /**
     * CODECHECK GUIDE: One-way match where current user is the learner.
     * This is the opposite direction of getTeachMatches().
     *
     * Get users who TEACH skills that the current user wants to LEARN
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
                us_learner.proficiency_level AS learner_level,
                CASE 
                    WHEN EXISTS (
                        SELECT 1 FROM exchanges 
                        WHERE ((requester_id = :current_user_id AND receiver_id = u.id)
                            OR (requester_id = u.id AND receiver_id = :current_user_id))
                        AND status = 'pending'
                    ) THEN 'pending'
                    WHEN EXISTS (
                        SELECT 1 FROM exchanges 
                        WHERE ((requester_id = :current_user_id AND receiver_id = u.id)
                            OR (requester_id = u.id AND receiver_id = :current_user_id))
                        AND status = 'active'
                    ) THEN 'connected'
                    ELSE 'available'
                END AS connection_status
            FROM users u
            INNER JOIN user_skills us_teacher
                ON u.id = us_teacher.user_id
                AND us_teacher.skill_type = 'teach'
            INNER JOIN user_skills us_learner
                ON us_learner.user_id = :current_user_id
                AND us_learner.skill_type = 'learn'
                AND us_learner.skill_name = us_teacher.skill_name
            WHERE u.id != :current_user_id
            ORDER BY 
                connection_status ASC,
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
            $skillName = $row->skill_name ?? '';
            $matches[] = [
                'id' => $row->id,
                'name' => $displayName,
                'email' => $row->email,
                'avatar' => $row->profile_picture ?? strtoupper(substr($displayName, 0, 2)),
                'skill' => 'Teaches ' . ucwords(str_replace('-', ' ', $skillName)),
                'skill_name' => $skillName,
                'teacher_level' => ucfirst($row->teacher_level ?? ''),
                'learner_level' => ucfirst($row->learner_level ?? ''),
                'connection_status' => $row->connection_status ?? 'available'
            ];
        }

        return $matches;
    }

    /**
     * CODECHECK GUIDE: Strongest match type.
     * This query requires both directions to exist: I teach them one skill, and they teach me one skill.
     *
     * Get mutual matches (users where both can teach AND learn from each other)
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
                WHERE requester_id = :current_user_id AND status IN ('active', 'pending')
                UNION
                SELECT requester_id FROM exchanges 
                WHERE receiver_id = :current_user_id AND status IN ('active', 'pending')
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
