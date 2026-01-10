-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 10, 2026 at 10:43 AM
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
-- Database: `sanipoint_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` varchar(36) NOT NULL,
  `user_id` varchar(36) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` varchar(36) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bathrooms`
--

CREATE TABLE `bathrooms` (
  `id` varchar(36) NOT NULL,
  `name` varchar(100) NOT NULL,
  `location` varchar(100) NOT NULL,
  `max_visitors` int(11) DEFAULT 10,
  `current_visitors` int(11) DEFAULT 0,
  `status` enum('available','needs_cleaning','being_cleaned','maintenance') DEFAULT 'available',
  `last_cleaned` timestamp NULL DEFAULT NULL,
  `last_cleaned_by` varchar(36) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bathrooms`
--

INSERT INTO `bathrooms` (`id`, `name`, `location`, `max_visitors`, `current_visitors`, `status`, `last_cleaned`, `last_cleaned_by`, `is_active`, `created_at`, `updated_at`) VALUES
('toilet-1-uuid-0000000000000000000000', 'Toilet 1', 'Lantai 1 - Kiri', 5, 0, 'available', NULL, NULL, 1, '2025-12-29 15:51:22', '2025-12-29 17:25:13'),
('toilet-2-uuid-0000000000000000000000', 'Toilet 2', 'Lantai 1 - Kanan', 5, 0, 'available', NULL, NULL, 1, '2025-12-29 15:51:22', '2025-12-29 15:51:22');

-- --------------------------------------------------------

--
-- Table structure for table `cleaning_logs`
--

CREATE TABLE `cleaning_logs` (
  `id` varchar(36) NOT NULL,
  `bathroom_id` varchar(36) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `start_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `end_time` timestamp NULL DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `points_earned` int(11) DEFAULT 0,
  `status` enum('in_progress','completed','cancelled') DEFAULT 'in_progress',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` varchar(36) NOT NULL,
  `user_id` varchar(36) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','warning','success','error') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` varchar(36) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `total_points` int(11) NOT NULL,
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  `received_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `total_points`, `status`, `received_at`, `cancelled_at`, `qr_code`, `notes`, `created_at`, `updated_at`) VALUES
('0ae67d31-2cc1-41b4-9637-f5f01ada102d', 'cd13f47a-7918-4e74-8d94-9b4697de3e65', 'ORD202512292994', 3, 'completed', NULL, NULL, NULL, NULL, '2025-12-28 19:28:27', '2025-12-28 19:28:27'),
('1fd981ee-093d-4d51-b41e-0f89a69ec6da', 'cd13f47a-7918-4e74-8d94-9b4697de3e65', 'ORD202512293060', 3, 'completed', NULL, NULL, NULL, NULL, '2025-12-28 19:19:35', '2025-12-28 19:19:35'),
('566fda30-ffd1-450c-a893-e4713f0fa23f', 'cd13f47a-7918-4e74-8d94-9b4697de3e65', 'ORD202512296209', 8, 'completed', NULL, NULL, NULL, NULL, '2025-12-28 18:49:26', '2025-12-28 18:49:26'),
('87080f05-6048-4fb8-aa19-9a3e61b3286c', 'cd13f47a-7918-4e74-8d94-9b4697de3e65', 'ORD202512290261', 3, '', '2025-12-28 19:41:48', NULL, NULL, NULL, '2025-12-28 19:34:39', '2025-12-28 19:41:48'),
('f6316d08-70de-489c-8998-8acdfeb69c44', 'cd13f47a-7918-4e74-8d94-9b4697de3e65', 'ORD202512294466', 3, '', '2025-12-28 20:34:57', NULL, NULL, NULL, '2025-12-28 19:29:41', '2025-12-28 20:34:57');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` varchar(36) NOT NULL,
  `order_id` varchar(36) NOT NULL,
  `product_id` varchar(36) NOT NULL,
  `quantity` int(11) NOT NULL,
  `points_per_item` int(11) NOT NULL,
  `total_points` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `points_per_item`, `total_points`) VALUES
('1bc36c02-ddb1-44dd-86a6-d8e86acfbebc', '87080f05-6048-4fb8-aa19-9a3e61b3286c', '550e8400-e29b-41d4-a716-446655440032', 1, 3, 3),
('56149b73-7223-4925-8567-3c78e35c92c3', '566fda30-ffd1-450c-a893-e4713f0fa23f', '550e8400-e29b-41d4-a716-446655440031', 1, 8, 8),
('5a7aa222-030d-48eb-816c-4c518c951a80', '0ae67d31-2cc1-41b4-9637-f5f01ada102d', '550e8400-e29b-41d4-a716-446655440032', 1, 3, 3),
('6f86ed9c-79dd-4412-96b7-fe470f7c518e', 'f6316d08-70de-489c-8998-8acdfeb69c44', '550e8400-e29b-41d4-a716-446655440032', 1, 3, 3),
('cc894a6e-86e4-41e3-a2cb-199791fa94a0', '1fd981ee-093d-4d51-b41e-0f89a69ec6da', '550e8400-e29b-41d4-a716-446655440032', 1, 3, 3);

-- --------------------------------------------------------

--
-- Table structure for table `points`
--

