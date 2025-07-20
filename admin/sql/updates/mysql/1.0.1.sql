-- Update script for version 1.0.1
-- Add payment acknowledgment field

ALTER TABLE `#__batenstein_registrations` 
ADD COLUMN `payment_acknowledgment` enum('Yes','No') NOT NULL DEFAULT 'No' 
AFTER `sign_date`;
