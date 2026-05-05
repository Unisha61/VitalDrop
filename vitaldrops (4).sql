-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 02, 2026 at 10:38 AM
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
-- Database: `vitaldrops`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `donor_id` int(11) DEFAULT NULL,
  `recipient_id` int(11) DEFAULT NULL,
  `appointment_date` date DEFAULT NULL,
  `appointment_time` time DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `cancel_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `donor_id`, `recipient_id`, `appointment_date`, `appointment_time`, `reason`, `status`, `cancel_reason`, `notes`, `created_at`, `updated_at`) VALUES
(1, 3, NULL, '2026-04-22', '19:02:00', '', 'completed', NULL, NULL, '2026-04-22 13:18:39', '2026-04-22 13:32:13'),
(2, 3, NULL, '2026-04-22', '23:45:00', '', 'completed', NULL, NULL, '2026-04-22 17:58:58', '2026-04-22 18:03:36'),
(3, 3, NULL, '2026-04-23', '10:16:00', '', 'completed', NULL, NULL, '2026-04-23 03:31:37', '2026-04-23 03:41:49'),
(4, 5, NULL, '2026-04-24', '12:04:00', '', 'completed', NULL, NULL, '2026-04-24 15:19:43', '2026-04-26 08:30:08'),
(5, 6, NULL, '2026-04-24', '21:11:00', '', 'completed', NULL, NULL, '2026-04-24 15:24:08', '2026-04-26 08:30:13'),
(6, 7, NULL, '2026-04-24', '21:20:00', '', 'completed', NULL, NULL, '2026-04-24 15:29:15', '2026-04-26 08:30:15'),
(7, 8, NULL, '2026-04-24', '21:30:00', '', 'completed', NULL, NULL, '2026-04-24 15:33:58', '2026-04-26 08:19:36'),
(8, 8, NULL, '2026-04-27', '18:21:00', '', 'completed', NULL, NULL, '2026-04-26 08:36:53', '2026-04-26 09:59:50'),
(11, 10, NULL, NULL, NULL, '', 'completed', NULL, NULL, '2026-04-26 12:05:12', '2026-04-26 12:10:22'),
(12, 10, 11, NULL, NULL, 'Direct Request Fulfillment for Request #9', 'completed', NULL, NULL, '2026-04-26 12:10:15', '2026-04-26 12:18:14'),
(13, 12, NULL, NULL, NULL, '', 'completed', NULL, NULL, '2026-04-26 12:17:22', '2026-04-26 12:34:51'),
(14, 13, NULL, NULL, NULL, '', 'cancelled', NULL, NULL, '2026-04-26 12:23:00', '2026-04-26 12:40:46'),
(15, 14, NULL, NULL, NULL, '', 'confirmed', NULL, NULL, '2026-04-26 12:26:50', '2026-05-02 08:30:24'),
(16, 13, 15, NULL, NULL, 'Direct Request Fulfillment for Request #10', 'completed', NULL, NULL, '2026-04-26 12:39:30', '2026-04-26 12:40:48');

-- --------------------------------------------------------

--
-- Table structure for table `blood_inventory`
--

CREATE TABLE `blood_inventory` (
  `id` int(11) NOT NULL,
  `blood_type` varchar(5) NOT NULL,
  `quantity` int(11) DEFAULT 0,
  `expiry_date` date DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blood_inventory`
--

INSERT INTO `blood_inventory` (`id`, `blood_type`, `quantity`, `expiry_date`, `last_updated`) VALUES
(1, 'O+', 458, NULL, '2026-04-26 09:59:50'),
(2, 'O-', 5, NULL, '2026-04-22 13:41:11'),
(3, 'A+', 906, NULL, '2026-04-26 12:40:48'),
(4, 'A-', 6, NULL, '2026-04-22 13:41:26'),
(5, 'B+', 1804, NULL, '2026-04-26 12:18:14'),
(6, 'B-', 6, NULL, '2026-04-22 13:42:35'),
(7, 'AB+', 5, NULL, '2026-04-22 13:42:44'),
(8, 'AB-', 5, NULL, '2026-04-22 13:42:51');


-- --------------------------------------------------------

--
-- Table structure for table `blood_requests`
--

