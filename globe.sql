-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 27, 2025 at 10:32 PM
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
-- Database: `globe`
--

-- --------------------------------------------------------

--
-- Table structure for table `lawyers`
--

CREATE TABLE `lawyers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(190) NOT NULL,
  `pass_hash` varchar(255) NOT NULL,
  `role` enum('lawyer','admin') NOT NULL DEFAULT 'lawyer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lawyers`
--

INSERT INTO `lawyers` (`id`, `name`, `email`, `pass_hash`, `role`, `created_at`) VALUES
(1, 'Atty. Alex Austria', 'aaustria@globe.com.ph', '$2y$10$o21hfiiwXUMEj0qgLNLMgeRgmB/KyBFYAM2693lTpQLhK8bHNUk7q', 'lawyer', '2025-10-26 05:47:59');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_code` varchar(20) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `full_name` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `priority` enum('Low','Normal','High','Urgent') DEFAULT 'Normal',
  `due_date` date DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `grp` varchar(50) NOT NULL,
  `tribe` varchar(200) DEFAULT NULL,
  `assigned_lawyer` varchar(200) DEFAULT NULL,
  `cc_emails` text DEFAULT NULL,
  `summary` text NOT NULL,
  `contract_type` varchar(100) NOT NULL,
  `contract_other` varchar(200) DEFAULT NULL,
  `customer` varchar(200) NOT NULL,
  `vendor` varchar(200) NOT NULL,
  `pd_nature` varchar(120) NOT NULL,
  `pd_other_text` varchar(255) DEFAULT NULL,
  `clauses` text NOT NULL,
  `doc_link` varchar(1000) DEFAULT NULL,
  `status` enum('Pending','In Review','For Revisions','Completed') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `ticket_code`, `created_at`, `full_name`, `email`, `priority`, `due_date`, `completed_at`, `grp`, `tribe`, `assigned_lawyer`, `cc_emails`, `summary`, `contract_type`, `contract_other`, `customer`, `vendor`, `pd_nature`, `pd_other_text`, `clauses`, `doc_link`, `status`) VALUES
(4, 'GDA-250694', '2025-10-27 10:16:35', 'Reign Gel Ogma', 'kentnotcant@gmail.com', 'Normal', '2025-11-03', NULL, 'ISDP|ALEX', '', 'Atty. Alex Austria <kentnotcant@gmail.com>', 'kentnotcant@gmail.com', 'sefgasgraeg', 'Data Sharing Agreement (DSA)', '', 'ergerge', 'eagrfdefg', 'Globe processes partner data', '', 'ergregas', 'N/A', 'For Revisions'),
(5, 'GDA-108828', '2025-10-27 10:40:24', 'Lod Idle Bautista', 'jerrypaje045@gmail.com', 'Normal', '2025-11-03', '2025-10-27 10:41:48', 'ISG|ALEX', '', 'Atty. Alex Austria <kentnotcant@gmail.com>', 'kentnotcant@gmail.com', 'wefgedfqwaer', 'Non-Disclosure Agreement (NDA)', '', 'qwefqewf', 'qwefqwefq', 'OTHER', '', 'qwefqwfe', 'N/A', 'Completed'),
(6, 'GDA-905943', '2025-10-27 11:52:55', 'Lod Idle Bautista', 'jerrypaje045@gmail.com', 'Normal', '2025-11-03', NULL, 'NTG|ALEX', '', 'Atty. Alex Austria <kentnotcant@gmail.com>', 'kentnotcant@gmail.com', '2dsfvserrrrrrrrrrrrrrrrrr wedfqawef', 'Data Processing Agreement (DPA)', '', 'qefqef', 'adfweqfdfa', 'Both parties share/process data', '', 'qwefqwfe', 'N/A', 'Pending'),
(7, 'GDA-695813', '2025-10-27 12:00:40', 'Kim Dominic Valenzuela', 'kentnotcant@gmail.com', 'Normal', '2025-11-03', NULL, 'NTG|ALEX', '', 'Atty. Alex Austria <kentnotcant@gmail.com>', 'kentnotcant@gmail.com', 'qwergfqergwegr', 'Non-Disclosure Agreement (NDA)', '', 'ewgrwgrw', 'wergwergw', 'Partner processes Globe data', '', 'wergwerg', 'N/A', 'Pending'),
(8, 'GDA-368351', '2025-10-27 12:08:07', 'Baby Clarisse Perez', 'jerrypaje045@gmail.com', 'Normal', '2025-11-03', NULL, 'ICG|ALEX', '', 'Atty. Alex Austria <kentnotcant@gmail.com>', 'kentnotcant@gmail.com', 'qwefqwefq', 'Non-Disclosure Agreement (NDA)', '', '4tgfqe3rgqa', 'qwefqwef', 'Both parties share/process data', '', 'qwefqefq', 'N/A', 'For Revisions'),
(9, 'GDA-159140', '2025-10-27 12:45:02', 'Jerry S. Paje', 'jerrypaje045@gmail.com', 'Normal', '2025-11-03', NULL, 'STT|FRANCINE', '', 'Atty. Francine Turo <ksperez.degullado@gmail.com>', 'ksperez.degullado@gmail.com', 'wqefqfq3f', 'Data Sharing Agreement (DSA)', '', 'qwefqawefqa', 'qwefqwef', 'Both parties share/process data', '', 'qwefqwfe', 'N/A', 'Pending'),
(10, 'GDA-486374', '2025-10-27 12:52:17', 'John Lexter Ilao', 'kentperez30@gmail.com', 'Normal', '2025-11-03', '2025-10-28 05:22:54', 'OSMCX|FRANCINE', '', 'Atty. Francine Turo <ksperez.degullado@gmail.com>', 'ksperez.degullado@gmail.com', 'wfqaewfq', 'Non-Disclosure Agreement (NDA)', '', 'qwefqwef', 'ewqfqefq', 'Partner processes Globe data', '', 'qqwefqwe', 'N/A', 'Completed'),
(11, 'GDA-093024', '2025-10-28 05:21:23', 'John Wick', 'jerrypaje045@gmail.com', 'Normal', '2025-11-04', NULL, 'B2B', '', 'Atty. Francine Turo <ksperez.degullado@gmail.com>', 'kentperez30@gmail.com', 'lorem ipsum lorem ipsum', 'Non-Disclosure Agreement (NDA)', '', 'Customer', 'Vendor', 'Both parties share/process data', '', 'Lorem Ipsum', 'N/A', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_files`
--

