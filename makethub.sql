-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 19, 2026 at 03:19 PM
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
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `confirmed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(95, 'ORD-20260309-09708', 14, 1300.00, 'Pending', '2026-03-09 12:01:40'),
(96, 'ORD-20260312-69891', 14, 26120.00, 'Pending', '2026-03-12 19:47:56'),
(97, 'ORD-20260313-16234', 61, 13000.00, 'Pending', '2026-03-13 21:22:35'),
(98, 'ORD-20260313-24099', 61, 3980.00, 'Pending', '2026-03-13 21:24:10'),
(99, 'ORD-20260313-70261', 61, 120.00, 'Pending', '2026-03-13 21:25:25'),
(100, 'ORD-20260316-79342', 14, 3900.00, 'Pending', '2026-03-16 02:48:42');

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
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`item_id`, `order_id`, `product_id`, `seller_id`, `quantity`, `price`, `subtotal`) VALUES
(114, 66, 8, 18, 1, 67.00, 0.00),
(115, 67, 8, 18, 3, 67.00, 0.00),
(116, 68, 2, 17, 2, 13000.00, 0.00),
(117, 69, 3, 17, 2, 1300.00, 0.00),
(118, 69, 2, 17, 2, 13000.00, 0.00),
(119, 69, 7, 17, 2, 123.00, 0.00),
(120, 69, 8, 18, 6, 67.00, 0.00),
(121, 70, 7, 17, 1, 123.00, 0.00),
(122, 70, 3, 17, 1, 1300.00, 0.00),
(123, 70, 2, 17, 1, 13000.00, 0.00),
(124, 70, 1, 17, 1, 40.00, 0.00),
(125, 70, 8, 18, 1, 67.00, 0.00),
(126, 71, 7, 17, 1, 123.00, 0.00),
(127, 71, 3, 17, 1, 1300.00, 0.00),
(128, 71, 2, 17, 1, 13000.00, 0.00),
(129, 71, 1, 17, 1, 40.00, 0.00),
(130, 71, 8, 18, 1, 67.00, 0.00),
(131, 72, 7, 17, 1, 123.00, 0.00),
(132, 72, 3, 17, 1, 1300.00, 0.00),
(133, 72, 2, 17, 1, 13000.00, 0.00),
(134, 72, 1, 17, 1, 40.00, 0.00),
(135, 72, 8, 18, 1, 67.00, 0.00),
(136, 73, 7, 17, 1, 123.00, 0.00),
(137, 73, 3, 17, 1, 1300.00, 0.00),
(138, 73, 2, 17, 1, 13000.00, 0.00),
(139, 73, 1, 17, 1, 40.00, 0.00),
(140, 73, 8, 18, 1, 67.00, 0.00),
(141, 74, 3, 17, 1, 1300.00, 0.00),
(142, 75, 7, 17, 2, 123.00, 0.00),
(143, 76, 3, 17, 1, 1300.00, 0.00),
(144, 76, 1, 17, 1, 40.00, 0.00),
(145, 77, 8, 18, 1, 67.00, 0.00),
(146, 78, 7, 17, 2, 123.00, 0.00),
(147, 78, 3, 17, 2, 1300.00, 0.00),
(148, 78, 8, 18, 1, 67.00, 0.00),
(149, 79, 8, 18, 1, 67.00, 0.00),
(150, 80, 8, 18, 2, 67.00, 0.00),
(151, 81, 7, 17, 1, 123.00, 0.00),
(172, 89, 3, 17, 1, 1300.00, 0.00),
(173, 89, 2, 17, 1, 13000.00, 0.00),
(174, 89, 1, 17, 1, 40.00, 0.00),
(175, 92, 2, 17, 8, 13000.00, 0.00),
(176, 92, 8, 18, 1, 67.00, 0.00),
(177, 95, 3, 17, 1, 1300.00, 0.00),
(178, 96, 1, 17, 2, 40.00, 0.00),
(179, 96, 2, 17, 2, 13000.00, 0.00),
(180, 96, 4, 19, 1, 40.00, 0.00),
(181, 97, 2, 17, 1, 13000.00, 13000.00),
(182, 98, 6, 17, 3, 1300.00, 3900.00),
(183, 98, 4, 19, 2, 40.00, 80.00),
(184, 99, 4, 19, 3, 40.00, 120.00),
(185, 100, 6, 17, 3, 1300.00, 3900.00);

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
  `image_phash` varchar(64) NOT NULL,
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

INSERT INTO `productservicesrentals` (`product_id`, `user_id`, `product_name`, `category`, `price`, `stock_quantity`, `image_path`, `image_hash`, `image_phash`, `image_width`, `image_height`, `image_size_kb`, `image_format`, `status`, `created_at`, `updated_at`) VALUES
(1, 17, 'Passion Juice', 'Food & Snacks', 40.00, 54, 'uploads/products/product_69b326f6b4ae99.57949411.webp', '1e36755c2dd8ab73353d6fea7ca79357', '0101011011010010110110010101001101001011011011010110000101110001', 700, 700, 57, 'webp', 'active', '2026-02-25 21:50:12', '2026-03-12 20:49:58'),
(2, 17, 'Bike', 'Home Items', 13000.00, 10, 'uploads/products/product_69b3272c767636.51142839.webp', '749ca7546a4f7101a17832addcac2dc8', '1110000011100001111000111010001100100001000100011001001110011000', 700, 519, 55, 'webp', 'active', '2026-02-25 22:52:53', '2026-03-13 21:22:35'),
(4, 19, 'Passion Juice', 'Food & Snacks', 40.00, 28, 'uploads/products/product_69a017086d9a78.65234946.webp', '57cce22a22b1386a867d231a364f5109', '', 700, 700, 33, 'webp', 'active', '2026-02-26 09:48:56', '2026-03-13 21:25:25'),
(5, 17, 'Mango Juice', 'Food & Snacks', 100.00, 40, 'uploads/products/product_69b3260a3a9752.03514655.webp', '777858f62267ddffd97c8263af29c6b0', '0001010000010110000101100001011001001101000000000000000000010110', 700, 475, 28, 'webp', 'active', '2026-03-12 20:44:39', '2026-03-12 20:46:02'),
(6, 17, 'JUG', 'Home Items', 1300.00, 5, 'uploads/products/product_69b326575ae702.73952612.webp', '8b7412a1d74c7b1c3299194b1aebbfd8', '1011001011110011011110010111100100110011011110110110100111101000', 700, 700, 35, 'webp', 'active', '2026-03-12 20:47:19', '2026-03-16 02:48:43');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscribers`
--

