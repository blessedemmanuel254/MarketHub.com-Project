-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 26, 2026 at 01:10 PM
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
-- Database: `makethub`
--

-- --------------------------------------------------------

--
-- Table structure for table `agent_commissions`
--

CREATE TABLE `agent_commissions` (
  `id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `source_user_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `commission_type` enum('activation','advertising') DEFAULT 'activation',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `status` enum('pending','paid') DEFAULT 'paid'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `agent_commissions`
--

INSERT INTO `agent_commissions` (`id`, `agent_id`, `source_user_id`, `level`, `amount`, `commission_type`, `created_at`, `status`) VALUES
(12, 32, 33, 1, 100.00, 'activation', '2026-03-24 02:19:32', 'paid'),
(11, 29, 32, 2, 40.00, 'activation', '2026-03-24 02:02:55', 'paid'),
(10, 31, 32, 1, 100.00, 'activation', '2026-03-24 02:02:55', 'paid'),
(9, 29, 31, 1, 100.00, 'activation', '2026-03-24 02:18:10', 'paid'),
(8, 29, 30, 1, 100.00, 'activation', '2026-03-24 01:42:31', 'paid'),
(7, 0, 29, 1, 100.00, 'activation', '2026-03-24 01:36:01', 'pending'),
(13, 31, 33, 2, 40.00, 'activation', '2026-03-24 02:19:32', 'paid'),
(14, 29, 33, 3, 20.00, 'activation', '2026-03-24 02:19:32', 'paid'),
(15, 33, 34, 1, 100.00, 'activation', '2026-03-24 07:13:11', 'paid'),
(16, 32, 34, 2, 40.00, 'activation', '2026-03-24 07:13:11', 'paid'),
(17, 31, 34, 3, 20.00, 'activation', '2026-03-24 07:13:11', 'paid'),
(18, 29, 35, 1, 100.00, 'activation', '2026-03-24 02:17:48', 'paid'),
(19, 32, 36, 1, 100.00, 'activation', '2026-03-24 07:15:28', 'pending'),
(20, 31, 36, 2, 40.00, 'activation', '2026-03-24 07:15:28', 'pending'),
(21, 29, 36, 3, 20.00, 'activation', '2026-03-24 07:15:28', 'pending'),
(22, 31, 37, 1, 100.00, 'activation', '2026-03-25 18:16:55', 'paid'),
(23, 29, 37, 2, 40.00, 'activation', '2026-03-25 18:16:55', 'paid'),
(24, 33, 38, 1, 100.00, 'activation', '2026-03-24 08:28:39', 'paid'),
(25, 32, 38, 2, 40.00, 'activation', '2026-03-24 08:28:39', 'paid'),
(26, 31, 38, 3, 20.00, 'activation', '2026-03-24 08:28:39', 'paid'),
(27, 32, 40, 1, 100.00, 'activation', '2026-03-25 22:28:43', 'paid'),
(28, 31, 40, 2, 40.00, 'activation', '2026-03-25 22:28:43', 'paid'),
(29, 29, 40, 3, 20.00, 'activation', '2026-03-25 22:28:43', 'paid'),
(30, 32, 41, 1, 100.00, 'activation', '2026-03-25 22:33:12', 'paid'),
(31, 31, 41, 2, 40.00, 'activation', '2026-03-25 22:33:12', 'paid'),
(32, 29, 41, 3, 20.00, 'activation', '2026-03-25 22:33:12', 'paid'),
(33, 31, 42, 1, 100.00, 'activation', '2026-03-25 23:41:13', 'paid'),
(34, 29, 42, 2, 40.00, 'activation', '2026-03-25 23:41:13', 'paid'),
(35, 42, 43, 1, 100.00, 'activation', '2026-03-25 23:53:21', 'paid'),
(36, 31, 43, 2, 40.00, 'activation', '2026-03-25 23:53:21', 'paid'),
(37, 29, 43, 3, 20.00, 'activation', '2026-03-25 23:53:21', 'paid');

-- --------------------------------------------------------

--
-- Table structure for table `markethub_products`
--

CREATE TABLE `markethub_products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'KES',
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `download_file` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `image_hash` varchar(255) DEFAULT NULL,
  `image_phash` text DEFAULT NULL,
  `image_size_kb` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `markethub_products`
--

INSERT INTO `markethub_products` (`id`, `product_name`, `price`, `currency`, `description`, `category`, `image`, `download_file`, `is_active`, `created_at`, `image_hash`, `image_phash`, `image_size_kb`) VALUES
(1, 'Passion Juice', 978.00, 'KES', 'iuyiuui', NULL, 'uploads/company_products/prod_69c4e17ce6a845.39137814.webp', NULL, 1, '2026-03-26 07:34:21', NULL, NULL, NULL),
(2, 'Mango Juice', 1231.00, 'KES', 'iuyiuui', NULL, 'uploads/company_products/prod_69c4e313c95513.77756663.webp', NULL, 1, '2026-03-26 07:41:07', NULL, NULL, NULL),
(3, 'jug', 1121.00, 'KES', 'Coming home with the entire family and not even one will be left behind unless otherwise;', 'Electronics', 'uploads/company_products/prod_69c4f6dea12eb8.54298825.webp', NULL, 0, '2026-03-26 09:05:34', NULL, NULL, NULL),
(4, 'MarketHub', 34312.00, 'KES', '333333333333333333333333333333 feeeeeeeeeeeeeeeeeeee', 'Electronics', 'uploads/company_products/prod_69c50d5fb3d908.15851086.webp', NULL, 1, '2026-03-26 10:41:35', NULL, NULL, NULL),
(5, 'Mango Juice', 345.00, 'KES', 'ewqrrrrrrrrrrrrrrrrrrrnfnnnnn gfdhhhhhhhhhhhhhhhhhhhhhhhh', 'Food & Snacks', 'uploads/company_products/prod_69c50dc04e2805.67167511.webp', NULL, 1, '2026-03-26 10:43:12', NULL, NULL, NULL),
(6, 'Passion Juice', 543.00, 'KES', 'hdfdggggggggggggggggggggggggg hgggdf dfhhhhhhhhhhhhh', 'Food & Snacks', 'uploads/company_products/prod_69c5119a5736e9.56792022.webp', NULL, 1, '2026-03-26 10:59:38', NULL, NULL, NULL),
(7, 'HIKHKKK', 5454.00, 'KES', 'RDFGDF fdfffffffffffffffffffffffsssss dfasssssssssssssssssss fsda', 'Stationery', 'uploads/company_products/prod_69c512471e5542.21360509.webp', NULL, 1, '2026-03-26 11:02:31', NULL, NULL, NULL),
(8, 'Passion Juice', 40.00, 'KES', 'sadfa  gssssssssddffffffffffffffff sdfgggggggggggggggggggg', 'Food & Snacks', 'uploads/company_products/prod_69c5161e0ce133.64505098.webp', NULL, 0, '2026-03-26 11:18:54', NULL, NULL, NULL),
(9, 'Kal Kkhka', 12400.00, 'KES', 'We are going live very very soon, please come let\'s start', 'Electronics', 'uploads/company_products/prod_69c51798082202.69859765.webp', NULL, 0, '2026-03-26 11:25:12', NULL, NULL, NULL),
(10, 'Kal Kkhka', 12400.00, 'KES', 'We are going live very very soon, please come let\'s start', 'Electronics', 'uploads/company_products/prod_69c51970ded4f4.72495902.webp', NULL, 0, '2026-03-26 11:33:05', NULL, NULL, NULL),
(11, 'Mug Tailor', 2000.00, 'KES', 'A very useful product that must never miss in your home items shelf;', 'Home Items', 'uploads/company_products/prod_69c519e4f34205.43642842.webp', NULL, 0, '2026-03-26 11:35:01', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `mpesa_payments`
--

CREATE TABLE `mpesa_payments` (
  `payment_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `order_id` bigint(20) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `mpesa_receipt` varchar(50) DEFAULT NULL,
  `checkout_request_id` varchar(100) DEFAULT NULL,
  `merchant_request_id` varchar(100) DEFAULT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `confirmed_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
(93, 'ORD-20260305-55635', 14, 4000.00, 'Pending', '2026-03-05 11:31:15'),
(95, 'ORD-20260306-66785', 16, 80.00, 'Pending', '2026-03-06 18:57:42'),
(96, 'ORD-20260306-20534', 16, 22800.00, 'Pending', '2026-03-06 19:00:34'),
(97, 'ORD-20260308-05958', 16, 40.00, 'Pending', '2026-03-08 08:34:49'),
(98, 'ORD-20260322-70950', 14, 68956.00, 'Pending', '2026-03-22 01:49:39'),
(99, 'ORD-20260322-64375', 14, 4008.00, 'Pending', '2026-03-22 01:50:09'),
(100, 'ORD-20260322-66244', 14, 4008.00, 'Pending', '2026-03-22 01:50:17'),
(101, 'ORD-20260322-80951', 14, 2600.00, 'Pending', '2026-03-22 01:50:45'),
(102, 'ORD-20260325-14184', 39, 1300.00, 'Pending', '2026-03-25 06:01:26'),
(103, 'ORD-20260325-84897', 39, 40.00, 'Pending', '2026-03-25 06:02:54'),
(104, 'ORD-20260325-61234', 39, 40.00, 'Pending', '2026-03-25 06:03:26'),
(105, 'ORD-20260325-93983', 39, 53460.00, 'Pending', '2026-03-25 06:04:33'),
(106, 'ORD-20260325-59555', 39, 40.00, 'Pending', '2026-03-25 06:05:36'),
(107, 'ORD-20260325-54834', 39, 14300.00, 'Pending', '2026-03-25 06:07:28'),
(108, 'ORD-20260325-74124', 39, 40.00, 'Pending', '2026-03-25 06:09:16'),
(109, 'ORD-20260325-95784', 39, 64948.00, 'Pending', '2026-03-25 06:10:17'),
(110, 'ORD-20260325-92386', 39, 40.00, 'Pending', '2026-03-25 06:11:07');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`item_id`, `order_id`, `product_id`, `seller_id`, `quantity`, `price`, `subtotal`) VALUES
(177, 93, 6, 15, 1, 4000.00, 0.00),
(178, 95, 4, 19, 2, 40.00, 0.00),
(179, 96, 6, 15, 1, 4000.00, 0.00),
(180, 96, 2, 17, 1, 13000.00, 0.00),
(181, 96, 3, 17, 1, 1300.00, 0.00),
(182, 96, 5, 20, 1, 4500.00, 0.00),
(183, 97, 4, 19, 1, 40.00, 0.00),
(184, 98, 6, 15, 1, 4008.00, 4008.00),
(185, 98, 8, 15, 1, 64948.00, 64948.00),
(186, 99, 6, 15, 1, 4008.00, 4008.00),
(187, 100, 6, 15, 1, 4008.00, 4008.00),
(188, 101, 3, 17, 2, 1300.00, 2600.00),
(189, 102, 3, 17, 1, 1300.00, 1300.00),
(190, 103, 1, 17, 1, 40.00, 40.00),
(191, 104, 1, 17, 1, 40.00, 40.00),
(192, 105, 2, 17, 4, 13000.00, 52000.00),
(193, 105, 3, 17, 1, 1300.00, 1300.00),
(194, 105, 1, 17, 1, 40.00, 40.00),
(195, 105, 4, 19, 3, 40.00, 120.00),
(196, 106, 1, 17, 1, 40.00, 40.00),
(197, 107, 3, 17, 1, 1300.00, 1300.00),
(198, 107, 2, 17, 1, 13000.00, 13000.00),
(199, 108, 4, 19, 1, 40.00, 40.00),
(200, 109, 8, 15, 1, 64948.00, 64948.00),
(201, 110, 4, 19, 1, 40.00, 40.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `transaction_id` varchar(150) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `image_phash` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `productservicesrentals`
--

INSERT INTO `productservicesrentals` (`product_id`, `user_id`, `product_name`, `category`, `price`, `stock_quantity`, `image_path`, `image_hash`, `image_width`, `image_height`, `image_size_kb`, `image_format`, `status`, `created_at`, `updated_at`, `image_phash`) VALUES
(1, 17, 'Passion Juice', 'Food & Snacks', 40.00, 52, 'uploads/products/product_699f6e94012145.78607241.webp', '197441253198dfbd6ec2ead44aff3e40', 700, 700, 57, 'webp', 'active', '2026-02-25 21:50:12', '2026-03-25 06:05:36', NULL),
(2, 17, 'Bike', 'Home Items', 13000.00, 7, 'uploads/products/product_699f7d45913b39.70833140.webp', '4688f6d846fc96bdba4db3f75f16d9b3', 700, 519, 55, 'webp', 'active', '2026-02-25 22:52:53', '2026-03-25 06:07:28', NULL),
(3, 17, 'jug', 'Home Items', 1300.00, 39, 'uploads/products/product_699f7dab95bf65.10914891.webp', '902284afaaefe5a5a6f55c09cea05089', 700, 700, 40, 'webp', 'active', '2026-02-25 22:54:35', '2026-03-25 06:07:28', NULL),
(4, 19, 'Passion Juice', 'Food & Snacks', 40.00, 26, 'uploads/products/product_69a017086d9a78.65234946.webp', '57cce22a22b1386a867d231a364f5109', 700, 700, 33, 'webp', 'active', '2026-02-26 09:48:56', '2026-03-25 06:11:07', NULL),
(5, 20, 'Matress', 'Home Items', 4500.00, 3, 'uploads/products/product_69a08decb192e1.62675128.webp', 'e24629049379675f3a1b4309bb699f4a', 700, 527, 27, 'webp', 'active', '2026-02-26 18:16:12', '2026-03-06 19:00:34', NULL),
(6, 15, 'Bicycle', 'Home Items', 4008.00, 6, 'uploads/products/product_69a59b4d0cd4e9.76902524.webp', '15ab1b502eaee9e786e973b38bf2e051', 700, 393, 10, 'webp', 'active', '2026-03-02 14:14:36', '2026-03-22 01:50:17', NULL),
(8, 15, 'HP Elite Book G', 'Home Items', 64948.00, 11, 'uploads/products/product_69af542ed14d19.08555660.webp', '332f4633790b42f07617bd6c21296451', 700, 525, 6, 'webp', 'active', '2026-03-09 23:13:50', '2026-03-25 06:10:17', '00100100001000100010001110000011');

-- --------------------------------------------------------

--
-- Table structure for table `seller_ratings`
--

CREATE TABLE `seller_ratings` (
  `rating_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `review` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscribers`
--

CREATE TABLE `subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` bigint(20) NOT NULL,
  `payer_id` int(11) NOT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `transaction_type` enum('product_purchase','service_payment','rental_payment','agent_activation','agent_commission','withdrawal','refund','platform_fee') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'KES',
  `reference_code` varchar(100) DEFAULT NULL,
  `payment_method` enum('mpesa','card','wallet','bank','cash') DEFAULT 'mpesa',
  `related_order_id` bigint(20) DEFAULT NULL,
  `related_property_id` bigint(20) DEFAULT NULL,
  `status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `account_type` enum('buyer','seller','sales_agent','administrator','property_owner') NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `status` enum('active','suspended') DEFAULT 'active',
  `country` varchar(100) NOT NULL DEFAULT 'Kenya',
  `county` varchar(100) NOT NULL,
  `ward` varchar(100) NOT NULL,
  `address` varchar(150) DEFAULT NULL,
  `business_name` varchar(50) DEFAULT NULL,
  `business_model` varchar(50) DEFAULT NULL,
  `business_type` varchar(50) DEFAULT NULL,
  `market_scope` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `agency_code` varchar(50) DEFAULT NULL,
  `referred_by` int(11) DEFAULT NULL,
  `agent_activated_at` datetime DEFAULT NULL,
  `subscription_expires_at` datetime DEFAULT NULL,
  `must_change_password` tinyint(1) DEFAULT 0,
  `economic_period_count` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `username`, `email`, `phone`, `profile_image`, `bio`, `password`, `account_type`, `is_verified`, `status`, `country`, `county`, `ward`, `address`, `business_name`, `business_model`, `business_type`, `market_scope`, `created_at`, `updated_at`, `agency_code`, `referred_by`, `agent_activated_at`, `subscription_expires_at`, `must_change_password`, `economic_period_count`) VALUES
