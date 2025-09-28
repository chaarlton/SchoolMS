-- Remove duplicate IDs in escr_subjects, keeping one row per ID
CREATE TABLE temp_escr_subjects AS SELECT * FROM escr_subjects GROUP BY ID;
DELETE FROM escr_subjects;
INSERT INTO escr_subjects SELECT * FROM temp_escr_subjects;
DROP TABLE temp_escr_subjects;

-- Ensure ID columns are AUTO_INCREMENT for auto incrementing
ALTER TABLE `escr_subjects` MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `student_grades` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `student_login` MODIFY `ID` int(255) NOT NULL AUTO_INCREMENT;
ALTER TABLE `student_registrations` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `users` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- Add student_number column to student_login for unique formatted student numbers
ALTER TABLE `student_login` ADD COLUMN `student_number` VARCHAR(20) DEFAULT NULL AFTER `Student_Number`;

-- Add profile_picture column to student_login for storing profile image paths
ALTER TABLE `student_login` ADD COLUMN `profile_picture` VARCHAR(255) DEFAULT 'uploads/profiles/sample.png' AFTER `status`;

-- Create table for student number sequences
CREATE TABLE `student_number_sequences` (
  `year` VARCHAR(10) PRIMARY KEY,
  `last_number` INT NOT NULL DEFAULT 0
);