INSERT INTO `subscribers` (`id`, `email`, `created_at`) VALUES
(1, 'emmanueltindi23@gmail.com', '2026-03-18 12:06:22'),
(3, 'bWFya2V0aHViMTc0NUBnbWFpbC5jb20=', '2026-03-18 12:14:54'),
(4, 'bWFya2V0aHViMTc0NTdAZ21haWwuY29t', '2026-03-18 12:17:27'),
(5, 'ZW1tYW51ZWx0aW5kaTIzM0BnbWFpbC5jb20=', '2026-03-18 12:28:31'),
(6, 'ZW1tYW51ZWx0aW5kaTIyQGdtYWlsLmNvbQ==', '2026-03-18 12:30:15'),
(7, 'ZW1tYW51ZWx0aW5kaTIzMzkwQGdtYWlsLmNvbQ==', '2026-03-18 12:33:36'),
(8, 'ZW1tYW51ZWx0aW5kaTIzQGdtYWlsLmNvbQ==', '2026-03-18 12:37:12'),
(9, '7ccdedcf6a30fea513b27023af9adb7a0dfda2538fc266646004402cfe819c4d', '2026-03-18 13:11:02'),
(10, '2f8c494234404228f22150e6c43df2ce8c1abc425c9437f261fdb9d3ad014634', '2026-03-18 13:11:49'),
(11, '3a061b54051d605787c5a5c86eb04fc8eec85d5651a2f87db4e43eb02ad9001b', '2026-03-18 13:14:54'),
(12, '4c1b4a701e33e6892ca5c3be55499d39b19992036cfa15d981d63a56cfbf08c8', '2026-03-18 13:25:59'),
(13, 'e41a8023d1da797e77983ebb1be640b7733631bdec8c850e3051362824c3e88e', '2026-03-18 13:29:15'),
(14, 'ca6d009a75c57f03ee4794fc72f4b37b6783bdea5bd57c550e58fd7304cdae28', '2026-03-19 07:44:15'),
(15, 'ae89acbeaeba3150a9af2b38ab5ccdecfed6fe947db95280b488977064eab574', '2026-03-19 07:55:32'),
(16, '2e53750bb7af0776f8311a121e062e1f7b894c87663261e6fa61006c3783dd49', '2026-03-19 08:01:01'),
(17, '394e0d66a493b63ea7d2ddb446bd77f2d7eb5ac283a38c6d5900a9aa31aee063', '2026-03-19 08:01:14'),
(18, '727f493df298a02d0b57176e9ce4d1794dfb1f33566dfb619be4c703b3730473', '2026-03-19 08:35:39'),
(19, 'cdcf6b77e5e36672f2b3961f1f5aa7b4cc14f5801e27a09966682403e4ed1eb1', '2026-03-19 08:51:40'),
(20, 'fadf747b0dcc46e19e93e88afe3548ba28ecb2c48249fb59c6e083265c797a43', '2026-03-19 08:53:38'),
(21, '81d0079b49fb7a1978f25cad179e4989c00f35bdb331258d2e602813c3961008', '2026-03-19 08:53:57'),
(22, '8d0283734618087264193ff7fa721148848566d3c78934220df48edb991250f5', '2026-03-19 08:54:16');

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
  `must_change_password` tinyint(1) DEFAULT 0,
  `economic_period_count` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `username`, `email`, `phone`, `profile_image`, `bio`, `password`, `account_type`, `is_verified`, `status`, `country`, `county`, `ward`, `address`, `business_name`, `business_model`, `business_type`, `market_scope`, `created_at`, `updated_at`, `agency_code`, `referred_by`, `agent_activated_at`, `must_change_password`, `economic_period_count`) VALUES
