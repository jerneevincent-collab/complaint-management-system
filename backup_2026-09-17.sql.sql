-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 22, 2026 at 01:57 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `complaint_management_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `actions`
--

CREATE TABLE `actions` (
  `action_id` int(11) NOT NULL,
  `complaint_id` int(11) NOT NULL,
  `investigation_id` int(11) DEFAULT NULL,
  `action_type` enum('Corrective Action','Preventive Action','Employee Action','Process Improvement','Service Recovery','Policy Recommendation','No Action Required') NOT NULL,
  `action_description` text NOT NULL,
  `responsible_person` int(11) NOT NULL,
  `target_date` date DEFAULT NULL,
  `status` enum('Pending','In Progress','Completed','Verified') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `actions`
--

INSERT INTO `actions` (`action_id`, `complaint_id`, `investigation_id`, `action_type`, `action_description`, `responsible_person`, `target_date`, `status`, `created_at`) VALUES
(1, 2, 1, 'Employee Action', 'None', 2, '2026-09-17', 'Verified', '2026-09-17 03:14:24'),
(2, 3, 2, 'Corrective Action', 'Reviewed and addressed the issue', 1, '0000-00-00', 'Verified', '2026-09-17 03:56:55'),
(3, 20, 4, 'Employee Action', 'none', 1, '2026-09-30', 'Verified', '2026-09-24 02:22:57');

-- --------------------------------------------------------

--
-- Table structure for table `action_monitoring`
--

CREATE TABLE `action_monitoring` (
  `monitoring_id` int(11) NOT NULL,
  `action_id` int(11) NOT NULL,
  `completion_date` date DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `is_overdue` tinyint(1) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `assignment_id` int(11) NOT NULL,
  `complaint_id` int(11) NOT NULL,
  `assigned_to` int(11) NOT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `assignment_date` date NOT NULL,
  `due_date` date NOT NULL,
  `instructions` text DEFAULT NULL,
  `status` enum('active','reassigned','completed') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`assignment_id`, `complaint_id`, `assigned_to`, `assigned_by`, `department_id`, `assignment_date`, `due_date`, `instructions`, `status`, `created_at`) VALUES
(1, 2, 2, NULL, 3, '0026-09-16', '2026-10-01', 'None', 'reassigned', '2026-09-16 09:11:59'),
(2, 1, 1, NULL, 1, '2026-09-16', '2026-09-20', 'None', 'reassigned', '2026-09-16 09:15:34'),
(3, 3, 1, NULL, 3, '2026-09-17', '2026-09-30', 'None', 'reassigned', '2026-09-17 03:46:48'),
(4, 19, 1, NULL, 3, '2026-09-23', '2026-09-30', 'none', 'active', '2026-09-23 13:12:18'),
(5, 20, 1, NULL, 1, '2026-09-24', '2026-09-30', 'note', 'active', '2026-09-24 02:19:13'),
(6, 22, 1, NULL, 1, '2026-10-22', '2026-10-25', '', 'active', '2026-10-22 11:47:15'),
(7, 21, 1, NULL, 1, '2026-10-22', '2026-10-25', '', 'active', '2026-10-22 11:50:14');

-- --------------------------------------------------------

--
-- Table structure for table `assignment_history`
--

CREATE TABLE `assignment_history` (
  `history_id` int(11) NOT NULL,
  `complaint_id` int(11) NOT NULL,
  `previous_assignee` int(11) DEFAULT NULL,
  `new_assignee` int(11) NOT NULL,
  `reason_for_reassignment` varchar(255) DEFAULT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `assignment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignment_history`
--

INSERT INTO `assignment_history` (`history_id`, `complaint_id`, `previous_assignee`, `new_assignee`, `reason_for_reassignment`, `assigned_by`, `assignment_date`) VALUES
(1, 2, 1, 1, 'Not available', NULL, '2026-09-16 09:14:29'),
(2, 2, 1, 2, 'Not available', NULL, '2026-09-16 09:16:02'),
(3, 3, 1, 1, 'not available', NULL, '2026-09-20 02:41:07'),
(4, 1, 1, 1, 'not available', NULL, '2026-09-23 12:30:08');

-- --------------------------------------------------------

--
-- Table structure for table `attachments`
--

CREATE TABLE `attachments` (
  `attachment_id` int(11) NOT NULL,
  `complaint_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `activity` varchar(255) NOT NULL,
  `record_affected` varchar(100) DEFAULT NULL,
  `previous_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`log_id`, `user_id`, `activity`, `record_affected`, `previous_value`, `new_value`, `ip_address`, `created_at`) VALUES
(1, 1, 'Closed complaint', 'complaint_id: 2', 'Resolved', 'Closed', NULL, '2026-09-17 03:31:15'),
(2, 1, 'Closed complaint', 'complaint_id: 3', 'Resolved', 'Closed', NULL, '2026-09-16 22:02:12'),
(3, 2, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-17 04:28:25'),
(4, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-17 09:46:09'),
(5, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-18 10:51:08'),
(6, 2, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-18 11:57:18'),
(7, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-18 11:57:33'),
(8, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-18 16:03:35'),
(9, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-19 00:44:12'),
(10, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-19 03:52:29'),
(11, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-19 03:52:55'),
(12, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-19 06:27:54'),
(13, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-19 06:28:20'),
(14, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 02:24:51'),
(15, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 02:26:21'),
(16, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 02:37:19'),
(17, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 02:38:32'),
(18, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 02:40:21'),
(19, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 02:50:49'),
(20, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 04:29:10'),
(21, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 04:29:39'),
(22, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 05:11:16'),
(23, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 05:22:34'),
(24, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 06:54:57'),
(25, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 09:17:28'),
(26, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 09:19:25'),
(27, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 09:26:56'),
(28, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 09:42:59'),
(29, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 09:50:27'),
(30, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 09:57:31'),
(31, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 10:04:13'),
(32, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 10:09:46'),
(33, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 10:12:58'),
(34, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 15:08:12'),
(35, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 15:33:33'),
(36, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 15:35:32'),
(37, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 16:18:47'),
(38, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 16:19:41'),
(39, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 16:20:24'),
(40, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 16:22:25'),
(41, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 16:30:41'),
(42, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 16:31:53'),
(43, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 23:25:55'),
(44, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 23:34:56'),
(45, 2, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 23:41:09'),
(46, 2, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 23:41:21'),
(47, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 23:42:54'),
(48, 2, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-20 23:43:09'),
(49, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-21 06:14:46'),
(50, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-21 06:23:51'),
(51, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-21 06:35:02'),
(52, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-21 06:37:36'),
(53, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-21 15:42:12'),
(54, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-22 01:39:40'),
(55, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-22 01:56:13'),
(56, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-22 08:00:08'),
(57, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-22 08:03:20'),
(58, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-22 11:00:40'),
(59, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-22 11:01:49'),
(60, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-22 11:14:52'),
(61, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-22 13:58:43'),
(62, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-22 13:59:14'),
(63, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-22 14:07:45'),
(64, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-22 14:08:37'),
(65, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-22 14:09:02'),
(66, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-22 14:12:37'),
(67, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-22 14:17:29'),
(68, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-22 14:20:55'),
(69, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-22 14:24:14'),
(70, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 02:51:45'),
(71, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 03:08:18'),
(72, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 03:10:44'),
(73, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 03:13:26'),
(74, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 03:13:47'),
(75, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 03:30:46'),
(76, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 03:31:11'),
(77, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 03:38:12'),
(78, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 03:40:11'),
(79, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 04:06:11'),
(80, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 10:14:03'),
(81, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 10:15:20'),
(82, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 10:47:58'),
(83, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 10:48:27'),
(84, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 10:52:15'),
(85, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 10:52:50'),
(86, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 10:55:04'),
(87, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 10:59:47'),
(88, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 11:00:23'),
(89, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 11:04:41'),
(90, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 11:13:48'),
(91, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 11:14:20'),
(92, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 11:16:37'),
(93, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 11:20:38'),
(94, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 11:25:00'),
(95, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 11:25:41'),
(96, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 11:28:07'),
(97, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 11:42:40'),
(98, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 11:44:40'),
(99, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 11:49:12'),
(100, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 11:49:32'),
(101, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 11:55:42'),
(102, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 12:06:56'),
(103, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 12:16:13'),
(104, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 12:18:26'),
(105, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 12:24:23'),
(106, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 12:27:43'),
(107, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 12:44:06'),
(108, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 12:52:26'),
(109, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 12:57:57'),
(110, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 13:01:11'),
(111, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 13:05:53'),
(112, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-23 13:09:04'),
(113, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-24 02:10:12'),
(114, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-24 02:11:31'),
(115, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-24 02:27:30'),
(116, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-26 04:53:12'),
(117, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-26 05:25:06'),
(118, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-26 05:45:42'),
(119, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-26 05:59:12'),
(120, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-26 06:04:09'),
(121, 1, 'Closed complaint', 'complaint_id: 20', 'Resolved', 'Closed', NULL, '2026-09-26 00:05:11'),
(122, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-27 09:56:16'),
(123, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-27 10:33:02'),
(124, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-27 10:38:26'),
(125, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-29 07:16:45'),
(126, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-29 07:19:22'),
(127, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-10-06 13:34:55'),
(128, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-10-11 07:15:59'),
(129, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-10-12 12:41:34'),
(130, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-10-12 14:24:10'),
(131, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-10-12 14:26:23'),
(132, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-10-21 09:18:17'),
(133, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-10-21 09:19:28'),
(134, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-10-21 09:52:13'),
(135, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-10-21 22:59:36'),
(136, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-10-21 23:11:18'),
(137, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-10-21 23:51:47'),
(138, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-10-22 00:05:54'),
(139, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-10-22 11:32:51'),
(140, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-10-22 11:38:35');

-- --------------------------------------------------------

--
-- Table structure for table `complainants`
--

CREATE TABLE `complainants` (
  `complainant_id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `organization_department` varchar(150) DEFAULT NULL,
  `preferred_contact_method` enum('email','phone','sms') DEFAULT 'email',
  `date_registered` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complainants`
--

INSERT INTO `complainants` (`complainant_id`, `full_name`, `contact_number`, `email`, `address`, `organization_department`, `preferred_contact_method`, `date_registered`, `status`) VALUES
(1, 'Marcus Baron', '09877777777', 'marcus@gmail.co', 'Diadi', 'BSIT', 'email', '2026-09-16 07:09:31', 'active'),
(3, 'Ioan Gonzales', '0999999999', 'Ioan@gmail.com', 'Bayombong', 'BSCS', 'email', '2026-09-16 07:35:40', 'active'),
(4, 'Kenneth Kyle', '09666666666', 'kyle@gmail.com', 'Bambang', 'BSCS', 'phone', '2026-09-16 07:44:07', 'active'),
(5, 'Charlez Jules Espiritu', '09345627658', 'jules@gmail.com', 'Diadi', 'BSIT', 'email', '2026-09-17 03:41:17', 'active'),
(6, 'Jed Razon', '09643865413', 'jed@gmail.com', 'Bundok', 'BSIT', 'email', '2026-09-17 03:41:56', 'active'),
(7, 'Lance Goloyugo', '0987687679', 'lance@gmail.com', 'La Torre', 'BSIT', 'email', '2026-09-17 03:42:44', 'inactive'),
(8, 'Ashley Labitoria', '09634286548', 'ashley@gmail.com', 'Bayombong', 'BSTM', 'phone', '2026-09-19 01:01:10', 'active'),
(9, 'Andrei Taborda', '09767679563', 'andrei@gmail.com', 'Solano', 'BSIT', 'sms', '2026-09-19 06:00:29', 'active'),
(10, 'Timothy Ferrer', '094567541127', 'timothy@gmail.com', 'Bayombong', 'BSIT', 'email', '2026-09-19 06:10:23', 'active'),
(11, 'Franxyz Olano', '09453762976', 'franxyz@gmail.com', 'Isabela', 'BSIT', 'email', '2026-09-19 06:24:03', 'active'),
(12, 'Sean Navarette', '09897654829', 'sean@gmail.com', 'Solano', 'BSIT', 'phone', '2026-09-19 06:42:12', 'inactive'),
(13, 'Laurence Andrei Diaz', '09657648825', 'laurence@gmail.com', 'Solano', 'BSFM', 'email', '2026-09-20 05:17:54', 'inactive'),
(14, 'Juan Dela Rosa', '09654835291', 'rosa@gmail.com', 'Bayombong', 'BSA', 'email', '2026-09-20 06:31:08', 'inactive'),
(15, 'Orhea Odhie', '098563211352', 'orhea@gmail.com', 'Solano', 'BSN', 'phone', '2026-09-20 09:21:49', 'inactive'),
(16, 'Tim Hortons', '09785564392', 'tim@gmail.com', 'California', 'BSIT', 'email', '2026-09-20 09:25:41', 'inactive'),
(17, 'Natoy Leih', '09864568324', 'natoy@gmail.com', 'Bayombong', 'BSIT', 'email', '2026-09-23 11:35:24', 'active'),
(18, 'Wilson Barete', '09631126746', 'wilson@gmail.com', 'Bayombong', 'BSTM', 'email', '2026-09-23 11:53:34', 'active'),
(19, 'Andrei Manalo', '09864569372', 'andrei@gmail.com', 'Bayombong', 'BSFM', 'email', '2026-09-23 12:05:09', 'active'),
(20, 'Adonis Papa', '09865334572', 'adonis@gmail.com', 'Solano', 'BSIT', 'phone', '2026-09-23 12:23:15', 'inactive'),
(21, 'Renato Paquito', '09876548362', 'paquito@mail.com', 'Bayombong', 'BSCS', 'email', '2026-09-23 13:08:01', 'inactive'),
(22, 'Laurence Andrei Diaz', '09657365971', 'laurence@gmail.com', 'Solano', 'BSFM', 'email', '2026-10-06 13:34:04', 'active'),
(23, 'Odhie Diaz', '09654835291', 'odhie@gmail.com', 'Solano', 'BSIT', 'sms', '2026-10-21 09:50:22', 'active'),
(24, 'Franxyz Olano Po', '09876457837', 'olano@gmail.com', 'Solano', 'BSIT', 'email', '2026-10-21 23:12:55', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `complaint_id` int(11) NOT NULL,
  `complaint_number` varchar(30) NOT NULL,
  `complainant_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `date_filed` date NOT NULL,
  `location` varchar(150) DEFAULT NULL,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `status` enum('Submitted','For Assignment','Assigned','Under Investigation','Action Required','For Resolution','Resolved','Closed') DEFAULT 'Submitted',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`complaint_id`, `complaint_number`, `complainant_id`, `category_id`, `subject`, `description`, `date_filed`, `location`, `priority`, `status`, `created_at`) VALUES
