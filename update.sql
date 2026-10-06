-- Run ONLY if you already imported the old database.sql
USE mnb_resort;
ALTER TABLE accommodations ADD COLUMN photo VARCHAR(255) NULL;
