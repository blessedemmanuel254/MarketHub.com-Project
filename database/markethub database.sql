-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 11, 2026 at 01:50 PM
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
-- Table structure for table `agent_commissions`
--

CREATE TABLE `agent_commissions` (
  `id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `source_user_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `commission_type` enum('activation','advertising') DEFAULT 'activation',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `agent_wallet`
--

CREATE TABLE `agent_wallet` (
  `agent_id` int(11) NOT NULL,
  `balance` decimal(10,2) DEFAULT 0.00,
  `total_earned` decimal(10,2) DEFAULT 0.00,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `agent_wallet`
--

INSERT INTO `agent_wallet` (`agent_id`, `balance`, `total_earned`, `updated_at`) VALUES
(49, 0.00, 0.00, '2026-03-11 00:50:06');

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
  `created_at` timestamp NULL DEFAULT current_timestamp()
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
(92, 'ORD-20260304-64535', 16, 104067.00, 'Pending', '2026-03-04 10:44:48'),
(95, 'ORD-20260309-09708', 14, 1300.00, 'Pending', '2026-03-09 12:01:40');

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
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`item_id`, `order_id`, `product_id`, `seller_id`, `quantity`, `price`) VALUES
(114, 66, 8, 18, 1, 67.00),
(115, 67, 8, 18, 3, 67.00),
(116, 68, 2, 17, 2, 13000.00),
(117, 69, 3, 17, 2, 1300.00),
(118, 69, 2, 17, 2, 13000.00),
(119, 69, 7, 17, 2, 123.00),
(120, 69, 8, 18, 6, 67.00),
(121, 70, 7, 17, 1, 123.00),
(122, 70, 3, 17, 1, 1300.00),
(123, 70, 2, 17, 1, 13000.00),
(124, 70, 1, 17, 1, 40.00),
(125, 70, 8, 18, 1, 67.00),
(126, 71, 7, 17, 1, 123.00),
(127, 71, 3, 17, 1, 1300.00),
(128, 71, 2, 17, 1, 13000.00),
(129, 71, 1, 17, 1, 40.00),
(130, 71, 8, 18, 1, 67.00),
(131, 72, 7, 17, 1, 123.00),
(132, 72, 3, 17, 1, 1300.00),
(133, 72, 2, 17, 1, 13000.00),
(134, 72, 1, 17, 1, 40.00),
(135, 72, 8, 18, 1, 67.00),
(136, 73, 7, 17, 1, 123.00),
(137, 73, 3, 17, 1, 1300.00),
(138, 73, 2, 17, 1, 13000.00),
(139, 73, 1, 17, 1, 40.00),
(140, 73, 8, 18, 1, 67.00),
(141, 74, 3, 17, 1, 1300.00),
(142, 75, 7, 17, 2, 123.00),
(143, 76, 3, 17, 1, 1300.00),
(144, 76, 1, 17, 1, 40.00),
(145, 77, 8, 18, 1, 67.00),
(146, 78, 7, 17, 2, 123.00),
(147, 78, 3, 17, 2, 1300.00),
(148, 78, 8, 18, 1, 67.00),
(149, 79, 8, 18, 1, 67.00),
(150, 80, 8, 18, 2, 67.00),
(151, 81, 7, 17, 1, 123.00),
(172, 89, 3, 17, 1, 1300.00),
(173, 89, 2, 17, 1, 13000.00),
(174, 89, 1, 17, 1, 40.00),
(175, 92, 2, 17, 8, 13000.00),
(176, 92, 8, 18, 1, 67.00),
(177, 95, 3, 17, 1, 1300.00);

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `productservicesrentals`
--

INSERT INTO `productservicesrentals` (`product_id`, `user_id`, `product_name`, `category`, `price`, `stock_quantity`, `image_path`, `image_hash`, `image_width`, `image_height`, `image_size_kb`, `image_format`, `status`, `created_at`, `updated_at`) VALUES
(1, 17, 'Passion Juice', 'Food & Snacks', 40.00, 56, 'uploads/products/product_699f6e94012145.78607241.webp', '197441253198dfbd6ec2ead44aff3e40', 700, 700, 57, 'webp', 'active', '2026-02-25 21:50:12', '2026-02-25 21:50:12'),
(2, 17, 'Bike', 'Home Items', 13000.00, 13, 'uploads/products/product_699f7d45913b39.70833140.webp', '4688f6d846fc96bdba4db3f75f16d9b3', 700, 519, 55, 'webp', 'active', '2026-02-25 22:52:53', '2026-02-25 22:52:53'),
(3, 17, 'jug', 'Home Items', 1300.00, 44, 'uploads/products/product_699f7dab95bf65.10914891.webp', '902284afaaefe5a5a6f55c09cea05089', 700, 700, 40, 'webp', 'active', '2026-02-25 22:54:35', '2026-03-09 12:01:40'),
(4, 19, 'Passion Juice', 'Food & Snacks', 40.00, 34, 'uploads/products/product_69a017086d9a78.65234946.webp', '57cce22a22b1386a867d231a364f5109', 700, 700, 33, 'webp', 'active', '2026-02-26 09:48:56', '2026-02-26 09:48:56');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `economic_period_count` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `username`, `email`, `phone`, `profile_image`, `bio`, `password`, `account_type`, `is_verified`, `status`, `country`, `county`, `ward`, `address`, `business_name`, `business_model`, `business_type`, `market_scope`, `created_at`, `updated_at`, `agency_code`, `referred_by`, `agent_activated_at`, `economic_period_count`) VALUES
(14, 'EMMANUEL WERANGAI', 'buyer_001', 'YnV5ZXJfMDAxQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYzMA==', NULL, '', '$2y$10$eZaCoaJd1r6NPqecOo8gX.fhXnk2sCbxyrfRLL7YoGllHWaLmyS5W', 'buyer', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', NULL, NULL, NULL, NULL, '2026-02-25 13:54:11', '2026-03-09 18:04:35', NULL, NULL, NULL, NULL),
(15, 'EMMANUEL WERANGAI', 'seller_001', 'c2VsbGVyXzAwMUBnbWFpbC5jb20=', 'KzI1NDc3MzAyOTQ0MA==', NULL, 'I am here', '$2y$10$CqCArt9rG4O278HT4JRwz.OT8yNHssp6D9Wlvg50VHBBnVRrkGobi', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni', 'kisumu ndogo', 'Makethub', NULL, 'shop', 'Local', '2026-02-25 14:18:40', '2026-03-04 06:45:20', NULL, NULL, NULL, NULL),
(16, 'EMMANUEL WERANGAI', 'buyer_002', 'YnV5ZXJfMDAyQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYzMQ==', NULL, NULL, '$2y$10$DVxzrzSTosLmpkLoCos9QeLNGDPAmW3YolY5W6dYMhV7FnTSE2OIW', 'buyer', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Watamu', NULL, NULL, NULL, NULL, '2026-02-25 14:26:12', '2026-02-25 14:26:12', NULL, NULL, NULL, NULL),
(17, 'EMMANUEL WERANGAI', 'seller_002', 'c2VsbGVyXzAwMkBnbWFpbC5jb20=', 'KzI1NDc1OTU3ODYzMg==', NULL, NULL, '$2y$10$VABFqJUUCuGnx3M5NN6sfuDJ640MPia5z5mofYZv0.niTtM8u6cva', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', 'main canteen', NULL, 'canteen', 'Local', '2026-02-25 14:29:15', '2026-02-25 14:29:15', NULL, NULL, NULL, NULL),
(18, 'EMMANUEL WERANGAI', 'seller_003', 'c2VsbGVyXzAwM0BnbWFpbC5jb20=', 'KzI1NDc1OTU3ODYzMw==', NULL, NULL, '$2y$10$kdTNspptrvARewNR3/9PQ.0lx4fwWXrF33TPqC7.VRsa2dXMW1sfG', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', 'BETRADES MANAGEMENT', NULL, 'supermarket', 'Local', '2026-02-25 14:31:11', '2026-02-25 14:31:11', NULL, NULL, NULL, NULL),
(19, 'EMMANUEL WERANGAI', 'seller_004', 'c2VsbGVyXzAwNEBnbWFsaS5jb20=', 'KzI1NDc1OTU3ODYzNQ==', NULL, NULL, '$2y$10$OR16Ab7u7PiToTnqRTGzCeNrlBf4E4Cyr.eAyoGYnLmyN2foUtvK2', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Gede', 'Fred Pharmacy', 'products', 'supermarket', 'local', '2026-02-26 09:26:57', '2026-02-26 09:26:57', NULL, NULL, NULL, NULL),
(20, 'Sheila Barasa', 'admin_001', 'ZW1tYW51ZWx0aW5kaTIzQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYzOQ==', NULL, NULL, '$2y$10$QE2Yt9Dg465QGVMZVj4ds.RW8dvGVv0Kh9cCL2jzbbPN0cLs/i/pu', 'buyer', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Naivas', NULL, NULL, NULL, NULL, '2026-03-04 16:23:46', '2026-03-09 19:05:27', NULL, NULL, NULL, NULL),
(21, 'EMMANUEL WERANGAI', 'admin_002', 'ZW1tYW51ZWx0aW5kaTIyQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYzNw==', NULL, NULL, '$2y$10$wXvvXl3huLmsyfaBVngweudcSiAv2g2btyvkJqILmsqJBMcTtdsMi', 'administrator', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-04 16:51:33', '2026-03-04 16:51:33', NULL, NULL, NULL, NULL),
(22, 'Adyline Cherono', 'seller_005', 'c2VsbGVyXzAwNUBnbWFpbC5jb20=', 'KzI1NDcwODY3MDM5Ng==', NULL, NULL, '$2y$10$jduCXGurRvzRC20WfG2N9ehYyB6RJlk6SnROkJ5HlNAmRqz6JveQm', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Mlika mwizi', 'mama adrian shop', 'products', 'canteen', 'local', '2026-03-04 18:44:28', '2026-03-04 18:44:28', NULL, NULL, NULL, NULL),
(23, 'Elijah Barasa', 'blessedemmanuel258', 'ZW1tYW51ZWx0aW5kaTI4QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYzOA==', NULL, NULL, '$2y$10$Q4EqtpdY0nlhULKLoI3wrO.PnjzwpYjniSTi.T468AUSCnq.YfWOq', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 19:44:41', '2026-03-09 19:08:47', '05C0FAFF', NULL, NULL, NULL),
(24, 'EMMANUEL WERANGAI', 'blessedemmanuel259', 'ZW1tYW51ZWx0aW5kaTI5QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODg3Ng==', NULL, NULL, '$2y$10$s7xcA2eUS3J/XjtapX66.ew.juyyse1sQA/LsLxxHSGOSmi8LglDi', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 19:49:12', '2026-03-11 07:54:40', '4BD0D5A8', NULL, '2026-03-11 10:54:40', 1),
(25, 'EMMANUEL WERANGAI', 'blessedemmanuel251', 'ZW1tYW51ZWx0aW5kaTIxQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODM0NQ==', NULL, NULL, '$2y$10$pEQu.sJshyZpbPPQm7oc7.T.oApDK0iDjaubSNy1zHyDtGBnDqZAW', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 19:50:16', '2026-03-05 19:50:16', 'E5EE3E04', 24, NULL, NULL),
(26, 'Blessed Emmanuel', 'agent_001', 'YWdlbnRfMDAxQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYyMw==', NULL, NULL, '$2y$10$QttwBhgfEqpkerZFR2Xqbe8QjWx8bB8U.xCbOJ07OID7uflfhrQFa', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 19:54:24', '2026-03-05 19:54:24', 'DBE25C71', 24, NULL, NULL),
(27, 'Blessed Emmanuel', 'agent_002', 'YWdlbnRfMDAyQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYyOQ==', NULL, NULL, '$2y$10$88kTe0mRkp6XNtzm.9yf0uqyTot.53Q1QzzVwqsf8XpHuD/AIGS7e', 'sales_agent', 1, 'suspended', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 19:56:07', '2026-03-11 08:05:56', '574B94B3', 24, '2026-03-11 11:05:56', 1),
(28, 'EMMANUEL WERANGAI', 'agent_003', 'YWdlbnRfMDAzQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODY4Nw==', NULL, NULL, '$2y$10$N4//.tzoTnFxqdfmuFTNa.4wJJLzs0JaCgLPnAunUwtwsqFspTmOa', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 19:58:27', '2026-03-05 19:58:27', 'A6C50F36', 24, NULL, NULL),
(29, 'EMMANUEL WERANGAI', 'agent_004', 'YWdlbnRfMDA0QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODY0Mw==', NULL, NULL, '$2y$10$EOtCwhrV/jCQYYHil/Y45uLUB1OKVV6QZ6CNa8eAbusI6P2Zwz.OW', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:07:16', '2026-03-05 20:07:16', '7AAFF72B', NULL, NULL, NULL),
(30, 'EMMANUEL WERANGAI', 'agent_005', 'YWdlbnRfMDA1QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODY0NQ==', NULL, NULL, '$2y$10$VxcIev3D1cupiDEDkKFMr.6xmRqWC9mJgr5h416d7MqF.9.Qbhs9G', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:08:21', '2026-03-05 20:08:21', 'F4700C3E', NULL, NULL, NULL),
(31, 'EMMANUEL WERANGAI', 'agent_006', 'YWdlbnRfMDA2QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAwNQ==', NULL, NULL, '$2y$10$.90aYKj0PyiM5r0Vj.yG5uIzZ9sKJKz.53KunC6mxE/8.SCzAbpbe', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:16:49', '2026-03-05 20:16:49', '1CEE060F', NULL, NULL, NULL),
(32, 'EMMANUEL WERANGAI', 'agent_007', 'YWdlbnRfMDA3QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAwNw==', NULL, NULL, '$2y$10$cFidq5ds8kXff9okR4JL7.AWG4M9tInTB1JqKQ8zlBLFJMQaD3.pO', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:19:07', '2026-03-05 20:19:07', '110A64B4', NULL, NULL, NULL),
(33, 'EMMANUEL Wanji', 'wanjala', 'YWdlbnRfMDA4QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAwOA==', NULL, NULL, '$2y$10$loS7O2tNUZ0uVnBtRt0UUuvY9W2my.zWPM7bedcykvvDwY9THkrWi', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:22:09', '2026-03-09 18:10:22', '13B8E3CD', 28, NULL, NULL),
(34, 'EMMANUEL WERANGAI', 'agent_009', 'YWdlbnRfMDA5QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAwOQ==', NULL, NULL, '$2y$10$S7O0QIC/hsVFUb.aLtO39em578pKcbDwbUktgazsLW084HFdRP/zu', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:24:02', '2026-03-05 20:24:02', '1FFE9D40', NULL, NULL, NULL),
(35, 'EMMANUEL WERANGAI', 'agent_010', 'YWdlbnRfMDEwQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxMA==', NULL, NULL, '$2y$10$zPwTRP899ZKT7YtweOmVHuTRrmZrxGBwjpzvaJGYBk4TK4kntymua', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:32:10', '2026-03-05 20:32:10', '503AFDE4', 28, NULL, NULL),
(36, 'EMMANUEL WERANGAI', 'agent_011', 'YWdlbnRfMDExQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxMQ==', NULL, NULL, '$2y$10$ojtPgyAyEwbx.V4MSxarLOEHlVaYtRt0XrQumm/CbB3RV0GKm1s3O', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:35:18', '2026-03-11 05:20:25', '2556FDBD', 27, '2026-03-11 08:20:25', 1),
(37, 'Kisembi Hyalo', 'agent_012', 'YWdlbnRfMDEyQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxMg==', NULL, NULL, '$2y$10$Dlveo6Ny1.KwZKOdpCqvCeVEJZa6jm55DnGigHUxRsocRrO1yasse', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:42:09', '2026-03-05 20:42:09', '3CCFB557', NULL, NULL, NULL),
(38, 'EMMANUEL WERANGAI', 'agent_013', 'YWdlbnRfMDEzQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxMw==', NULL, NULL, '$2y$10$bG8ZaUPS8Tkx9gGGzc99H.BDm242/yP5282ZKxlvWBsbBGudjg5dG', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:50:18', '2026-03-05 20:50:18', '66CCD3B4', 23, NULL, NULL),
(39, 'DASHCAM BUNDI', 'agent_014', 'YWdlbnRfMDE0QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxNA==', NULL, NULL, '$2y$10$OmMKXPH4EfiygWGP3etlJeMKHl3dt8Nk1J1IAPb.fJxEiVivm8QNq', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:52:12', '2026-03-05 20:52:12', 'DB375B5B', 26, NULL, NULL),
(40, 'EMMANUEL WERANGAI', 'agent_015', 'YWdlbnRfMDE1QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxNQ==', NULL, NULL, '$2y$10$LBlcMBAkDRgpX5KjpwM46evzOKroLGUVAIUibrwYGkNCbyf51zrU2', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:55:09', '2026-03-09 18:04:27', '4FA2A924', 39, NULL, NULL),
(41, 'EMMANUEL Wangari', 'agent_016', 'YWdlbnRfMDE2QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODcwMQ==', NULL, NULL, '$2y$10$HIsc/sxh0aQBv8Ob/QKp1u3vmnV9th/9ZAQ8o6M7eH6A/jB3yNGG6', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:58:30', '2026-03-09 17:17:12', 'E1B02841', 40, NULL, NULL),
(42, 'EMMANUEL WERANGAI', 'agent_017', 'YWdlbnRfMDE3QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxNw==', NULL, NULL, '$2y$10$Kzq5SpMxd7bCnAymotAweu43VcUrOEdLNbSKBKpTo7fPsoiH2q6Sa', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 21:01:07', '2026-03-05 21:01:07', 'D8E51616', 41, NULL, NULL),
(43, 'EMMANUEL WERANGAI', 'agent_018', 'YWdlbnRfMDE4QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxOA==', NULL, NULL, '$2y$10$hCienXkHmtf2nev63yPm8.TOq3iJ3r1xfReTAX0d/9l7U3CPPXRcy', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 21:04:05', '2026-03-11 05:58:06', '79B3A9CC', 27, '2026-03-11 08:58:06', 3),
(44, 'EMMANUEL WERANGAI', 'agent_19', 'YWdlbnRfMTlAZ21haWwuY29t', 'KzI1NDc1OTU3ODAxOQ==', NULL, NULL, '$2y$10$ePgT/PAUzpcukoOrGArbYOTjw8p0u1GYr0VFSqCl2OCMrxpdugNkS', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 21:25:58', '2026-03-11 08:12:38', '2CC06969', 43, '2026-03-11 11:12:38', 100),
(45, 'Bramuel Wafula', 'agent_20', 'YWdlbnRfMjBAZ21haWwuY29t', 'KzI1NDc1OTU3ODYyMA==', NULL, NULL, '$2y$10$EhJt6Fc6hj0v9Q9gMhqjB.o5RPUahtRp.A0O.NVRzf6sgr.a0r4fS', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-07 15:16:19', '2026-03-09 17:31:16', 'AC06AE5F', 24, NULL, NULL),
(46, 'EMMANUEL WERANGAI', 'buyer_007', 'YnV5ZXJfMDA3QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODcwMA==', NULL, NULL, '$2y$10$P0pEi8er/MHJjMoSFPC4h.9zMW0cEDEDY5x6whDU89S6WufGI.Mfe', 'buyer', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', NULL, NULL, NULL, NULL, '2026-03-07 22:35:44', '2026-03-09 17:15:38', NULL, NULL, NULL, NULL),
(47, 'Maxwell Gadhambi', 'seller_700', 'c2VsbGVyXzcwMEBnbWFpbC5pY29t', 'KzI1NDc1OTU3ODk0NA==', NULL, NULL, '$2y$10$V1EwRZfZr6UpLnrnu/Rdju0YQr7TDM0cfWKp/bhVfzkWoUEAgKyCC', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', 'BETRADES MANAGEMENT', 'products', 'service_provider', 'local', '2026-03-07 22:38:40', '2026-03-09 19:03:23', NULL, NULL, NULL, NULL),
(48, 'Stephen Munene', 'agent_021', 'YWdlbnRfMDIxQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYyMQ==', NULL, NULL, '$2y$10$AVTZ6iumLpvWaXkyJS9fP.7v8mfJCgRfZ1vy47neuSffWtmUUokHy', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-07 22:40:45', '2026-03-09 17:00:33', 'DCFD122D', NULL, NULL, NULL),
(49, 'Sarah Makenya', 'agent_023', 'YWdlbnRfMDIzQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAyMw==', NULL, NULL, '$2y$10$vAevNL81TjMAx5p.nFMjWuDC6aBbVjoxWSM42/JZR2fF290dUN0Eq', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Mwea', '', '', '', '', '2026-03-11 00:50:06', '2026-03-11 00:50:06', '95CB9FD2', 48, NULL, 0);

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

--
-- Dumping data for table `user_cart`
--

INSERT INTO `user_cart` (`cart_id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(23, 14, 4, 2, '2026-02-26 09:52:07', '2026-03-09 12:00:59'),
(25, 14, 1, 1, '2026-03-09 12:00:54', '2026-03-09 12:00:54');

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
(14, 15, '2026-02-25 20:34:14'),
(14, 17, '2026-02-25 23:27:58'),
(14, 18, '2026-02-25 20:26:17'),
(14, 19, '2026-03-09 09:43:57'),
(14, 22, '2026-03-09 09:43:53'),
(16, 17, '2026-02-25 21:49:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agent_commissions`
--
ALTER TABLE `agent_commissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `agent_wallet`
--
ALTER TABLE `agent_wallet`
  ADD PRIMARY KEY (`agent_id`);

--
-- Indexes for table `markethub_products`
--
ALTER TABLE `markethub_products`
  ADD PRIMARY KEY (`id`);

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
  ADD KEY `idx_user_image` (`user_id`,`image_hash`);

--
-- Indexes for table `seller_ratings`
--
ALTER TABLE `seller_ratings`
  ADD PRIMARY KEY (`rating_id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `buyer_id` (`buyer_id`);

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agent_commissions`
--
ALTER TABLE `agent_commissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `markethub_products`
--
ALTER TABLE `markethub_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=178;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `productservicesrentals`
--
ALTER TABLE `productservicesrentals`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `seller_ratings`
--
ALTER TABLE `seller_ratings`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `user_cart`
--
ALTER TABLE `user_cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `productservicesrentals`
--
ALTER TABLE `productservicesrentals`
  ADD CONSTRAINT `productservicesrentals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `seller_ratings`
--
ALTER TABLE `seller_ratings`
  ADD CONSTRAINT `seller_ratings_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `seller_ratings_ibfk_2` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`payer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `user_cart`
--
ALTER TABLE `user_cart`
  ADD CONSTRAINT `user_cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `productservicesrentals` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_followers`
--
ALTER TABLE `user_followers`
  ADD CONSTRAINT `user_followers_ibfk_1` FOREIGN KEY (`follower_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_followers_ibfk_2` FOREIGN KEY (`followed_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
