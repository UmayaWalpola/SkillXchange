INSERT INTO skills (skill_name, description, created_at)
SELECT 'web-development', 'Build websites and web applications.', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM skills WHERE LOWER(skill_name) = LOWER('web-development')
);

INSERT INTO skills (skill_name, description, created_at)
SELECT 'graphic-design', 'Create visual designs, branding, and digital assets.', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM skills WHERE LOWER(skill_name) = LOWER('graphic-design')
);

INSERT INTO skills (skill_name, description, created_at)
SELECT 'photography', 'Capture and edit photos for creative or professional use.', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM skills WHERE LOWER(skill_name) = LOWER('photography')
);

INSERT INTO skills (skill_name, description, created_at)
SELECT 'cooking', 'Learn food preparation, recipes, and kitchen techniques.', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM skills WHERE LOWER(skill_name) = LOWER('cooking')
);

INSERT INTO skills (skill_name, description, created_at)
SELECT 'language-spanish', 'Practice Spanish language speaking, listening, and writing.', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM skills WHERE LOWER(skill_name) = LOWER('language-spanish')
);

INSERT INTO skills (skill_name, description, created_at)
SELECT 'language-french', 'Practice French language speaking, listening, and writing.', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM skills WHERE LOWER(skill_name) = LOWER('language-french')
);

INSERT INTO skills (skill_name, description, created_at)
SELECT 'music-guitar', 'Play guitar and improve technique, rhythm, and chords.', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM skills WHERE LOWER(skill_name) = LOWER('music-guitar')
);

INSERT INTO skills (skill_name, description, created_at)
SELECT 'music-piano', 'Learn piano fundamentals, songs, and performance skills.', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM skills WHERE LOWER(skill_name) = LOWER('music-piano')
);

INSERT INTO skills (skill_name, description, created_at)
SELECT 'yoga', 'Develop yoga practice, mobility, and mindfulness.', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM skills WHERE LOWER(skill_name) = LOWER('yoga')
);

INSERT INTO skills (skill_name, description, created_at)
SELECT 'fitness', 'Improve exercise routines, fitness planning, and wellness habits.', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM skills WHERE LOWER(skill_name) = LOWER('fitness')
);

INSERT INTO skills (skill_name, description, created_at)
SELECT 'writing', 'Strengthen writing for creative, academic, or professional goals.', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM skills WHERE LOWER(skill_name) = LOWER('writing')
);

INSERT INTO skills (skill_name, description, created_at)
SELECT 'marketing', 'Learn digital marketing, promotion, and audience growth.', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM skills WHERE LOWER(skill_name) = LOWER('marketing')
);

INSERT INTO skills (skill_name, description, created_at)
SELECT 'data-science', 'Analyze data and build machine learning or analytics workflows.', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM skills WHERE LOWER(skill_name) = LOWER('data-science')
);

INSERT INTO skills (skill_name, description, created_at)
SELECT 'video-editing', 'Edit video content for storytelling, social media, or production.', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM skills WHERE LOWER(skill_name) = LOWER('video-editing')
);
