-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 18, 2026 at 12:56 PM
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
(1, 2, 1, 'Employee Action', 'None', 2, '2026-09-17', 'Completed', '2026-09-17 03:14:24'),
(2, 3, 2, 'Corrective Action', 'Reviewed and addressed the issue', 1, '0000-00-00', 'Completed', '2026-09-17 03:56:55');

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
(2, 1, 1, NULL, 1, '2026-09-16', '2026-09-20', 'None', 'active', '2026-09-16 09:15:34'),
(3, 3, 1, NULL, 3, '2026-09-17', '2026-09-30', 'None', 'active', '2026-09-17 03:46:48');

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
(2, 2, 1, 2, 'Not available', NULL, '2026-09-16 09:16:02');

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
(5, 1, 'User logged in', NULL, NULL, NULL, '::1', '2026-09-18 10:51:08');

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
(7, 'Lance Goloyugo', '0987687679', 'lance@gmail.com', 'La Torre', 'BSIT', 'email', '2026-09-17 03:42:44', 'active');

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
(7, 'CMS-2026-0007', 5, 6, 'Failed', 'failed sub', '2026-09-17', 'Bayombong', 'medium', 'Submitted', '2026-09-17 10:37:26');

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
(1, 'Service Complaint', 1, '2026-09-16 02:06:24'),
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
(2, 3, 1, '2026-09-17', '2026-09-30', 'Completed', '2026-09-17 03:50:38');

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
(2, 3, 'Grade Review', 'Issue has been reviewed and resolved after coordination with the concerned department.', '2026-09-17', 1, 'None', NULL, '2026-09-17 04:01:00');

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
(1, 3, 3, 'Vincent Jernee Diaz', 'jerneevincent@gmail.com', '$2y$10$ztifrUaGVr5C9sBkat4t/OcFUS4msKhdxvtF3BZBKP/OffLK3Nn0O', '09611394471', 'IT System', 'active', '2026-09-16 09:06:23'),
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
  MODIFY `action_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `action_monitoring`
--
ALTER TABLE `action_monitoring`
  MODIFY `monitoring_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `assignment_history`
--
ALTER TABLE `assignment_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `attachments`
--
ALTER TABLE `attachments`
  MODIFY `attachment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `complainants`
--
ALTER TABLE `complainants`
  MODIFY `complainant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `complaint_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
  MODIFY `investigation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  MODIFY `resolution_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
