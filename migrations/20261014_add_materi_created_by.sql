ALTER TABLE materi
    ADD COLUMN created_by INT NULL,
    ADD INDEX idx_materi_created_by (created_by),
    ADD CONSTRAINT fk_materi_created_by
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON DELETE SET NULL;
