<?php

class SkillMatch extends Database {

    private $db;

    public function __construct() {
        $this->db = new Database;
    }
/**
 * Get ALL matches with compatibility scoring
 * Returns array grouped by match percentage
 */
// In: app/models/SkillMatch.php

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
                    WHERE ((sender_id = :current_user_id AND receiver_id = u.id)
                        OR (sender_id = u.id AND receiver_id = :current_user_id))
                    AND status = 'pending'
                ) THEN 'pending'
                WHEN EXISTS (
                    SELECT 1 FROM exchanges 
                    WHERE ((sender_id = :current_user_id AND receiver_id = u.id)
                        OR (sender_id = u.id AND receiver_id = :current_user_id))
                    AND status = 'accepted'
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
        -- REMOVED THE FILTER - now we show all matches with status
        GROUP BY u.id, u.username, u.email, u.profile_picture
        HAVING i_teach_them IS NOT NULL OR they_teach_me IS NOT NULL
    ");
    
    $this->db->bind(':current_user_id', $userId);
    $results = $this->db->resultSet();

    // Process results and calculate match scores
    $matches = [
        'perfect' => [],
        'great' => [],
        'good' => [],
    ];

    foreach ($results as $row) {
        $match = $this->processMatch($row);
        
        // Add connection status
        $match['connection_status'] = $row->connection_status ?? 'available';
        
        // Calculate match score
        $score = $this->calculateMatchScore($match);
        
        if ($score >= 100) {
            $matches['perfect'][] = $match;
        } elseif ($score >= 50) {
            $matches['great'][] = $match;
        } elseif ($score >= 30) {
            $matches['good'][] = $match;
        }
    }

return $matches;

    return $matches;
}

/**
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

    // Determine if mutual
    $match['is_mutual'] = !empty($match['i_teach']) && !empty($match['they_teach']);
    $match['total_skills'] = count($match['i_teach']) + count($match['they_teach']);

    return $match;
}

/**
 * Calculate match score (0-100+)
 */
private function calculateMatchScore($match) {
    $score = 0;

    // Base points for any match
    $score += 30;

    // MUTUAL MATCH BONUS (70 points) - THIS IS KEY!
    if ($match['is_mutual']) {
        $score += 70;
    }

    // Multiple skills bonus (up to 20 points)
    if ($match['total_skills'] >= 4) {
        $score += 20;
    } elseif ($match['total_skills'] >= 3) {
        $score += 15;
    } elseif ($match['total_skills'] >= 2) {
        $score += 10;
    }

    // Proficiency level bonus (up to 10 points)
    // Check if any skills have advanced-beginner pairing (great for teaching)
    foreach ($match['i_teach'] as $skill) {
        if ($skill['my_level'] === 'Advanced' && $skill['their_level'] === 'Beginner') {
            $score += 5;
            break;
        }
    }
    foreach ($match['they_teach'] as $skill) {
        if ($skill['their_level'] === 'Advanced' && $skill['my_level'] === 'Beginner') {
            $score += 5;
            break;
        }
    }

    return $score;
}

/**
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
            -- ADD COMMA HERE ↑ (line 8)
            CASE 
                WHEN EXISTS (
                    SELECT 1 FROM exchanges 
                    WHERE ((sender_id = :current_user_id AND receiver_id = u.id)
                        OR (sender_id = u.id AND receiver_id = :current_user_id))
                    AND status = 'pending'
                ) THEN 'pending'
                WHEN EXISTS (
                    SELECT 1 FROM exchanges 
                    WHERE ((sender_id = :current_user_id AND receiver_id = u.id)
                        OR (sender_id = u.id AND receiver_id = :current_user_id))
                    AND status = 'accepted'
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
                    WHERE ((sender_id = :current_user_id AND receiver_id = u.id)
                        OR (sender_id = u.id AND receiver_id = :current_user_id))
                    AND status = 'pending'
                ) THEN 'pending'
                WHEN EXISTS (
                    SELECT 1 FROM exchanges 
                    WHERE ((sender_id = :current_user_id AND receiver_id = u.id)
                        OR (sender_id = u.id AND receiver_id = :current_user_id))
                    AND status = 'accepted'
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
