-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 24, 2026 at 12:44 AM
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
(1, 25, 26, 1, 100.00, 'activation', '2026-03-23 23:32:09', 'paid');

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
(101, 'ORD-20260322-80951', 14, 2600.00, 'Pending', '2026-03-22 01:50:45');

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
(188, 101, 3, 17, 2, 1300.00, 2600.00);

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
(1, 17, 'Passion Juice', 'Food & Snacks', 40.00, 56, 'uploads/products/product_699f6e94012145.78607241.webp', '197441253198dfbd6ec2ead44aff3e40', 700, 700, 57, 'webp', 'active', '2026-02-25 21:50:12', '2026-02-25 21:50:12', NULL),
(2, 17, 'Bike', 'Home Items', 13000.00, 12, 'uploads/products/product_699f7d45913b39.70833140.webp', '4688f6d846fc96bdba4db3f75f16d9b3', 700, 519, 55, 'webp', 'active', '2026-02-25 22:52:53', '2026-03-06 19:00:34', NULL),
(3, 17, 'jug', 'Home Items', 1300.00, 42, 'uploads/products/product_699f7dab95bf65.10914891.webp', '902284afaaefe5a5a6f55c09cea05089', 700, 700, 40, 'webp', 'active', '2026-02-25 22:54:35', '2026-03-22 01:50:45', NULL),
(4, 19, 'Passion Juice', 'Food & Snacks', 40.00, 31, 'uploads/products/product_69a017086d9a78.65234946.webp', '57cce22a22b1386a867d231a364f5109', 700, 700, 33, 'webp', 'active', '2026-02-26 09:48:56', '2026-03-08 08:34:49', NULL),
(5, 20, 'Matress', 'Home Items', 4500.00, 3, 'uploads/products/product_69a08decb192e1.62675128.webp', 'e24629049379675f3a1b4309bb699f4a', 700, 527, 27, 'webp', 'active', '2026-02-26 18:16:12', '2026-03-06 19:00:34', NULL),
(6, 15, 'Bicycle', 'Home Items', 4008.00, 6, 'uploads/products/product_69a59b4d0cd4e9.76902524.webp', '15ab1b502eaee9e786e973b38bf2e051', 700, 393, 10, 'webp', 'active', '2026-03-02 14:14:36', '2026-03-22 01:50:17', NULL),
(8, 15, 'HP Elite Book G', 'Home Items', 64948.00, 12, 'uploads/products/product_69af542ed14d19.08555660.webp', '332f4633790b42f07617bd6c21296451', 700, 525, 6, 'webp', 'active', '2026-03-09 23:13:50', '2026-03-22 01:49:39', '00100100001000100010001110000011');

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
(14, 'EMMANUEL WERANGAI', 'buyer_001', 'YnV5ZXJfMDAxQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYzMA==', NULL, NULL, '$2y$10$eZaCoaJd1r6NPqecOo8gX.fhXnk2sCbxyrfRLL7YoGllHWaLmyS5W', 'buyer', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', NULL, NULL, NULL, NULL, '2026-02-25 13:54:11', '2026-02-25 13:54:11', NULL, NULL, NULL, NULL, 0, NULL),
(15, 'EMMANUEL WERANGAI', 'seller_001', 'c2VsbGVyXzAwMUBnbWFpbC5jb20=', 'KzI1NDc3MzAyOTQ0MA==', NULL, NULL, '$2y$10$CqCArt9rG4O278HT4JRwz.OT8yNHssp6D9Wlvg50VHBBnVRrkGobi', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'kisumu ndogo', 'market hub', NULL, 'shop', 'Local', '2026-02-25 14:18:40', '2026-02-25 14:18:40', NULL, NULL, NULL, NULL, 0, NULL),
(16, 'EMMANUEL WERANGAI', 'buyer_002', 'YnV5ZXJfMDAyQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYzMQ==', NULL, NULL, '$2y$10$DVxzrzSTosLmpkLoCos9QeLNGDPAmW3YolY5W6dYMhV7FnTSE2OIW', 'buyer', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Watamu', NULL, NULL, NULL, NULL, '2026-02-25 14:26:12', '2026-03-21 22:09:00', NULL, NULL, NULL, NULL, 0, NULL),
(17, 'EMMANUEL WERANGAI', 'seller_002', 'c2VsbGVyXzAwMkBnbWFpbC5jb20=', 'KzI1NDc1OTU3ODYzMg==', NULL, NULL, '$2y$10$VABFqJUUCuGnx3M5NN6sfuDJ640MPia5z5mofYZv0.niTtM8u6cva', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', 'main canteen', NULL, 'canteen', 'Local', '2026-02-25 14:29:15', '2026-02-25 14:29:15', NULL, NULL, NULL, NULL, 0, NULL),
(18, 'EMMANUEL WERANGAI', 'seller_003', 'c2VsbGVyXzAwM0BnbWFpbC5jb20=', 'KzI1NDc1OTU3ODYzMw==', NULL, NULL, '$2y$10$kdTNspptrvARewNR3/9PQ.0lx4fwWXrF33TPqC7.VRsa2dXMW1sfG', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', 'BETRADES MANAGEMENT', NULL, 'supermarket', 'Local', '2026-02-25 14:31:11', '2026-02-25 14:31:11', NULL, NULL, NULL, NULL, 0, NULL),
(19, 'EMMANUEL WERANGAI', 'seller_004', 'c2VsbGVyXzAwNEBnbWFsaS5jb20=', 'KzI1NDc1OTU3ODYzNQ==', NULL, NULL, '$2y$10$OR16Ab7u7PiToTnqRTGzCeNrlBf4E4Cyr.eAyoGYnLmyN2foUtvK2', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Gede', 'Fred Pharmacy', 'products', 'supermarket', 'local', '2026-02-26 09:26:57', '2026-02-26 09:26:57', NULL, NULL, NULL, NULL, 0, NULL),
(20, 'Antonai Mayende', 'Logan', 'YW50b25haW1heWVuZGVAZ21haWwuY29t', 'KzI1NDc0ODE4NDI2NA==', NULL, NULL, '$2y$10$5DtlnPtdK6QABTq9t.k3hutH2.LCA.IRacCsPkcKcZjFhNznLFpPO', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kibaoni', 'Antonai Mayende', 'products', 'shop', 'local', '2026-02-26 18:02:34', '2026-02-26 18:02:34', NULL, NULL, NULL, NULL, 0, NULL),
(21, 'EMMANUEL TINDI', 'admin_002', 'ZW1tYW51ZWx0aW5kaTIzQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYzNw==', NULL, NULL, '$2y$10$A0zeQcrmvrrk.uP8pSpwFeUfQJ3qFc9bVJentpZ4DEUwvVjb9QMZS', 'administrator', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Naivas', '', '', '', '', '2026-03-04 17:27:25', '2026-03-04 17:27:25', NULL, NULL, NULL, NULL, 0, NULL),
(24, 'Kamau Kasi', 'agent_001', 'YWdlbnRfMDAxQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYzNA==', NULL, '', '$2y$10$8ZA.JMP7dEQmS1/kJbUpqeSy6G46Tk0YOl1zj7gp.R1o9MvQu9pOG', '', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni', 'Kilifi', NULL, NULL, NULL, NULL, '2026-03-22 01:21:33', '2026-03-22 01:22:43', '609A6DCE', NULL, NULL, NULL, 0, 0),
(23, 'EMMANUEL TINDI', 'property_owner_001', 'cHJvcGVydHlfb3duZXJfMDAxQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODY4OQ==', NULL, NULL, '$2y$10$ry31rKkNzYi9LIIpr8Ajp.7BkSj8BbovXXAbEO/iNxTff14fX/Ery', 'property_owner', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Naivas', '', '', '', '', '2026-03-04 17:41:30', '2026-03-21 22:09:22', NULL, NULL, NULL, NULL, 0, NULL),
(25, 'Tina Batka', 'agent_002', 'YWdlbnRfMDAyQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAwMg==', NULL, NULL, '$2y$10$ByjOXYcsBMEsUDPUHESnkuJE8G2msAmZU1rZK99NTiWbo.ivcPbOK', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', NULL, NULL, NULL, NULL, '2026-03-23 20:55:50', '2026-03-23 20:55:50', '5A395D68', NULL, '2026-03-24 02:23:37', NULL, 0, 1),
(26, 'Makethub', 'agent_003', 'YWdlbnRfMDAzQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAwMQ==', NULL, NULL, '$2y$10$fcmFguLtKZ1YmNP.R8xloeXGSNZudeIzv.Dqwvv15zW3cBH.AmtxK', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Naivas', NULL, NULL, NULL, NULL, '2026-03-23 23:30:34', '2026-03-23 23:30:34', 'B0804056', 25, '2026-03-24 02:32:09', NULL, 0, 1);

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
(1, 22, 'sales', 0.00, 0.00, '2026-03-21 22:22:04', '2026-03-21 22:22:04'),
(2, 22, 'agency', 0.00, 0.00, '2026-03-21 22:22:04', '2026-03-21 22:22:04'),
(3, 25, 'sales', 0.00, 0.00, '2026-03-23 23:23:37', '2026-03-23 23:23:37'),
(4, 25, 'agency', 100.00, 100.00, '2026-03-23 23:23:37', '2026-03-23 23:32:09'),
(5, 26, 'sales', 0.00, 0.00, '2026-03-23 23:32:09', '2026-03-23 23:32:09'),
(6, 26, 'agency', 0.00, 0.00, '2026-03-23 23:32:09', '2026-03-23 23:32:09');

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
(1, 4, 100.00, 'completed', 'credit', 'Level 1 commission from agent 26', '26', '2026-03-23 23:32:09', '2026-03-23 23:32:09');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=189;

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
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `user_cart`
--
ALTER TABLE `user_cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `wallet_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