CREATE TABLE `ticket_files` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `original` varchar(255) NOT NULL,
  `saved_as` varchar(255) NOT NULL,
  `mime` varchar(120) DEFAULT NULL,
  `size_bytes` bigint(20) UNSIGNED DEFAULT NULL,
  `uploaded_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket_files`
--

INSERT INTO `ticket_files` (`id`, `ticket_id`, `original`, `saved_as`, `mime`, `size_bytes`, `uploaded_at`) VALUES
(4, 4, 'Render_Mockup_4000_4000_2025-04-18.png', '4_18cb284083.png', 'image/png', 9657909, '2025-10-27 10:16:35'),
(5, 5, 'CompTIA Security.docx', '5_0bfea01e74.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 53193, '2025-10-27 10:40:24'),
(6, 6, 'resume1.docx', '6_7b22123877.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 30194, '2025-10-27 11:52:55'),
(7, 7, 'SignatureJPEG-removebg-preview.png', '7_bf41514bfc.png', 'image/png', 33189, '2025-10-27 12:00:40'),
(8, 8, 'resume1.docx', '8_552c5f430b.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 30194, '2025-10-27 12:08:07'),
(9, 9, 'HOUSE RENT AUDIT.xlsx', '9_d1c6b1f87d.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 8839, '2025-10-27 12:45:02'),
(10, 10, 'RESUME PEREZ.pdf', '10_9654be4228.pdf', 'application/pdf', 208557, '2025-10-27 12:52:17'),
(11, 11, 'HOUSE RENT AUDIT.xlsx', '11_c5ed015312.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 8839, '2025-10-28 05:21:23');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `name` varchar(120) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `lawyers`
--
ALTER TABLE `lawyers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ticket_files`
--
ALTER TABLE `ticket_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_files_ticket` (`ticket_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `lawyers`
--
ALTER TABLE `lawyers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `ticket_files`
--
ALTER TABLE `ticket_files`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ticket_files`
--
ALTER TABLE `ticket_files`
  ADD CONSTRAINT `fk_files_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
