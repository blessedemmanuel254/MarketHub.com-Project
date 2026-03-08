-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 08, 2026 at 02:27 PM
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
  `agent_activated_at` datetime DEFAULT NULL
) ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `username`, `email`, `phone`, `profile_image`, `bio`, `password`, `account_type`, `is_verified`, `status`, `country`, `county`, `ward`, `address`, `business_name`, `business_model`, `business_type`, `market_scope`, `created_at`, `updated_at`, `agency_code`, `referred_by`, `agent_activated_at`) VALUES
(14, 'EMMANUEL WERANGAI', 'buyer_001', 'YnV5ZXJfMDAxQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYzMA==', NULL, '', '$2y$10$eZaCoaJd1r6NPqecOo8gX.fhXnk2sCbxyrfRLL7YoGllHWaLmyS5W', 'buyer', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', NULL, NULL, NULL, NULL, '2026-02-25 13:54:11', '2026-03-03 06:00:55', NULL, NULL, NULL),
(15, 'EMMANUEL WERANGAI', 'seller_001', 'c2VsbGVyXzAwMUBnbWFpbC5jb20=', 'KzI1NDc3MzAyOTQ0MA==', NULL, 'I am here', '$2y$10$CqCArt9rG4O278HT4JRwz.OT8yNHssp6D9Wlvg50VHBBnVRrkGobi', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni', 'kisumu ndogo', 'market hub', NULL, 'shop', 'Local', '2026-02-25 14:18:40', '2026-03-04 06:45:20', NULL, NULL, NULL),
(16, 'EMMANUEL WERANGAI', 'buyer_002', 'YnV5ZXJfMDAyQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYzMQ==', NULL, NULL, '$2y$10$DVxzrzSTosLmpkLoCos9QeLNGDPAmW3YolY5W6dYMhV7FnTSE2OIW', 'buyer', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Watamu', NULL, NULL, NULL, NULL, '2026-02-25 14:26:12', '2026-02-25 14:26:12', NULL, NULL, NULL),
(17, 'EMMANUEL WERANGAI', 'seller_002', 'c2VsbGVyXzAwMkBnbWFpbC5jb20=', 'KzI1NDc1OTU3ODYzMg==', NULL, NULL, '$2y$10$VABFqJUUCuGnx3M5NN6sfuDJ640MPia5z5mofYZv0.niTtM8u6cva', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', 'main canteen', NULL, 'canteen', 'Local', '2026-02-25 14:29:15', '2026-02-25 14:29:15', NULL, NULL, NULL),
(18, 'EMMANUEL WERANGAI', 'seller_003', 'c2VsbGVyXzAwM0BnbWFpbC5jb20=', 'KzI1NDc1OTU3ODYzMw==', NULL, NULL, '$2y$10$kdTNspptrvARewNR3/9PQ.0lx4fwWXrF33TPqC7.VRsa2dXMW1sfG', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', 'BETRADES MANAGEMENT', NULL, 'supermarket', 'Local', '2026-02-25 14:31:11', '2026-02-25 14:31:11', NULL, NULL, NULL),
(19, 'EMMANUEL WERANGAI', 'seller_004', 'c2VsbGVyXzAwNEBnbWFsaS5jb20=', 'KzI1NDc1OTU3ODYzNQ==', NULL, NULL, '$2y$10$OR16Ab7u7PiToTnqRTGzCeNrlBf4E4Cyr.eAyoGYnLmyN2foUtvK2', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Gede', 'Fred Pharmacy', 'products', 'supermarket', 'local', '2026-02-26 09:26:57', '2026-02-26 09:26:57', NULL, NULL, NULL),
(20, 'EMMANUEL TINDI', 'admin_001', 'ZW1tYW51ZWx0aW5kaTIzQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYzOQ==', NULL, NULL, '$2y$10$QE2Yt9Dg465QGVMZVj4ds.RW8dvGVv0Kh9cCL2jzbbPN0cLs/i/pu', 'buyer', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Naivas', NULL, NULL, NULL, NULL, '2026-03-04 16:23:46', '2026-03-04 16:23:46', NULL, NULL, NULL),
(21, 'EMMANUEL WERANGAI', 'admin_002', 'ZW1tYW51ZWx0aW5kaTIyQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYzNw==', NULL, NULL, '$2y$10$wXvvXl3huLmsyfaBVngweudcSiAv2g2btyvkJqILmsqJBMcTtdsMi', 'administrator', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-04 16:51:33', '2026-03-04 16:51:33', NULL, NULL, NULL),
(22, 'Adyline Cherono', 'seller_005', 'c2VsbGVyXzAwNUBnbWFpbC5jb20=', 'KzI1NDcwODY3MDM5Ng==', NULL, NULL, '$2y$10$jduCXGurRvzRC20WfG2N9ehYyB6RJlk6SnROkJ5HlNAmRqz6JveQm', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Mlika mwizi', 'mama adrian shop', 'products', 'canteen', 'local', '2026-03-04 18:44:28', '2026-03-04 18:44:28', NULL, NULL, NULL),
(23, 'EMMANUEL WERANGAI', 'blessedemmanuel258', 'ZW1tYW51ZWx0aW5kaTI4QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYzOA==', NULL, NULL, '$2y$10$Q4EqtpdY0nlhULKLoI3wrO.PnjzwpYjniSTi.T468AUSCnq.YfWOq', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 19:44:41', '2026-03-05 19:44:41', '05C0FAFF', NULL, NULL),
(24, 'EMMANUEL WERANGAI', 'blessedemmanuel259', 'ZW1tYW51ZWx0aW5kaTI5QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODg3Ng==', NULL, NULL, '$2y$10$s7xcA2eUS3J/XjtapX66.ew.juyyse1sQA/LsLxxHSGOSmi8LglDi', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 19:49:12', '2026-03-05 19:49:12', '4BD0D5A8', NULL, NULL),
(25, 'EMMANUEL WERANGAI', 'blessedemmanuel251', 'ZW1tYW51ZWx0aW5kaTIxQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODM0NQ==', NULL, NULL, '$2y$10$pEQu.sJshyZpbPPQm7oc7.T.oApDK0iDjaubSNy1zHyDtGBnDqZAW', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 19:50:16', '2026-03-05 19:50:16', 'E5EE3E04', 24, NULL),
(26, 'Blessed Emmanuel', 'agent_001', 'YWdlbnRfMDAxQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYyMw==', NULL, NULL, '$2y$10$QttwBhgfEqpkerZFR2Xqbe8QjWx8bB8U.xCbOJ07OID7uflfhrQFa', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 19:54:24', '2026-03-05 19:54:24', 'DBE25C71', 24, NULL),
(27, 'Blessed Emmanuel', 'agent_002', 'YWdlbnRfMDAyQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYyOQ==', NULL, NULL, '$2y$10$88kTe0mRkp6XNtzm.9yf0uqyTot.53Q1QzzVwqsf8XpHuD/AIGS7e', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 19:56:07', '2026-03-05 19:56:07', '574B94B3', 24, NULL),
(28, 'EMMANUEL WERANGAI', 'agent_003', 'YWdlbnRfMDAzQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODY4Nw==', NULL, NULL, '$2y$10$N4//.tzoTnFxqdfmuFTNa.4wJJLzs0JaCgLPnAunUwtwsqFspTmOa', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 19:58:27', '2026-03-05 19:58:27', 'A6C50F36', 24, NULL),
(29, 'EMMANUEL WERANGAI', 'agent_004', 'YWdlbnRfMDA0QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODY0Mw==', NULL, NULL, '$2y$10$EOtCwhrV/jCQYYHil/Y45uLUB1OKVV6QZ6CNa8eAbusI6P2Zwz.OW', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:07:16', '2026-03-05 20:07:16', '7AAFF72B', NULL, NULL),
(30, 'EMMANUEL WERANGAI', 'agent_005', 'YWdlbnRfMDA1QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODY0NQ==', NULL, NULL, '$2y$10$VxcIev3D1cupiDEDkKFMr.6xmRqWC9mJgr5h416d7MqF.9.Qbhs9G', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:08:21', '2026-03-05 20:08:21', 'F4700C3E', NULL, NULL),
(31, 'EMMANUEL WERANGAI', 'agent_006', 'YWdlbnRfMDA2QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAwNQ==', NULL, NULL, '$2y$10$.90aYKj0PyiM5r0Vj.yG5uIzZ9sKJKz.53KunC6mxE/8.SCzAbpbe', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:16:49', '2026-03-05 20:16:49', '1CEE060F', NULL, NULL),
(32, 'EMMANUEL WERANGAI', 'agent_007', 'YWdlbnRfMDA3QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAwNw==', NULL, NULL, '$2y$10$cFidq5ds8kXff9okR4JL7.AWG4M9tInTB1JqKQ8zlBLFJMQaD3.pO', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:19:07', '2026-03-05 20:19:07', '110A64B4', NULL, NULL),
(33, 'EMMANUEL WERANGAI', 'agent_008', 'YWdlbnRfMDA4QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAwOA==', NULL, NULL, '$2y$10$loS7O2tNUZ0uVnBtRt0UUuvY9W2my.zWPM7bedcykvvDwY9THkrWi', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:22:09', '2026-03-05 20:22:09', '13B8E3CD', 28, NULL),
(34, 'EMMANUEL WERANGAI', 'agent_009', 'YWdlbnRfMDA5QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAwOQ==', NULL, NULL, '$2y$10$S7O0QIC/hsVFUb.aLtO39em578pKcbDwbUktgazsLW084HFdRP/zu', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:24:02', '2026-03-05 20:24:02', '1FFE9D40', NULL, NULL),
(35, 'EMMANUEL WERANGAI', 'agent_010', 'YWdlbnRfMDEwQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxMA==', NULL, NULL, '$2y$10$zPwTRP899ZKT7YtweOmVHuTRrmZrxGBwjpzvaJGYBk4TK4kntymua', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:32:10', '2026-03-05 20:32:10', '503AFDE4', 28, NULL),
(36, 'EMMANUEL WERANGAI', 'agent_011', 'YWdlbnRfMDExQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxMQ==', NULL, NULL, '$2y$10$ojtPgyAyEwbx.V4MSxarLOEHlVaYtRt0XrQumm/CbB3RV0GKm1s3O', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:35:18', '2026-03-05 20:35:18', '2556FDBD', 27, NULL),
(37, 'Kisembi Hyalo', 'agent_012', 'YWdlbnRfMDEyQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxMg==', NULL, NULL, '$2y$10$Dlveo6Ny1.KwZKOdpCqvCeVEJZa6jm55DnGigHUxRsocRrO1yasse', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:42:09', '2026-03-05 20:42:09', '3CCFB557', NULL, NULL),
(38, 'EMMANUEL WERANGAI', 'agent_013', 'YWdlbnRfMDEzQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxMw==', NULL, NULL, '$2y$10$bG8ZaUPS8Tkx9gGGzc99H.BDm242/yP5282ZKxlvWBsbBGudjg5dG', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:50:18', '2026-03-05 20:50:18', '66CCD3B4', 23, NULL),
(39, 'DASHCAM BUNDI', 'agent_014', 'YWdlbnRfMDE0QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxNA==', NULL, NULL, '$2y$10$OmMKXPH4EfiygWGP3etlJeMKHl3dt8Nk1J1IAPb.fJxEiVivm8QNq', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:52:12', '2026-03-05 20:52:12', 'DB375B5B', 26, NULL),
(40, 'EMMANUEL WERANGAI', 'agent_015', 'YWdlbnRfMDE1QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxNQ==', NULL, NULL, '$2y$10$LBlcMBAkDRgpX5KjpwM46evzOKroLGUVAIUibrwYGkNCbyf51zrU2', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:55:09', '2026-03-05 20:55:09', '4FA2A924', 39, NULL),
(41, 'EMMANUEL WERANGAI', 'agent_016', 'YWdlbnRfMDE2QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxNg==', NULL, NULL, '$2y$10$HIsc/sxh0aQBv8Ob/QKp1u3vmnV9th/9ZAQ8o6M7eH6A/jB3yNGG6', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 20:58:30', '2026-03-05 20:58:30', 'E1B02841', 40, NULL),
(42, 'EMMANUEL WERANGAI', 'agent_017', 'YWdlbnRfMDE3QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxNw==', NULL, NULL, '$2y$10$Kzq5SpMxd7bCnAymotAweu43VcUrOEdLNbSKBKpTo7fPsoiH2q6Sa', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 21:01:07', '2026-03-05 21:01:07', 'D8E51616', 41, NULL),
(43, 'EMMANUEL WERANGAI', 'agent_018', 'YWdlbnRfMDE4QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxOA==', NULL, NULL, '$2y$10$hCienXkHmtf2nev63yPm8.TOq3iJ3r1xfReTAX0d/9l7U3CPPXRcy', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 21:04:05', '2026-03-05 21:04:05', '79B3A9CC', 27, NULL),
(44, 'EMMANUEL WERANGAI', 'agent_19', 'YWdlbnRfMTlAZ21haWwuY29t', 'KzI1NDc1OTU3ODAxOQ==', NULL, NULL, '$2y$10$ePgT/PAUzpcukoOrGArbYOTjw8p0u1GYr0VFSqCl2OCMrxpdugNkS', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-05 21:25:58', '2026-03-05 21:25:58', '2CC06969', 43, NULL),
(45, 'EMMANUEL WERANGAI', 'agent_20', 'YWdlbnRfMjBAZ21haWwuY29t', 'KzI1NDc1OTU3ODYyMA==', NULL, NULL, '$2y$10$EhJt6Fc6hj0v9Q9gMhqjB.o5RPUahtRp.A0O.NVRzf6sgr.a0r4fS', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-07 15:16:19', '2026-03-07 15:16:19', 'AC06AE5F', 24, NULL),
(46, 'EMMANUEL WERANGAI', 'buyer_007', 'YnV5ZXJfMDA3QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODcwMA==', NULL, NULL, '$2y$10$P0pEi8er/MHJjMoSFPC4h.9zMW0cEDEDY5x6whDU89S6WufGI.Mfe', 'buyer', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', NULL, NULL, NULL, NULL, '2026-03-07 22:35:44', '2026-03-07 22:35:44', NULL, NULL, NULL),
(47, 'EMMANUEL WERANGAI', 'seller_700', 'c2VsbGVyXzcwMEBnbWFpbC5pY29t', 'KzI1NDc1OTU3ODkwMA==', NULL, NULL, '$2y$10$V1EwRZfZr6UpLnrnu/Rdju0YQr7TDM0cfWKp/bhVfzkWoUEAgKyCC', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', 'BETRADES MANAGEMENT', 'products', 'service_provider', 'local', '2026-03-07 22:38:40', '2026-03-07 22:38:40', NULL, NULL, NULL),
(48, 'EMMANUEL WERANGAI', 'agent_021', 'YWdlbnRfMDIxQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYyMQ==', NULL, NULL, '$2y$10$AVTZ6iumLpvWaXkyJS9fP.7v8mfJCgRfZ1vy47neuSffWtmUUokHy', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-07 22:40:45', '2026-03-07 22:40:45', 'DCFD122D', NULL, NULL);

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
