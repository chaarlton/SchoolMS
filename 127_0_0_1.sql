-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 24, 2025 at 03:57 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `escr_dbase`
--
CREATE DATABASE IF NOT EXISTS `escr_dbase` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `escr_dbase`;

-- --------------------------------------------------------

--
-- Table structure for table `escr_subjects`
--

CREATE TABLE `escr_subjects` (
  `subj_code` varchar(255) NOT NULL,
  `subj_desc` varchar(255) NOT NULL,
  `unit_count` int(255) NOT NULL,
  `level` varchar(255) NOT NULL,
  `ID` int(11) NOT NULL,
  `subj_sort` varchar(11) NOT NULL,
  `semester` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `escr_subjects`
--

INSERT INTO `escr_subjects` (`subj_code`, `subj_desc`, `unit_count`, `level`, `ID`, `subj_sort`, `semester`) VALUES
('USELF', 'Understanding Self', 3, '1ST YEAR', 0, 'IT', 1),
('HIST', 'Readings in Philippine History\r\n', 3, '1ST YEAR', 1, 'IT', 1),
('MATHMW\r\n', 'Mathematics in the Modern World\r\n', 3, '1ST YEAR', 2, 'IT', 1),
('ITINTRO\r\n', 'Introduction to Computing\r\n', 3, '1ST YEAR', 3, 'IT', 1),
('MS&T\r\n', 'Mathematics, Science and Technology\r\n', 3, '1ST YEAR', 4, 'IT', 1),
('PROG1\r\n', 'Computer Programming 1\r\n', 3, '1ST YEAR', 5, 'IT', 1),
('PE1\r\n', 'PATHFIT1- Movement Competency Training\r\n', 2, '1ST YEAR', 6, 'IT', 1),
('INFOMNGT', 'Information Management', 3, '2ND YEAR', 54, 'IT', 1),
('STECH&SOC', 'Science, Technology, and Society', 3, '2ND YEAR', 55, 'IT', 1),
('IT-APP', 'IT Application Tools in Business', 3, '2ND YEAR', 56, 'IT', 1),
('SYSARC', 'Systems Integration and Architecture', 3, '2ND YEAR', 57, 'IT', 1),
('NETWORK1', 'Networking 1', 3, '2ND YEAR', 58, 'IT', 1),
('PE 3', 'PATHFIT 3 - Traditional and Recreational Games', 2, '2ND YEAR', 59, 'IT', 1),
('NSTP 1', 'National Service Training Program 1', 3, '2ND YEAR', 60, 'IT', 1),
('APPDEV', 'Applications Development and Emerging Technologies', 3, '3RD YEAR', 61, 'IT', 1),
('WEBPROG1', 'Web Development and Programming 1', 3, '3RD YEAR', 62, 'IT', 1),
('SYSAD', 'Systems Administration and Maintenance', 3, '3RD YEAR', 63, 'IT', 1),
('CWORLD', 'The Contemporary World', 3, '3RD YEAR', 64, 'IT', 1),
('A&H', 'Arts and Humanities', 3, '3RD YEAR', 65, 'IT', 1),
('Rizal', 'Life and Works of Rizal', 3, '3RD YEAR', 66, 'IT', 1),
('OJT', 'Practicum (540Hrs)', 9, '4TH YEAR', 67, 'IT', 1),
('CAPSTONE1', 'Capstone Project  1', 6, '4TH YEAR', 69, 'IT', 1);

-- --------------------------------------------------------

--
-- Table structure for table `student_grades`
--

CREATE TABLE `student_grades` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `registration_id` int(11) DEFAULT NULL,
  `subject_id` int(11) NOT NULL,
  `grade` varchar(10) DEFAULT NULL,
  `date_recorded` timestamp NOT NULL DEFAULT current_timestamp(),
  `semester` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_grades`
--

INSERT INTO `student_grades` (`id`, `student_id`, `registration_id`, `subject_id`, `grade`, `date_recorded`, `semester`) VALUES
(2, 1, NULL, 65, '1', '2025-09-09 07:58:33', 1),
(3, 1, NULL, 61, '1.5', '2025-09-09 07:58:33', 1),
(4, 1, NULL, 64, '2', '2025-09-09 07:58:33', 1),
(5, 1, NULL, 66, '2', '2025-09-09 07:58:33', 1),
(6, 1, NULL, 63, '1', '2025-09-09 07:58:33', 1),
(7, 1, NULL, 62, '1.5', '2025-09-09 07:58:33', 1),
(14, 1, NULL, 1, '0', '2025-09-11 02:19:58', 1),
(15, 1, NULL, 3, '0', '2025-09-11 02:19:58', 1),
(16, 1, NULL, 2, '0', '2025-09-11 02:19:58', 1),
(17, 1, NULL, 4, '0', '2025-09-11 02:19:58', 1),
(18, 1, NULL, 6, '0', '2025-09-11 02:19:58', 1),
(19, 1, NULL, 5, '0', '2025-09-11 02:19:58', 1),
(20, 1, NULL, 0, '0', '2025-09-11 02:19:58', 1),
(35, 1, NULL, 54, '1', '2025-09-24 01:28:22', 1),
(36, 1, NULL, 56, '1.25', '2025-09-24 01:28:22', 1),
(37, 1, NULL, 58, '3', '2025-09-24 01:28:22', 1),
(38, 1, NULL, 60, '2.25', '2025-09-24 01:28:22', 1),
(39, 1, NULL, 59, '5', '2025-09-24 01:28:22', 1),
(40, 1, NULL, 55, '7', '2025-09-24 01:28:22', 1),
(41, 1, NULL, 57, '1', '2025-09-24 01:28:22', 1);

-- --------------------------------------------------------

--
-- Table structure for table `student_login`
--

CREATE TABLE `student_login` (
  `ID` int(255) NOT NULL,
  `L_name` varchar(99) NOT NULL,
  `F_name` varchar(99) NOT NULL,
  `M_name` varchar(99) NOT NULL,
  `age` int(255) NOT NULL,
  `bday` varchar(255) NOT NULL,
  `pbirth` varchar(255) NOT NULL,
  `gender` varchar(255) NOT NULL,
  `marital` varchar(255) NOT NULL,
  `yr_lvl` varchar(255) NOT NULL,
  `crs` varchar(255) NOT NULL,
  `street` varchar(255) NOT NULL,
  `brgy` varchar(255) NOT NULL,
  `city_mun` varchar(255) NOT NULL,
  `Date` date NOT NULL DEFAULT current_timestamp(),
  `Student_Number` varchar(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `Username` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_login`
--

INSERT INTO `student_login` (`ID`, `L_name`, `F_name`, `M_name`, `age`, `bday`, `pbirth`, `gender`, `marital`, `yr_lvl`, `crs`, `street`, `brgy`, `city_mun`, `Date`, `Student_Number`, `status`, `Username`, `Password`) VALUES
(1, 'hernandez', 'charlton', 'faustinorio', 0, '2002-06-02', 'binangonan rizal', 'MALE', 'SINGLE', '2ND YEAR COLLEGE', 'INFORMATION TECHNOLOGY (BSIT)', 'sto nino', 'bilibiran', 'binangonan', '2025-09-09', 'A25-IT-0002', 'ENROLLED', '', ''),
(6, 'san jose', 'juan carlo', 'Di ko alam', 0, '2003-10-28', 'cardona', 'MALE', 'SINGLE', '1ST YEAR COLLEGE', 'INFORMATION TECHNOLOGY (BSIT)', 'teST', 'Test', 'teST', '2025-09-09', NULL, 'REGULAR', '', '');

--
-- Triggers `student_login`
--
DELIMITER $$
CREATE TRIGGER `set_student_status` BEFORE INSERT ON `student_login` FOR EACH ROW BEGIN
    -- If the student is college (1st to 4th year), mark as REGULAR
    IF NEW.yr_lvl IN ('1ST YEAR COLLEGE', '2ND YEAR COLLEGE', '3RD YEAR COLLEGE', '4TH YEAR COLLEGE') THEN
        SET NEW.status = 'REGULAR';
    ELSE
        SET NEW.status = 'IRREGULAR';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `student_registrations`
--

CREATE TABLE `student_registrations` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `academic_year` varchar(9) NOT NULL,
  `semester` enum('1','2') NOT NULL,
  `year_level` enum('1ST YEAR','2ND YEAR','3RD YEAR','4TH YEAR') NOT NULL,
  `status` enum('ENROLLED','COMPLETED','DROPPED','CANCELLED') DEFAULT 'ENROLLED',
  `date_registered` timestamp NOT NULL DEFAULT current_timestamp(),
  `course` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_registrations`
--

INSERT INTO `student_registrations` (`id`, `student_id`, `academic_year`, `semester`, `year_level`, `status`, `date_registered`, `course`) VALUES
(2, 1, '2025-2026', '1', '2ND YEAR', 'ENROLLED', '2025-09-15 02:55:02', 'INFORMATION TECHNOLOGY (BSIT)');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `temp_password` varchar(255) DEFAULT NULL,
  `role` enum('admin','student') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `student_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `temp_password`, `role`, `created_at`, `student_id`) VALUES
