-- Insert test applications into project_applications table
-- These applications are from users applying to projects owned by organization 37 (Pretty Software)

INSERT INTO `project_applications` (`project_id`, `user_id`, `message`, `status`, `applied_at`) VALUES
(7, 41, 'I have extensive experience with Flutter and NodeJS. I am very interested in contributing to the ZCode project!', 'pending', NOW()),
(8, 41, 'I am proficient in HTML5, CSS3, JavaScript, PHP and MySQL. I would love to work on CodeCollab Hub!', 'pending', NOW()),
(7, 5, 'I am enthusiastic about mobile app development and want to learn Flutter while contributing to ZCode.', 'pending', NOW()),
(9, 20, 'With my data science background, I believe I can contribute significantly to SkillMentor project.', 'pending', NOW()),
(10, 30, 'I have design skills and UI/UX experience. I am excited about the LearnLab project!', 'pending', NOW());

-- Verify the insertions
SELECT 
    pa.id,
    pa.project_id,
    p.name as project_name,
    u.username,
    u.email,
    pa.message,
    pa.status,
    pa.applied_at
FROM project_applications pa
JOIN projects p ON pa.project_id = p.id
JOIN users u ON pa.user_id = u.id
WHERE p.organization_id = 37
ORDER BY pa.applied_at DESC;