(1, 'CMS-2026-0001', 1, 5, 'Overcharged', 'Urgent', '2026-09-16', 'Bayombong', 'medium', 'Assigned', '2026-09-16 07:56:36'),
(2, 'CMS-2026-0002', 3, 5, 'Tuition Fee', 'can\'t afford tuition fee', '2026-09-30', 'Bayombong', 'medium', 'Closed', '2026-09-16 08:06:17'),
(3, 'CMS-2026-0003', 7, 6, 'Failed', 'None', '2026-09-17', 'Bayombong', 'medium', 'Closed', '2026-09-17 03:44:36'),
(4, 'CMS-2026-0004', 5, 6, 'Failed', 'failed subjects', '0026-09-30', 'Bayombong', 'medium', 'Submitted', '2026-09-17 10:06:41'),
(5, 'CMS-2026-0005', 5, 6, 'Failed', 'failed subject', '2026-09-17', 'Bayombong', 'medium', 'Submitted', '2026-09-17 10:28:54'),
(6, 'CMS-2026-0006', 5, 6, 'Failed', 'failed subject', '2026-09-17', 'Bayombong', 'medium', 'Submitted', '2026-09-17 10:30:54'),
(7, 'CMS-2026-0007', 5, 6, 'Failed', 'failed sub', '2026-09-17', 'Bayombong', 'medium', 'Submitted', '2026-09-17 10:37:26'),
(8, 'CMS-2026-0008', 6, 6, 'Failed', 'failed subject', '2026-09-18', 'Bayombong', 'medium', 'Submitted', '2026-09-18 11:56:49'),
(9, 'CMS-2026-0009', 10, 6, 'Failed', 'Failed Subject', '2026-09-19', 'Bayombong', 'medium', 'Submitted', '2026-09-19 06:17:31'),
(10, 'CMS-2026-0010', 11, 5, 'Tuition Fee', 'None', '2026-09-18', 'Isabela', 'medium', 'Submitted', '2026-09-19 06:24:45'),
(11, 'CMS-2026-0011', 12, 6, 'Concern about my grades', 'Report an academic concerns', '2026-09-18', 'Bambang', 'high', 'Closed', '2026-09-19 06:44:47'),
(12, 'CMS-2026-0012', 13, 6, 'Failed', 'failed subject', '2026-09-20', 'Bayombong', 'medium', 'Submitted', '2026-09-20 05:18:24'),
(13, 'CMS-2026-0013', 14, 2, 'Employee Attitude', 'the employee was rude', '2026-09-20', 'Bayombong', 'medium', 'Submitted', '2026-09-20 06:33:51'),
(14, 'CMS-2026-0014', 15, 2, 'Employee Attitude', 'employee was rude', '2026-09-20', '', 'medium', 'Submitted', '2026-09-20 09:22:25'),
(15, 'CMS-2026-0015', 16, 6, 'Failed', 'failed sub', '2026-09-20', 'Bayombong', 'medium', 'Submitted', '2026-09-20 09:26:13'),
(16, 'CMS-2026-0016', 17, 6, 'Failed', 'failed subject', '2026-09-23', 'Bayombong', 'medium', 'Submitted', '2026-09-23 11:36:03'),
(17, 'CMS-2026-0017', 18, 3, 'Damaged Product', 'received a small damage from the prodcut', '2026-09-23', 'Bayombong', 'medium', 'Submitted', '2026-09-23 11:55:07'),
(18, 'CMS-2026-0018', 19, 5, 'Tuition Fee', 'none', '2026-09-23', 'Bayombong', 'medium', 'Submitted', '2026-09-23 12:05:41'),
(19, 'CMS-2026-0019', 20, 5, 'Tuition Fee', 'hard to pay tuition pee', '2026-09-23', 'Solano', 'medium', 'Under Investigation', '2026-09-23 12:23:51'),
(20, 'CMS-2026-0020', 21, 2, 'Employee Attitude', 'employee was rude', '2026-09-23', 'Bayombong', 'medium', 'Closed', '2026-09-23 13:08:36'),
(21, 'CMS-2026-0021', 22, 5, 'Tuition Fee', 'none', '2023-06-05', 'Bayombong', 'medium', 'Assigned', '2026-10-06 13:34:33'),
(22, 'CMS-2026-0022', 23, 6, 'Failed', 'Failed Subjects', '2026-10-21', 'Bayombong', 'medium', 'Assigned', '2026-10-21 09:51:26');

