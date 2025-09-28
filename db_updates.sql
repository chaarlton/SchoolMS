-- Add student_type column to student_login table
ALTER TABLE `student_login` ADD COLUMN `student_type` ENUM('new','transferee','old') DEFAULT 'new' AFTER `crs`;

-- Add enrollment_type column to student_registrations table
ALTER TABLE `student_registrations` ADD COLUMN `enrollment_type` ENUM('regular','irregular') DEFAULT 'regular' AFTER `status`;

-- Add academic_year column to student_grades table to distinguish subjects across years
ALTER TABLE `student_grades` ADD COLUMN `academic_year` VARCHAR(20) NOT NULL DEFAULT '' AFTER `student_id`;
