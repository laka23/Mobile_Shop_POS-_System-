-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql100.byetcluster.com
-- Generation Time: Jan 09, 2026 at 11:00 PM
-- Server version: 10.6.22-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_40600774_pos_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `branch_code` char(3) NOT NULL,
  `location` varchar(200) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `name`, `branch_code`, `location`, `status`, `created_at`) VALUES
(1, 'Nexora', 'NEX', 'Horana', 'active', '2025-12-05 13:30:31');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`) VALUES
(1, 'Iphone', '2025-12-05 13:31:09'),
(2, 'Bt items', '2025-12-07 14:06:52');

-- --------------------------------------------------------

--
-- Table structure for table `license`
--

CREATE TABLE `license` (
  `id` int(11) NOT NULL,
  `registration_code` varchar(100) NOT NULL,
  `company_name` varchar(200) NOT NULL,
  `activation_date` datetime NOT NULL,
  `status` enum('active','inactive','expired') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `license`
--

INSERT INTO `license` (`id`, `registration_code`, `company_name`, `activation_date`, `status`, `created_at`) VALUES
(1, 'TEST-1234-ABCD-5678', 'NEXTORA', '2025-12-04 18:36:36', 'active', '2025-12-05 02:36:36');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `category_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category_id`, `description`, `created_at`) VALUES
(1, 'Smart Phone', 1, 'Wholesale', '2025-12-05 13:31:41'),
(2, 'Handsfree', 2, 'svfgsdrg', '2025-12-07 14:07:14');

-- --------------------------------------------------------

--
-- Table structure for table `repairs`
--

CREATE TABLE `repairs` (
  `id` int(11) NOT NULL,
  `repair_number` varchar(50) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `device_name` varchar(100) NOT NULL,
  `imei_last5` varchar(5) DEFAULT NULL,
  `error_description` text NOT NULL,
  `repair_cost` decimal(10,2) NOT NULL,
  `parts_replaced` text DEFAULT NULL,
  `status` enum('pending','completed') DEFAULT 'pending',
  `registered_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `repairs`
--