-- --------------------------------------------------------

--
-- Table structure for table `complaint_categories`
--

CREATE TABLE `complaint_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaint_categories`
--

INSERT INTO `complaint_categories` (`category_id`, `category_name`, `is_active`, `created_at`) VALUES
(1, 'Service Complaint', 1, '2026-09-15 18:06:24'),
(2, 'Employee Complaint', 1, '2026-09-16 02:06:24'),
(3, 'Product Complaint', 1, '2026-09-16 02:06:24'),
(4, 'Facility Complaint', 1, '2026-09-16 02:06:24'),
(5, 'Financial Complaint', 1, '2026-09-16 02:06:24'),
(6, 'Academic Complaint', 1, '2026-09-16 02:06:24'),
(7, 'Technical Complaint', 1, '2026-09-16 02:06:24'),
(8, 'Other', 1, '2026-09-16 02:06:24');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`, `created_at`) VALUES
(1, 'Administration Office', '2026-09-16 02:06:24'),
(2, 'Customer Service', '2026-09-16 02:06:24'),
(3, 'IT Department', '2026-09-16 02:06:24');

-- --------------------------------------------------------

--
-- Table structure for table `investigations`
--

CREATE TABLE `investigations` (
  `investigation_id` int(11) NOT NULL,
  `complaint_id` int(11) NOT NULL,
  `investigator_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `target_completion_date` date DEFAULT NULL,
  `status` enum('Assigned','In Progress','Evidence Gathering','Findings Prepared','Completed') DEFAULT 'Assigned',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `investigations`