CREATE TABLE `points` (
  `id` varchar(36) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `current_balance` int(11) DEFAULT 0,
  `total_earned` int(11) DEFAULT 0,
  `total_spent` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `points`
--

INSERT INTO `points` (`id`, `user_id`, `current_balance`, `total_earned`, `total_spent`, `updated_at`) VALUES
('19a62b0a-c143-4b72-96cf-784a89db807f', 'cd13f47a-7918-4e74-8d94-9b4697de3e65', 10, 30, 20, '2025-12-28 19:34:39'),
('2d40f6da-5096-42fb-882b-3bce1168b416', '40bf7577-4e10-4ecd-97ac-e662f2381bae', 0, 0, 0, '2025-12-28 17:49:04'),
('550e8400-e29b-41d4-a716-446655440040', '550e8400-e29b-41d4-a716-446655440001', 20, 50, 30, '2025-12-28 19:06:41');

-- --------------------------------------------------------

--
-- Table structure for table `point_transactions`
--

CREATE TABLE `point_transactions` (
  `id` varchar(36) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `transaction_type` enum('earned','spent','transfer_in','transfer_out') NOT NULL,
  `amount` int(11) NOT NULL,
  `balance_after` int(11) NOT NULL,
  `reference_type` enum('cleaning','purchase','transfer') NOT NULL,
  `reference_id` varchar(36) DEFAULT NULL,
  `from_user_id` varchar(36) DEFAULT NULL,
  `to_user_id` varchar(36) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `point_transactions`
--

INSERT INTO `point_transactions` (`id`, `user_id`, `transaction_type`, `amount`, `balance_after`, `reference_type`, `reference_id`, `from_user_id`, `to_user_id`, `description`, `created_at`) VALUES
('0354f0ff-c4c7-48f4-9266-9bdbcec12293', '550e8400-e29b-41d4-a716-446655440001', 'transfer_out', 10, 40, 'transfer', NULL, NULL, 'cd13f47a-7918-4e74-8d94-9b4697de3e65', '', '2025-12-28 18:07:19'),
('3b39f63c-c490-49a4-9b91-34355f3a21d0', 'cd13f47a-7918-4e74-8d94-9b4697de3e65', 'spent', 3, 19, 'purchase', '1fd981ee-093d-4d51-b41e-0f89a69ec6da', NULL, NULL, 'Order: ORD202512293060', '2025-12-28 19:19:35'),
('510dec0a-cb09-4228-a2a5-c0477a800e38', 'cd13f47a-7918-4e74-8d94-9b4697de3e65', 'spent', 8, 2, 'purchase', '566fda30-ffd1-450c-a893-e4713f0fa23f', NULL, NULL, 'Order: ORD202512296209', '2025-12-28 18:49:26'),
('636db1f9-59b8-443d-b452-a052529171fc', '550e8400-e29b-41d4-a716-446655440001', 'transfer_out', 20, 20, 'transfer', NULL, NULL, 'cd13f47a-7918-4e74-8d94-9b4697de3e65', '', '2025-12-28 19:06:41'),
('be59bd2e-ec3f-4465-8f3c-c4a748ff9476', 'cd13f47a-7918-4e74-8d94-9b4697de3e65', 'transfer_in', 10, 10, 'transfer', NULL, '550e8400-e29b-41d4-a716-446655440001', NULL, '', '2025-12-28 18:07:19'),
('e011fd32-1803-4d01-b40a-5f5bf5fec72f', 'cd13f47a-7918-4e74-8d94-9b4697de3e65', 'transfer_in', 20, 22, 'transfer', NULL, '550e8400-e29b-41d4-a716-446655440001', NULL, '', '2025-12-28 19:06:41'),
('edf4d092-cd39-4737-a717-631cd323f79b', 'cd13f47a-7918-4e74-8d94-9b4697de3e65', 'spent', 3, 13, 'purchase', 'f6316d08-70de-489c-8998-8acdfeb69c44', NULL, NULL, 'Order: ORD202512294466', '2025-12-28 19:29:41'),
('efa740f0-48bc-4a7f-9c2d-60510c8ae29f', 'cd13f47a-7918-4e74-8d94-9b4697de3e65', 'spent', 3, 10, 'purchase', '87080f05-6048-4fb8-aa19-9a3e61b3286c', NULL, NULL, 'Order: ORD202512290261', '2025-12-28 19:34:39'),
('f35feafa-5488-4d2b-a261-806bdc0709ba', 'cd13f47a-7918-4e74-8d94-9b4697de3e65', 'spent', 3, 16, 'purchase', '0ae67d31-2cc1-41b4-9637-f5f01ada102d', NULL, NULL, 'Order: ORD202512292994', '2025-12-28 19:28:27');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` varchar(36) NOT NULL,
  `category_id` varchar(36) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price_points` int(11) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price_points`, `stock`, `image_url`, `is_active`, `created_at`, `updated_at`) VALUES
('550e8400-e29b-41d4-a716-446655440030', '550e8400-e29b-41d4-a716-446655440020', 'Indomie Goreng', 'Mie instan rasa ayam bawang', 5, 100, NULL, 1, '2025-12-20 07:40:52', '2026-01-10 07:46:09'),
('550e8400-e29b-41d4-a716-446655440031', '550e8400-e29b-41d4-a716-446655440020', 'Chitato', 'Keripik kentang rasa sapi panggang', 8, 49, NULL, 1, '2025-12-20 07:40:52', '2025-12-28 18:49:26'),
('550e8400-e29b-41d4-a716-446655440032', '550e8400-e29b-41d4-a716-446655440021', 'Aqua 600ml', 'Air mineral kemasan', 3, 196, NULL, 1, '2025-12-20 07:40:52', '2025-12-28 19:34:39'),
('550e8400-e29b-41d4-a716-446655440033', '550e8400-e29b-41d4-a716-446655440021', 'Teh Botol Sosro', 'Minuman teh manis', 4, 150, NULL, 1, '2025-12-20 07:40:52', '2025-12-20 07:40:52'),
('550e8400-e29b-41d4-a716-446655440034', '550e8400-e29b-41d4-a716-446655440022', 'Sabun Mandi', 'Sabun mandi cair', 15, 30, NULL, 1, '2025-12-20 07:40:52', '2025-12-20 07:40:52');

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `id` varchar(36) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`id`, `name`, `description`, `is_active`, `created_at`) VALUES
('550e8400-e29b-41d4-a716-446655440020', 'Makanan', 'Produk makanan dan snack', 1, '2025-12-20 07:40:52'),
('550e8400-e29b-41d4-a716-446655440021', 'Minuman', 'Minuman segar dan kemasan', 1, '2025-12-20 07:40:52'),
('550e8400-e29b-41d4-a716-446655440022', 'Kebutuhan Sehari-hari', 'Produk kebutuhan harian', 1, '2025-12-20 07:40:52');

-- --------------------------------------------------------

--
-- Table structure for table `qr_codes`
--

CREATE TABLE `qr_codes` (
  `id` varchar(36) NOT NULL,
  `order_id` varchar(36) NOT NULL,
  `qr_code` varchar(255) NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rfid_cards`
--

CREATE TABLE `rfid_cards` (
  `id` varchar(36) NOT NULL,
  `uid` varchar(20) NOT NULL,
  `user_id` varchar(36) DEFAULT NULL,
  `nama_pemilik` varchar(100) DEFAULT NULL,
  `peran` enum('Admin','Karyawan','Guest') DEFAULT 'Guest',
  `status` enum('Aktif','Nonaktif') DEFAULT 'Aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rfid_cards`
--

INSERT INTO `rfid_cards` (`id`, `uid`, `user_id`, `nama_pemilik`, `peran`, `status`, `created_at`) VALUES
('rfid-admin-1-000000000000000000001', 'B490FBB0', NULL, 'Admin Card 1', 'Admin', 'Aktif', '2025-12-29 15:51:22'),
('rfid-admin-2-000000000000000000002', 'C6861BFF', NULL, 'Admin Card 2', 'Admin', 'Aktif', '2025-12-29 15:51:22'),
('rfid-emp-40bf7577-4e10-4ecd-97ac-e66', 'KAR-8223E0', '40bf7577-4e10-4ecd-97ac-e662f2381bae', 'bezn store admin', 'Karyawan', 'Aktif', '2025-12-29 15:51:22'),
('rfid-emp-550e8400-e29b-41d4-a716-446', 'EMP001', '550e8400-e29b-41d4-a716-446655440001', 'Budi Santoso', 'Karyawan', 'Aktif', '2025-12-29 15:51:22'),
('rfid-emp-cd13f47a-7918-4e74-8d94-9b4', 'KAR-2D2E7A', 'cd13f47a-7918-4e74-8d94-9b4697de3e65', 'yanto nglamak', 'Karyawan', 'Aktif', '2025-12-29 15:51:22');

-- --------------------------------------------------------

--
-- Table structure for table `rfid_logs`
--

CREATE TABLE `rfid_logs` (
  `id` varchar(36) NOT NULL,
  `bathroom_id` varchar(36) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `action` enum('start_cleaning','finish_cleaning') NOT NULL,
  `rfid_code` varchar(50) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sensors`
--

CREATE TABLE `sensors` (
  `id` varchar(36) NOT NULL,
  `bathroom_id` varchar(36) NOT NULL,
  `sensor_type` enum('mq135','ir','rfid') NOT NULL,
  `sensor_code` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sensors`
--

INSERT INTO `sensors` (`id`, `bathroom_id`, `sensor_type`, `sensor_code`, `is_active`, `created_at`) VALUES
('sensor-ir-1-00000000000000000000001', 'toilet-1-uuid-0000000000000000000000', 'ir', 'IR_T1', 1, '2025-12-29 15:51:22'),
('sensor-ir-2-00000000000000000000002', 'toilet-2-uuid-0000000000000000000000', 'ir', 'IR_T2', 1, '2025-12-29 15:51:22'),
('sensor-mq-1-00000000000000000000001', 'toilet-1-uuid-0000000000000000000000', 'mq135', 'MQ135_T1', 1, '2025-12-29 15:51:22'),
('sensor-mq-2-00000000000000000000002', 'toilet-2-uuid-0000000000000000000000', 'mq135', 'MQ135_T2', 1, '2025-12-29 15:51:22'),
('sensor-rfid-1-0000000000000000000001', 'toilet-1-uuid-0000000000000000000000', 'rfid', 'RFID_T1', 1, '2025-12-29 15:51:22'),
('sensor-rfid-2-0000000000000000000002', 'toilet-2-uuid-0000000000000000000000', 'rfid', 'RFID_T2', 1, '2025-12-29 15:51:22');

-- --------------------------------------------------------

--
-- Table structure for table `sensor_logs`
--

CREATE TABLE `sensor_logs` (
  `id` varchar(36) NOT NULL,
  `sensor_id` varchar(36) NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `unit` varchar(20) DEFAULT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sensor_logs`
--

INSERT INTO `sensor_logs` (`id`, `sensor_id`, `value`, `unit`, `recorded_at`) VALUES
('034040f2-aa70-430d-a652-8c8c0095c10b', 'sensor-mq-1-00000000000000000000001', 450.00, 'ppm', '2025-12-29 16:57:23'),
('0c210db3-85ac-4268-b64c-87f270d78b15', 'sensor-mq-1-00000000000000000000001', 450.00, 'ppm', '2025-12-29 16:56:23'),
('1a46bdc5-0c67-43cc-8a31-38d4c7697c10', 'sensor-mq-1-00000000000000000000001', 450.00, 'ppm', '2025-12-29 17:21:23'),
('1d24a5fd-a50a-4b28-b0cd-62dd30a0385a', 'sensor-ir-1-00000000000000000000001', 3.00, 'visitors', '2025-12-29 16:58:23'),
('2d1688b7-b705-4e02-b4e8-ba5be7335cda', 'sensor-mq-1-00000000000000000000001', 450.00, 'ppm', '2025-12-29 17:23:13'),
('35646515-61fd-4f84-b371-bf0f7bece291', 'sensor-mq-1-00000000000000000000001', 450.00, 'ppm', '2025-12-29 17:03:13'),
('35a651f7-0664-43a0-93bd-17ad0c775359', 'sensor-ir-1-00000000000000000000001', 3.00, 'visitors', '2025-12-29 17:03:13'),
('3cd26bb0-5fad-47ac-8ee4-1bcc633226e2', 'sensor-mq-1-00000000000000000000001', 450.00, 'ppm', '2025-12-29 17:20:53'),
('3f52a7f2-a501-44a0-939c-b1d5668a3093', 'sensor-mq-1-00000000000000000000001', 450.00, 'ppm', '2025-12-29 16:55:40'),
('466cce34-9b51-48ea-b4e1-f1164339b423', 'sensor-mq-1-00000000000000000000001', 450.00, 'ppm', '2025-12-29 17:01:13'),
('50ddf7d5-77c4-4276-9b08-627a32e54265', 'sensor-ir-1-00000000000000000000001', 3.00, 'visitors', '2025-12-29 16:57:23'),
('5a4a5ef3-022e-4bb5-845d-b6fb870fb456', 'sensor-mq-1-00000000000000000000001', 450.00, 'ppm', '2025-12-29 16:56:53'),
('5d384164-f751-45a6-9e95-456435f57546', 'sensor-ir-1-00000000000000000000001', 3.00, 'visitors', '2025-12-29 17:01:13'),
('607f290f-8915-474a-8a3b-ad327e0bde8e', 'sensor-mq-1-00000000000000000000001', 450.00, 'ppm', '2025-12-29 17:02:13'),
('61fe2c4c-b518-4238-96b6-e1462d183d85', 'sensor-ir-1-00000000000000000000001', 3.00, 'visitors', '2025-12-29 16:55:22'),
('66067682-0023-4e3b-a2b3-baded0ddd6cf', 'sensor-ir-1-00000000000000000000001', 3.00, 'visitors', '2025-12-29 17:20:12'),
('67d9e46c-6f71-4437-97f9-c25dfcdb4598', 'sensor-mq-1-00000000000000000000001', 450.00, 'ppm', '2025-12-29 16:58:23'),
('68ffa902-304a-43fc-9278-400e3a74121c', 'sensor-mq-1-00000000000000000000001', 450.00, 'ppm', '2025-12-29 17:04:13'),
('6c07cb5a-b749-4b65-a861-970e75a5cf58', 'sensor-ir-1-00000000000000000000001', 3.00, 'visitors', '2025-12-29 17:20:23'),
('7818a54a-6d27-46cd-8cd3-755cb465f157', 'sensor-ir-1-00000000000000000000001', 3.00, 'visitors', '2025-12-29 17:25:13'),
('7f724fc3-b090-49de-a296-078960190e0f', 'sensor-ir-1-00000000000000000000001', 3.00, 'visitors', '2025-12-29 16:56:23'),
('809f0300-eb75-4060-8330-8dfab8bd634f', 'sensor-mq-1-00000000000000000000001', 450.00, 'ppm', '2025-12-29 17:20:12'),
('81a92fe4-9a3e-4079-8e88-b380ab463f61', 'sensor-ir-1-00000000000000000000001', 3.00, 'visitors', '2025-12-29 17:22:13'),
('89069d17-f1c1-4bd6-9636-5374a567839b', 'sensor-ir-1-00000000000000000000001', 3.00, 'visitors', '2025-12-29 17:20:53'),
('8f5df19f-d62a-49b4-948e-e90db55a6d15', 'sensor-ir-1-00000000000000000000001', 3.00, 'visitors', '2025-12-29 17:21:23'),
('9690ef59-4cfe-445e-bc25-d707ddaad3f5', 'sensor-mq-1-00000000000000000000001', 450.00, 'ppm', '2025-12-29 17:20:23'),
('98fa691e-5fd3-4269-acd2-a886b773bf17', 'sensor-mq-1-00000000000000000000001', 450.00, 'ppm', '2025-12-29 16:59:13'),
('a9a2b6a1-4bf0-46a0-9102-fa5baa4fb9f1', 'sensor-mq-1-00000000000000000000001', 450.00, 'ppm', '2025-12-29 17:22:13'),
('b004ef69-a010-4b5e-8173-1ee0c96a992a', 'sensor-ir-1-00000000000000000000001', 3.00, 'visitors', '2025-12-29 16:59:13'),
('b38269ba-aa72-4f52-8d49-5e6b27bce232', 'sensor-mq-1-00000000000000000000001', 450.00, 'ppm', '2025-12-29 16:55:22'),
('bad32b00-1742-4d98-8a90-7566f9ccbd1c', 'sensor-ir-1-00000000000000000000001', 3.00, 'visitors', '2025-12-29 17:23:13'),
('be8298a2-5f51-46eb-8888-2e2b3b7827c5', 'sensor-ir-1-00000000000000000000001', 3.00, 'visitors', '2025-12-29 17:02:13'),
('bf3c0326-9f27-47bb-a84d-96822d9f269f', 'sensor-mq-1-00000000000000000000001', 450.00, 'ppm', '2025-12-29 17:00:14'),
('c5f07ddb-200d-4bdb-80cc-e6bc3f2fd69b', 'sensor-ir-1-00000000000000000000001', 3.00, 'visitors', '2025-12-29 16:56:53'),
('cbf4e410-2d96-4836-b1af-1cb3915a6440', 'sensor-ir-1-00000000000000000000001', 3.00, 'visitors', '2025-12-29 17:00:14'),
('d22b99aa-d1a0-49e8-8ab8-a47b4eb2ac7b', 'sensor-ir-1-00000000000000000000001', 3.00, 'visitors', '2025-12-29 16:55:40'),
('d53845a8-7289-44bf-b65a-06a69fa7b260', 'sensor-mq-1-00000000000000000000001', 450.00, 'ppm', '2025-12-29 17:24:13'),
('db5501a4-3b28-4e6e-b140-5dd40a90682f', 'sensor-ir-1-00000000000000000000001', 3.00, 'visitors', '2025-12-29 17:04:13'),
('dbad83d6-32ef-4b67-94d4-47ae7698e01f', 'sensor-ir-1-00000000000000000000001', 3.00, 'visitors', '2025-12-29 16:57:53'),
('dce1ecc5-7866-4073-ab65-18ba7f50e32c', 'sensor-mq-1-00000000000000000000001', 450.00, 'ppm', '2025-12-29 16:55:53'),
('e2239d26-8c68-4fb8-8b39-bff4ed6ab500', 'sensor-mq-1-00000000000000000000001', 450.00, 'ppm', '2025-12-29 17:25:13'),
('ecf0126f-622a-4a17-9efb-661f517bf106', 'sensor-mq-1-00000000000000000000001', 450.00, 'ppm', '2025-12-29 16:57:53'),
('f3a3da35-ba88-4e9d-97e2-49073d703c1e', 'sensor-ir-1-00000000000000000000001', 3.00, 'visitors', '2025-12-29 16:55:23'),
('f3a85301-aeff-4be8-bd8a-1e2331bdbd4e', 'sensor-ir-1-00000000000000000000001', 3.00, 'visitors', '2025-12-29 17:24:13'),
('f9414ac1-c49b-40a6-8d91-0559197064fa', 'sensor-mq-1-00000000000000000000001', 450.00, 'ppm', '2025-12-29 16:55:23'),
('fc28920e-6c55-49c9-9e48-3585e18dcacf', 'sensor-ir-1-00000000000000000000001', 3.00, 'visitors', '2025-12-29 16:55:53');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` varchar(36) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES
('550e8400-e29b-41d4-a716-446655440010', 'points_per_cleaning', '10', 'Points earned per cleaning session', '2025-12-20 07:40:52'),
('550e8400-e29b-41d4-a716-446655440011', 'max_visitors_default', '10', 'Default maximum visitors before cleaning required', '2025-12-20 07:40:52'),
('550e8400-e29b-41d4-a716-446655440012', 'cleaning_timeout_minutes', '30', 'Maximum time allowed for cleaning session', '2025-12-20 07:40:52');

-- --------------------------------------------------------

--
-- Table structure for table `usage_logs`
--

CREATE TABLE `usage_logs` (
  `id` varchar(36) NOT NULL,
  `bathroom_id` varchar(36) NOT NULL,
  `uid_pengakses` varchar(20) DEFAULT NULL,
  `user_id` varchar(36) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `action_type` enum('enter','exit','admin_reset','start_cleaning','finish_cleaning') NOT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usage_logs`
--

INSERT INTO `usage_logs` (`id`, `bathroom_id`, `uid_pengakses`, `user_id`, `keterangan`, `action_type`, `waktu`) VALUES
('0835eb1c-f089-4eb9-863d-a6d837a5dd54', 'toilet-1-uuid-0000000000000000000000', 'B490FBB0', NULL, 'Admin reset system - Toilet 1', 'admin_reset', '2025-12-29 16:55:23'),
('0eb5ec07-a8a1-4afc-b05f-f71e72457ab2', 'toilet-1-uuid-0000000000000000000000', 'SENSOR', NULL, 'Visitor count: 3, Gas: 450 ppm', 'enter', '2025-12-29 16:56:53'),
('19b77284-c3f8-4852-bb7c-ff2c7ca8756c', 'toilet-1-uuid-0000000000000000000000', 'B490FBB0', NULL, 'Admin reset system - Toilet 1', 'admin_reset', '2025-12-29 17:23:13'),
('1ce8cd87-4e93-4a4a-90a7-d5405920f975', 'toilet-1-uuid-0000000000000000000000', 'B490FBB0', NULL, 'Admin reset system - Toilet 1', 'admin_reset', '2025-12-29 17:20:23'),
('290d93fb-9ad0-454e-a103-d85ed444417a', 'toilet-1-uuid-0000000000000000000000', 'SENSOR', NULL, 'Visitor count: 3, Gas: 450 ppm', 'enter', '2025-12-29 16:55:22'),
('3053850b-77db-47d5-9d57-fd8b13c4ccf2', 'toilet-1-uuid-0000000000000000000000', 'B490FBB0', NULL, 'Admin reset system - Toilet 1', 'admin_reset', '2025-12-29 17:24:13'),
('38d82b36-29a5-4a3e-9c2e-e8f8a3e6f08f', 'toilet-1-uuid-0000000000000000000000', 'B490FBB0', NULL, 'Admin reset system - Toilet 1', 'admin_reset', '2025-12-29 16:55:22'),
('3a38241f-be5d-4107-ad5a-2223cf75cef4', 'toilet-1-uuid-0000000000000000000000', 'B490FBB0', NULL, 'Admin reset system - Toilet 1', 'admin_reset', '2025-12-29 16:55:53'),
('433841fa-f363-4cef-93ed-7a021abf3827', 'toilet-1-uuid-0000000000000000000000', 'B490FBB0', NULL, 'Admin reset system - Toilet 1', 'admin_reset', '2025-12-29 17:21:23'),
('433bf2f4-2d7d-4b17-bad4-ae3ba2d7159a', 'toilet-1-uuid-0000000000000000000000', 'SENSOR', NULL, 'Visitor count: 3, Gas: 450 ppm', 'enter', '2025-12-29 16:59:13'),
('43423206-3ab7-4549-bb55-6bfd28f9141a', 'toilet-1-uuid-0000000000000000000000', 'B490FBB0', NULL, 'Admin reset system - Toilet 1', 'admin_reset', '2025-12-29 16:56:23'),
('51649dde-1a3a-40d7-9652-a6ab0550e2af', 'toilet-1-uuid-0000000000000000000000', 'B490FBB0', NULL, 'Admin reset system - Toilet 1', 'admin_reset', '2025-12-29 17:04:13'),
('5419bd04-d6d8-4dad-a060-a11f56f0d65b', 'toilet-1-uuid-0000000000000000000000', 'B490FBB0', NULL, 'Admin reset system - Toilet 1', 'admin_reset', '2025-12-29 16:56:53'),
('58b7f3d7-fdab-4d7a-aa34-d6e3ce6fe440', 'toilet-1-uuid-0000000000000000000000', 'SENSOR', NULL, 'Visitor count: 3, Gas: 450 ppm', 'enter', '2025-12-29 17:00:14'),
('5acafe8d-4163-4389-ab4f-04c0b912a39b', 'toilet-1-uuid-0000000000000000000000', 'B490FBB0', NULL, 'Admin reset system - Toilet 1', 'admin_reset', '2025-12-29 17:20:12'),
('6171fef0-4db2-49ee-a9d6-397cb7697d4d', 'toilet-1-uuid-0000000000000000000000', 'SENSOR', NULL, 'Visitor count: 3, Gas: 450 ppm', 'enter', '2025-12-29 17:21:23'),
('6590fe7c-c6a1-4cfb-8533-4574a8677730', 'toilet-1-uuid-0000000000000000000000', 'SENSOR', NULL, 'Visitor count: 3, Gas: 450 ppm', 'enter', '2025-12-29 16:57:23'),
('6e565049-81a0-4281-876b-3b1a3361877c', 'toilet-1-uuid-0000000000000000000000', 'B490FBB0', NULL, 'Admin reset system - Toilet 1', 'admin_reset', '2025-12-29 16:55:42'),
('715860ac-eaad-49d9-bdaa-096ab079b310', 'toilet-1-uuid-0000000000000000000000', 'SENSOR', NULL, 'Visitor count: 3, Gas: 450 ppm', 'enter', '2025-12-29 16:56:23'),
('7a7fb1f7-667e-4bc3-80f5-b8f80154fc0e', 'toilet-1-uuid-0000000000000000000000', 'B490FBB0', NULL, 'Admin reset system - Toilet 1', 'admin_reset', '2025-12-29 17:02:13'),
('7ab4d5ca-b7f3-4694-8e6c-45d2392547ef', 'toilet-1-uuid-0000000000000000000000', 'B490FBB0', NULL, 'Admin reset system - Toilet 1', 'admin_reset', '2025-12-29 17:00:14'),
('7b46868b-c111-42be-af6f-735c6476f54a', 'toilet-1-uuid-0000000000000000000000', 'B490FBB0', NULL, 'Admin reset system - Toilet 1', 'admin_reset', '2025-12-29 17:20:53'),
('81c4af37-d6ca-4965-91ce-3f2d9512c33f', 'toilet-1-uuid-0000000000000000000000', 'SENSOR', NULL, 'Visitor count: 3, Gas: 450 ppm', 'enter', '2025-12-29 16:55:23'),
('8a728c7c-d30a-48f1-a24c-295ee5688e96', 'toilet-1-uuid-0000000000000000000000', 'SENSOR', NULL, 'Visitor count: 3, Gas: 450 ppm', 'enter', '2025-12-29 16:58:23'),
('8badb741-5194-4468-bcaa-8e2e2125eecb', 'toilet-1-uuid-0000000000000000000000', 'SENSOR', NULL, 'Visitor count: 3, Gas: 450 ppm', 'enter', '2025-12-29 17:20:53'),
('8d34068c-5b59-445c-9662-1816134cd9fb', 'toilet-1-uuid-0000000000000000000000', 'SENSOR', NULL, 'Visitor count: 3, Gas: 450 ppm', 'enter', '2025-12-29 17:25:13'),
('8f64f089-3845-440d-aa09-d59f8c4da16d', 'toilet-1-uuid-0000000000000000000000', 'B490FBB0', NULL, 'Admin reset system - Toilet 1', 'admin_reset', '2025-12-29 16:57:23'),
('906eef02-172c-4297-b9fb-b100dab1e0be', 'toilet-1-uuid-0000000000000000000000', 'B490FBB0', NULL, 'Admin reset system - Toilet 1', 'admin_reset', '2025-12-29 16:58:23'),
('918992d6-eeb4-44d9-b0e6-86d902cbdae3', 'toilet-1-uuid-0000000000000000000000', 'B490FBB0', NULL, 'Admin reset system - Toilet 1', 'admin_reset', '2025-12-29 16:57:53'),
('91eb2994-61d8-494c-a04f-61bb7e8c892a', 'toilet-1-uuid-0000000000000000000000', 'B490FBB0', NULL, 'Admin reset system - Toilet 1', 'admin_reset', '2025-12-29 17:03:13'),
('97c5532e-75e4-4099-8540-21bde24ef50e', 'toilet-1-uuid-0000000000000000000000', 'SENSOR', NULL, 'Visitor count: 3, Gas: 450 ppm', 'enter', '2025-12-29 16:55:53'),
('98edcd1c-cad4-4ff8-8e59-d3e1d0495139', 'toilet-1-uuid-0000000000000000000000', 'SENSOR', NULL, 'Visitor count: 3, Gas: 450 ppm', 'enter', '2025-12-29 16:57:53'),
('a24ce200-8e3b-4ea3-9963-8bba919926b0', 'toilet-1-uuid-0000000000000000000000', 'SENSOR', NULL, 'Visitor count: 3, Gas: 450 ppm', 'enter', '2025-12-29 17:20:12'),
('a696094a-3686-403a-b76a-eaf7551ad5d7', 'toilet-1-uuid-0000000000000000000000', 'SENSOR', NULL, 'Visitor count: 3, Gas: 450 ppm', 'enter', '2025-12-29 17:01:13'),
('a8af699a-d16e-4dea-8f56-a5853c4c3262', 'toilet-1-uuid-0000000000000000000000', 'B490FBB0', NULL, 'Admin reset system - Toilet 1', 'admin_reset', '2025-12-29 17:01:13'),
('b20d4638-8094-4e81-8402-dee62fb9121e', 'toilet-1-uuid-0000000000000000000000', 'SENSOR', NULL, 'Visitor count: 3, Gas: 450 ppm', 'enter', '2025-12-29 17:04:13'),
('b8b61a57-7e93-42c3-bfed-ffe5a48717c6', 'toilet-1-uuid-0000000000000000000000', 'SENSOR', NULL, 'Visitor count: 3, Gas: 450 ppm', 'enter', '2025-12-29 17:03:13'),
('b8b8d13b-8347-4cc2-b1e0-0a8b01e2561d', 'toilet-1-uuid-0000000000000000000000', 'SENSOR', NULL, 'Visitor count: 3, Gas: 450 ppm', 'enter', '2025-12-29 17:24:13'),
('c6f59358-e21a-496e-82cc-954d9bf38226', 'toilet-1-uuid-0000000000000000000000', 'SENSOR', NULL, 'Visitor count: 3, Gas: 450 ppm', 'enter', '2025-12-29 17:22:13'),
('d2179a94-7c11-40f1-97e2-1fe13b2e0bfd', 'toilet-1-uuid-0000000000000000000000', 'SENSOR', NULL, 'Visitor count: 3, Gas: 450 ppm', 'enter', '2025-12-29 16:55:40'),
('d4a9b597-05a6-447a-8b22-2d6ec5a4ca81', 'toilet-1-uuid-0000000000000000000000', 'SENSOR', NULL, 'Visitor count: 3, Gas: 450 ppm', 'enter', '2025-12-29 17:20:23'),
('e739ed0d-7cbf-46a7-bbd0-5371ca1dbc08', 'toilet-1-uuid-0000000000000000000000', 'SENSOR', NULL, 'Visitor count: 3, Gas: 450 ppm', 'enter', '2025-12-29 17:02:13'),
('eddb5553-823e-4ff3-8e60-44efaf0dd686', 'toilet-1-uuid-0000000000000000000000', 'SENSOR', NULL, 'Visitor count: 3, Gas: 450 ppm', 'enter', '2025-12-29 17:23:13'),
('f7665eca-cb8f-490d-bae2-749d565a61e5', 'toilet-1-uuid-0000000000000000000000', 'B490FBB0', NULL, 'Admin reset system - Toilet 1', 'admin_reset', '2025-12-29 16:59:13'),
('fc401426-c6f6-4cf7-b366-dd3f2883d6e8', 'toilet-1-uuid-0000000000000000000000', 'B490FBB0', NULL, 'Admin reset system - Toilet 1', 'admin_reset', '2025-12-29 17:25:13'),
('fdd04706-de81-408a-a4bd-a9f6601d51a2', 'toilet-1-uuid-0000000000000000000000', 'B490FBB0', NULL, 'Admin reset system - Toilet 1', 'admin_reset', '2025-12-29 17:22:13');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` varchar(36) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `pin` varchar(255) DEFAULT NULL,
  `pin_created_at` timestamp NULL DEFAULT NULL,
  `last_password_change` timestamp NULL DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','karyawan') NOT NULL,
  `employee_code` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `can_monitor_bathroom` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `theme` enum('light','dark','system') DEFAULT 'system',
  `points` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `pin`, `pin_created_at`, `last_password_change`, `full_name`, `email`, `phone`, `role`, `employee_code`, `is_active`, `can_monitor_bathroom`, `created_at`, `updated_at`, `theme`, `points`) VALUES
('40bf7577-4e10-4ecd-97ac-e662f2381bae', 'karyawan002', '$2y$10$j.oLDzxWNf95JHo0mfNCA.43zbFdXHFt0kCIx/ca/SR6pGlnPKccm', '$2y$10', '2025-12-28 18:00:53', NULL, 'bezn store admin', 'bezn.digital@gmail.com', '085189643588', 'karyawan', 'KAR-8223E0', 1, 1, '2025-12-28 17:49:04', '2025-12-28 18:00:53', 'system', 0),
('550e8400-e29b-41d4-a716-446655440000', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '$2y$10', '2025-12-28 18:00:54', NULL, 'System Administrator', NULL, NULL, 'admin', 'ADM001', 1, 1, '2025-12-20 07:40:52', '2025-12-28 18:00:54', 'system', 0),
('550e8400-e29b-41d4-a716-446655440001', 'karyawan1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '$2y$10$V1PUpuS8PmeaVs6oelcnDuo5aTUgJKn8ObU6sn8NQ7fFo9B5o1fC6', '2025-12-28 18:02:57', NULL, 'Budi Santoso', 'budi@example.com', '081234567890', 'karyawan', 'EMP001', 1, 1, '2025-12-20 07:40:52', '2025-12-28 18:06:56', 'system', 0),
('cd13f47a-7918-4e74-8d94-9b4697de3e65', 'karyawan003', '$2y$10$1j0h5poLJ/3kdIxHhM9HQebmDIWTSVCTiC4Uv.yYovDMSTvzwg4eS', '$2y$10', '2025-12-28 18:00:54', NULL, 'yanto nglamak', 'nglamak123@sp.id', '08988934723', 'karyawan', 'KAR-2D2E7A', 1, 1, '2025-12-28 17:49:57', '2025-12-28 18:00:54', 'system', 0);

-- --------------------------------------------------------

--
-- Table structure for table `visitor_counter`
--

CREATE TABLE `visitor_counter` (
  `id` varchar(36) NOT NULL,
  `bathroom_id` varchar(36) NOT NULL,
  `count_in` int(11) DEFAULT 0,
  `count_out` int(11) DEFAULT 0,
  `current_occupancy` int(11) DEFAULT 0,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `bathrooms`
--
ALTER TABLE `bathrooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `last_cleaned_by` (`last_cleaned_by`);

--
-- Indexes for table `cleaning_logs`
--
ALTER TABLE `cleaning_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bathroom_id` (`bathroom_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `points`
--
ALTER TABLE `points`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `point_transactions`
--
ALTER TABLE `point_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `from_user_id` (`from_user_id`),
  ADD KEY `to_user_id` (`to_user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qr_codes`
--
ALTER TABLE `qr_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `qr_code` (`qr_code`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `rfid_cards`
--
ALTER TABLE `rfid_cards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uid` (`uid`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `rfid_logs`
--
ALTER TABLE `rfid_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bathroom_id` (`bathroom_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sensors`
--
ALTER TABLE `sensors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sensor_code` (`sensor_code`),
  ADD KEY `bathroom_id` (`bathroom_id`);

--
-- Indexes for table `sensor_logs`
--
ALTER TABLE `sensor_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sensor_id` (`sensor_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `usage_logs`
--
ALTER TABLE `usage_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bathroom_id` (`bathroom_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `employee_code` (`employee_code`);

--
-- Indexes for table `visitor_counter`
--
ALTER TABLE `visitor_counter`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bathroom_id` (`bathroom_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `bathrooms`
--
ALTER TABLE `bathrooms`
  ADD CONSTRAINT `bathrooms_ibfk_1` FOREIGN KEY (`last_cleaned_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `cleaning_logs`
--
ALTER TABLE `cleaning_logs`
  ADD CONSTRAINT `cleaning_logs_ibfk_1` FOREIGN KEY (`bathroom_id`) REFERENCES `bathrooms` (`id`),
  ADD CONSTRAINT `cleaning_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `points`
--
ALTER TABLE `points`
  ADD CONSTRAINT `points_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `point_transactions`
--
ALTER TABLE `point_transactions`
  ADD CONSTRAINT `point_transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `point_transactions_ibfk_2` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `point_transactions_ibfk_3` FOREIGN KEY (`to_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`);

--
-- Constraints for table `qr_codes`
--
ALTER TABLE `qr_codes`
  ADD CONSTRAINT `qr_codes_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `rfid_cards`
--
ALTER TABLE `rfid_cards`
  ADD CONSTRAINT `rfid_cards_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `rfid_logs`
--
ALTER TABLE `rfid_logs`
  ADD CONSTRAINT `rfid_logs_ibfk_1` FOREIGN KEY (`bathroom_id`) REFERENCES `bathrooms` (`id`),
  ADD CONSTRAINT `rfid_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `sensors`
--
ALTER TABLE `sensors`
  ADD CONSTRAINT `sensors_ibfk_1` FOREIGN KEY (`bathroom_id`) REFERENCES `bathrooms` (`id`);

--
-- Constraints for table `sensor_logs`
--
ALTER TABLE `sensor_logs`
  ADD CONSTRAINT `sensor_logs_ibfk_1` FOREIGN KEY (`sensor_id`) REFERENCES `sensors` (`id`);

--
-- Constraints for table `usage_logs`
--
ALTER TABLE `usage_logs`
  ADD CONSTRAINT `usage_logs_ibfk_1` FOREIGN KEY (`bathroom_id`) REFERENCES `bathrooms` (`id`),
  ADD CONSTRAINT `usage_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `visitor_counter`
--
ALTER TABLE `visitor_counter`
  ADD CONSTRAINT `visitor_counter_ibfk_1` FOREIGN KEY (`bathroom_id`) REFERENCES `bathrooms` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