(40, 'Kamlia Sengi', 'agent_012', 'YWdlbnRfMDEyQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxMg==', NULL, NULL, '$2y$10$n.ah07lULglUjMYUG/a6nuEDs5NwndRp9.eg7A.k/BpQijnJQUyyO', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Sarang\' dk', NULL, NULL, NULL, NULL, '2026-03-25 22:27:04', '2026-03-25 22:27:04', 'B4054F90', 32, NULL, '2026-04-25 01:28:43', 0, 1),
(15, 'Pst Kip Frie', 'seller_001', 'c2VsbGVyXzAwMUBnbWFpbC5jb20=', 'KzI1NDc3MzAyOTQ0MA==', NULL, 'We deliver', '$2y$10$CqCArt9rG4O278HT4JRwz.OT8yNHssp6D9Wlvg50VHBBnVRrkGobi', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni', 'Misufii', 'Market Hub', 'products', 'shop', 'Local', '2026-02-25 14:18:40', '2026-03-24 21:31:06', NULL, NULL, NULL, NULL, 0, NULL),
(38, 'STEPHEN OFTHEBIBLE', 'agent_011', 'YWdlbnRfMDExQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxMQ==', NULL, NULL, '$2y$10$Ll4PIS0M0545BnfIf81fce01fNzXQjYd9TlmJbjzUXWcPK8/JuIou', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-24 08:09:58', '2026-03-24 08:09:58', 'EEC4D699', 33, NULL, '2026-04-23 11:28:39', 1, 1),
(17, 'EMMANUEL WERANGAI', 'seller_002', 'c2VsbGVyXzAwMkBnbWFpbC5jb20=', 'KzI1NDc1OTU3ODYzMg==', NULL, NULL, '$2y$10$VABFqJUUCuGnx3M5NN6sfuDJ640MPia5z5mofYZv0.niTtM8u6cva', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', 'main canteen', NULL, 'canteen', 'Local', '2026-02-25 14:29:15', '2026-02-25 14:29:15', NULL, NULL, NULL, NULL, 0, NULL),
(18, 'EMMANUEL WERANGAI', 'seller_003', 'c2VsbGVyXzAwM0BnbWFpbC5jb20=', 'KzI1NDc1OTU3ODYzMw==', NULL, NULL, '$2y$10$kdTNspptrvARewNR3/9PQ.0lx4fwWXrF33TPqC7.VRsa2dXMW1sfG', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', 'BETRADES MANAGEMENT', NULL, 'supermarket', 'Local', '2026-02-25 14:31:11', '2026-02-25 14:31:11', NULL, NULL, NULL, NULL, 0, NULL),
(19, 'EMMANUEL WERANGAI', 'seller_004', 'c2VsbGVyXzAwNEBnbWFsaS5jb20=', 'KzI1NDc1OTU3ODYzNQ==', NULL, NULL, '$2y$10$OR16Ab7u7PiToTnqRTGzCeNrlBf4E4Cyr.eAyoGYnLmyN2foUtvK2', 'seller', 0, 'suspended', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Gede', 'Fred Pharmacy', 'products', 'supermarket', 'local', '2026-02-26 09:26:57', '2026-02-26 09:26:57', NULL, NULL, NULL, NULL, 0, NULL),
(41, 'sHAMRADI IDHKNA', 'agent_013', 'YWdlbnRfMDEzQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxMw==', NULL, NULL, '$2y$10$kO6zA8LwzGVv/c/FZ83f4.CQiEMiClHHa7SNUhvcrKeTWREzXGwRe', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Sarang\' dk', NULL, NULL, NULL, NULL, '2026-03-25 22:33:02', '2026-03-25 22:33:02', 'A6DA3D49', 32, NULL, '2026-04-25 01:33:12', 0, 1),
(21, 'EMMANUEL TINDI', 'admin_002', 'ZW1tYW51ZWx0aW5kaTIzQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYzNw==', NULL, NULL, '$2y$10$A0zeQcrmvrrk.uP8pSpwFeUfQJ3qFc9bVJentpZ4DEUwvVjb9QMZS', 'administrator', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Naivas', '', '', '', '', '2026-03-04 17:27:25', '2026-03-04 17:27:25', NULL, NULL, NULL, NULL, 0, NULL),
(29, 'EMMANUEL WERANGAI', 'agent_001', 'YWdlbnRfMDAxQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAwMQ==', NULL, NULL, '$2y$10$EH6ofE.Xf/06zE4QnmmWQOA5Wfl63t9TH0pj47Atn0CsZCwnZZLyW', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', NULL, NULL, NULL, NULL, '2026-03-24 01:36:01', '2026-03-24 01:36:01', 'FEC011A5', NULL, NULL, '2026-04-23 04:39:24', 0, 1),
(23, 'EMMANUEL TINDI', 'property_owner_001', 'cHJvcGVydHlfb3duZXJfMDAxQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODY4OQ==', NULL, NULL, '$2y$10$ry31rKkNzYi9LIIpr8Ajp.7BkSj8BbovXXAbEO/iNxTff14fX/Ery', 'property_owner', 0, 'suspended', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Naivas', '', '', '', '', '2026-03-04 17:41:30', '2026-03-21 22:09:22', NULL, NULL, NULL, NULL, 0, NULL),
(30, 'sam hiddgd', 'agent_002', 'YWdlbnRfMDAyQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAwMw==', NULL, NULL, '$2y$10$L8h6XLjPZAq/FI/y/nYTiuY6pr73V.PSrq6daqb00xAGs653HL3Ra', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', NULL, NULL, NULL, NULL, '2026-03-24 01:42:06', '2026-03-24 01:42:06', '8EFB950D', 29, NULL, '2026-04-23 04:42:31', 0, 1),
(31, 'Maket Hub', 'agent_003', 'YWdlbnRfMDAzQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAwNA==', NULL, NULL, '$2y$10$h7KtsjFR/w3TwGFtWUAzCOuSqHszv9tUTxB6KY9op1T.tThxb6NSu', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Moiko', NULL, NULL, NULL, NULL, '2026-03-24 01:59:12', '2026-03-24 01:59:12', '6D76B306', 29, NULL, '2026-04-23 05:18:10', 0, 1),
(32, 'Sha iskkoe', 'agent_004', 'YWdlbnRfMDA0QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAwNQ==', NULL, 'I am here', '$2y$10$KmxL5.BNgKJyGRkecHeQ1OaYAQ6p1S2xwLuKrArPBBeayiAqcQc9.', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni', 'Kilifi', NULL, NULL, NULL, NULL, '2026-03-24 02:02:27', '2026-03-24 20:47:25', '14C1242F', 31, NULL, '2026-04-23 05:02:55', 0, 1),
(33, 'vesa bledr', 'agent_006', 'YWdlbnRfMDA2QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAwNg==', NULL, NULL, '$2y$10$cRX61aoXJZyhVhpHB0pvjOBbWbwPuY.J31T7amQ8LarazliknUlBK', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', NULL, NULL, NULL, NULL, '2026-03-24 02:06:43', '2026-03-24 02:06:43', 'D52A7567', 32, NULL, '2026-04-23 05:19:32', 0, 1),
(34, 'agent Bunda', 'agent_007', 'YWdlbnRfMDA3QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAwNw==', NULL, NULL, '$2y$10$OdFGFa4PykQoC73syulnEuQdor.ddvnNC5ir0JShcmYCweiCQenC.', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'kamois', NULL, NULL, NULL, NULL, '2026-03-24 02:11:06', '2026-03-24 02:11:06', '7F9D1742', 33, NULL, '2026-04-23 10:13:11', 0, 1),
(35, 'sham hashi', 'agent_008', 'YWdlbnRfMDA4QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAwOA==', NULL, NULL, '$2y$10$hIFRQCX1t3aHXqmF91BmPu5/697RL6h19L3vjvAuEV6lNYPoqQGuu', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-24 02:16:39', '2026-03-24 02:16:39', '6676A6FC', 29, NULL, '2026-04-23 05:17:48', 1, 1),
(39, 'Shanig ahsila', 'Buyer_001', 'YnV5ZXJfMDAxQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3MTAwMQ==', NULL, 'Coming soon...', '$2y$10$VHIyn6QSW8k0hZTovIn7.esSC/FM6/rGrjPnqokftNCx0W1tqPI0a', 'buyer', 0, 'suspended', 'Kenya', 'Kilifi', 'Sokoni', 'Kilifi', NULL, NULL, NULL, NULL, '2026-03-24 20:50:39', '2026-03-25 06:14:53', NULL, NULL, NULL, NULL, 0, NULL),
(42, 'Gaaffr mdsa', 'agent_014', 'YWdlbnRfMDE0QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxNA==', NULL, NULL, '$2y$10$IU2DqtE0RaLWsy5OU9uUk./Lm0lKU/0sZGEtLVsIIhCo6Wj36E3y2', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', NULL, NULL, NULL, NULL, '2026-03-25 23:40:45', '2026-03-25 23:40:45', 'F015A0BB', 31, NULL, '2026-04-25 02:41:13', 0, 1),
(43, 'KAMANHA HALSOM', 'agent_15', 'YWdlbnRfMTVAZ21haWwuY29t', 'KzI1NDc1OTU3ODAxNQ==', NULL, NULL, '$2y$10$2ue6KdTIUoxi/3bCXOcauehONklYSdydxEgPUxT8CplkO69U8nxt2', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'zombkia', NULL, NULL, NULL, NULL, '2026-03-25 23:53:09', '2026-03-25 23:53:09', '06B3D824', 42, NULL, '2026-04-25 02:53:21', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_cart`
--

CREATE TABLE `user_cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_followers`
--

CREATE TABLE `user_followers` (
  `follower_id` int(11) NOT NULL,
  `followed_id` int(11) NOT NULL,
  `followed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_followers`
--

INSERT INTO `user_followers` (`follower_id`, `followed_id`, `followed_at`) VALUES
(14, 15, '2026-03-07 17:26:55'),
(14, 17, '2026-03-07 17:27:16'),
(14, 18, '2026-02-25 20:26:17'),
(14, 19, '2026-02-26 12:54:30'),
(16, 15, '2026-02-26 13:04:54'),
(16, 17, '2026-02-25 21:49:03'),
(16, 18, '2026-03-06 19:02:14'),
(16, 19, '2026-03-06 19:02:16'),
(16, 20, '2026-02-26 18:17:46'),
(20, 17, '2026-02-26 18:25:49');

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `wallet_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `wallet_type` enum('sales','agency','sales_agent','property_owner','administrator','buyer','seller') NOT NULL,
  `balance` decimal(12,2) DEFAULT 0.00,
  `total_transacted` decimal(12,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`wallet_id`, `user_id`, `wallet_type`, `balance`, `total_transacted`, `created_at`, `updated_at`) VALUES
(20, 31, 'sales', 0.00, 0.00, '2026-03-24 02:18:10', '2026-03-24 02:18:10'),
(19, 35, 'agency', 0.00, 0.00, '2026-03-24 02:17:48', '2026-03-24 02:17:48'),
(18, 35, 'sales', 0.00, 0.00, '2026-03-24 02:17:48', '2026-03-24 02:17:48'),
(17, 31, 'agency', 500.00, 500.00, '2026-03-24 02:02:55', '2026-03-25 23:53:21'),
(16, 32, 'agency', 380.00, 380.00, '2026-03-24 02:02:55', '2026-03-25 22:33:12'),
(15, 32, 'sales', 0.00, 0.00, '2026-03-24 02:02:55', '2026-03-24 02:02:55'),
(14, 30, 'agency', 0.00, 0.00, '2026-03-24 01:42:31', '2026-03-24 01:42:31'),
(13, 30, 'sales', 0.00, 0.00, '2026-03-24 01:42:31', '2026-03-24 01:42:31'),
(12, 29, 'agency', 500.00, 500.00, '2026-03-24 01:39:24', '2026-03-25 23:53:21'),
(11, 29, 'sales', 0.00, 0.00, '2026-03-24 01:39:24', '2026-03-24 01:39:24'),
(21, 33, 'sales', 0.00, 0.00, '2026-03-24 02:19:32', '2026-03-24 02:19:32'),
(22, 33, 'agency', 200.00, 200.00, '2026-03-24 02:19:32', '2026-03-24 08:28:39'),
(23, 34, 'sales', 0.00, 0.00, '2026-03-24 07:13:11', '2026-03-24 07:13:11'),
(24, 34, 'agency', 0.00, 0.00, '2026-03-24 07:13:11', '2026-03-24 07:13:11'),
(25, 38, 'sales', 0.00, 0.00, '2026-03-24 08:28:39', '2026-03-24 08:28:39'),
(26, 38, 'agency', 0.00, 0.00, '2026-03-24 08:28:39', '2026-03-24 08:28:39'),
(27, 39, 'buyer', 0.00, 0.00, '2026-03-24 20:50:39', '2026-03-24 20:50:39'),
(28, 37, 'sales', 0.00, 0.00, '2026-03-25 18:16:55', '2026-03-25 18:16:55'),
(29, 37, 'agency', 0.00, 0.00, '2026-03-25 18:16:55', '2026-03-25 18:16:55'),
(30, 40, 'sales', 0.00, 0.00, '2026-03-25 22:28:43', '2026-03-25 22:28:43'),
(31, 40, 'agency', 0.00, 0.00, '2026-03-25 22:28:43', '2026-03-25 22:28:43'),
(32, 41, 'sales', 0.00, 0.00, '2026-03-25 22:33:12', '2026-03-25 22:33:12'),
(33, 41, 'agency', 0.00, 0.00, '2026-03-25 22:33:12', '2026-03-25 22:33:12'),
(34, 42, 'sales', 0.00, 0.00, '2026-03-25 23:41:13', '2026-03-25 23:41:13'),
(35, 42, 'agency', 100.00, 100.00, '2026-03-25 23:41:13', '2026-03-25 23:53:21'),
(36, 43, 'sales', 0.00, 0.00, '2026-03-25 23:53:21', '2026-03-25 23:53:21'),
(37, 43, 'agency', 0.00, 0.00, '2026-03-25 23:53:21', '2026-03-25 23:53:21');

-- --------------------------------------------------------

--
-- Table structure for table `wallet_transactions`
--

CREATE TABLE `wallet_transactions` (
  `transaction_id` int(11) NOT NULL,
  `wallet_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `status` enum('pending','completed','failed','rejected') NOT NULL DEFAULT 'pending',
  `transaction_type` enum('credit','debit') NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `reference_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `wallet_transactions`
--

INSERT INTO `wallet_transactions` (`transaction_id`, `wallet_id`, `amount`, `status`, `transaction_type`, `description`, `reference_id`, `created_at`, `updated_at`) VALUES
(10, 12, 100.00, 'completed', 'credit', 'Level 1 commission from agent 31', '31', '2026-03-24 02:18:10', '2026-03-24 02:18:10'),
(9, 12, 100.00, 'completed', 'credit', 'Level 1 commission from agent 35', '35', '2026-03-24 02:17:48', '2026-03-24 02:17:48'),
(8, 12, 40.00, 'completed', 'credit', 'Level 2 commission from agent 32', '32', '2026-03-24 02:02:55', '2026-03-24 02:02:55'),
(7, 17, 100.00, 'completed', 'credit', 'Level 1 commission from agent 32', '32', '2026-03-24 02:02:55', '2026-03-24 02:02:55'),
(6, 12, 100.00, 'completed', 'credit', 'Level 1 commission from agent 30', '30', '2026-03-24 01:42:31', '2026-03-24 01:42:31'),
(11, 16, 100.00, 'completed', 'credit', 'Level 1 commission from agent 33', '33', '2026-03-24 02:19:32', '2026-03-24 02:19:32'),
(12, 17, 40.00, 'completed', 'credit', 'Level 2 commission from agent 33', '33', '2026-03-24 02:19:32', '2026-03-24 02:19:32'),
(13, 12, 20.00, 'completed', 'credit', 'Level 3 commission from agent 33', '33', '2026-03-24 02:19:32', '2026-03-24 02:19:32'),
(14, 22, 100.00, 'completed', 'credit', 'Level 1 commission from agent 34', '34', '2026-03-24 07:13:11', '2026-03-24 07:13:11'),
(15, 16, 40.00, 'completed', 'credit', 'Level 2 commission from agent 34', '34', '2026-03-24 07:13:11', '2026-03-24 07:13:11'),
(16, 17, 20.00, 'completed', 'credit', 'Level 3 commission from agent 34', '34', '2026-03-24 07:13:11', '2026-03-24 07:13:11'),
(17, 22, 100.00, 'completed', 'credit', 'Level 1 commission from agent 38', '38', '2026-03-24 08:28:39', '2026-03-24 08:28:39'),
(18, 16, 40.00, 'completed', 'credit', 'Level 2 commission from agent 38', '38', '2026-03-24 08:28:39', '2026-03-24 08:28:39'),
(19, 17, 20.00, 'completed', 'credit', 'Level 3 commission from agent 38', '38', '2026-03-24 08:28:39', '2026-03-24 08:28:39'),
(20, 17, 100.00, 'completed', 'credit', 'Level 1 commission from agent 37', '37', '2026-03-25 18:16:55', '2026-03-25 18:16:55'),
(21, 12, 40.00, 'completed', 'credit', 'Level 2 commission from agent 37', '37', '2026-03-25 18:16:55', '2026-03-25 18:16:55'),
(22, 16, 100.00, 'completed', 'credit', 'Level 1 commission from agent 40', '40', '2026-03-25 22:28:43', '2026-03-25 22:28:43'),
(23, 17, 40.00, 'completed', 'credit', 'Level 2 commission from agent 40', '40', '2026-03-25 22:28:43', '2026-03-25 22:28:43'),
(24, 12, 20.00, 'completed', 'credit', 'Level 3 commission from agent 40', '40', '2026-03-25 22:28:43', '2026-03-25 22:28:43'),
(25, 16, 100.00, 'completed', 'credit', 'Level 1 commission from agent 41', '41', '2026-03-25 22:33:12', '2026-03-25 22:33:12'),
(26, 17, 40.00, 'completed', 'credit', 'Level 2 commission from agent 41', '41', '2026-03-25 22:33:12', '2026-03-25 22:33:12'),
(27, 12, 20.00, 'completed', 'credit', 'Level 3 commission from agent 41', '41', '2026-03-25 22:33:12', '2026-03-25 22:33:12'),
(28, 17, 100.00, 'completed', 'credit', 'Level 1 commission from agent 42', '42', '2026-03-25 23:41:13', '2026-03-25 23:41:13'),
(29, 12, 40.00, 'completed', 'credit', 'Level 2 commission from agent 42', '42', '2026-03-25 23:41:13', '2026-03-25 23:41:13'),
(30, 35, 100.00, 'completed', 'credit', 'Level 1 commission from agent 43', '43', '2026-03-25 23:53:21', '2026-03-25 23:53:21'),
(31, 17, 40.00, 'completed', 'credit', 'Level 2 commission from agent 43', '43', '2026-03-25 23:53:21', '2026-03-25 23:53:21'),
(32, 12, 20.00, 'completed', 'credit', 'Level 3 commission from agent 43', '43', '2026-03-25 23:53:21', '2026-03-25 23:53:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agent_commissions`
--
ALTER TABLE `agent_commissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_agent_id` (`agent_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `markethub_products`
--
ALTER TABLE `markethub_products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mpesa_payments`
--
ALTER TABLE `mpesa_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `mpesa_receipt` (`mpesa_receipt`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `order_code` (`order_code`),
  ADD KEY `buyer_id` (`buyer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`);

--
-- Indexes for table `productservicesrentals`
--
ALTER TABLE `productservicesrentals`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `idx_user_created` (`user_id`,`created_at`),
  ADD KEY `idx_user_product` (`user_id`,`product_name`),
  ADD KEY `idx_user_image` (`user_id`,`image_hash`),
  ADD KEY `idx_image_hash` (`image_hash`),
  ADD KEY `idx_image_phash` (`image_phash`);

--
-- Indexes for table `seller_ratings`
--
ALTER TABLE `seller_ratings`
  ADD PRIMARY KEY (`rating_id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `buyer_id` (`buyer_id`);

--
-- Indexes for table `subscribers`
--
ALTER TABLE `subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD UNIQUE KEY `reference_code` (`reference_code`),
  ADD KEY `payer_id` (`payer_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD UNIQUE KEY `username_2` (`username`),
  ADD UNIQUE KEY `email_2` (`email`),
  ADD UNIQUE KEY `phone_2` (`phone`),
  ADD UNIQUE KEY `referral_code` (`agency_code`);

--
-- Indexes for table `user_cart`
--
ALTER TABLE `user_cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `user_followers`
--
ALTER TABLE `user_followers`
  ADD PRIMARY KEY (`follower_id`,`followed_id`),
  ADD KEY `followed_id` (`followed_id`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`wallet_id`),
  ADD UNIQUE KEY `unique_wallet` (`user_id`,`wallet_type`);

--
-- Indexes for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `wallet_id` (`wallet_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agent_commissions`
--
ALTER TABLE `agent_commissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `markethub_products`
--
ALTER TABLE `markethub_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `mpesa_payments`
--
ALTER TABLE `mpesa_payments`
  MODIFY `payment_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=202;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `productservicesrentals`
--
ALTER TABLE `productservicesrentals`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `seller_ratings`
--
ALTER TABLE `seller_ratings`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `user_cart`
--
ALTER TABLE `user_cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `wallet_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
