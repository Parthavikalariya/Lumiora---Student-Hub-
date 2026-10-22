-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 22, 2026 at 07:15 AM
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
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` bigint(20) UNSIGNED NOT NULL,
  `admin_user_id` int(10) UNSIGNED NOT NULL,
  `action` varchar(60) NOT NULL,
  `target_type` varchar(40) NOT NULL,
  `target_id` int(10) UNSIGNED DEFAULT NULL,
  `details` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`log_id`, `admin_user_id`, `action`, `target_type`, `target_id`, `details`, `created_at`) VALUES
(1, 2, 'admin_login', 'user', 2, 'Administrator signed in to Lumiora', '2026-10-19 04:26:22'),
(2, 2, 'admin_login', 'user', 2, 'Administrator signed in to Lumiora', '2026-10-19 04:29:25'),
(3, 2, 'admin_logout', 'user', 2, 'Administrator signed out of Lumiora', '2026-10-19 04:30:28'),
(4, 2, 'admin_login', 'user', 2, 'Administrator signed in to Lumiora', '2026-10-19 06:21:08'),
(5, 2, 'user_deactivated', 'user', 1, 'Student account gtarang212 changed to inactive', '2026-10-19 06:22:47'),
(6, 2, 'admin_login', 'user', 2, 'Administrator signed in to Lumiora', '2026-10-22 05:13:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_audit_logs_created` (`created_at`,`log_id`),
  ADD KEY `idx_audit_logs_actor` (`admin_user_id`,`created_at`),
  ADD KEY `idx_audit_logs_target` (`target_type`,`target_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_logs_admin` FOREIGN KEY (`admin_user_id`) REFERENCES `user_accounts` (`user_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
