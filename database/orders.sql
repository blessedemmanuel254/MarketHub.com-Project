-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 04, 2026 at 12:05 PM
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
-- Database: `markethub`
--

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `order_code` varchar(50) DEFAULT NULL,
  `buyer_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_status` enum('Pending','Paid','Shipped','Completed','Cancelled') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `order_code`, `buyer_id`, `total_amount`, `order_status`, `created_at`) VALUES
(66, 'ORD-20260304-95201', 16, 67.00, 'Pending', '2026-03-04 09:05:15'),
(67, 'ORD-20260304-27673', 16, 201.00, 'Pending', '2026-03-04 09:13:52'),
(68, 'ORD-20260304-19820', 16, 26000.00, 'Pending', '2026-03-04 09:34:09'),
(69, 'ORD-20260304-96267', 16, 29248.00, 'Pending', '2026-03-04 09:41:51'),
(70, 'ORD-20260304-15701', 16, 14530.00, 'Pending', '2026-03-04 09:43:32'),
(71, 'ORD-20260304-28104', 16, 14530.00, 'Pending', '2026-03-04 09:44:25'),
(72, 'ORD-20260304-95341', 16, 14530.00, 'Pending', '2026-03-04 09:45:25'),
(73, 'ORD-20260304-37441', 16, 14530.00, 'Pending', '2026-03-04 09:46:27'),
(74, 'ORD-20260304-58925', 16, 1300.00, 'Pending', '2026-03-04 09:47:08'),
(75, 'ORD-20260304-34134', 16, 246.00, 'Pending', '2026-03-04 09:48:33'),
(76, 'ORD-20260304-57262', 16, 1340.00, 'Pending', '2026-03-04 09:50:54'),
(77, 'ORD-20260304-55373', 16, 67.00, 'Pending', '2026-03-04 09:53:28'),
(78, 'ORD-20260304-65880', 16, 2913.00, 'Pending', '2026-03-04 10:02:27'),
(79, 'ORD-20260304-71815', 16, 67.00, 'Pending', '2026-03-04 10:03:34'),
(80, 'ORD-20260304-48558', 16, 134.00, 'Pending', '2026-03-04 10:03:49'),
(81, 'ORD-20260304-46753', 16, 123.00, 'Pending', '2026-03-04 10:18:22'),
(89, 'ORD-20260304-72306', 16, 14340.00, 'Pending', '2026-03-04 10:40:57'),
(92, 'ORD-20260304-64535', 16, 104067.00, 'Pending', '2026-03-04 10:44:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `order_code` (`order_code`),
  ADD KEY `buyer_id` (`buyer_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
