-- phpMyAdmin SQL Dump
-- version 5.2.1
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tmo`
--

-- --------------------------------------------------------

--
-- Table structure for table `drivers_list`
--

CREATE TABLE `drivers_list` (
  `id` int(30) NOT NULL,
  `license_id_no` varchar(100) NOT NULL,
  `name` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = active, 2 = suspended, 3 = banned',
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drivers_list`
--

INSERT INTO `drivers_list` (`id`, `license_id_no`, `name`, `status`, `date_created`, `date_updated`) VALUES
(1, 'C12-222-000122', 'Apolonia, Reyven D', 1, '2021-08-19 10:45:48', '2024-05-19 17:05:11'),
(4, 'D12-111-000123', 'Mendoza, April Joemhel L', 1, '2021-08-19 14:56:09', '2024-05-19 17:02:14');

-- --------------------------------------------------------

--
-- Table structure for table `drivers_meta`
--

CREATE TABLE `drivers_meta` (
  `driver_id` int(30) DEFAULT NULL,
  `meta_field` text NOT NULL,
  `meta_value` text NOT NULL,
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drivers_meta`
--

INSERT INTO `drivers_meta` (`driver_id`, `meta_field`, `meta_value`, `date_updated`) VALUES
(4, 'license_id_no', 'D12-111-000123', '2024-05-19 17:02:43'),
(4, 'lastname', 'Mendoza', '2024-05-19 17:02:43'),
(4, 'firstname', 'April Joemhel', '2024-05-19 17:02:43'),
(4, 'middlename', 'L', '2024-05-19 17:02:43'),
(4, 'dob', '2002-04-22', '2024-05-19 17:02:43'),
(4, 'present_address', 'Dalima, Tuy', '2024-05-19 17:02:43'),
(4, 'permanent_address', 'Dalima, Tuy', '2024-05-19 17:02:43'),
(4, 'civil_status', 'Single', '2024-05-19 17:02:43'),
(4, 'nationality', 'Filipino', '2024-05-19 17:02:43'),
(4, 'contact', '09123789456', '2024-05-19 17:02:43'),
(4, 'license_type', 'Student', '2024-05-19 17:02:43'),
(4, 'image_path', 'uploads/drivers/4.jpg', '2024-05-19 17:02:43'),
(4, 'driver_id', '4', '2024-05-19 17:02:43'),
(4, 'image_path', 'uploads/drivers/4.jpg', '2024-05-19 17:02:43'),
(1, 'license_id_no', 'C12-222-000122', '2024-05-19 17:05:11'),
(1, 'lastname', 'Apolonia', '2024-05-19 17:05:11'),
(1, 'firstname', 'Reyven', '2024-05-19 17:05:11'),
(1, 'middlename', 'D', '2024-05-19 17:05:11'),
(1, 'dob', '1997-06-23', '2024-05-19 17:05:11'),
(1, 'present_address', 'Sambungan, Calatagan', '2024-05-19 17:05:11'),
(1, 'permanent_address', 'Sambungan, Calatagan', '2024-05-19 17:05:11'),
(1, 'civil_status', 'Single', '2024-05-19 17:05:11'),
(1, 'nationality', 'Filipino', '2024-05-19 17:05:11'),
(1, 'contact', '09123456789', '2024-05-19 17:05:11'),
(1, 'license_type', 'Non-Professional', '2024-05-19 17:05:11'),
(1, 'image_path', 'uploads/drivers/1.jpg', '2024-05-19 17:05:11'),
(1, 'driver_id', '1', '2024-05-19 17:05:11'),
(1, 'image_path', 'uploads/drivers/1.jpg', '2024-05-19 17:05:11');

-- --------------------------------------------------------

--
-- Table structure for table `offenses`
--

CREATE TABLE `offenses` (
  `id` int(30) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `fine` float NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0=Inactive, 1=Active',
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `offenses`
--

INSERT INTO `offenses` (`id`, `code`, `name`, `description`, `fine`, `status`, `date_created`, `date_updated`) VALUES
(1, 'OT-1001', 'Driving without License', '', 650, 1, '2021-08-19 09:14:43', '2024-05-19 17:08:27'),
(2, 'TO-1002', 'Running Over Speed Limit', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', 1000, 1, '2021-08-19 13:54:51', '2024-05-19 17:08:21');

-- --------------------------------------------------------

--
-- Table structure for table `offense_items`
--

CREATE TABLE `offense_items` (
  `driver_offense_id` int(30) NOT NULL,
  `offense_id` int(30) DEFAULT NULL,
  `fine` float NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=pending, 1=paid',
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `offense_items`
--

INSERT INTO `offense_items` (`driver_offense_id`, `offense_id`, `fine`, `status`, `date_created`) VALUES
(1, 1, 650, 1, '2021-08-18 15:00:00'),
(1, 2, 1000, 1, '2021-08-18 15:00:00'),
(3, 1, 650, 1, '2024-12-25 21:00:00'),
(3, 2, 1000, 1, '2024-12-25 21:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `offense_list`
--

CREATE TABLE `offense_list` (
  `id` int(30) NOT NULL,
  `driver_id` int(30) NOT NULL,
  `officer_name` text NOT NULL,
  `officer_id` text NOT NULL,
  `ticket_no` text NOT NULL,
  `total_amount` float NOT NULL,
  `remarks` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=pending, 1=paid',
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `offense_list`
--

INSERT INTO `offense_list` (`id`, `driver_id`, `officer_name`, `officer_id`, `ticket_no`, `total_amount`, `remarks`, `status`, `date_created`, `date_updated`) VALUES
(1, 1, 'Aldrin Firme', 'OFC-789456123', '123456789', 1650, '', 1, '2021-08-18 15:00:00', '2024-05-19 17:07:34'),
(3, 1, 'Ron Leo Salazar', '9', '1431241241', 1650, '', 1, '2024-12-25 21:00:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `officers`
--

CREATE TABLE `officers` (
  `id` int(11) NOT NULL,
  `tmoId` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `birthday` date NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `address` text NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `officers`
--

INSERT INTO `officers` (`id`, `tmoId`, `name`, `birthday`, `gender`, `address`, `username`, `password`, `created_at`, `updated_at`) VALUES
(3, 'TMO2781', 'Reyven Apolonia', '2002-09-19', 'Male', 'Nasugbu, Batangas', 'Shylack', '$2y$10$adpoZ7MTaQQ.rUxYgoY0SOkd9tGPmfwxPMklc23TU0ZN8in9.LCyy', '2024-12-15 08:39:28', '2024-12-15 08:39:28'),
(4, 'TMO9650', 'John Person', '2024-12-15', 'Male', 'Nasugbu, Batangas', 'jpacia', '$2y$10$d8hOifNB.SHUTxCNp81Z2ebyKODNTreBxVWTtfTCILhLzrRrSt./q', '2024-12-15 08:43:19', '2024-12-15 08:43:19'),
(26, 'TMO5224', 'April Joemhel Mendoza', '2024-12-15', 'Male', 'Nasugbu, Batangas', 'misoy', '$2y$10$5xdbAePTSimqGn.RN.dFpOOdrGg391ITKe0TATAby16dGvW/x.s82', '2024-12-15 09:10:19', '2024-12-15 09:10:19');

-- --------------------------------------------------------

--
-- Table structure for table `system_info`
--

CREATE TABLE `system_info` (
  `id` int(30) NOT NULL,
  `meta_field` text NOT NULL,
  `meta_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_info`
--

INSERT INTO `system_info` (`id`, `meta_field`, `meta_value`) VALUES
(1, 'name', 'TMO Traffic Management System'),
(6, 'short_name', 'E-Tiket'),
(11, 'logo', 'uploads/1716112740_E-Tiket.png'),
(13, 'user_avatar', 'uploads/user_avatar.jpg'),
(14, 'cover', 'uploads/1716112740_pngtree-modern-city-traffic-background-image_198183.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(50) NOT NULL,
  `firstname` varchar(250) NOT NULL,
  `lastname` varchar(250) NOT NULL,
  `username` text NOT NULL,
  `password` text NOT NULL,
  `avatar` text DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `type` tinyint(1) NOT NULL DEFAULT 0,
  `date_added` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `accident_reports`
--

CREATE TABLE `accident_reports` (
  `id` int(30) NOT NULL,
  `driver_id` int(30) NOT NULL,
  `officer_id` int(30) DEFAULT NULL,
  `location` text NOT NULL,
  `description` text NOT NULL,
  `date_of_accident` datetime NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `lastname`, `username`, `password`, `avatar`, `last_login`, `type`, `date_added`, `date_updated`) VALUES
(1, 'John Person', 'Pacia', 'admin', '21232f297a57a5a743894a0e4a801fc3', 'uploads/1716108240_439446902_804506751145406_5129286335223214712_n.jpg', NULL, 1, '2021-01-20 14:02:37', '2024-05-19 16:44:10'),
(9, 'Ron Leo', 'Salazar', 'enforcer1', '200820e3227815ed1756a6b531e7e0d2', 'uploads/1716108600_RON.jpg', NULL, 2, '2021-08-19 09:24:25', '2024-05-19 16:50:19'),
(10, 'Aaron Josh', 'Kao', 'enforcer2', '200820e3227815ed1756a6b531e7e0d2', 'uploads/1716108660_4e01b3ec-afce-4b94-bfd2-953a234f469b.jpg', NULL, 2, '2024-05-19 16:51:47', NULL),
(11, 'Reyven', 'Apolonia', 'Shylack', '81dc9bdb52d04dc20036dbd8313ed055', NULL, NULL, 1, '2024-12-15 13:05:28', NULL),
(12, 'April Joemhel', 'Mendoza', 'misoy', '81dc9bdb52d04dc20036dbd8313ed055', NULL, NULL, 1, '2024-12-15 13:06:54', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `drivers_list`
--
ALTER TABLE `drivers_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `drivers_meta`
--
ALTER TABLE `drivers_meta`
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `offenses`
--
ALTER TABLE `offenses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `offense_items`
--
ALTER TABLE `offense_items`
  ADD KEY `driver_offense_id` (`driver_offense_id`),
  ADD KEY `offense_id` (`offense_id`);

--
-- Indexes for table `offense_list`
--
ALTER TABLE `offense_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `officers`
--
ALTER TABLE `officers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tmoId` (`tmoId`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `system_info`
--
ALTER TABLE `system_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `accident_reports`
--
ALTER TABLE `accident_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `officer_id` (`officer_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `drivers_list`
--
ALTER TABLE `drivers_list`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `offenses`
--
ALTER TABLE `offenses`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `offense_list`
--
ALTER TABLE `offense_list`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `officers`
--
ALTER TABLE `officers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `system_info`
--
ALTER TABLE `system_info`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `accident_reports`
--
ALTER TABLE `accident_reports`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `drivers_meta`
--
ALTER TABLE `drivers_meta`
  ADD CONSTRAINT `drivers_meta_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `drivers_list` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `offense_items`
--
ALTER TABLE `offense_items`
  ADD CONSTRAINT `offense_items_ibfk_1` FOREIGN KEY (`driver_offense_id`) REFERENCES `offense_list` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `offense_items_ibfk_2` FOREIGN KEY (`offense_id`) REFERENCES `offenses` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION;

--
-- Constraints for table `offense_list`
--
ALTER TABLE `offense_list`
  ADD CONSTRAINT `offense_list_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `drivers_list` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `accident_reports`
--
ALTER TABLE `accident_reports`
  ADD CONSTRAINT `fk_accident_reports_driver` FOREIGN KEY (`driver_id`) REFERENCES `drivers_list` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_accident_reports_officer` FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;