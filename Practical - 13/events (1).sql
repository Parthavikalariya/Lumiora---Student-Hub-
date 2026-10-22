-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 18, 2026 at 09:31 AM
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
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(10) UNSIGNED NOT NULL,
  `event_name` varchar(150) NOT NULL,
  `category` varchar(40) NOT NULL,
  `event_date` date NOT NULL,
  `venue` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `poster_path` varchar(255) DEFAULT NULL,
  `event_status` enum('open','closed') NOT NULL DEFAULT 'open',
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `event_name`, `category`, `event_date`, `venue`, `description`, `poster_path`, `event_status`, `is_archived`, `updated_at`) VALUES
(1, 'Web Development Workshop', 'Workshop', '2026-11-05', 'Computer Lab 1', 'Introduction to full-stack web development', NULL, 'open', 0, NULL),
(2, 'Coding Challenge', 'Technical', '2026-11-12', 'Seminar Hall', 'Problem solving and programming competition', NULL, 'open', 0, NULL),
(3, 'College Cultural Fest', 'Cultural', '2026-12-03', 'Auditorium', 'Student performances and cultural activities', NULL, 'open', 0, NULL),
(4, 'AI Innovation Seminar', 'Seminar', '2026-11-20', 'Seminar Hall 2', 'Guest talk and practical AI applications.', NULL, 'open', 0, NULL),
(5, 'Intercollege Sports Meet', 'Sports', '2026-11-26', 'Sports Ground', 'Football and track competitions.', NULL, 'open', 0, NULL),
(6, 'Cybersecurity Awareness Workshop', 'Workshop', '2026-12-08', 'Computer Lab 3', 'Safe browsing, password hygiene and security fundamentals.', NULL, 'open', 0, NULL),
(7, 'Campus Startup Pitch', 'Technical', '2026-12-12', 'Innovation Center', 'Student teams present startup ideas.', NULL, 'open', 0, NULL),
(8, 'Photography Exhibition', 'Cultural', '2026-12-15', 'Exhibition Hall', 'Showcase of student photography projects.', NULL, 'closed', 0, '2026-10-13 10:32:02'),
(9, 'Data Science Bootcamp', 'Workshop', '2026-12-19', 'Computer Lab 2', 'Hands-on Python data analysis.', NULL, 'open', 0, NULL),
(10, 'Advanced Web Development Workshop', 'Workshop', '2026-11-25', 'Computer Lab 2', '', 'event-posters/05afa7fe135fea5a3b05b766467ea100.png', 'open', 0, '2026-10-13 10:34:19'),
(11, 'Advanced Cloud Computing Workshop', 'Workshop', '2026-12-30', 'Cloud Computing Lab', 'Practical training on AWS, cloud deployment and virtual machines.', NULL, 'open', 0, '2026-10-17 17:02:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `idx_events_status_archived_date` (`is_archived`,`event_status`,`event_date`),
  ADD KEY `idx_events_category` (`category`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
