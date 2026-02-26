-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 26, 2026 at 10:55 AM
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
-- Table structure for table `productservicesrentals`
--

CREATE TABLE `productservicesrentals` (
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `image_path` varchar(255) NOT NULL,
  `image_hash` varchar(32) DEFAULT NULL,
  `image_width` int(11) DEFAULT NULL,
  `image_height` int(11) DEFAULT NULL,
  `image_size_kb` int(11) DEFAULT NULL,
  `image_format` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `productservicesrentals`
--

INSERT INTO `productservicesrentals` (`product_id`, `user_id`, `product_name`, `category`, `price`, `stock_quantity`, `image_path`, `image_hash`, `image_width`, `image_height`, `image_size_kb`, `image_format`, `status`, `created_at`, `updated_at`) VALUES
(1, 17, 'Passion Juice', 'Food & Snacks', 40.00, 56, 'uploads/products/product_699f6e94012145.78607241.webp', '197441253198dfbd6ec2ead44aff3e40', 700, 700, 57, 'webp', 'active', '2026-02-25 21:50:12', '2026-02-25 21:50:12'),
(2, 17, 'Bike', 'Home Items', 13000.00, 13, 'uploads/products/product_699f7d45913b39.70833140.webp', '4688f6d846fc96bdba4db3f75f16d9b3', 700, 519, 55, 'webp', 'active', '2026-02-25 22:52:53', '2026-02-25 22:52:53'),
(3, 17, 'jug', 'Home Items', 1300.00, 45, 'uploads/products/product_699f7dab95bf65.10914891.webp', '902284afaaefe5a5a6f55c09cea05089', 700, 700, 40, 'webp', 'active', '2026-02-25 22:54:35', '2026-02-25 22:54:35'),
(4, 19, 'Passion Juice', 'Food & Snacks', 40.00, 34, 'uploads/products/product_69a017086d9a78.65234946.webp', '57cce22a22b1386a867d231a364f5109', 700, 700, 33, 'webp', 'active', '2026-02-26 09:48:56', '2026-02-26 09:48:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `productservicesrentals`
--
ALTER TABLE `productservicesrentals`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `idx_user_created` (`user_id`,`created_at`),
  ADD KEY `idx_user_product` (`user_id`,`product_name`),
  ADD KEY `idx_user_image` (`user_id`,`image_hash`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `productservicesrentals`
--
ALTER TABLE `productservicesrentals`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `productservicesrentals`
--
ALTER TABLE `productservicesrentals`
  ADD CONSTRAINT `productservicesrentals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
