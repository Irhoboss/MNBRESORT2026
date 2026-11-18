ALTER TABLE users MODIFY user_role ENUM('Guest','Staff','Admin','Housekeeping') NOT NULL DEFAULT 'Guest';

ALTER TABLE accommodations
  ADD COLUMN IF NOT EXISTS operational_status ENUM('Available','Occupied','Dirty','Cleaning','Under Inspection','Under Maintenance') NOT NULL DEFAULT 'Available',
  ADD COLUMN IF NOT EXISTS maintenance_notes VARCHAR(500) NULL;

CREATE TABLE IF NOT EXISTS housekeeping_tasks (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  task_code VARCHAR(30) NOT NULL,
  room_id INT NOT NULL,
  assigned_staff_id INT NULL,
  created_by INT NOT NULL,
  reservation_id INT NULL,
  task_category VARCHAR(80) NOT NULL,
  description VARCHAR(500) NULL,
  priority ENUM('Low','Normal','High','Urgent') NOT NULL DEFAULT 'Normal',
  status ENUM('Pending','Assigned','Accepted','In Progress','Paused','Completed','Awaiting Inspection','Approved','Rejected','Cancelled') NOT NULL DEFAULT 'Pending',
  assigned_at TIMESTAMP NULL,
  accepted_at TIMESTAMP NULL,
  started_at TIMESTAMP NULL,
  paused_at TIMESTAMP NULL,
  resumed_at TIMESTAMP NULL,
  completed_at TIMESTAMP NULL,
  expected_completion_at DATETIME NULL,
  cleaning_notes TEXT NULL,
  cancellation_reason VARCHAR(500) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_housekeeping_task_code (task_code),
  KEY idx_housekeeping_room (room_id),
  KEY idx_housekeeping_staff_status (assigned_staff_id,status),
  KEY idx_housekeeping_reservation (reservation_id),
  CONSTRAINT fk_housekeeping_task_room FOREIGN KEY (room_id) REFERENCES accommodations(room_id) ON DELETE RESTRICT,
  CONSTRAINT fk_housekeeping_task_staff FOREIGN KEY (assigned_staff_id) REFERENCES users(user_id) ON DELETE SET NULL,
  CONSTRAINT fk_housekeeping_task_creator FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE RESTRICT,
  CONSTRAINT fk_housekeeping_task_reservation FOREIGN KEY (reservation_id) REFERENCES reservations(reservation_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS housekeeping_checklist_items (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category VARCHAR(80) NOT NULL,
  item_name VARCHAR(160) NOT NULL,
  description VARCHAR(500) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_housekeeping_checklist_item (category,item_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS housekeeping_inspections (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  housekeeping_task_id INT UNSIGNED NOT NULL,
  room_id INT NOT NULL,
  inspected_by INT NULL,
  inspection_result ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  inspection_notes TEXT NULL,
  rejection_reason VARCHAR(500) NULL,
  inspected_at TIMESTAMP NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_housekeeping_task_inspection (housekeeping_task_id),
  KEY idx_housekeeping_inspection_room (room_id),
  CONSTRAINT fk_housekeeping_inspection_task FOREIGN KEY (housekeeping_task_id) REFERENCES housekeeping_tasks(id) ON DELETE CASCADE,
  CONSTRAINT fk_housekeeping_inspection_room FOREIGN KEY (room_id) REFERENCES accommodations(room_id) ON DELETE RESTRICT,
  CONSTRAINT fk_housekeeping_inspection_user FOREIGN KEY (inspected_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS housekeeping_checklist_results (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  inspection_id INT UNSIGNED NOT NULL,
  checklist_item_id INT UNSIGNED NOT NULL,
  result ENUM('Passed','Failed') NOT NULL,
  remarks VARCHAR(500) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_housekeeping_result (inspection_id,checklist_item_id),
  KEY idx_housekeeping_result_item (checklist_item_id),
  CONSTRAINT fk_housekeeping_result_inspection FOREIGN KEY (inspection_id) REFERENCES housekeeping_inspections(id) ON DELETE CASCADE,
  CONSTRAINT fk_housekeeping_result_item FOREIGN KEY (checklist_item_id) REFERENCES housekeeping_checklist_items(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS housekeeping_activity_logs (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT NULL,
  housekeeping_task_id INT UNSIGNED NULL,
  room_id INT NULL,
  action VARCHAR(80) NOT NULL,
  description VARCHAR(500) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_housekeeping_activity_user (user_id),
  KEY idx_housekeeping_activity_task (housekeeping_task_id),
  KEY idx_housekeeping_activity_room (room_id),
  CONSTRAINT fk_housekeeping_activity_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
  CONSTRAINT fk_housekeeping_activity_task FOREIGN KEY (housekeeping_task_id) REFERENCES housekeeping_tasks(id) ON DELETE SET NULL,
  CONSTRAINT fk_housekeeping_activity_room FOREIGN KEY (room_id) REFERENCES accommodations(room_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE notifications
  ADD COLUMN IF NOT EXISTS room_id INT NULL,
  ADD COLUMN IF NOT EXISTS housekeeping_task_id INT UNSIGNED NULL,
  ADD KEY IF NOT EXISTS idx_notification_room (room_id),
  ADD KEY IF NOT EXISTS idx_notification_housekeeping_task (housekeeping_task_id);

INSERT IGNORE INTO housekeeping_checklist_items(category,item_name,description,is_active) VALUES
('Bedroom','Bed properly arranged','Bed is neat and correctly arranged.',1),
('Bedroom','Clean bedsheets and pillowcases','Linens are clean and free of stains.',1),
('Bedroom','Floor properly cleaned','Floor is swept and mopped.',1),
('Bedroom','Furniture dust-free','All furniture surfaces are dust-free.',1),
('Bedroom','Trash bins emptied','Trash bins are empty and relined.',1),
('Bathroom','Toilet properly sanitized','Toilet is cleaned and sanitized.',1),
('Bathroom','Sink and mirrors cleaned','Sink and mirrors have no visible dirt.',1),
('Bathroom','Shower area cleaned','Shower area is cleaned and drained.',1),
('Bathroom','Towels replaced','Fresh towels are available.',1),
('Bathroom','Bathroom supplies replenished','Toilet paper and bathroom supplies are stocked.',1),
('Room Amenities','Air conditioning functional','Air conditioning is operational.',1),
('Room Amenities','Television functional','Television is operational.',1),
('Room Amenities','Remote controls available','Required remote controls are present.',1),
('Room Amenities','Drinking water replenished','Drinking water is replenished.',1),
('Overall Room Condition','No unpleasant odor','The room has no unpleasant odor.',1),
('Overall Room Condition','No visible dirt or stains','No visible dirt or stains remain.',1),
('Overall Room Condition','No damaged furniture','Furniture is intact and undamaged.',1),
('Overall Room Condition','Room ready for occupancy','Room is arranged and ready for guests.',1);
