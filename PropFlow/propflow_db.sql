-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 01, 2026 at 07:20 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `propflow_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `application_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`application_id`, `tenant_id`, `property_id`, `status`, `message`, `created_at`) VALUES
(1, 12, 4, 'pending', '', '2026-02-25 19:28:09'),
(2, 12, 5, 'accepted', '', '2026-02-25 19:35:23'),
(3, 25, 6, 'accepted', '', '2026-02-25 21:08:41'),
(4, 26, 6, 'pending', '', '2026-02-25 21:10:55'),
(5, 28, 6, 'accepted', '', '2026-03-03 11:33:44');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_requests`
--

CREATE TABLE `maintenance_requests` (
  `request_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `issue_category` enum('plumbing','electrical','structural','other') NOT NULL,
  `description` text NOT NULL,
  `landlord_notes` text DEFAULT NULL,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `status` enum('pending','in_progress','resolved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maintenance_requests`
--

INSERT INTO `maintenance_requests` (`request_id`, `tenant_id`, `property_id`, `issue_category`, `description`, `landlord_notes`, `priority`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'plumbing', 'Leaky sink in kitchen', 'Plumber scheduled for tomorrow', 'medium', 'in_progress', '2026-02-02 17:57:36', '2026-02-25 16:30:19'),
(2, 25, 6, 'electrical', 'Meter appears to be damaged. There is no power in the entire apartment', 'An electrician has been scheduled to come to your apartment on the 26th of February', 'high', 'resolved', '2026-02-25 22:01:37', '2026-03-03 11:36:25'),
(3, 28, 6, 'plumbing', 'The sink is leaking', 'A plumber has been scheduled to come to your property in 2 days', 'medium', 'in_progress', '2026-03-03 11:35:51', '2026-03-03 11:36:52');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 7, 'New application for property: 3-Bedroom Townhouse', 0, '2026-02-25 19:28:09'),
(2, 11, 'New application for property: Test Villa', 0, '2026-02-25 19:35:23'),
(3, 12, 'Congratulations! Your application has been accepted. You can now login to pay rent.', 1, '2026-02-25 19:58:05'),
(4, 13, 'New application for property: 2-Bedroom Flat', 0, '2026-02-25 21:08:41'),
(5, 13, 'New application for property: 2-Bedroom Flat', 0, '2026-02-25 21:10:55'),
(6, 25, 'Congratulations! Your application has been accepted. You can now login to pay rent.', 1, '2026-02-25 21:54:15'),
(7, 25, 'Your maintenance request status has been updated to: In Progress. Notes: An electrician has been scheduled to come to your ...', 1, '2026-02-25 22:03:17'),
(8, 13, 'New application for property: 2-Bedroom Flat', 0, '2026-03-03 11:33:44'),
(9, 28, 'Congratulations! Your application has been accepted. You can now login to pay rent.', 1, '2026-03-03 11:34:21'),
(10, 28, 'Your payment of GH₵ 2,400.00 for 2-Bedroom Flat was successful. Thank you!', 1, '2026-03-03 11:35:16'),
(11, 13, 'You received a rent payment of GH₵ 2,400.00 for 2-Bedroom Flat.', 0, '2026-03-03 11:35:16'),
(12, 13, 'New maintenance request (plumbing) submitted for 2-Bedroom Flat.', 0, '2026-03-03 11:35:51'),
(13, 25, 'Your maintenance request status has been updated to: Resolved. Notes: An electrician has been scheduled to come to your ...', 0, '2026-03-03 11:36:25'),
(14, 28, 'Your maintenance request status has been updated to: In Progress. Notes: A plumber has been scheduled to come to your prope...', 1, '2026-03-03 11:36:52');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `tenancy_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('paystack','momo','cash') NOT NULL,
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `transaction_reference` varchar(100) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `tenancy_id`, `amount`, `payment_method`, `payment_status`, `transaction_reference`, `payment_date`) VALUES
(1, 3, 2400.00, 'paystack', 'paid', 'ref_699f706da83c4', '2026-02-25 21:58:06'),
(2, 4, 2400.00, 'paystack', 'paid', 'ref_69a6c76521daf', '2026-03-03 11:35:03');

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `property_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `property_type` enum('house','apartment','single_room') NOT NULL,
  `lease_type` enum('renting','leasing') DEFAULT 'renting',
  `payment_period` enum('monthly','quarterly','bi-annually','yearly') DEFAULT 'monthly',
  `map_location` text DEFAULT NULL,
  `status` enum('available','occupied','archived') DEFAULT 'available',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`property_id`, `owner_id`, `title`, `description`, `address`, `city`, `price`, `property_type`, `lease_type`, `payment_period`, `map_location`, `status`, `latitude`, `longitude`, `created_at`) VALUES
