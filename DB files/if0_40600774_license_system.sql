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
-- Database: `if0_40600774_license_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `license_devices`
--

CREATE TABLE `license_devices` (
  `id` int(11) NOT NULL,
  `license_key` varchar(100) DEFAULT NULL,
  `device_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `license_devices`
--

INSERT INTO `license_devices` (`id`, `license_key`, `device_id`, `created_at`) VALUES
(0, 'TEST-1234-ABCD-5678', 'PC01', '2025-12-05 02:34:44'),
(0, 'TEST-1234-ABCD-5678', '8b0ca8fadc3c97508573be2d722708fe', '2025-12-05 02:36:36');

-- --------------------------------------------------------

--
-- Table structure for table `license_keys`
--

CREATE TABLE `license_keys` (
  `id` int(11) NOT NULL,
  `license_key` varchar(100) DEFAULT NULL,
  `company_name` varchar(200) DEFAULT NULL,
  `license_type` enum('permanent','subscription') DEFAULT 'permanent',
  `max_branches` int(11) DEFAULT 1,
  `max_devices` int(11) DEFAULT 1,
  `expiry_date` date DEFAULT NULL,
  `status` enum('active','inactive','expired') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `license_keys`
--

INSERT INTO `license_keys` (`id`, `license_key`, `company_name`, `license_type`, `max_branches`, `max_devices`, `expiry_date`, `status`, `created_at`) VALUES
(0, 'LIC-BC507F02924E937A', 'G NET', 'subscription', 5, 3, '2025-12-31', 'active', '2025-12-05 02:51:37');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