(0, 'admin', 'Admin123', NULL, 'admin', '2025-09-08 14:31:04', 0),
(17, 'chernandez', 'GSrW7NZ9', NULL, 'student', '2025-09-15 02:55:02', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `escr_subjects`
--
ALTER TABLE `escr_subjects`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `student_grades`
--
ALTER TABLE `student_grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`,`subject_id`),
  ADD UNIQUE KEY `student_subject_semester_unique` (`student_id`,`subject_id`,`semester`),
  ADD KEY `fk_subject` (`subject_id`),
  ADD KEY `fk_grades_registration` (`registration_id`);

--
-- Indexes for table `student_login`
--
ALTER TABLE `student_login`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Student_Number` (`Student_Number`),
  ADD UNIQUE KEY `Student_Number_3` (`Student_Number`),
  ADD UNIQUE KEY `Student_Number_5` (`Student_Number`),
  ADD KEY `Student_Number_2` (`Student_Number`);
ALTER TABLE `student_login` ADD FULLTEXT KEY `Student_Number_4` (`Student_Number`);

--
-- Indexes for table `student_registrations`
--
ALTER TABLE `student_registrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_registration_student` (`student_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `escr_subjects`
--
ALTER TABLE `escr_subjects`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `student_grades`
--
ALTER TABLE `student_grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `student_login`
--
ALTER TABLE `student_login`
  MODIFY `ID` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `student_registrations`
--
ALTER TABLE `student_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `student_grades`
--
ALTER TABLE `student_grades`
  ADD CONSTRAINT `fk_grades_registration` FOREIGN KEY (`registration_id`) REFERENCES `student_registrations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_student` FOREIGN KEY (`student_id`) REFERENCES `student_login` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_subject` FOREIGN KEY (`subject_id`) REFERENCES `escr_subjects` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `student_registrations`
--
ALTER TABLE `student_registrations`
  ADD CONSTRAINT `fk_registration_student` FOREIGN KEY (`student_id`) REFERENCES `student_login` (`ID`);
--
-- Database: `phpmyadmin`
--
CREATE DATABASE IF NOT EXISTS `phpmyadmin` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `phpmyadmin`;

-- --------------------------------------------------------

--
-- Table structure for table `pma__bookmark`
--

CREATE TABLE `pma__bookmark` (
  `id` int(10) UNSIGNED NOT NULL,
  `dbase` varchar(255) NOT NULL DEFAULT '',
  `user` varchar(255) NOT NULL DEFAULT '',
  `label` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `query` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Bookmarks';

-- --------------------------------------------------------

--
-- Table structure for table `pma__central_columns`
--

CREATE TABLE `pma__central_columns` (
  `db_name` varchar(64) NOT NULL,
  `col_name` varchar(64) NOT NULL,
  `col_type` varchar(64) NOT NULL,
  `col_length` text DEFAULT NULL,
  `col_collation` varchar(64) NOT NULL,
  `col_isNull` tinyint(1) NOT NULL,
  `col_extra` varchar(255) DEFAULT '',
  `col_default` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Central list of columns';

-- --------------------------------------------------------

--
-- Table structure for table `pma__column_info`
--

CREATE TABLE `pma__column_info` (
  `id` int(5) UNSIGNED NOT NULL,
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `column_name` varchar(64) NOT NULL DEFAULT '',
  `comment` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `mimetype` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `transformation` varchar(255) NOT NULL DEFAULT '',
  `transformation_options` varchar(255) NOT NULL DEFAULT '',
  `input_transformation` varchar(255) NOT NULL DEFAULT '',
  `input_transformation_options` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Column information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__designer_settings`