(1, 3, 'Verification Apt', 'A test property for verification', '123 Test St', 'Accra', 1500.00, 'apartment', 'renting', 'monthly', NULL, 'archived', NULL, NULL, '2026-02-02 17:56:06'),
(3, 7, 'Modern 3-Bedroom House', 'Semi-Furnished 3 bedroom house', 'MQ9R+J7Q Haatso', 'Accra', 40000.00, 'house', 'leasing', 'bi-annually', 'https://www.google.com/maps/embed?pb=!1m17!1m12!1m3!1d3970.304190174188!2d-0.20927779999999999!3d5.6690833!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m2!1m1!2zNcKwNDAnMDguNyJOIDDCsDEyJzMzLjQiVw!5e0!3m2!1sen!2sgh!4v1771779227165!5m2!1sen!2sgh', 'available', NULL, NULL, '2026-02-22 16:54:10'),
(4, 7, '3-Bedroom Townhouse', '3 bedroom townhome, semi furnished with 2.5 baths, 1 guest room, kitchen, office and security detail', 'MQWX+8JQ Agbogba', 'Accra', 30000.00, 'house', 'renting', 'bi-annually', 'https://www.google.com/maps/embed?pb=!1m17!1m12!1m3!1d3970.1197347627217!2d-0.2034720250137941!3d5.695835494285921!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m2!1m1!2zNcKwNDEnNDUuMCJOIDDCsDEyJzAzLjIiVw!5e0!3m2!1sen!2sgh!4v1771873046200!5m2!1sen!2sgh', 'available', NULL, NULL, '2026-02-23 18:58:19'),
(5, 11, 'Test Villa', 'A nice test villa', '123 Test St', 'Test City', 500.00, 'apartment', 'renting', 'monthly', '', 'archived', NULL, NULL, '2026-02-25 19:32:06'),
(6, 13, '2-Bedroom Flat', 'Non-furnished 2-Bedroom Flat', 'Greater Accra Region, Adentan Municipal, Adenta East', 'Accra', 2400.00, 'apartment', 'renting', 'quarterly', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3970.0320615049!2d-0.15909422467040876!3d5.708507232096602!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xfdf9dc105467b3f%3A0x1685e6ba8f5e4891!2sBlock%2085%2C%20Adenta%20SSNIT%20FLATS!5e0!3m2!1sen!2sgh!4v1772051311371!5m2!1sen!2sgh', 'occupied', NULL, NULL, '2026-02-25 20:28:42'),
(7, 27, 'Demo Apartment', 'A lovely demo apartment.', '123 Demo St123 Demo St', 'Demo CityDemo City', 2000.00, 'apartment', 'renting', 'monthly', '', 'archived', NULL, NULL, '2026-03-01 14:17:48');

-- --------------------------------------------------------

--
-- Table structure for table `property_images`
--

CREATE TABLE `property_images` (
  `image_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_images`
--

INSERT INTO `property_images` (`image_id`, `property_id`, `image_path`, `uploaded_at`) VALUES
(1, 3, '699b34b2126150.86279828.png', '2026-02-22 16:54:10'),
(2, 3, '699b34b2139289.28233403.png', '2026-02-22 16:54:10'),
(3, 3, '699b34b21400d8.00773906.png', '2026-02-22 16:54:10'),
(4, 3, '699b34b214b180.83096189.png', '2026-02-22 16:54:10'),
(5, 3, '699b34b2150fe5.79596806.png', '2026-02-22 16:54:10'),
(6, 4, '699ca34b80fae1.50763677.png', '2026-02-23 18:58:19'),
(7, 4, '699ca34b820228.93103586.png', '2026-02-23 18:58:19'),
(8, 4, '699ca34b8258a5.42996296.png', '2026-02-23 18:58:19'),
(9, 4, '699ca34b82c016.20780174.png', '2026-02-23 18:58:19'),
(10, 4, '699ca34b82df24.02909704.png', '2026-02-23 18:58:19'),
(11, 6, '699f5b7acd0494.59101950.png', '2026-02-25 20:28:42');

-- --------------------------------------------------------

--
-- Table structure for table `tenancies`
--

CREATE TABLE `tenancies` (
  `tenancy_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `rent_amount` decimal(10,2) NOT NULL,
  `payment_frequency` enum('monthly','quarterly','bi-annually','yearly') NOT NULL,
  `status` enum('active','ended') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenancies`
--

INSERT INTO `tenancies` (`tenancy_id`, `tenant_id`, `property_id`, `start_date`, `end_date`, `rent_amount`, `payment_frequency`, `status`) VALUES
(1, 2, 1, '2026-02-02', '2027-02-02', 1500.00, 'monthly', 'active'),
(2, 12, 5, '2026-03-01', NULL, 500.00, 'monthly', 'active'),
(3, 25, 6, '2026-02-28', NULL, 2400.00, 'quarterly', 'active'),
(4, 28, 6, '2026-03-10', NULL, 2400.00, 'quarterly', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('landlord','tenant','admin') NOT NULL,
  `status` enum('active','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `phone`, `password_hash`, `role`, `status`, `created_at`) VALUES
(1, 'Hassan Yakubu', 'yhassan677@gmail.com', '0204200934', '$2y$10$EArqHL/0cwyqdLSYQm8eUeJWd2vINUjEEdppfqQxH7lPrxVbV1JNO', 'admin', 'active', '2026-02-02 16:51:03'),
(2, 'Demo Tenant', 'tenant_id2@test.com', '0500000001', '$2y$10$dMWszV/kLGorP0TxGUFy4eH7QWl2RcyamoIPTFlvJ/x1vK3C3dlwy', 'tenant', 'active', '2026-02-02 16:57:20'),
(3, 'Devin Booker', 'dbook@gmail.com', '0011223344', '$2y$10$dMWszV/kLGorP0TxGUFy4eH7QWl2RcyamoIPTFlvJ/x1vK3C3dlwy', 'landlord', 'active', '2026-02-02 16:57:39'),
(4, 'Demo Landlord', 'demo.landlord.0101@example.com', '0555555555', '$2y$10$X9v5.FaWaElLwQh7UXyyvuIbk0mqFTuBqzSOlQ4E6sPB0LC82aDHK', 'landlord', 'active', '2026-02-10 15:26:04'),
(5, 'Demo Tenant', 'demo.tenant.0101@example.com', '0244444444', '$2y$10$X9v5.FaWaElLwQh7UXyyvuIbk0mqFTuBqzSOlQ4E6sPB0LC82aDHK', 'tenant', 'active', '2026-02-10 15:26:49'),
(6, 'Tomo Do', 'tdomo@y.com', '027826781', '$2y$10$X9v5.FaWaElLwQh7UXyyvuIbk0mqFTuBqzSOlQ4E6sPB0LC82aDHK', 'landlord', 'active', '2026-02-21 19:50:37'),
(7, 'El Virtues Construction Ltd.', 'elvirtues@gmail.com', '0208464036', '$2y$10$wpqWAykd8wz1rcibIvjwkOKiyn7N9SiYUFKtQfk0/5IC2xetbmLYK', 'landlord', 'active', '2026-02-22 15:59:33'),
(8, 'Bruno Fernandes', 'bruno@gmail.com', '0200584869', '$2y$10$RxfzDKs72Aj9vedU28bBLu6BVT3MVUwOqvkmhG/4TDfardDZFtu3O', 'tenant', 'active', '2026-02-22 17:33:23'),
(9, 'Landlord PropFlow', 'landlord@propflow.com', '0123456789', '$2y$10$k4lPHDgCb73gSqAe81Lseu/bn9TV7j29TvwqdKbcu5bs5ZJx9gQkq', 'landlord', 'active', '2026-02-22 19:03:22'),
(10, 'Landlord Kofi', 'kofi_test@example.com', '0244123456', '$2y$10$eJkzrvo0EnU6OBrxygkxXujo/Nz03AK5bsCCIc6un9ADKiaU711r6', 'landlord', 'active', '2026-02-25 18:13:00'),
(11, 'Kofi', 'kofi@example.com', '0244112233', '$2y$10$vJYgwBNus.lQtciClSxnRur3B2I0NNEqQiGdHZ3KlQoesp60hsoZm', 'landlord', 'active', '2026-02-25 19:23:46'),
(12, 'Ama', 'ama@example.com', '0244556677', '$2y$10$4WI3iU5OiAdN5WsNjxOqZuQzN5W6nwpEQHYQvyL0qeknzpy5bAXcS', 'tenant', 'active', '2026-02-25 19:25:50'),
(13, 'SSNIT Flat', 'ssnit@gmail.com', '0556437659', '$2y$10$CyB1fHnYH8IZt97G/O6pROrm/2DIDPv4WdwGKqt6JWh/PwwpA2NB.', 'landlord', 'active', '2026-02-25 20:19:41'),
(15, 'Test', 'test1772051551@example.com', '123', 'hash', 'tenant', 'active', '2026-02-25 20:32:31'),
(16, 'Test', 'test1772051586b@example.com', '123', 'hash', 'tenant', 'active', '2026-02-25 20:33:06'),
(17, 'Test User', 'bugtest@example.com', '1234567890', '$2y$10$dccITccEoQqXy7J4P49.2.HPiIncOSNPh.WoWoo5aKt7gJfhByzSO', 'tenant', 'active', '2026-02-25 20:34:58'),
(18, 'Fresh User', 'freshuser@example.com', '1234567890', '$2y$10$3uGiX5hSgBLd4RRwpv0WHu0Rg7bpmWHVz4A7SPq1ozpaHPahmZNTu', 'tenant', 'active', '2026-02-25 20:39:08'),
(19, 'Curl Test', 'curlest1772052114@example.com', '123', '$2y$10$gW4UiheA.rc.Ol389EGMweG9wn1J0qC7c2j/TNDdSKbDrLGSr3z8W', 'tenant', 'active', '2026-02-25 20:41:54'),
(20, 'Curl Test', 'curlest1772052124@example.com', '123', '$2y$10$MUVTIjCET5qZsTPmSRj6/OM86GZ.MPM//Me0Fzk0brxHJwTJ7KLjO', 'tenant', 'active', '2026-02-25 20:42:04'),
(21, 'Curl Test', 'curlest1772052138z@example.com', '123', '$2y$10$jgX6Qbe9vibIeFYfST7GZOchbwXM.GP1LQjggX5YgBRAmhdAvD872', 'tenant', 'active', '2026-02-25 20:42:18'),
(22, 'Curl Test', 'curlest1772052173y@example.com', '123', '$2y$10$voTIFEc09qD1h1FSs8MqCObmbFvDa0xU55BHAEM/1QuSlunqvqL.6', 'tenant', 'active', '2026-02-25 20:42:53'),
(23, 'Curl Test', 'curlest1772052189x@example.com', '123', '$2y$10$iQuuBUmMGJdhd35JM0SX/OOQ7JDTGJVjjleVYJ5sL9YVEqgdPUG6K', 'tenant', 'active', '2026-02-25 20:43:09'),
(24, 'Test', 'test1772052451b@example.com', '123', 'hash', 'tenant', 'active', '2026-02-25 20:47:31'),
(25, 'Reginald Ofori', 'reginald@gmail.com', '0202009876', '$2y$10$JATNUznwKXBCr55K9d/8b.gnu.bQxxR0FZzRgw1NaaFb6iqwgByGu', 'tenant', 'active', '2026-02-25 21:04:47'),
(26, 'Senam', 'senam@gmail.com', '0504339812', '$2y$10$TcwohOVh1B1MEDupoaOpcOalV/5DhaptS6f1TPY2SNSvhLuNBXpKq', 'tenant', 'active', '2026-02-25 21:10:28'),
(27, 'Demo Landlord', 'demolandlord@example.com', '1234567890', '$2y$10$KPamLu7XYTF8nUCnFS5xzeArGG03SujIa7hGddSWrsKshb4XdoF9W', 'landlord', 'active', '2026-03-01 14:01:19'),
(28, 'Kwame', 'kwame@gmail.com', '0204200934', '$2y$10$w.p9cmkLw7Tmq1h6gedoduBgxD/xhaN2D3fria/CqtEj2WQ96ydO2', 'tenant', 'active', '2026-03-03 11:33:07'),
(29, 'Landlord Demo', 'landlord_demo@test.com', '0123456789', '$2y$10$Llmkt3Zy1pNrF/OSUUat8.JMjA1u7Q0w9dGajTAWM6lrAQemR8I.i', 'landlord', 'active', '2026-03-14 13:28:21'),
(30, 'Tenant Demo', 'tenant_demo@test.com', '0987654321', '$2y$10$LFQLMobiJC05yeQUjzC2beFyra9hh8WW5LexwTTDEm7Oe0mPUWUxu', 'tenant', 'active', '2026-03-14 13:32:27'),
(31, 'Landlord New Account', 'landlord_new@test.com', '1112223333', '$2y$10$oRainQC7bzlG5rjJ5DfRH.YS2hGEFXqVwrWWDCm0AqXUt6yuKsbBK', 'landlord', 'active', '2026-03-14 22:31:08'),
(32, 'Landlord New', 'landlord_demo_new@test.com', '0123456789', '$2y$10$KqeJ27C5zxwJm4ddEjHyvevw7qK4gaH4VF4x3EMxKbpLr1/1PimQG', 'landlord', 'active', '2026-03-14 23:03:04'),
(33, 'Tenant User', 'tenant@example.com', '0244444444', '$2y$10$pkS282o/ijln7SrMoVSF4ePvFUmV1I/fMFHUHi0AnXmYFbzmShyDe', 'tenant', 'active', '2026-03-14 23:17:22'),
(34, 'John Doe', 'john@demo.com', '1234567890', '$2y$10$JIdDiUFnwJhI0GVClX8TeO.dDZgnS3h3SXj5Km3N.2g4lJECMkLi.', 'tenant', 'active', '2026-04-01 15:07:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `tenancy_id` (`tenancy_id`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`property_id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `property_images`
--
ALTER TABLE `property_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `tenancies`
--
ALTER TABLE `tenancies`
  ADD PRIMARY KEY (`tenancy_id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `property_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `property_images`
--
ALTER TABLE `property_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tenancies`
--
ALTER TABLE `tenancies`
  MODIFY `tenancy_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`property_id`) ON DELETE CASCADE;

--
-- Constraints for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD CONSTRAINT `maintenance_requests_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `maintenance_requests_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`property_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`tenancy_id`) REFERENCES `tenancies` (`tenancy_id`) ON DELETE CASCADE;

--
-- Constraints for table `properties`
--
ALTER TABLE `properties`
  ADD CONSTRAINT `properties_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `property_images`
--
ALTER TABLE `property_images`
  ADD CONSTRAINT `property_images_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`property_id`) ON DELETE CASCADE;

--
-- Constraints for table `tenancies`
--
ALTER TABLE `tenancies`
  ADD CONSTRAINT `tenancies_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tenancies_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`property_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
