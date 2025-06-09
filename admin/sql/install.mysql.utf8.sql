DROP TABLE IF EXISTS `#__batenstein_registrations`;
-- Create table for storing registrations with health information
CREATE TABLE IF NOT EXISTS `#__batenstein_registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  
  -- Personal Information
  `first_name` varchar(255) NOT NULL,
  `calling_name` varchar(255) NOT NULL,
  `name_prefix` varchar(50) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `postal_code_city` varchar(255) NOT NULL,
  `birth_date` date NOT NULL,
  `birth_place` varchar(255) NOT NULL,
  `scout_section` enum('welpen','scouts','explorers','stam','sikas','plus') NOT NULL DEFAULT 'welpen',
  
  -- Personal Contact (for Scouts/Explorers)
  `phone_number` varchar(50) DEFAULT NULL,
  `email_address` varchar(255) DEFAULT NULL,
  
  -- Parent/Guardian Contact Details
  `parent1_name` varchar(255) NOT NULL,
  `parent1_phone_number` varchar(50) NOT NULL,
  `parent1_email_address` varchar(255) NOT NULL,
  `parent2_name` varchar(255) DEFAULT NULL,
  `parent2_phone_number` varchar(50) DEFAULT NULL,
  `parent2_email_address` varchar(255) DEFAULT NULL,
  
  -- Payment Information
  `iban` varchar(50) NOT NULL,
  `account_name` varchar(255) NOT NULL,
  `sign_date` date NOT NULL,
  
  -- Image/Video Permissions
  `images_website` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `images_social` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `images_newspaper` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  
  -- Health Information
  `can_swim` enum('Yes','No') NOT NULL DEFAULT 'No',
  `swim_diplomas` varchar(500) DEFAULT NULL,
  
  -- Emergency Contact
  `emergency_contact_name` varchar(255) NOT NULL,
  `emergency_contact_relation` varchar(255) NOT NULL,
  `emergency_contact_phone` varchar(50) NOT NULL,
  
  -- Medical Information
  `special_health_care` enum('Yes','No') NOT NULL DEFAULT 'No',
  `special_health_care_details` text DEFAULT NULL,
  `medication` enum('Yes','No') NOT NULL DEFAULT 'No',
  `medication_details` text DEFAULT NULL,
  `allergies` enum('Yes','No') NOT NULL DEFAULT 'No',
  `allergies_details` text DEFAULT NULL,
  `diet` enum('Yes','No') NOT NULL DEFAULT 'No',
  `diet_details` text DEFAULT NULL,
  
  -- Insurance and Medical Contacts
  `health_insurance` varchar(255) NOT NULL,
  `policy_number` varchar(100) NOT NULL,
  `gp_name` varchar(255) NOT NULL,
  `gp_address` varchar(500) NOT NULL,
  `gp_phone` varchar(50) NOT NULL,
  `dentist_name` varchar(255) DEFAULT NULL,
  `dentist_address` varchar(500) DEFAULT NULL,
  `dentist_phone` varchar(50) DEFAULT NULL,
  
  -- Emergency Treatment Consent
  `emergency_treatment_consent` enum('Yes','No') NOT NULL DEFAULT 'No',
  
  -- Additional Information
  `comments` text DEFAULT NULL,
  
  -- System fields
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;