--

CREATE TABLE `pma__designer_settings` (
  `username` varchar(64) NOT NULL,
  `settings_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Settings related to Designer';

-- --------------------------------------------------------

--
-- Table structure for table `pma__export_templates`
--

CREATE TABLE `pma__export_templates` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL,
  `export_type` varchar(10) NOT NULL,
  `template_name` varchar(64) NOT NULL,
  `template_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved export templates';

-- --------------------------------------------------------

--
-- Table structure for table `pma__favorite`
--

CREATE TABLE `pma__favorite` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Favorite tables';

-- --------------------------------------------------------

--
-- Table structure for table `pma__history`
--

CREATE TABLE `pma__history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db` varchar(64) NOT NULL DEFAULT '',
  `table` varchar(64) NOT NULL DEFAULT '',
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp(),
  `sqlquery` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='SQL history for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__navigationhiding`
--

CREATE TABLE `pma__navigationhiding` (
  `username` varchar(64) NOT NULL,
  `item_name` varchar(64) NOT NULL,
  `item_type` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Hidden items of navigation tree';

-- --------------------------------------------------------

--
-- Table structure for table `pma__pdf_pages`
--

CREATE TABLE `pma__pdf_pages` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `page_nr` int(10) UNSIGNED NOT NULL,
  `page_descr` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='PDF relation pages for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__recent`
--

CREATE TABLE `pma__recent` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Recently accessed tables';

--
-- Dumping data for table `pma__recent`
--

INSERT INTO `pma__recent` (`username`, `tables`) VALUES
('root', '[{\"db\":\"escr_dbase\",\"table\":\"users\"},{\"db\":\"escr_dbase\",\"table\":\"student_login\"},{\"db\":\"escr_dbase\",\"table\":\"Student_Login\"}]');

-- --------------------------------------------------------

--
-- Table structure for table `pma__relation`
--

CREATE TABLE `pma__relation` (
  `master_db` varchar(64) NOT NULL DEFAULT '',
  `master_table` varchar(64) NOT NULL DEFAULT '',
  `master_field` varchar(64) NOT NULL DEFAULT '',
  `foreign_db` varchar(64) NOT NULL DEFAULT '',
  `foreign_table` varchar(64) NOT NULL DEFAULT '',
  `foreign_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Relation table';

-- --------------------------------------------------------

--
-- Table structure for table `pma__savedsearches`
--

CREATE TABLE `pma__savedsearches` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `search_name` varchar(64) NOT NULL DEFAULT '',
  `search_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved searches';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_coords`
--

CREATE TABLE `pma__table_coords` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `pdf_page_number` int(11) NOT NULL DEFAULT 0,
  `x` float UNSIGNED NOT NULL DEFAULT 0,
  `y` float UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table coordinates for phpMyAdmin PDF output';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_info`
--

CREATE TABLE `pma__table_info` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `display_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_uiprefs`
--

CREATE TABLE `pma__table_uiprefs` (
  `username` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `prefs` text NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Tables'' UI preferences';

--
-- Dumping data for table `pma__table_uiprefs`
--

INSERT INTO `pma__table_uiprefs` (`username`, `db_name`, `table_name`, `prefs`, `last_update`) VALUES
('root', 'escr_dbase', 'student_login', '{\"CREATE_TIME\":\"2025-09-08 21:52:55\"}', '2025-09-08 14:05:30');

-- --------------------------------------------------------

--
-- Table structure for table `pma__tracking`
--

CREATE TABLE `pma__tracking` (
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `version` int(10) UNSIGNED NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `schema_snapshot` text NOT NULL,
  `schema_sql` text DEFAULT NULL,
  `data_sql` longtext DEFAULT NULL,
  `tracking` set('UPDATE','REPLACE','INSERT','DELETE','TRUNCATE','CREATE DATABASE','ALTER DATABASE','DROP DATABASE','CREATE TABLE','ALTER TABLE','RENAME TABLE','DROP TABLE','CREATE INDEX','DROP INDEX','CREATE VIEW','ALTER VIEW','DROP VIEW') DEFAULT NULL,
  `tracking_active` int(1) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Database changes tracking for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__userconfig`
--

CREATE TABLE `pma__userconfig` (
  `username` varchar(64) NOT NULL,
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `config_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User preferences storage for phpMyAdmin';

--
-- Dumping data for table `pma__userconfig`
--

INSERT INTO `pma__userconfig` (`username`, `timevalue`, `config_data`) VALUES
('root', '2025-09-08 14:24:01', '{\"Console\\/Mode\":\"collapse\"}');

-- --------------------------------------------------------

--
-- Table structure for table `pma__usergroups`
--

CREATE TABLE `pma__usergroups` (
  `usergroup` varchar(64) NOT NULL,
  `tab` varchar(64) NOT NULL,
  `allowed` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User groups with configured menu items';

-- --------------------------------------------------------

--
-- Table structure for table `pma__users`
--

CREATE TABLE `pma__users` (
  `username` varchar(64) NOT NULL,
  `usergroup` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Users and their assignments to user groups';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pma__central_columns`
--
ALTER TABLE `pma__central_columns`
  ADD PRIMARY KEY (`db_name`,`col_name`);

--
-- Indexes for table `pma__column_info`
--
ALTER TABLE `pma__column_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `db_name` (`db_name`,`table_name`,`column_name`);

--
-- Indexes for table `pma__designer_settings`
--
ALTER TABLE `pma__designer_settings`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_user_type_template` (`username`,`export_type`,`template_name`);

--
-- Indexes for table `pma__favorite`
--
ALTER TABLE `pma__favorite`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__history`
--
ALTER TABLE `pma__history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`,`db`,`table`,`timevalue`);

--
-- Indexes for table `pma__navigationhiding`
--
ALTER TABLE `pma__navigationhiding`
  ADD PRIMARY KEY (`username`,`item_name`,`item_type`,`db_name`,`table_name`);

--
-- Indexes for table `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  ADD PRIMARY KEY (`page_nr`),
  ADD KEY `db_name` (`db_name`);

--
-- Indexes for table `pma__recent`
--
ALTER TABLE `pma__recent`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__relation`
--
ALTER TABLE `pma__relation`
  ADD PRIMARY KEY (`master_db`,`master_table`,`master_field`),
  ADD KEY `foreign_field` (`foreign_db`,`foreign_table`);

--
-- Indexes for table `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_savedsearches_username_dbname` (`username`,`db_name`,`search_name`);

--
-- Indexes for table `pma__table_coords`
--
ALTER TABLE `pma__table_coords`
  ADD PRIMARY KEY (`db_name`,`table_name`,`pdf_page_number`);

--
-- Indexes for table `pma__table_info`
--
ALTER TABLE `pma__table_info`
  ADD PRIMARY KEY (`db_name`,`table_name`);

--
-- Indexes for table `pma__table_uiprefs`
--
ALTER TABLE `pma__table_uiprefs`
  ADD PRIMARY KEY (`username`,`db_name`,`table_name`);

--
-- Indexes for table `pma__tracking`
--
ALTER TABLE `pma__tracking`
  ADD PRIMARY KEY (`db_name`,`table_name`,`version`);

--
-- Indexes for table `pma__userconfig`
--
ALTER TABLE `pma__userconfig`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__usergroups`
--
ALTER TABLE `pma__usergroups`
  ADD PRIMARY KEY (`usergroup`,`tab`,`allowed`);

--
-- Indexes for table `pma__users`
--
ALTER TABLE `pma__users`
  ADD PRIMARY KEY (`username`,`usergroup`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__column_info`
--
ALTER TABLE `pma__column_info`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__history`
--
ALTER TABLE `pma__history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  MODIFY `page_nr` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Database: `test`
--
CREATE DATABASE IF NOT EXISTS `test` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `test`;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
