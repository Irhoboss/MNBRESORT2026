-- Run only if your existing users table has neither photo nor profile_photo.
USE mnb_resort;
ALTER TABLE users ADD COLUMN profile_photo VARCHAR(255) NULL;
