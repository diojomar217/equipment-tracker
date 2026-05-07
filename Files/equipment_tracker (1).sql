-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 07, 2026 at 03:51 AM
-- Server version: 8.0.42
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `equipment_tracker`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`) VALUES
(1, 'Computer', '2026-05-07 02:14:35'),
(2, 'Tool', '2026-05-07 02:14:35'),
(3, 'Furniture', '2026-05-07 02:14:35'),
(4, 'Electronics', '2026-05-07 02:14:35'),
(5, 'Lab Equipment', '2026-05-07 02:14:35');

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `id` int NOT NULL,
  `name` varchar(150) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `status` enum('AVAILABLE','BORROWED','MAINTENANCE') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'AVAILABLE',
  `assigned_to` varchar(100) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `return_location` varchar(150) NOT NULL,
  `qr_code` text,
  `image` varchar(255) DEFAULT NULL,
  `status_updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`id`, `name`, `category`, `status`, `assigned_to`, `location`, `return_location`, `qr_code`, `image`, `status_updated_at`, `created_at`) VALUES
(1, 'Power Station', '4', 'AVAILABLE', NULL, '2', '2', 'equipment_id=1', 'uploads/equipment/1778083823_c7edc135759bf12a.png', '2026-05-07 09:48:10', '2026-05-06 22:48:01');

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `name`, `created_at`) VALUES
(1, 'Room 101', '2026-05-07 02:11:32'),
(2, 'Storage', '2026-05-07 02:11:32'),
(3, 'Office', '2026-05-07 02:11:32'),
(4, 'Lab', '2026-05-07 02:11:32'),
(5, 'TEST', '2026-05-07 02:14:55');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int NOT NULL,
  `equipment_id` int NOT NULL,
  `action` varchar(50) DEFAULT NULL,
  `user` varchar(100) DEFAULT NULL,
  `notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `equipment_id`, `action`, `user`, `notes`, `created_at`) VALUES
(1, 1, 'CHECK_OUT', 'staff', NULL, '2026-05-07 01:31:50'),
(2, 1, 'CHECK_IN', 'staff', NULL, '2026-05-07 01:31:54'),
(3, 1, 'CHECK_OUT', 'staff', NULL, '2026-05-07 01:34:01'),
(4, 1, 'CHECK_IN', 'staff', NULL, '2026-05-07 01:34:04'),
(5, 1, 'BORROW', 'staff', NULL, '2026-05-07 01:53:48'),
(6, 1, 'RETURN', 'staff', NULL, '2026-05-07 01:54:17'),
(7, 1, 'BORROW', 'staff', NULL, '2026-05-07 01:55:15'),
(8, 1, 'RETURN', 'staff', NULL, '2026-05-07 02:04:08'),
(9, 1, 'BORROW', 'staff', NULL, '2026-05-07 02:05:58'),
(10, 1, 'RETURN', 'staff', NULL, '2026-05-07 02:06:05'),
(11, 1, 'MAINTENANCE', 'admin', NULL, '2026-05-07 07:29:55'),
(12, 1, 'COMPLETE_MAINTENANCE', 'admin', NULL, '2026-05-07 07:34:46'),
(13, 1, 'BORROW', 'staff', NULL, '2026-05-07 07:58:27'),
(14, 1, 'RETURN', 'staff', NULL, '2026-05-07 07:58:32'),
(15, 1, 'BORROW', 'staff', NULL, '2026-05-07 07:58:39'),
(16, 1, 'RETURN', 'staff', NULL, '2026-05-07 09:40:27'),
(17, 1, 'BORROW', 'staff', NULL, '2026-05-07 09:40:42'),
(18, 1, 'RETURN', 'staff', NULL, '2026-05-07 09:40:46'),
(19, 1, 'BORROW', 'staff', NULL, '2026-05-07 09:41:26'),
(20, 1, 'RETURN', 'staff', NULL, '2026-05-07 09:43:44'),
(21, 1, 'BORROW', 'staff', NULL, '2026-05-07 09:47:41'),
(22, 1, 'RETURN', 'staff', NULL, '2026-05-07 09:48:10');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `equipment_id` int DEFAULT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'general',
  `status` enum('UNREAD','READ') NOT NULL DEFAULT 'UNREAD',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Staff') NOT NULL DEFAULT 'Staff',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'admin', 'John Doe', NULL, '$2y$10$GG5ja5e0ML3VLw1j.C.XZuNdVhbkHg2dPXqi5nkknZxsRqvl9RJSO', 'Admin', '2026-05-06 20:39:10'),
(2, 'staff', 'Juan Dela Cruz', NULL, '$2y$10$01hQqA1gx/v34FY/vnkWz.gF2T9qPY8Sxsr1A6otoqzhrkk1snv2C', 'Staff', '2026-05-06 20:39:10');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipment_id` (`equipment_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipment_id` (`equipment_id`),
  ADD KEY `status` (`status`),
  ADD KEY `type` (`type`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
