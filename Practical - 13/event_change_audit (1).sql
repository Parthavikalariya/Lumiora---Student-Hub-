-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 18, 2026 at 09:32 AM
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
-- Database: `studenthub_pr8_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `event_change_audit`
--

CREATE TABLE `event_change_audit` (
  `audit_id` bigint(20) UNSIGNED NOT NULL,
  `event_id` int(10) UNSIGNED NOT NULL,
  `admin_user_id` int(10) UNSIGNED NOT NULL,
  `action` enum('created','updated','archived','restored','opened','closed') NOT NULL,
  `details` varchar(220) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event_change_audit`
--

INSERT INTO `event_change_audit` (`audit_id`, `event_id`, `admin_user_id`, `action`, `details`, `created_at`) VALUES
(1, 10, 2, 'created', 'Event created by admin', '2026-10-13 04:52:13'),
(2, 10, 2, 'updated', 'Event details updated', '2026-10-13 04:53:12'),
(3, 10, 2, 'updated', 'Event details updated', '2026-10-13 04:53:51'),
(4, 8, 2, 'opened', 'Admin changed event status', '2026-10-13 05:01:14'),
(5, 8, 2, 'closed', 'Admin changed event status', '2026-10-13 05:02:02'),
(6, 10, 2, 'archived', 'Admin changed event status', '2026-10-13 05:03:05'),
(7, 10, 2, 'restored', 'Admin changed event status', '2026-10-13 05:04:19'),
(8, 11, 2, 'created', 'Event created through Live Event Studio', '2026-10-17 11:18:18'),
(9, 11, 2, 'updated', 'Event updated through Live Event Studio', '2026-10-17 11:20:54'),
(10, 11, 2, 'archived', 'Soft delete through Live Event Studio', '2026-10-17 11:24:17'),
(11, 11, 2, 'restored', 'Restored through Live Event Studio', '2026-10-17 11:32:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `event_change_audit`
--
ALTER TABLE `event_change_audit`
  ADD PRIMARY KEY (`audit_id`),
  ADD KEY `fk_event_audit_event` (`event_id`),
  ADD KEY `fk_event_audit_admin` (`admin_user_id`),
  ADD KEY `idx_event_audit_created` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `event_change_audit`
--
ALTER TABLE `event_change_audit`
  MODIFY `audit_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `event_change_audit`
--
ALTER TABLE `event_change_audit`
  ADD CONSTRAINT `fk_event_audit_admin` FOREIGN KEY (`admin_user_id`) REFERENCES `user_accounts` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_event_audit_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
