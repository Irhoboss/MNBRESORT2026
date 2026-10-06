-- Run once on existing databases to store room photos directly in the accommodations table.
USE mnb_resort;
ALTER TABLE accommodations MODIFY photo MEDIUMTEXT NULL;
