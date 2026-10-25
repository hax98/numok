ALTER TABLE partners
    ADD COLUMN acquisition_utm_source VARCHAR(100) NULL,
    ADD COLUMN acquisition_utm_medium VARCHAR(100) NULL,
    ADD COLUMN acquisition_utm_campaign VARCHAR(100) NULL,
    ADD COLUMN acquisition_utm_content VARCHAR(100) NULL,
    ADD COLUMN acquisition_utm_term VARCHAR(100) NULL,
    ADD COLUMN acquisition_utm_id VARCHAR(100) NULL;