CREATE TABLE `blood_requests` (
  `id` int(11) NOT NULL,
  `donor_id` int(11) DEFAULT NULL,
  `recipient_id` int(11) NOT NULL,
  `units_needed` int(11) NOT NULL,
  `urgency` enum('normal','high','critical') DEFAULT 'normal',
  `medical_reason` text DEFAULT NULL,
  `status` enum('pending','approved','donor_accepted','fulfilled','rejected','cancelled') DEFAULT 'pending',
  `cancel_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blood_requests`
--

INSERT INTO `blood_requests` (`id`, `donor_id`, `recipient_id`, `units_needed`, `urgency`, `medical_reason`, `status`, `cancel_reason`, `created_at`, `updated_at`) VALUES
(1, 3, 4, 1, 'normal', '', 'fulfilled', NULL, '2026-04-22 13:39:05', '2026-04-22 13:44:11'),
(2, 3, 4, 450, 'normal', '', 'fulfilled', NULL, '2026-04-23 03:39:53', '2026-04-26 08:32:56'),
(3, 3, 4, 450, 'normal', '', 'cancelled', '', '2026-04-23 03:47:44', '2026-04-26 08:18:46'),
(4, 3, 4, 450, 'normal', '', 'fulfilled', NULL, '2026-04-26 08:18:21', '2026-04-26 08:33:08'),
(5, 8, 9, 450, 'normal', '', 'fulfilled', NULL, '2026-04-26 08:23:46', '2026-04-26 08:33:01'),
(6, 7, 9, 450, 'high', '', 'fulfilled', NULL, '2026-04-26 08:26:01', '2026-04-26 08:33:05'),
(7, 8, 9, 450, 'normal', '', 'fulfilled', NULL, '2026-04-26 08:35:43', '2026-04-26 10:18:15'),
(9, 10, 11, 450, 'high', '', 'donor_accepted', NULL, '2026-04-26 12:06:11', '2026-04-26 12:10:15'),
(10, 13, 15, 450, 'normal', '', 'donor_accepted', NULL, '2026-04-26 12:38:06', '2026-04-26 12:39:30');

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `donor_id` int(11) NOT NULL,
  `blood_type` varchar(5) NOT NULL,
  `units` int(11) NOT NULL,
  `time` varchar(5) DEFAULT NULL,
  `donation_date` date NOT NULL,
  `status` enum('pending','completed','rejected','collected','processed','distributed','expired') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donations`
--

INSERT INTO `donations` (`id`, `donor_id`, `blood_type`, `units`, `time`, `donation_date`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 3, 'O+', 1, NULL, '2026-04-22', 'completed', NULL, '2026-04-22 13:32:13', '2026-04-22 13:32:13'),
(2, 3, 'O+', 450, NULL, '2026-04-22', 'completed', NULL, '2026-04-22 18:03:36', '2026-04-22 18:03:36'),
(3, 3, 'O+', 450, NULL, '2026-04-23', 'completed', NULL, '2026-04-23 03:41:49', '2026-04-23 03:41:49'),
(4, 8, 'O+', 450, NULL, '2026-04-26', 'completed', NULL, '2026-04-26 08:19:36', '2026-04-26 08:19:36'),
(5, 5, 'A+', 450, NULL, '2026-04-26', 'completed', NULL, '2026-04-26 08:30:08', '2026-04-26 08:30:08'),
(6, 6, 'A+', 450, NULL, '2026-04-26', 'completed', NULL, '2026-04-26 08:30:13', '2026-04-26 08:30:13'),
(7, 7, 'A+', 450, NULL, '2026-04-26', 'completed', NULL, '2026-04-26 08:30:15', '2026-04-26 08:30:15'),
(8, 8, 'O+', 450, NULL, '2026-04-26', 'completed', NULL, '2026-04-26 09:59:50', '2026-04-26 09:59:50'),
(11, 10, 'B+', 450, NULL, '2026-04-26', 'completed', NULL, '2026-04-26 12:10:22', '2026-04-26 12:10:22'),
(14, 10, 'B+', 450, NULL, '2026-04-26', 'completed', NULL, '2026-04-26 12:18:14', '2026-04-26 12:18:14'),
(15, 12, 'A+', 450, NULL, '2026-04-26', 'completed', NULL, '2026-04-26 12:34:51', '2026-04-26 12:34:51'),
(18, 13, 'A+', 450, NULL, '2026-04-26', 'completed', NULL, '2026-04-26 12:40:48', '2026-04-26 12:40:48');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `rating` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `health_records`
--

CREATE TABLE `health_records` (
  `id` int(11) NOT NULL,
  `donor_id` int(11) NOT NULL,
  `blood_pressure` varchar(10) DEFAULT NULL,
  `hemoglobin` decimal(5,2) DEFAULT NULL,
  `heart_rate` int(11) DEFAULT NULL,
  `temperature` decimal(4,2) DEFAULT NULL,
  `health_notes` text DEFAULT NULL,
  `recorded_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `health_records`
--

INSERT INTO `health_records` (`id`, `donor_id`, `blood_pressure`, `hemoglobin`, `heart_rate`, `temperature`, `health_notes`, `recorded_date`) VALUES
(1, 3, '120/90', 12.80, 74, 36.90, '', '2026-04-22 13:13:33'),
(2, 5, '150/95', 15.50, 70, 35.50, '', '2026-04-24 15:19:18'),
(3, 6, '150/95', 15.50, 70, 35.50, '', '2026-04-24 15:23:37'),
(4, 7, '150/95', 15.50, 70, 35.50, '', '2026-04-24 15:28:53'),
(5, 8, '150/95', 15.50, 80, 36.50, '', '2026-04-24 15:33:31'),
(6, 10, '145/90', 14.00, 80, 36.00, '', '2026-04-26 09:57:59'),
(7, 12, '120/80', 15.00, 70, 36.80, '', '2026-04-26 12:17:13'),
(8, 13, '120/80', 15.00, 70, 37.00, '', '2026-04-26 12:22:36'),
(9, 14, '120/80', 15.00, 77, 36.00, '', '2026-04-26 12:26:42');

-- --------------------------------------------------------

--
-- Table structure for table `transfusion_history`
--

CREATE TABLE `transfusion_history` (
  `id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `blood_type` varchar(5) NOT NULL,
  `units_received` int(11) NOT NULL,
  `transfusion_date` date NOT NULL,
  `transfusion_reason` text DEFAULT NULL,
  `hospital_name` varchar(100) DEFAULT NULL,
  `status` enum('completed','pending','cancelled') DEFAULT 'completed',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transfusion_history`
--

INSERT INTO `transfusion_history` (`id`, `recipient_id`, `blood_type`, `units_received`, `transfusion_date`, `transfusion_reason`, `hospital_name`, `status`, `notes`, `created_at`) VALUES
(1, 4, 'O+', 450, '2026-04-26', '', NULL, 'completed', NULL, '2026-04-26 08:32:56'),
(2, 9, 'A+', 450, '2026-04-26', '', NULL, 'completed', NULL, '2026-04-26 08:33:02'),
(3, 9, 'A+', 450, '2026-04-26', '', NULL, 'completed', NULL, '2026-04-26 08:33:05'),
(4, 4, 'O+', 450, '2026-04-26', '', NULL, 'completed', NULL, '2026-04-26 08:33:08'),
(5, 9, 'A+', 450, '2026-04-26', '', NULL, 'completed', NULL, '2026-04-26 10:18:15');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `blood_type` varchar(5) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` char(1) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `medical_condition` text DEFAULT NULL,
  `admission_reason` text DEFAULT NULL,
  `medications` text DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `emergency_contact` varchar(100) DEFAULT NULL,
  `emergency_phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','donor','recipient') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `phone`, `address`, `latitude`, `longitude`, `city`, `blood_type`, `date_of_birth`, `gender`, `weight`, `medical_condition`, `admission_reason`, `medications`, `allergies`, `emergency_contact`, `emergency_phone`, `role`, `created_at`, `updated_at`, `reset_token`, `reset_token_expiry`) VALUES
(2, 'System Administrator', 'admin@vitaldrops.com', '$2y$10$uzJzRrJEtwKwd9pKmpmGcu8moKwXOUrxqS0.PKnJBCaknFwQXrdEW', NULL, NULL, NULL, NULL, NULL, 'O+', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'admin', '2026-04-22 13:03:36', '2026-04-22 13:03:36', NULL, NULL),
(3, 'Anupa Kadel', 'anupa22@gmail.com', '$2y$10$aUZtxaPZjldwOzw6aaNFeu53JR4zDxCfV1hpPaeT/xIc8Eg5mOQLe', '9700000000', '', 27.69550840, 85.37221680, '0', 'O+', '0000-00-00', 'F', NULL, '', NULL, '', '', NULL, NULL, 'donor', '2026-04-22 13:10:32', '2026-04-22 13:14:27', NULL, NULL),
(4, 'Sacheeta Thapa', 'sacheeta22@gmail.com', '$2y$10$PYIlW6YzewwqmfC/Tca1GO.PHms0Yeef8FHTNPvxD7xlJGGW.UmN6', '9876543210', '', 27.67501750, 85.35487930, '0', 'O+', '2003-07-22', 'F', NULL, 'Anemia', '', '', '', '', '', 'recipient', '2026-04-22 13:36:03', '2026-04-22 13:37:01', NULL, NULL),
(5, 'Unisha Kattel', 'unisha61kattel@gmail.com', '$2y$10$FxazGMfuoq1J1EnJl9rMAuriVuAYRQrD.uD3svjKfD4jCUJ0afO0K', '9804961168', '', 27.69474338, 85.37228054, '0', 'A+', '0000-00-00', 'F', NULL, '', NULL, '', '', NULL, NULL, 'donor', '2026-04-24 15:16:44', '2026-04-24 15:18:07', NULL, NULL),
(6, 'Sweta Kattel', 'sweta22@gmail.com', '$2y$10$CA9wbWW6LU1szlwy5RGTKeaMjXDBtpQFuFyMLWU2IEpnsz0UTMvay', '9800000000', '', 27.70584172, 85.36894462, '0', 'A+', '0000-00-00', 'F', NULL, '', NULL, '', '', NULL, NULL, 'donor', '2026-04-24 15:22:38', '2026-04-24 15:23:20', NULL, NULL),
(7, 'Sachin Gautam', 'sachin77@gmail.com', '$2y$10$AyvdqPQdswlx5ndpkKiAyOWk91qVGQTAeavrOYwC/bdF6N/xC3nP6', '9800000000', '', 27.70295699, 85.38003767, '0', 'A+', '0000-00-00', 'M', NULL, '', NULL, '', '', NULL, NULL, 'donor', '2026-04-24 15:28:21', '2026-04-24 15:28:38', NULL, NULL),
(8, 'Pawan Kattel', 'pawan77@gmail.com', '$2y$10$r0ed88uuSGDMV8pyeKRaMO7voBXIUqI6XK1d9ZArcL2ZdhwaxSkj6', '9800000555', '', 27.79295688, 85.36603767, '0', 'O+', '0000-00-00', 'M', NULL, '', NULL, '', '', NULL, NULL, 'donor', '2026-04-24 15:32:14', '2026-04-24 15:32:45', NULL, NULL),
(9, 'Sumit Koirala', 'sumit77@gmail.com', '$2y$10$RJ8.1ImLs/sZ0dMk9kjh6ezGIEIPx4Wm36R/wqxUepuQTQ5DcKDI.', '9800000666', '', 26.79295688, 82.36603767, '0', 'A+', '2001-01-17', 'M', NULL, 'Leukemia', '', '', '', '', '', 'recipient', '2026-04-24 15:45:32', '2026-04-24 16:23:27', NULL, NULL),
(10, 'Muna Lamsal', 'muna55@gmail.com', '$2y$10$AIRJqUEK32Xmd1qLAlDoY..nQNGbz6qWXZ/GYSjWYnY5mZbLgfn1O', '9870000000', '', 27.71299400, 85.34105443, '0', 'B+', '0000-00-00', 'F', 55.00, '', NULL, '', '', NULL, NULL, 'donor', '2026-04-26 09:55:58', '2026-04-26 09:57:15', NULL, NULL),
(11, 'Hemsagar Kattel', 'hemsagar55@gmail.com', '$2y$10$KV/UMJs68UzxsSPC83sCiuo.I34GMakByVJbkcsPRrqTUUNE9vmcu', '9842698147', '', 26.79295688, 85.34105443, '0', 'B+', '0000-00-00', 'M', NULL, 'Post Surgery', '', '', '', '', '', 'recipient', '2026-04-26 10:01:16', '2026-04-26 10:02:00', NULL, NULL),
(12, 'Indira Kattel', 'indira11@gmail.com', '$2y$10$1Nr0hzwn/cNFL9RhNJd4eeG.v5r7e349u7iUdFMhCdi/6kr9VfrO.', '9842634550', 'Kathmandu', 27.71231590, 85.34242107, '0', 'A+', '0000-00-00', 'F', NULL, '', NULL, '', '', NULL, NULL, 'donor', '2026-04-26 12:15:54', '2026-04-26 12:16:38', NULL, NULL),
(13, 'Aashika Sapkota', 'aashika11@gmail.com', '$2y$10$9wOvleJ1sn80nBvlF5hLUufi1GA1hDd/QotHbjTgTxzCYYQnHvvqW', '9875681145', '', 27.71311743, 85.34370242, '0', 'A+', '0000-00-00', 'F', 54.90, '', NULL, '', '', NULL, NULL, 'donor', '2026-04-26 12:21:23', '2026-04-26 12:22:55', NULL, NULL),
(14, 'Muskan Gurung', 'muskan11@gmail.com', '$2y$10$VsBAzdehHJruar7UK.9tYe1hX595dhnDJDwjeXEB7L/h8D0Q.GDgK', '9842634600', 'Birtamode', 26.63405716, 87.97890852, '0', 'A+', '0000-00-00', '', 60.00, '', NULL, '', '', NULL, NULL, 'donor', '2026-04-26 12:25:35', '2026-04-26 12:26:09', NULL, NULL),
(15, 'Saurabhi Shrestha', 'saurabhi11@gmail.com', '$2y$10$MRiWYP0L2DTym0na81A/1.4WGa5s1tL5/bR79R9MzbIikMiEb7/pa', '9845534550', NULL, 27.68954166, 85.37971451, '0', 'A+', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'recipient', '2026-04-26 12:36:51', '2026-04-26 12:36:51', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `donor_id` (`donor_id`),
  ADD KEY `recipient_id` (`recipient_id`),
  ADD KEY `status` (`status`),
  ADD KEY `appointment_date` (`appointment_date`);

--
-- Indexes for table `blood_inventory`
--
ALTER TABLE `blood_inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `blood_type` (`blood_type`),
  ADD KEY `blood_type_2` (`blood_type`);

--
-- Indexes for table `blood_requests`
--
ALTER TABLE `blood_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `donor_id` (`donor_id`),
  ADD KEY `status` (`status`),
  ADD KEY `urgency` (`urgency`),
  ADD KEY `recipient_id` (`recipient_id`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `donor_id` (`donor_id`),
  ADD KEY `blood_type` (`blood_type`),
  ADD KEY `donation_date` (`donation_date`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `health_records`
--
ALTER TABLE `health_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `donor_id` (`donor_id`),
  ADD KEY `recorded_date` (`recorded_date`);

--
-- Indexes for table `transfusion_history`
--
ALTER TABLE `transfusion_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipient_id` (`recipient_id`),
  ADD KEY `transfusion_date` (`transfusion_date`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `email_2` (`email`),
  ADD KEY `role` (`role`),
  ADD KEY `idx_latitude_longitude` (`latitude`,`longitude`),
  ADD KEY `idx_city` (`city`),
  ADD KEY `idx_role_availability` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `blood_inventory`
--
ALTER TABLE `blood_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `blood_requests`
--
ALTER TABLE `blood_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `health_records`
--
ALTER TABLE `health_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `transfusion_history`
--
ALTER TABLE `transfusion_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`donor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `blood_requests`
--
ALTER TABLE `blood_requests`
  ADD CONSTRAINT `blood_requests_ibfk_1` FOREIGN KEY (`donor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `blood_requests_ibfk_2` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`donor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `health_records`
--
ALTER TABLE `health_records`
  ADD CONSTRAINT `health_records_ibfk_1` FOREIGN KEY (`donor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transfusion_history`
--
ALTER TABLE `transfusion_history`
  ADD CONSTRAINT `transfusion_history_ibfk_1` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
