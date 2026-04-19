ALTER TABLE notifications
    ADD COLUMN target_url VARCHAR(255) NULL AFTER message,
    ADD COLUMN entity_type VARCHAR(50) NULL AFTER target_url,
    ADD COLUMN entity_id INT(11) NULL AFTER entity_type,
    ADD COLUMN actor_user_id INT(11) NULL AFTER entity_id;