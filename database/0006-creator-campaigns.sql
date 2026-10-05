CREATE TABLE IF NOT EXISTS creator_campaigns (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 partner_id INT UNSIGNED NOT NULL,
 partner_program_id INT UNSIGNED NOT NULL,
 title VARCHAR(180) NOT NULL,
 brief TEXT NOT NULL,
 deliverables JSON NOT NULL,
 due_at DATETIME NOT NULL,
 status ENUM('proposed','accepted','declined','completed','cancelled') NOT NULL DEFAULT 'proposed',
 accepted_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 created_by INT UNSIGNED NOT NULL,
 INDEX creator_campaign_partner (partner_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS creator_action_queue (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 partner_id INT UNSIGNED NOT NULL,
 action_key VARCHAR(100) NOT NULL,
 message VARCHAR(500) NOT NULL,
 destination VARCHAR(100) NOT NULL,
 audience ENUM('creator','admin') NOT NULL,
 resolved_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY creator_action_dedupe (partner_id,action_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