--

INSERT INTO `investigations` (`investigation_id`, `complaint_id`, `investigator_id`, `start_date`, `target_completion_date`, `status`, `created_at`) VALUES
(1, 2, 1, '2026-09-16', '2026-09-25', 'Completed', '2026-09-16 09:29:12'),
(2, 3, 1, '2026-09-17', '2026-09-30', 'Completed', '2026-09-17 03:50:38'),
(3, 20, 1, '2026-09-23', '2026-09-30', 'Completed', '2026-09-23 13:11:28'),
(4, 20, 1, '2026-09-24', '2026-09-30', 'Completed', '2026-09-24 02:20:03'),
(5, 19, 1, '2026-09-24', '2026-09-30', 'Completed', '2026-09-24 02:21:35');

-- --------------------------------------------------------

--
-- Table structure for table `investigation_evidence`
--

CREATE TABLE `investigation_evidence` (
  `evidence_id` int(11) NOT NULL,
  `investigation_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `investigation_evidence`
--

INSERT INTO `investigation_evidence` (`evidence_id`, `investigation_id`, `file_name`, `file_path`, `uploaded_by`, `uploaded_at`) VALUES
(1, 1, '1789613844_dd2935c8-97f6-403e-9360-a9a3ae15ae06.jpg', 'uploads/evidence/1789613844_dd2935c8-97f6-403e-9360-a9a3ae15ae06.jpg', NULL, '2026-09-17 02:57:24');

-- --------------------------------------------------------

--
-- Table structure for table `investigation_findings`
--

CREATE TABLE `investigation_findings` (
  `finding_id` int(11) NOT NULL,
  `investigation_id` int(11) NOT NULL,
  `date_investigated` date NOT NULL,
  `evidence_collected` text DEFAULT NULL,
  `findings` text NOT NULL,
  `witnesses` varchar(255) DEFAULT NULL,
  `recommendation` text DEFAULT NULL,
  `classification` enum('Valid Complaint','Partially Valid','Unsubstantiated','Invalid') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `investigation_findings`
--

INSERT INTO `investigation_findings` (`finding_id`, `investigation_id`, `date_investigated`, `evidence_collected`, `findings`, `witnesses`, `recommendation`, `classification`, `created_at`) VALUES
(1, 1, '2026-09-16', 'CCTV', 'Confirmed', 'Al jean Laygo', 'None', 'Valid Complaint', '2026-09-16 09:34:34');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `complainant_id` int(11) DEFAULT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resolutions`
--

CREATE TABLE `resolutions` (
  `resolution_id` int(11) NOT NULL,
  `complaint_id` int(11) NOT NULL,
  `resolution_type` varchar(100) DEFAULT NULL,
  `resolution_description` text NOT NULL,
  `resolution_date` date NOT NULL,
  `resolved_by` int(11) NOT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `supporting_document` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resolutions`
--

INSERT INTO `resolutions` (`resolution_id`, `complaint_id`, `resolution_type`, `resolution_description`, `resolution_date`, `resolved_by`, `remarks`, `supporting_document`, `created_at`) VALUES
(1, 2, 'Change', 'None', '2026-09-17', 1, 'None', NULL, '2026-09-17 03:29:39'),
(2, 3, 'Grade Review', 'Issue has been reviewed and resolved after coordination with the concerned department.', '2026-09-17', 1, 'None', NULL, '2026-09-17 04:01:00'),
(3, 20, 'Employee Attitude Improvement', 'Maintain a Respectful and professional attitude', '2026-09-24', 1, '', NULL, '2026-09-24 02:26:50');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`, `description`, `created_at`) VALUES
(1, 'Administrator', 'Manages entire system', '2026-09-16 02:06:24'),
(2, 'Complaint Officer', 'Receives and manages complaints', '2026-09-16 02:06:24'),
(3, 'Investigator', 'Conducts investigation', '2026-09-16 02:06:24'),
(4, 'Action Officer', 'Implements actions', '2026-09-16 02:06:24'),
(5, 'Supervisor', 'Reviews and approves', '2026-09-16 02:06:24'),
(6, 'Complainant', 'Submits and monitors complaints', '2026-09-16 02:06:24');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `role_id`, `department_id`, `full_name`, `email`, `password`, `contact_number`, `specialization`, `status`, `created_at`) VALUES
(1, 1, 3, 'Vincent Jernee Diaz', 'jerneevincent@gmail.com', '$2y$10$ztifrUaGVr5C9sBkat4t/OcFUS4msKhdxvtF3BZBKP/OffLK3Nn0O', '09611394471', 'IT System', 'active', '2026-09-16 09:06:23'),
(2, 4, 3, 'Ioan Gonzales', 'gonzalesioan4@gmail.com', '$2y$10$0iuiyVXGSnimPzKTLoitxukkjytK5fm5a.KUosb.k0ohS27MA6xOO', '0987654321', 'Facility Maintenance', 'active', '2026-09-16 09:07:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `actions`
--
ALTER TABLE `actions`
  ADD PRIMARY KEY (`action_id`),
  ADD KEY `complaint_id` (`complaint_id`),
  ADD KEY `investigation_id` (`investigation_id`),
  ADD KEY `responsible_person` (`responsible_person`);

--
-- Indexes for table `action_monitoring`
--
ALTER TABLE `action_monitoring`
  ADD PRIMARY KEY (`monitoring_id`),
  ADD KEY `action_id` (`action_id`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `complaint_id` (`complaint_id`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `assigned_by` (`assigned_by`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `assignment_history`
--
ALTER TABLE `assignment_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `complaint_id` (`complaint_id`),
  ADD KEY `previous_assignee` (`previous_assignee`),
  ADD KEY `new_assignee` (`new_assignee`),
  ADD KEY `assigned_by` (`assigned_by`);

--
-- Indexes for table `attachments`
--
ALTER TABLE `attachments`
  ADD PRIMARY KEY (`attachment_id`),
  ADD KEY `complaint_id` (`complaint_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `complainants`
--
ALTER TABLE `complainants`
  ADD PRIMARY KEY (`complainant_id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`complaint_id`),
  ADD UNIQUE KEY `complaint_number` (`complaint_number`),
  ADD KEY `complainant_id` (`complainant_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_complaint_status` (`status`),
  ADD KEY `idx_complaint_date` (`date_filed`);

--
-- Indexes for table `complaint_categories`
--
ALTER TABLE `complaint_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`),
  ADD UNIQUE KEY `department_name` (`department_name`);

--
-- Indexes for table `investigations`
--
ALTER TABLE `investigations`
  ADD PRIMARY KEY (`investigation_id`),
  ADD KEY `complaint_id` (`complaint_id`),
  ADD KEY `investigator_id` (`investigator_id`);

--
-- Indexes for table `investigation_evidence`
--
ALTER TABLE `investigation_evidence`
  ADD PRIMARY KEY (`evidence_id`),
  ADD KEY `investigation_id` (`investigation_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `investigation_findings`
--
ALTER TABLE `investigation_findings`
  ADD PRIMARY KEY (`finding_id`),
  ADD KEY `investigation_id` (`investigation_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `complainant_id` (`complainant_id`);

--
-- Indexes for table `resolutions`
--
ALTER TABLE `resolutions`
  ADD PRIMARY KEY (`resolution_id`),
  ADD KEY `complaint_id` (`complaint_id`),
  ADD KEY `resolved_by` (`resolved_by`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `department_id` (`department_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `actions`
--
ALTER TABLE `actions`
  MODIFY `action_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `action_monitoring`
--
ALTER TABLE `action_monitoring`
  MODIFY `monitoring_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `assignment_history`
--
ALTER TABLE `assignment_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `attachments`
--
ALTER TABLE `attachments`
  MODIFY `attachment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=141;

--
-- AUTO_INCREMENT for table `complainants`
--
ALTER TABLE `complainants`
  MODIFY `complainant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `complaint_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `complaint_categories`
--
ALTER TABLE `complaint_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `investigations`
--
ALTER TABLE `investigations`
  MODIFY `investigation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `investigation_evidence`
--
ALTER TABLE `investigation_evidence`
  MODIFY `evidence_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `investigation_findings`
--
ALTER TABLE `investigation_findings`
  MODIFY `finding_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resolutions`
--
ALTER TABLE `resolutions`
  MODIFY `resolution_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `actions`
--
ALTER TABLE `actions`
  ADD CONSTRAINT `actions_ibfk_1` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`complaint_id`),
  ADD CONSTRAINT `actions_ibfk_2` FOREIGN KEY (`investigation_id`) REFERENCES `investigations` (`investigation_id`),
  ADD CONSTRAINT `actions_ibfk_3` FOREIGN KEY (`responsible_person`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `action_monitoring`
--
ALTER TABLE `action_monitoring`
  ADD CONSTRAINT `action_monitoring_ibfk_1` FOREIGN KEY (`action_id`) REFERENCES `actions` (`action_id`);

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`complaint_id`),
  ADD CONSTRAINT `assignments_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `assignments_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `assignments_ibfk_4` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`);

--
-- Constraints for table `assignment_history`
--
ALTER TABLE `assignment_history`
  ADD CONSTRAINT `assignment_history_ibfk_1` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`complaint_id`),
  ADD CONSTRAINT `assignment_history_ibfk_2` FOREIGN KEY (`previous_assignee`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `assignment_history_ibfk_3` FOREIGN KEY (`new_assignee`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `assignment_history_ibfk_4` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `attachments`
--
ALTER TABLE `attachments`
  ADD CONSTRAINT `attachments_ibfk_1` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`complaint_id`),
  ADD CONSTRAINT `attachments_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`complainant_id`) REFERENCES `complainants` (`complainant_id`),
  ADD CONSTRAINT `complaints_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `complaint_categories` (`category_id`);

--
-- Constraints for table `investigations`
--
ALTER TABLE `investigations`
  ADD CONSTRAINT `investigations_ibfk_1` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`complaint_id`),
  ADD CONSTRAINT `investigations_ibfk_2` FOREIGN KEY (`investigator_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `investigation_evidence`
--
ALTER TABLE `investigation_evidence`
  ADD CONSTRAINT `investigation_evidence_ibfk_1` FOREIGN KEY (`investigation_id`) REFERENCES `investigations` (`investigation_id`),
  ADD CONSTRAINT `investigation_evidence_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `investigation_findings`
--
ALTER TABLE `investigation_findings`
  ADD CONSTRAINT `investigation_findings_ibfk_1` FOREIGN KEY (`investigation_id`) REFERENCES `investigations` (`investigation_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`complainant_id`) REFERENCES `complainants` (`complainant_id`);

--
-- Constraints for table `resolutions`
--
ALTER TABLE `resolutions`
  ADD CONSTRAINT `resolutions_ibfk_1` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`complaint_id`),
  ADD CONSTRAINT `resolutions_ibfk_2` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