(14, 'EMMANUEL WERANGAI', 'buyer_001', 'YnV5ZXJfMDAxQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYzMA==', NULL, '', '$2y$10$eZaCoaJd1r6NPqecOo8gX.fhXnk2sCbxyrfRLL7YoGllHWaLmyS5W', 'buyer', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', NULL, NULL, NULL, NULL, '2026-02-25 13:54:11', '2026-03-09 18:04:35', NULL, NULL, NULL, 0, 0),
(15, 'EMMANUEL WERANGAI', 'seller_001', 'c2VsbGVyXzAwMUBnbWFpbC5jb20=', 'KzI1NDc3MzAyOTQ0MA==', NULL, 'I am here', '$2y$10$CqCArt9rG4O278HT4JRwz.OT8yNHssp6D9Wlvg50VHBBnVRrkGobi', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni', 'kisumu ndogo', 'market hub', NULL, 'shop', 'Local', '2026-02-25 14:18:40', '2026-03-04 06:45:20', NULL, NULL, NULL, 0, 0),
(16, 'EMMANUEL WERANGAI', 'buyer_002', 'YnV5ZXJfMDAyQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYzMQ==', NULL, NULL, '$2y$10$DVxzrzSTosLmpkLoCos9QeLNGDPAmW3YolY5W6dYMhV7FnTSE2OIW', 'buyer', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Watamu', NULL, NULL, NULL, NULL, '2026-02-25 14:26:12', '2026-02-25 14:26:12', NULL, NULL, NULL, 0, 0),
(17, 'EMMANUEL WERANGAI', 'seller_002', 'c2VsbGVyXzAwMkBnbWFpbC5jb20=', 'KzI1NDc1OTU3ODYzMg==', NULL, NULL, '$2y$10$VABFqJUUCuGnx3M5NN6sfuDJ640MPia5z5mofYZv0.niTtM8u6cva', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', 'main canteen', NULL, 'canteen', 'Local', '2026-02-25 14:29:15', '2026-02-25 14:29:15', NULL, NULL, NULL, 0, 0),
(18, 'EMMANUEL WERANGAI', 'seller_003', 'c2VsbGVyXzAwM0BnbWFpbC5jb20=', 'KzI1NDc1OTU3ODYzMw==', NULL, NULL, '$2y$10$kdTNspptrvARewNR3/9PQ.0lx4fwWXrF33TPqC7.VRsa2dXMW1sfG', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', 'BETRADES MANAGEMENT', NULL, 'supermarket', 'Local', '2026-02-25 14:31:11', '2026-02-25 14:31:11', NULL, NULL, NULL, 0, 0),
(19, 'EMMANUEL WERANGAI', 'seller_004', 'c2VsbGVyXzAwNEBnbWFsaS5jb20=', 'KzI1NDc1OTU3ODYzNQ==', NULL, NULL, '$2y$10$OR16Ab7u7PiToTnqRTGzCeNrlBf4E4Cyr.eAyoGYnLmyN2foUtvK2', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Gede', 'Fred Pharmacy', 'products', 'supermarket', 'local', '2026-02-26 09:26:57', '2026-02-26 09:26:57', NULL, NULL, NULL, 0, 0),
(20, 'Sheila Barasa', 'admin_001', 'ZW1tYW51ZWx0aW5kaTIzQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYzOQ==', NULL, NULL, '$2y$10$QE2Yt9Dg465QGVMZVj4ds.RW8dvGVv0Kh9cCL2jzbbPN0cLs/i/pu', 'buyer', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Naivas', NULL, NULL, NULL, NULL, '2026-03-04 16:23:46', '2026-03-09 19:05:27', NULL, NULL, NULL, 0, 0),
(21, 'EMMANUEL WERANGAI', 'admin_002', 'ZW1tYW51ZWx0aW5kaTIyQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYzNw==', NULL, NULL, '$2y$10$wXvvXl3huLmsyfaBVngweudcSiAv2g2btyvkJqILmsqJBMcTtdsMi', 'administrator', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-04 16:51:33', '2026-03-04 16:51:33', NULL, NULL, NULL, 0, 0),
(22, 'Adyline Cherono', 'seller_005', 'c2VsbGVyXzAwNUBnbWFpbC5jb20=', 'KzI1NDcwODY3MDM5Ng==', NULL, NULL, '$2y$10$jduCXGurRvzRC20WfG2N9ehYyB6RJlk6SnROkJ5HlNAmRqz6JveQm', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Mlika mwizi', 'mama adrian shop', 'products', 'canteen', 'local', '2026-03-04 18:44:28', '2026-03-04 18:44:28', NULL, NULL, NULL, 0, 0),
(23, 'Elijah Barasa', 'blessedemmanuel258', 'ZW1tYW51ZWx0aW5kaTI4QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYzOA==', NULL, NULL, '$2y$10$Q4EqtpdY0nlhULKLoI3wrO.PnjzwpYjniSTi.T468AUSCnq.YfWOq', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 19:44:41', '2026-03-09 19:08:47', '05C0FAFF', NULL, NULL, 0, 0),
(24, 'EMMANUEL WERANGAI', 'blessedemmanuel259', 'ZW1tYW51ZWx0aW5kaTI5QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODg3Ng==', NULL, NULL, '$2y$10$s7xcA2eUS3J/XjtapX66.ew.juyyse1sQA/LsLxxHSGOSmi8LglDi', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 19:49:12', '2026-03-11 07:54:40', '4BD0D5A8', NULL, '2026-03-11 10:54:40', 0, 0),
(25, 'EMMANUEL WERANGAI', 'blessedemmanuel251', 'ZW1tYW51ZWx0aW5kaTIxQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODM0NQ==', NULL, NULL, '$2y$10$pEQu.sJshyZpbPPQm7oc7.T.oApDK0iDjaubSNy1zHyDtGBnDqZAW', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 19:50:16', '2026-03-05 19:50:16', 'E5EE3E04', 24, NULL, 0, 0),
(26, 'Blessed Emmanuel', 'agent_001', 'YWdlbnRfMDAxQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYyMw==', NULL, NULL, '$2y$10$QttwBhgfEqpkerZFR2Xqbe8QjWx8bB8U.xCbOJ07OID7uflfhrQFa', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 19:54:24', '2026-03-05 19:54:24', 'DBE25C71', 24, NULL, 0, 0),
(27, 'Blessed Emmanuel', 'agent_002', 'YWdlbnRfMDAyQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYyOQ==', NULL, NULL, '$2y$10$88kTe0mRkp6XNtzm.9yf0uqyTot.53Q1QzzVwqsf8XpHuD/AIGS7e', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 19:56:07', '2026-03-11 08:05:56', '574B94B3', 24, '2026-03-11 11:05:56', 0, 0),
(28, 'EMMANUEL WERANGAI', 'agent_003', 'YWdlbnRfMDAzQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODY4Nw==', NULL, NULL, '$2y$10$N4//.tzoTnFxqdfmuFTNa.4wJJLzs0JaCgLPnAunUwtwsqFspTmOa', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 19:58:27', '2026-03-05 19:58:27', 'A6C50F36', 24, NULL, 0, 1),
(29, 'EMMANUEL WERANGAI', 'agent_004', 'YWdlbnRfMDA0QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODY0Mw==', NULL, NULL, '$2y$10$EOtCwhrV/jCQYYHil/Y45uLUB1OKVV6QZ6CNa8eAbusI6P2Zwz.OW', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:07:16', '2026-03-05 20:07:16', '7AAFF72B', NULL, NULL, 0, 0),
(30, 'EMMANUEL WERANGAI', 'agent_005', 'YWdlbnRfMDA1QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODY0NQ==', NULL, NULL, '$2y$10$VxcIev3D1cupiDEDkKFMr.6xmRqWC9mJgr5h416d7MqF.9.Qbhs9G', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:08:21', '2026-03-05 20:08:21', 'F4700C3E', NULL, NULL, 0, 0),
(31, 'EMMANUEL WERANGAI', 'agent_006', 'YWdlbnRfMDA2QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAwNQ==', NULL, NULL, '$2y$10$.90aYKj0PyiM5r0Vj.yG5uIzZ9sKJKz.53KunC6mxE/8.SCzAbpbe', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:16:49', '2026-03-05 20:16:49', '1CEE060F', NULL, NULL, 0, 0),
(32, 'EMMANUEL WERANGAI', 'agent_007', 'YWdlbnRfMDA3QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAwNw==', NULL, NULL, '$2y$10$cFidq5ds8kXff9okR4JL7.AWG4M9tInTB1JqKQ8zlBLFJMQaD3.pO', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:19:07', '2026-03-05 20:19:07', '110A64B4', NULL, NULL, 0, 0),
(33, 'EMMANUEL Wanji', 'wanjala', 'YWdlbnRfMDA4QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAwOA==', NULL, NULL, '$2y$10$loS7O2tNUZ0uVnBtRt0UUuvY9W2my.zWPM7bedcykvvDwY9THkrWi', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:22:09', '2026-03-09 18:10:22', '13B8E3CD', 28, NULL, 0, 0),
(34, 'EMMANUEL WERANGAI', 'agent_009', 'YWdlbnRfMDA5QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAwOQ==', NULL, NULL, '$2y$10$S7O0QIC/hsVFUb.aLtO39em578pKcbDwbUktgazsLW084HFdRP/zu', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:24:02', '2026-03-05 20:24:02', '1FFE9D40', NULL, NULL, 0, 0),
(35, 'EMMANUEL WERANGAI', 'agent_010', 'YWdlbnRfMDEwQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxMA==', NULL, NULL, '$2y$10$zPwTRP899ZKT7YtweOmVHuTRrmZrxGBwjpzvaJGYBk4TK4kntymua', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:32:10', '2026-03-05 20:32:10', '503AFDE4', 28, NULL, 0, 0),
(36, 'EMMANUEL WERANGAI', 'agent_011', 'YWdlbnRfMDExQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxMQ==', NULL, NULL, '$2y$10$ojtPgyAyEwbx.V4MSxarLOEHlVaYtRt0XrQumm/CbB3RV0GKm1s3O', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:35:18', '2026-03-11 05:20:25', '2556FDBD', 27, '2026-03-11 08:20:25', 0, 0),
(37, 'Kisembi Hyalo', 'agent_012', 'YWdlbnRfMDEyQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxMg==', NULL, NULL, '$2y$10$Dlveo6Ny1.KwZKOdpCqvCeVEJZa6jm55DnGigHUxRsocRrO1yasse', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:42:09', '2026-03-05 20:42:09', '3CCFB557', NULL, NULL, 0, 0),
(38, 'EMMANUEL WERANGAI', 'agent_013', 'YWdlbnRfMDEzQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxMw==', NULL, NULL, '$2y$10$bG8ZaUPS8Tkx9gGGzc99H.BDm242/yP5282ZKxlvWBsbBGudjg5dG', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:50:18', '2026-03-05 20:50:18', '66CCD3B4', 23, NULL, 0, 0),
(39, 'DASHCAM BUNDI', 'agent_014', 'YWdlbnRfMDE0QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxNA==', NULL, NULL, '$2y$10$OmMKXPH4EfiygWGP3etlJeMKHl3dt8Nk1J1IAPb.fJxEiVivm8QNq', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:52:12', '2026-03-05 20:52:12', 'DB375B5B', 26, NULL, 0, 0),
(40, 'EMMANUEL WERANGAI', 'agent_015', 'YWdlbnRfMDE1QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxNQ==', NULL, NULL, '$2y$10$LBlcMBAkDRgpX5KjpwM46evzOKroLGUVAIUibrwYGkNCbyf51zrU2', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:55:09', '2026-03-09 18:04:27', '4FA2A924', 39, NULL, 0, 0),
(41, 'EMMANUEL Wangari', 'agent_016', 'YWdlbnRfMDE2QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODcwMQ==', NULL, NULL, '$2y$10$HIsc/sxh0aQBv8Ob/QKp1u3vmnV9th/9ZAQ8o6M7eH6A/jB3yNGG6', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:58:30', '2026-03-09 17:17:12', 'E1B02841', 40, NULL, 0, 0),
(42, 'EMMANUEL WERANGAI', 'agent_017', 'YWdlbnRfMDE3QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxNw==', NULL, NULL, '$2y$10$Kzq5SpMxd7bCnAymotAweu43VcUrOEdLNbSKBKpTo7fPsoiH2q6Sa', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 21:01:07', '2026-03-05 21:01:07', 'D8E51616', 41, NULL, 0, 0),
(43, 'EMMANUEL WERANGAI', 'agent_018', 'YWdlbnRfMDE4QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxOA==', NULL, NULL, '$2y$10$hCienXkHmtf2nev63yPm8.TOq3iJ3r1xfReTAX0d/9l7U3CPPXRcy', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 21:04:05', '2026-03-11 05:58:06', '79B3A9CC', 27, '2026-03-11 08:58:06', 0, 0),
(44, 'EMMANUEL WERANGAI', 'agent_19', 'YWdlbnRfMTlAZ21haWwuY29t', 'KzI1NDc1OTU3ODAxOQ==', NULL, NULL, '$2y$10$ePgT/PAUzpcukoOrGArbYOTjw8p0u1GYr0VFSqCl2OCMrxpdugNkS', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 21:25:58', '2026-03-11 08:12:38', '2CC06969', 43, '2026-03-11 11:12:38', 0, 0),
(45, 'Bramuel Wafula', 'agent_20', 'YWdlbnRfMjBAZ21haWwuY29t', 'KzI1NDc1OTU3ODYyMA==', NULL, NULL, '$2y$10$EhJt6Fc6hj0v9Q9gMhqjB.o5RPUahtRp.A0O.NVRzf6sgr.a0r4fS', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-07 15:16:19', '2026-03-09 17:31:16', 'AC06AE5F', 24, NULL, 0, 0),
(46, 'EMMANUEL WERANGAI', 'buyer_007', 'YnV5ZXJfMDA3QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODcwMA==', NULL, NULL, '$2y$10$P0pEi8er/MHJjMoSFPC4h.9zMW0cEDEDY5x6whDU89S6WufGI.Mfe', 'buyer', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', NULL, NULL, NULL, NULL, '2026-03-07 22:35:44', '2026-03-09 17:15:38', NULL, NULL, NULL, 0, 0),
(47, 'Maxwell Gadhambi', 'seller_700', 'c2VsbGVyXzcwMEBnbWFpbC5pY29t', 'KzI1NDc1OTU3ODk0NA==', NULL, NULL, '$2y$10$V1EwRZfZr6UpLnrnu/Rdju0YQr7TDM0cfWKp/bhVfzkWoUEAgKyCC', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', 'BETRADES MANAGEMENT', 'products', 'service_provider', 'local', '2026-03-07 22:38:40', '2026-03-12 15:39:17', NULL, NULL, NULL, 0, 0),
(48, 'Stephen Munene', 'agent_021', 'YWdlbnRfMDIxQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYyMQ==', NULL, NULL, '$2y$10$AVTZ6iumLpvWaXkyJS9fP.7v8mfJCgRfZ1vy47neuSffWtmUUokHy', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-07 22:40:45', '2026-03-09 17:00:33', 'DCFD122D', NULL, NULL, 0, 0),
(49, 'Sarah Makenya', 'agent_023', 'YWdlbnRfMDIzQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAyMw==', NULL, NULL, '$2y$10$vAevNL81TjMAx5p.nFMjWuDC6aBbVjoxWSM42/JZR2fF290dUN0Eq', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Mwea', '', '', '', '', '2026-03-11 00:50:06', '2026-03-11 00:50:06', '95CB9FD2', 48, NULL, 0, 0),
(50, 'Market Hub', 'agent_111', 'bWFya2V0aHViMTc0NUBnbWFpLmNvbQ==', 'KzI1NDc1OTU3ODExMQ==', NULL, NULL, '$2y$10$fWTwm2sp5JjTwavXIbeNqOdz2hbNs8e5fiMmqScvbOBqLZNSlPv4e', 'sales_agent', 0, 'active', 'Kenya', 'Kenya', 'Sokoni Ward', 'Naivas', '', '', '', '', '2026-03-12 09:00:04', '2026-03-12 09:00:04', 'BB625837', NULL, NULL, 1, 0),
(51, 'Market Hub', 'agent_112', 'bWFya2V0aHViMTc0NUBnbWFpbC5jb20=', 'KzI1NDc1OTU3ODExMg==', NULL, NULL, '$2y$10$N0vy6TpPWtjEy2kAEN0JxuH7VwdMKk0JhwywcVFdkAlk0Qzva8lwm', 'sales_agent', 0, 'active', 'Kenya', 'Kenya', 'Sokoni Ward', 'Naivas', '', '', '', '', '2026-03-12 09:00:54', '2026-03-12 09:00:54', 'BE4EDF75', NULL, NULL, 1, 0),
(52, 'Market Hub', 'seller_044', 'bWFya2V0aHViMTc0NkBnbWFpbC5jb20=', 'KzI1NDc1OTU3ODA0NA==', NULL, NULL, '$2y$10$vDIBunJcrdeiR26o43lYxu50mgvEjqC.Z1DbWDLy6PUb43gZ54p5i', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Cherowamaye', '', '', '', '', '2026-03-12 11:47:32', '2026-03-12 12:36:23', '1BA286F8', NULL, NULL, 1, 0),
(53, 'Blessed Emmanuel', 'agent_29', 'YWdlbnRfMjlAZ21haWwuY29t', 'KzI1NDcwODY3MDM5MA==', NULL, NULL, '$2y$10$P/XbE8qnTh6btky3HnxYmeDmqGEqOI7h/0E5W0JHgdcgQrZlerszu', 'buyer', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', NULL, NULL, NULL, NULL, '2026-03-12 13:33:11', '2026-03-13 12:19:11', NULL, NULL, NULL, 0, 0),
(54, 'Blessed Emmanuel', 'agent_30', 'YWdlbnRfMzBAZ21haWwuY29t', 'KzI1NDcwODY3MDMzMA==', NULL, NULL, '$2y$10$lFMaGQ7JX3yvoP3/ffckkO9UZQs1siEnqcmXKyvQ8zNZNaxqtwUUm', 'buyer', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', NULL, NULL, NULL, NULL, '2026-03-12 13:51:17', '2026-03-12 13:51:17', NULL, NULL, NULL, 0, 0),
(55, 'Siny Rita', 'agent_031', 'YWdlbnRfMDMxQGdtYWlsLmNvbQ==', 'KzI1NDczMzMzNDQzMg==', NULL, NULL, '$2y$10$pKm6V6CLbloXBW/H.Gd3mOKpzWOeyEDg8ywIFYCjn.RMHSQkHtUiO', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Webuye', '', '', '', '', '2026-03-12 14:25:50', '2026-03-12 14:25:50', '834EB0C0', NULL, NULL, 1, 0),
(56, 'EMMANUEL WERANGAI', 'agent_033', 'YWdlbnRfMDMzQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAzMw==', NULL, NULL, '$2y$10$febojXFAsprvPSiPsNLmreS1jix9ohPPsacpofmzy36VPHwmoT2pS', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-12 14:36:51', '2026-03-12 14:36:51', '0D82E6B4', 55, NULL, 0, 0),
(57, 'Shakrah Raha', 'seller_006', 'c2VsbGVyXzAwNkBnbWFpbC5jb20=', 'KzI1NDc1OTU3ODAwNg==', NULL, NULL, '$2y$10$QsfUBx5StBMMbjol1zjixunzTFlMxudfTDsQW89lYXk9wn819QZPG', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', 'Baba Josee Shop', 'products', 'canteen', 'local', '2026-03-12 14:39:41', '2026-03-13 12:18:50', NULL, NULL, NULL, 0, 0),
(58, 'Babu Tash', 'agent_700', 'YWdlbnRfNzAwQGdtYWlsLmNvbQ==', 'KzI1NDcwODY3MDM5OQ==', NULL, NULL, '$2y$10$bzMjrP1/U4SvQKX.J7U5ausvpm6wi.4ByhR1tt0p7uwsisXi3ljW6', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-12 15:04:07', '2026-03-12 15:04:07', 'D64E3395', 56, NULL, 0, 0),
(59, 'Mercy Suli', 'agent_034', 'YWdlbnRfMDM0QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAzNA==', NULL, NULL, '$2y$10$OJ3lquG4JC2qHewwK9qrtOlXKy/hRiX4su74caZoPfnt2v7eANoPW', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Tewani', '', '', '', '', '2026-03-12 15:10:13', '2026-03-13 11:19:38', '5DA88488', 56, NULL, 1, 0),
(60, 'Kari Masi', 'agent_036', 'YWdlbnRfMDM2QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAzNg==', NULL, NULL, '$2y$10$KcqLgdebt6DmBxX65h6/iO7l9raIJWzg5mpq2Q6/ovI7IFKYmLC1e', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Lanag\'ata', '', '', '', '', '2026-03-12 17:17:31', '2026-03-13 12:18:22', '3B0D6312', 56, NULL, 1, 0),
(61, 'EMMANUEL WERANGAI', 'buyer_005', 'YnV5ZXJfMDA1QGdtYWlsLmNvbQ==', 'KzI1NDcwODY3MDM5Mw==', NULL, NULL, '$2y$10$N39tINcDGFj7nCZnS2fh1.qEXFhiOQbaJsixE2RMzx280xayI4RPq', 'buyer', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', NULL, NULL, NULL, NULL, '2026-03-13 11:29:53', '2026-03-13 12:20:32', NULL, NULL, NULL, 0, 0),
(63, 'EMMANUEL WERANGAI', 'buyer_006', 'YnV5ZXJfMDA2QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODA2Ng==', NULL, NULL, '$2y$10$DrImxEmZfqPDB/5CwqU1O.0xLcaLDGcjeB7qJLEqpiQz8bNSdX54a', 'buyer', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', NULL, NULL, NULL, NULL, '2026-03-13 12:05:56', '2026-03-13 12:05:56', NULL, NULL, NULL, 0, 0),
(64, 'Kam rahie', 'agent_037', 'YWdlbnRfMDM3QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAwMQ==', NULL, NULL, '$2y$10$LxfTaRQUPYFq2zdMrwGz.Od3IUfXZdF5.Z7tJ5VTRpLvWsnPsXBEO', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-13 12:25:18', '2026-03-13 12:25:18', '078ED12A', 26, NULL, 1, 0),
(65, 'Dhihh kenks', 'agent_38', 'YWdlbnRfMzhAZ21haWwuY29t', 'KzI1NDc1OTU3ODAwMg==', NULL, NULL, '$2y$10$0OovIRquc983QrkgB9Xn6ObiAedZa2uCxOdQNmiTSUKzvvTA3xlXq', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-13 12:27:25', '2026-03-13 12:27:25', '8D3B344F', 26, NULL, 1, 0),
(66, 'Shais kkoe', 'agent_39', 'YWdlbnRfMzlAZ21haWwuY29t', 'KzI1NDcwODY3MDMzOQ==', NULL, NULL, '$2y$10$hXHeX2WdZ1ntInJ8pqTVAO2Et8yEvPGLECsoBU7Lg5UZSpTF.CHSe', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-13 12:29:26', '2026-03-13 12:29:26', '0A640263', 26, NULL, 0, 0),
(67, 'EMMANUEL WERANGAI', 'agent_040', 'YXJra2hhdmVuMDM4QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODA0MA==', NULL, NULL, '$2y$10$Tb52PctYROKDkVVypfrxN.z1Z9KkgmyzAM4PRiGSor0IwIjWjhgPK', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-13 12:40:20', '2026-03-13 12:40:20', '17964421', 26, NULL, 1, 0),
(68, 'EMMANUEL WERANGAI', 'Seller_007', 'c2VsbGVyXzAwN0BnbWFpbC5jb20=', 'KzI1NDc1OTU3ODA3Nw==', NULL, NULL, '$2y$10$k8FHjEkH6UPRhB1XajVrUuye9yiPmV2AlDmQDWMw0fcr0e3kI0qau', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', 'Betrades Managementr', 'products', 'supermarket', 'local', '2026-03-13 13:19:28', '2026-03-13 13:19:28', NULL, NULL, NULL, 0, 0),
(69, 'Shein Yuhe', 'agent_41', 'YWdlbnRfNDFAZ21haWwuY29t', 'KzI1NDc1OTU3ODA0MQ==', NULL, NULL, '$2y$10$13K/wi3JZthpAKIobt337OiKe/42k1T1.oUngvRGD1gGDAm8qjhGW', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-13 13:39:18', '2026-03-13 13:39:18', 'F90A53BA', 67, NULL, 1, 0),
(70, 'Kulih Judha', 'agent_042', 'YWdlbnRfMDQyQGdtYWlsLmNvbQ==', 'KzI1NDcwODY3MDA0Mg==', NULL, NULL, '$2y$10$1Ofj5JZ5jfMZ7a/hCrWcge.lIzcaBJKlWoSJEMqAVbF8zmmtrAY5.', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-13 13:42:22', '2026-03-13 13:42:22', '356E410B', 67, NULL, 0, 0),
(71, 'Maket Hub', 'seller_009', 'c2VsbGVyXzAwOUBnbWFpbC5jb20=', 'KzI1NDc1OTU3ODczMA==', NULL, NULL, '$2y$10$zosrGuMrinhZwgsoGK/3KOBa2XjcSXp5gbwPAv8MH1dzTheYV22DG', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'nyeri village', 'Makindu Shop', 'products', 'supermarket', 'national', '2026-03-15 19:20:36', '2026-03-15 19:20:36', NULL, NULL, NULL, 0, 0),
(72, 'Basharian Sille', 'seller_010', 'c2VsbGVyXzAxMEBnbWFpbC5jb20=', 'KzI1NDc1OTU3ODExMA==', NULL, NULL, '$2y$10$qyYpDvuEAoyHOyRXNq4l7.rmOonHdIqYNsLnooBCOn1vYJY/u1Mk.', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kawangware', 'Dula Shop', 'products', 'shop', 'global', '2026-03-16 02:05:09', '2026-03-16 02:05:09', NULL, NULL, NULL, 0, 0),
(73, 'Test One', 'agent_43', 'YWdlbnRfNDNAZ21haWwuY29t', 'KzI1NDczMzMzNDQ0Mw==', NULL, NULL, '$2y$10$Osd.LNMPYbCTfUuwefuWWOR9hwHGwaBvCrVgv3qECk9Eo8Moz3iwi', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Shanzu', '', '', '', '', '2026-03-17 12:56:00', '2026-03-17 12:56:00', '1AF333C5', 56, NULL, 0, 0),
(74, 'Wallace Buyer', 'buyer_009', 'YnV5ZXJfMDA5QGdtYWlsLmNvbQ==', 'KzI1NDczMzMzNDQzOQ==', NULL, NULL, '$2y$10$hwC.mMfh0Zu0QaLWTCJVdOMzLqO4Ke/Q6qb8u94H599XyHwCpQYJG', 'buyer', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Shanye', NULL, NULL, NULL, NULL, '2026-03-18 20:15:32', '2026-03-18 20:15:32', NULL, NULL, NULL, 0, NULL),
(75, 'Wallace Seller', 'seller_008', 'c2VsbGVyXzAwOEBnbWFpbC5jb20=', 'KzI1NDc1OTU3ODA4OA==', NULL, NULL, '$2y$10$E9lvsaHFMqZKlGyw5xMRRuArRWbmEqJ0.9/xzOD8WU5X0ILu3.DoS', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Myrthia', 'Babu Taya Shop', 'products', 'kiosk', 'local', '2026-03-18 20:23:42', '2026-03-18 20:23:42', NULL, NULL, NULL, 0, NULL),
(76, 'Wallace Seller1', 'seller_014', 'c2VsbGVyXzAxNEBnbWFpbC5jb20=', 'KzI1NDczMzMzNDA5OQ==', NULL, NULL, '$2y$10$VJDlZPwPJuO1xaQsa9nD6On9/lOHKM3.CwIy9DokirtCxjWfL/lsG', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Tewani', 'Shami Shop', 'products', 'shop', 'national', '2026-03-18 20:32:37', '2026-03-18 20:32:37', NULL, NULL, NULL, 0, NULL),
(77, 'Wallace Agent', 'agent_050', 'YWdlbnRfMDUwQGdtYWlsLmNvbQ==', 'KzI1NDczMzMzNDA1MA==', NULL, NULL, '$2y$10$3sC9cTIOPC6ay2FvsaqRSerKBRupin/I837AZfT7/XKioXB0BK4uW', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Lanag\'ata', '', '', '', '', '2026-03-19 02:40:36', '2026-03-19 02:40:36', '6ECDC80C', 27, NULL, 1, 0),
(78, 'Wallace Agent', 'agent_051', 'YWdlbnRfMDUxQGdtYWlsLmNvbQ==', 'KzI1NDczMzMzNDUwMA==', NULL, NULL, '$2y$10$rKYzdY1x.CvM28h71acjyOHhS5FWKGmHH5QEz/LGD1C5sVk7Oc1r2', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Lanag\'ata', '', '', '', '', '2026-03-19 02:47:52', '2026-03-19 02:47:52', '8364D33B', 27, NULL, 1, 0),
(79, 'Maket Hub', 'agent_052', 'YWdlbnRfMDUyQGdtYWlsLmNvbQ==', 'KzI1NDczMzMzNDA1Mg==', NULL, NULL, '$2y$10$5sNys92QjqjRHJF.761aMu/uH4lGPmHQvO8lgFl.MAxcv97Ug.mRS', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Cherowamaye', '', '', '', '', '2026-03-19 02:57:46', '2026-03-19 02:57:46', '6B96708A', 56, NULL, 0, 0),
(80, 'EMMANUEL WERANGAI', 'agent_053', 'YWdlbnRfMDUzQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODA1Mw==', NULL, NULL, '$2y$10$Kec.esI3SQF3rIzfat1VY.H8mQqhUFeV2rt.aCaVvdnh7/k9j8vt.', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', NULL, NULL, NULL, NULL, '2026-03-19 11:15:16', '2026-03-19 11:15:16', 'E6A3DF91', NULL, NULL, 0, 0);

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
(34, 61, 2, 1, '2026-03-14 05:31:05', '2026-03-14 05:31:05'),
(36, 14, 2, 1, '2026-03-16 08:30:28', '2026-03-16 08:30:28');

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
(14, 17, '2026-03-16 02:31:17'),
(14, 18, '2026-02-25 20:26:17'),
(14, 19, '2026-03-09 09:43:57'),
(14, 22, '2026-03-16 02:58:10'),
(14, 57, '2026-03-16 02:34:16'),
(14, 71, '2026-03-16 02:35:00'),
(14, 72, '2026-03-16 02:31:31'),
(16, 15, '2026-03-12 14:51:09'),
(16, 17, '2026-02-25 21:49:03'),
(16, 22, '2026-03-12 14:51:12'),
(16, 57, '2026-03-12 14:51:13'),
(61, 15, '2026-03-13 21:21:29'),
(61, 17, '2026-03-13 21:21:28'),
(61, 22, '2026-03-13 21:21:30'),
(61, 57, '2026-03-13 21:21:32'),
(63, 15, '2026-03-13 12:09:14'),
(63, 17, '2026-03-13 12:09:10'),
(63, 18, '2026-03-13 12:09:23'),
(63, 19, '2026-03-13 12:09:22'),
(63, 22, '2026-03-13 12:09:15'),
(63, 57, '2026-03-13 12:09:16');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`wallet_id`, `user_id`, `wallet_type`, `balance`, `total_transacted`, `created_at`, `updated_at`) VALUES
(1, 74, 'buyer', 0.00, 0.00, '2026-03-18 20:15:32', '2026-03-18 20:15:32'),
(2, 75, '', 0.00, 0.00, '2026-03-18 20:23:42', '2026-03-18 20:23:42'),
(3, 76, 'seller', 0.00, 0.00, '2026-03-18 20:32:37', '2026-03-18 20:32:37'),
(4, 77, 'sales', 0.00, 0.00, '2026-03-19 02:40:36', '2026-03-19 02:40:36'),
(5, 77, 'agency', 0.00, 0.00, '2026-03-19 02:40:36', '2026-03-19 02:40:36'),
(6, 78, 'sales', 0.00, 0.00, '2026-03-19 02:47:52', '2026-03-19 02:47:52'),
(7, 78, 'agency', 0.00, 0.00, '2026-03-19 02:47:52', '2026-03-19 02:47:52'),
(8, 79, 'sales', 0.00, 0.00, '2026-03-19 02:57:46', '2026-03-19 02:57:46'),
(9, 79, 'agency', 0.00, 0.00, '2026-03-19 02:57:46', '2026-03-19 02:57:46'),
(10, 80, 'sales', 0.00, 0.00, '2026-03-19 11:15:16', '2026-03-19 11:15:16'),
(11, 80, 'agency', 0.00, 0.00, '2026-03-19 11:15:16', '2026-03-19 11:15:16');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agent_commissions`
--
ALTER TABLE `agent_commissions`
  ADD PRIMARY KEY (`id`);

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
  ADD KEY `idx_user_image` (`user_id`,`image_hash`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `markethub_products`
--
ALTER TABLE `markethub_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mpesa_payments`
--
ALTER TABLE `mpesa_payments`
  MODIFY `payment_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=186;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `productservicesrentals`
--
ALTER TABLE `productservicesrentals`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `seller_ratings`
--
ALTER TABLE `seller_ratings`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `user_cart`
--
ALTER TABLE `user_cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `wallet_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT;

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

--
-- Constraints for table `wallets`
--
ALTER TABLE `wallets`
  ADD CONSTRAINT `wallets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD CONSTRAINT `wallet_transactions_ibfk_1` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`wallet_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
