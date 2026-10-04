CREATE TABLE IF NOT EXISTS creator_profiles (
 partner_id INT UNSIGNED PRIMARY KEY,
 firebase_uid VARCHAR(128) NULL UNIQUE,
 repostit_email VARCHAR(255) NULL,
 consent_at DATETIME NULL,
 account_snapshot JSON NULL,
 analytics_snapshot JSON NULL,
 synced_at DATETIME NULL,
 referrals_synced_at DATETIME NULL,
 FOREIGN KEY (partner_id) REFERENCES partners(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS creator_social_profiles (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 partner_id INT UNSIGNED NOT NULL,
 platform VARCHAR(32) NOT NULL,
 profile_url VARCHAR(500) NOT NULL,
 display_name VARCHAR(180) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY partner_profile (partner_id, profile_url),
 FOREIGN KEY (partner_id) REFERENCES partners(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS creator_content (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 partner_id INT UNSIGNED NOT NULL,
 partner_program_id INT UNSIGNED NOT NULL,
 token CHAR(24) NOT NULL UNIQUE,
 title VARCHAR(180) NOT NULL,
 platform VARCHAR(32) NOT NULL,
 kind VARCHAR(32) NOT NULL,
 post_url VARCHAR(500) NULL,
 status ENUM('planned','submitted','verified') NOT NULL DEFAULT 'planned',
 provider_attempt_id VARCHAR(180) NULL,
 provider_metrics JSON NULL,
 provider_checked_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 submitted_at DATETIME NULL,
 verified_at DATETIME NULL,
 FOREIGN KEY (partner_id) REFERENCES partners(id) ON DELETE CASCADE,
 FOREIGN KEY (partner_program_id) REFERENCES partner_programs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS creator_referrals (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 partner_id INT UNSIGNED NOT NULL,
 partner_program_id INT UNSIGNED NOT NULL,
 customer_hash CHAR(64) NOT NULL UNIQUE,
 stripe_customer_id VARCHAR(100) NULL,
 content_token VARCHAR(100) NULL,
 signed_up_at DATETIME NOT NULL,
 first_publish_at DATETIME NULL,
 FOREIGN KEY (partner_id) REFERENCES partners(id) ON DELETE CASCADE,
 FOREIGN KEY (partner_program_id) REFERENCES partner_programs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS creator_bonus_payouts (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 partner_id INT UNSIGNED NOT NULL,
 threshold INT UNSIGNED NOT NULL,
 amount DECIMAL(10,2) NOT NULL,
 status ENUM('earned','paid') NOT NULL DEFAULT 'earned',
 earned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 paid_at DATETIME NULL,
 payment_reference VARCHAR(180) NULL,
 paid_by INT UNSIGNED NULL,
 UNIQUE KEY partner_milestone (partner_id, threshold),
 FOREIGN KEY (partner_id) REFERENCES partners(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS creator_audit_log (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 actor_id INT UNSIGNED NOT NULL,
 partner_id INT UNSIGNED NOT NULL,
 action VARCHAR(60) NOT NULL,
 details JSON NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS creator_tracking_aliases (
 tracking_code VARCHAR(50) PRIMARY KEY,
 partner_program_id INT UNSIGNED NOT NULL,
 FOREIGN KEY (partner_program_id) REFERENCES partner_programs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS portal_sessions (
 id VARCHAR(128) CHARACTER SET ascii COLLATE ascii_bin PRIMARY KEY,
 data MEDIUMTEXT NOT NULL,
 expires_at DATETIME NOT NULL,
 INDEX session_expiry (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