INSERT INTO `repairs` (`id`, `repair_number`, `branch_id`, `user_id`, `customer_name`, `device_name`, `imei_last5`, `error_description`, `repair_cost`, `parts_replaced`, `status`, `registered_date`, `completed_date`) VALUES
(1, 'REP202512057552', 1, 2, '0', 'Samsung Note 25 ultra ', '75421', 'Display', '10500.00', 'OLD', 'completed', '2025-12-05 13:40:56', '2025-12-05 05:42:31'),
(2, 'REP202512057602', 1, 2, '0', 'Samsung Note 25 ultra ', '45334', 'fcgndfgnnsdrt', '1000.00', '', 'completed', '2025-12-05 15:14:10', '2025-12-05 07:14:14'),
(3, 'REP202512077975', 1, 2, '0', 'Note 10 Plus', '98765', 'Cpu error', '5200.00', 'motherbboard', 'completed', '2025-12-07 17:09:22', '2025-12-07 09:09:52'),
(4, 'REP202512093930', 1, 2, '0', 'rog 2', '75421', 'mnjoi', '20000.00', 'dgfd', 'pending', '2025-12-09 14:37:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `returns`
--

CREATE TABLE `returns` (
  `id` int(11) NOT NULL,
  `return_number` varchar(50) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `replacement_given` tinyint(1) DEFAULT 0,
  `replacement_product_id` int(11) DEFAULT NULL,
  `replacement_quantity` int(11) DEFAULT NULL,
  `not_working` tinyint(1) DEFAULT 0,
  `return_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `returns`
--

INSERT INTO `returns` (`id`, `return_number`, `branch_id`, `user_id`, `product_id`, `customer_name`, `quantity`, `reason`, `replacement_given`, `replacement_product_id`, `replacement_quantity`, `not_working`, `return_date`) VALUES
(1, 'RET202512054508', 1, 2, 1, 'kevin', 1, 'warranty ', 1, 1, 1, 0, '2025-12-05 13:38:00');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `sale_number` varchar(50) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','card','bank_transfer') NOT NULL DEFAULT 'cash',
  `sale_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `sale_number`, `branch_id`, `user_id`, `total_amount`, `payment_method`, `sale_date`) VALUES
(1, 'SAL202512052689', 1, 2, '340000.00', '', '2025-12-05 13:36:11'),
(2, 'SAL202512052540', 1, 2, '205225.00', '', '2025-12-05 15:06:19'),
(3, 'SAL202512052677', 1, 2, '180000.00', '', '2025-12-05 15:07:03'),
(4, 'SAL202512055149', 1, 2, '22500.00', '', '2025-12-05 15:07:17'),
(5, 'SAL202512054866', 1, 2, '180000.00', '', '2025-12-05 15:14:30'),
(6, 'SAL202512052683', 1, 2, '2000.00', '', '2025-12-05 15:15:16'),
(7, 'SAL202512058919', 1, 2, '20000.00', '', '2025-12-05 15:29:12'),
(8, 'SAL202512077583', 1, 2, '100.00', '', '2025-12-07 12:45:26'),
(9, 'SAL202512070432', 1, 2, '100.00', '', '2025-12-07 12:50:20'),
(10, 'SAL202512077842', 1, 2, '200.00', '', '2025-12-07 13:12:42'),
(11, 'SAL202512078006', 1, 2, '200.00', '', '2025-12-07 13:17:26'),
(12, 'SAL202512073546', 1, 2, '180000.00', '', '2025-12-07 13:33:11'),
(13, 'SAL202512078029', 1, 2, '182100.00', '', '2025-12-07 13:34:58'),
(14, 'SAL202512076359', 1, 2, '100.00', '', '2025-12-07 13:37:32'),
(15, 'SAL202512079425', 1, 2, '200.00', '', '2025-12-07 13:48:57'),
(16, 'SAL202512078281', 1, 2, '180000.00', '', '2025-12-07 13:49:12'),
(17, 'SAL202512075207', 1, 2, '950.00', '', '2025-12-07 14:09:31'),
(18, 'SAL202512073771', 1, 2, '200.00', 'cash', '2025-12-07 14:14:07'),
(19, 'SAL202512070648', 1, 2, '150.00', 'cash', '2025-12-07 14:14:41'),
(20, 'SAL202512078678', 1, 2, '3700.00', '', '2025-12-07 14:46:38'),
(21, 'SAL202512078970', 1, 2, '1700.00', '', '2025-12-07 14:47:18'),
(22, 'SAL202512073459', 1, 2, '100.00', '', '2025-12-07 14:51:42'),
(23, 'SAL202512074029', 1, 2, '180000.00', '', '2025-12-07 14:52:16'),
(24, 'SAL202512074747', 1, 2, '1700.00', '', '2025-12-07 14:54:14'),
(25, 'SAL202512079161', 1, 2, '100.00', '', '2025-12-07 14:55:57'),
(26, 'SAL202512074380', 1, 2, '1500.00', '', '2025-12-07 14:56:57'),
(27, 'SAL202512074379', 1, 2, '150.00', '', '2025-12-07 14:59:55'),
(28, 'SAL202512075654', 1, 2, '1.00', '', '2025-12-07 15:02:20'),
(29, 'TEST1765119777', 1, 2, '500.00', 'cash', '2025-12-07 15:02:56'),
(30, 'SAL202512079167', 1, 2, '100.00', '', '2025-12-07 15:03:50'),
(31, 'SAL202512073445', 1, 2, '1700.00', '', '2025-12-07 15:04:03'),
(32, 'SAL202512073841', 1, 2, '100.00', '', '2025-12-07 15:10:48'),
(33, 'SAL202512073434', 1, 2, '100.00', '', '2025-12-07 15:14:59'),
(34, 'SAL202512075985', 1, 2, '100.00', '', '2025-12-07 15:19:59'),
(35, 'SAL202512074336', 1, 2, '1700.00', '', '2025-12-07 15:20:14'),
(36, 'SAL202512079122', 1, 2, '1700.00', '', '2025-12-07 15:28:47'),
(37, 'SAL202512078227', 1, 2, '100.00', '', '2025-12-07 15:31:30'),
(38, 'SAL202512078479', 1, 2, '100.00', '', '2025-12-07 15:34:10'),
(39, 'SAL202512073491', 1, 2, '2500.00', '', '2025-12-07 15:44:29'),
(40, 'SAL202512071050', 1, 2, '1700.00', '', '2025-12-07 15:46:42'),
(41, 'SAL202512078506', 1, 2, '1000.00', '', '2025-12-07 15:48:37'),
(42, 'SAL202512078570', 1, 2, '100.00', '', '2025-12-07 15:49:10'),
(43, 'SAL202512073025', 1, 2, '14363.00', '', '2025-12-07 15:52:47'),
(44, 'SAL202512075179', 1, 2, '2000.00', '', '2025-12-07 15:54:49'),
(45, 'SAL202512076576', 1, 2, '142.00', '', '2025-12-07 15:58:43'),
(46, 'SAL202512073155', 1, 2, '150.00', '', '2025-12-07 15:59:33'),
(47, 'SAL202512077060', 1, 2, '64641.10', '', '2025-12-07 15:59:59'),
(48, 'SAL202512077569', 1, 2, '64641.10', '', '2025-12-07 16:03:35'),
(49, 'SAL202512071512', 1, 2, '2500.00', '', '2025-12-07 16:03:51'),
(50, 'SAL202512072818', 1, 2, '665646.00', '', '2025-12-07 16:05:05'),
(51, 'SAL202512075862', 1, 2, '5444.00', '', '2025-12-07 16:06:24'),
(52, 'SAL202512075035', 1, 2, '45253.00', '', '2025-12-07 16:07:51'),
(53, 'SAL202512075708', 1, 2, '45253.00', '', '2025-12-07 16:09:18'),
(54, 'SAL202512078446', 1, 2, '45253.00', '', '2025-12-07 16:09:23'),
(55, 'SAL202512073807', 1, 2, '546.00', '', '2025-12-07 16:14:31'),
(56, 'SAL202512072780', 1, 2, '436.00', '', '2025-12-07 16:24:33'),
(57, 'SAL202512075272', 1, 2, '483.00', '', '2025-12-07 16:26:31'),
(58, 'SAL202512072693', 1, 2, '483.00', '', '2025-12-07 16:27:22'),
(59, 'SAL202512071477', 1, 2, '24212.00', '', '2025-12-07 16:43:44'),
(60, 'SAL202512078234', 1, 2, '1700.00', '', '2025-12-07 17:12:03'),
(61, 'SAL202512163043', 1, 2, '41.00', '', '2025-12-16 02:03:17');

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `manual_item_name` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount` decimal(5,2) DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `manual_item_name`, `quantity`, `price`, `discount`, `subtotal`) VALUES
(1, 1, 1, NULL, 2, '200000.00', '15.00', '340000.00'),
(2, 2, 1, NULL, 1, '200000.00', '10.00', '180000.00'),
(4, 3, 1, NULL, 1, '200000.00', '10.00', '180000.00'),
(6, 5, 1, NULL, 1, '200000.00', '10.00', '180000.00'),
(13, 12, 1, NULL, 1, '200000.00', '10.00', '180000.00'),
(17, 16, 1, NULL, 1, '200000.00', '10.00', '180000.00'),
(22, 21, 2, NULL, 1, '1700.00', '0.00', '1700.00'),
(24, 23, 1, NULL, 1, '200000.00', '10.00', '180000.00'),
(25, 24, 2, NULL, 1, '1700.00', '0.00', '1700.00'),
(32, 31, 2, NULL, 1, '1700.00', '0.00', '1700.00'),
(36, 35, 2, NULL, 1, '1700.00', '0.00', '1700.00'),
(37, 36, 2, NULL, 1, '1700.00', '0.00', '1700.00'),
(41, 40, 2, NULL, 1, '1700.00', '0.00', '1700.00'),
(49, 48, 0, 'srfgsaf', 1, '64641.10', '0.00', '64641.10'),
(50, 49, 0, 'fsafsaf', 1, '2500.00', '0.00', '2500.00'),
(51, 50, 0, 'wefawefawfaf', 1, '665646.00', '0.00', '665646.00'),
(52, 51, 0, 'asefsarfsr', 1, '5444.00', '0.00', '5444.00'),
(53, 52, 0, 'erfaf', 1, '45253.00', '0.00', '45253.00'),
(54, 53, 0, 'erfaf', 1, '45253.00', '0.00', '45253.00'),
(55, 54, 0, 'erfaf', 1, '45253.00', '0.00', '45253.00'),
(56, 55, 0, 'WDD', 1, '546.00', '0.00', '546.00'),
(57, 56, 0, 'dadafa', 1, '436.00', '0.00', '436.00'),
(58, 57, 0, 'rthrdth', 1, '483.00', '0.00', '483.00'),
(59, 58, 0, 'rthrdth', 1, '483.00', '0.00', '483.00'),
(60, 59, 0, 'dhgdg', 1, '412.00', '0.00', '412.00'),
(61, 59, 2, NULL, 14, '1700.00', '0.00', '23800.00'),
(62, 60, 2, NULL, 1, '1700.00', '0.00', '1700.00'),
(63, 61, 0, 'r yuh (ryr)', 1, '41.00', '0.00', '41.00');

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

CREATE TABLE `stock` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `price` decimal(10,2) NOT NULL,
  `discount` decimal(5,2) DEFAULT 0.00,
  `barcode` varchar(50) NOT NULL,
  `alert_level` int(11) DEFAULT 5,
  `added_by` int(11) DEFAULT NULL,
  `added_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `stock`
--

INSERT INTO `stock` (`id`, `product_id`, `branch_id`, `quantity`, `price`, `discount`, `barcode`, `alert_level`, `added_by`, `added_date`) VALUES
(1, 1, 1, 31, '200000.00', '10.00', 'NEX000014987', 5, NULL, '2025-12-05 13:32:44'),
(2, 2, 1, 9, '1700.00', '0.00', 'NEX000028238', 5, NULL, '2025-12-07 14:07:30');

-- --------------------------------------------------------

--
-- Table structure for table `stock_transfers`
--

CREATE TABLE `stock_transfers` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `from_branch_id` int(11) NOT NULL,
  `to_branch_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `transferred_by` int(11) NOT NULL,
  `transfer_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `branch_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `role`, `branch_id`, `status`, `created_at`) VALUES
(1, 'admin', '$2y$10$3jCeckRQkNGcxD2g6EK.1eUq.Oui.ghyVdiGFgaCWWN252jZ9OS2u', 'System Administrator', 'admin', NULL, 'active', '2025-12-04 19:47:36'),
(2, 'zed', '$2y$10$zzNOmf337MROSJ4Pd/XiTOHSYxPaiZTICuEh8a7ouCZ7bICgP..gG', 'zed', 'user', 1, 'active', '2025-12-05 13:33:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `branch_code` (`branch_code`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `license`
--
ALTER TABLE `license`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `registration_code` (`registration_code`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `repairs`
--
ALTER TABLE `repairs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `repair_number` (`repair_number`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `return_number` (`return_number`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `replacement_product_id` (`replacement_product_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sale_number` (`sale_number`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD UNIQUE KEY `unique_product_branch` (`product_id`,`branch_id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `added_by` (`added_by`);

--
-- Indexes for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `from_branch_id` (`from_branch_id`),
  ADD KEY `to_branch_id` (`to_branch_id`),
  ADD KEY `transferred_by` (`transferred_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `branch_id` (`branch_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `license`
--
ALTER TABLE `license`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `repairs`
--
ALTER TABLE `repairs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `repairs`
--
ALTER TABLE `repairs`
  ADD CONSTRAINT `repairs_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `repairs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `returns`
--
ALTER TABLE `returns`
  ADD CONSTRAINT `returns_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `returns_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `returns_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `returns_ibfk_4` FOREIGN KEY (`replacement_product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  ADD CONSTRAINT `stock_transfers_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `stock_transfers_ibfk_2` FOREIGN KEY (`from_branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `stock_transfers_ibfk_3` FOREIGN KEY (`to_branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `stock_transfers_ibfk_4` FOREIGN KEY (`transferred_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
