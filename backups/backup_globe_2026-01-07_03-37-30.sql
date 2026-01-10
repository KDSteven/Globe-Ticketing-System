-- Backup of globe
-- Generated: 2026-01-07T03:37:30+01:00

SET foreign_key_checks = 0;


-- ----------------------------
-- Table structure for `holidays`
-- ----------------------------
DROP TABLE IF EXISTS `holidays`;
CREATE TABLE `holidays` (
  `date` date NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of `holidays`
-- ----------------------------
INSERT INTO `holidays` (`date`,`description`) VALUES ('2025-12-19','la langs');
INSERT INTO `holidays` (`date`,`description`) VALUES ('2026-01-22','Birthday ko to Nigga');


-- ----------------------------
-- Table structure for `lawyers`
-- ----------------------------
DROP TABLE IF EXISTS `lawyers`;
CREATE TABLE `lawyers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(190) NOT NULL,
  `pass_hash` varchar(255) NOT NULL,
  `role` enum('lawyer','admin') NOT NULL DEFAULT 'lawyer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `archived_at` datetime DEFAULT NULL,
  `archived_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_lawyers_archived_at` (`archived_at`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of `lawyers`
-- ----------------------------
INSERT INTO `lawyers` (`id`,`name`,`email`,`pass_hash`,`role`,`created_at`,`archived_at`,`archived_by`) VALUES ('1','Atty. Alex Austria','aaustria@globe.com.ph','$2y$10$o21hfiiwXUMEj0qgLNLMgeRgmB/KyBFYAM2693lTpQLhK8bHNUk7q','lawyer','2025-10-26 13:47:59',NULL,NULL);
INSERT INTO `lawyers` (`id`,`name`,`email`,`pass_hash`,`role`,`created_at`,`archived_at`,`archived_by`) VALUES ('2','Kent Steven D. Perez','ksperez.degullado@gmail.com','$2y$10$DvSO/5eH/RwToYt5ApfUT.q0zTsPpWr5wXvhio/DxvMfoyQ8QLLne','lawyer','2025-11-11 11:10:14','2026-01-07 10:35:47','3');
INSERT INTO `lawyers` (`id`,`name`,`email`,`pass_hash`,`role`,`created_at`,`archived_at`,`archived_by`) VALUES ('3','Admin Steven','kent.perez@globe.com.ph','$2y$10$c43AHd7CVP8YqgfCT/0/.OlTJplJDjQABGaPkMDola5vsn/s6dHM2','admin','2025-12-04 09:59:43',NULL,NULL);
INSERT INTO `lawyers` (`id`,`name`,`email`,`pass_hash`,`role`,`created_at`,`archived_at`,`archived_by`) VALUES ('4','Atty. Raissa Villanueva','raissa.villanueva@globe.com.ph','$2y$10$1YgLXnPttI9KLjI8yKGaFejHlg8QAvQKDP6luD4WQNPJ12yYPDZXe','lawyer','2025-12-04 18:55:20',NULL,NULL);
INSERT INTO `lawyers` (`id`,`name`,`email`,`pass_hash`,`role`,`created_at`,`archived_at`,`archived_by`) VALUES ('5','Roselyn Serrano','rgserrano@globe.com.ph','$2y$10$1YgLXnPttI9KLjI8yKGaFejHlg8QAvQKDP6luD4WQNPJ12yYPDZXe','admin','2025-12-04 18:55:28',NULL,NULL);
INSERT INTO `lawyers` (`id`,`name`,`email`,`pass_hash`,`role`,`created_at`,`archived_at`,`archived_by`) VALUES ('6','Atty. Francine Turo','francine.turo@globe.com.ph','$2y$10$i5mmAaCoyFZUTeA8ZRwbpOim9nS4Ly0mF1.TpUsXb1vTTHC3ol0YK','lawyer','2025-12-05 13:51:46',NULL,NULL);


-- ----------------------------
-- Table structure for `password_resets`
-- ----------------------------
DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `email` varchar(190) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT 0,
  `verified_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`email`),
  UNIQUE KEY `uniq_email` (`email`),
  UNIQUE KEY `uq_email` (`email`),
  KEY `idx_password_resets_expires` (`expires_at`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of `password_resets`
-- ----------------------------


-- ----------------------------
-- Table structure for `routing_rules`
-- ----------------------------
DROP TABLE IF EXISTS `routing_rules`;
CREATE TABLE `routing_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_type` varchar(255) NOT NULL,
  `assigned_lawyer` int(11) NOT NULL,
  `cc_emails` text DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `assigned_lawyer` (`assigned_lawyer`),
  CONSTRAINT `routing_rules_ibfk_1` FOREIGN KEY (`assigned_lawyer`) REFERENCES `lawyers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of `routing_rules`
-- ----------------------------
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('3','Broadband Business (BB)','4','rgserrano@globe.com.ph','1','2025-12-04 19:58:14');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('4','Enterprise Data and Strategic Services (EDS)','4','rgserrano@globe.com.ph','1','2025-12-04 19:58:47');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('5','Product Engineering and Digital Growth (PEDG)','2','kent.perez@globe.com.ph','1','2025-12-04 19:59:34');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('6','Consumer Mobile Business (CMB)','6','rgserrano@globe.com.ph','1','2025-12-05 13:52:28');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('7','Channel Management (CMG)','6','rgserrano@globe.com.ph','1','2025-12-05 13:53:49');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('8','Marketing (MKT)','6','rgserrano@globe.com.ph','1','2025-12-05 13:54:15');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('9','Office of the Chief Commercial Officer (CCO)','6','rgserrano@globe.com.ph','1','2025-12-05 14:00:39');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('10','Network Technical Group (NTG)','4','rgserrano@globe.com.ph','1','2025-12-05 14:01:11');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('11','Information Services Group (ISG)','4','rgserrano@globe.com.ph','1','2025-12-05 14:02:29');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('12','Corporate and Legal Services Group (CLSG)','4','rgserrano@globe.com.ph','1','2025-12-05 14:02:49');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('13','Corporate Communications (CorpComm)','4','rgserrano@globe.com.ph','1','2025-12-05 14:03:05');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('14','ST Telemedia (STT)','6','rgserrano@globe.com.ph','1','2025-12-05 14:07:19');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('15','Office of Strategy Mgmt & Customer Experience (OSMCX)','6','rgserrano@globe.com.ph','1','2025-12-05 14:08:05');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('16','Finance & Administration (FBA)','6','rgserrano@globe.com.ph','1','2025-12-05 14:08:24');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('17','Human Resources (HR)','6','rgserrano@globe.com.ph','1','2025-12-05 14:08:37');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('18','Information Services Group (ISG)','6','rgserrano@globe.com.ph','1','2025-12-05 14:08:55');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('19','Information Security and Data Privacy (ISDP)','6','rgserrano@globe.com.ph','1','2025-12-05 14:09:30');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('20','Key Accounts - Hyperscaler','4','rgserrano@globe.com.ph','1','2025-12-05 14:10:08');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('21','Key Accounts - Wholesale 2','4','rgserrano@globe.com.ph','1','2025-12-05 14:12:54');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('22','Key Accounts - Conglo 2','4','rgserrano@globe.com.ph','1','2025-12-05 14:15:29');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('23','Strategic Verticals - FSI 1','4','rgserrano@globe.com.ph','1','2025-12-05 14:15:51');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('24','Strategic Verticals - IT & BPM 1','4','rgserrano@globe.com.ph','1','2025-12-05 14:16:08');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('25','Strategic Verticals - IT & BPM 3','4','rgserrano@globe.com.ph','1','2025-12-05 14:16:21');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('26','Strategic Verticals - Supply Chain 2','4','rgserrano@globe.com.ph','1','2025-12-05 14:16:30');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('27','Strategic Verticals - Supply Chain 4','4','rgserrano@globe.com.ph','1','2025-12-05 14:16:43');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('28','Geo & OMNI - NCL','4','rgserrano@globe.com.ph','1','2025-12-05 16:00:47');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('29','Geo & OMNI - NGMA','4','rgserrano@globe.com.ph','1','2025-12-05 16:01:11');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('30','Geo & OMNI - SGMA 2','4','rgserrano@globe.com.ph','1','2025-12-05 16:01:24');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('31','Geo & OMNI - VIS 2','4','rgserrano@globe.com.ph','1','2025-12-05 16:01:39');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('32','Geo & OMNI - OMNI','4','rgserrano@globe.com.ph','1','2025-12-05 16:02:30');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('33','Partner Lifecycle Management (PLM)','4','rgserrano@globe.com.ph','1','2025-12-05 16:02:45');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('34','GTIBH','4','rgserrano@globe.com.ph','1','2025-12-05 16:03:06');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('36','Key Accounts - Wholesale 1','6','rgserrano@globe.com.ph','1','2025-12-05 16:04:18');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('37','Key Accounts - Conglo 1','6','rgserrano@globe.com.ph','1','2025-12-05 16:04:28');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('38','Key Accounts - Conglo 3','6','rgserrano@globe.com.ph','1','2025-12-05 16:04:36');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('39','Strategic Verticals - FSI 2','6','rgserrano@globe.com.ph','1','2025-12-05 16:04:47');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('40','Strategic Verticals - IT & BPM 2','6','rgserrano@globe.com.ph','1','2025-12-05 16:04:57');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('41','Strategic Verticals - Supply Chain 1','6','rgserrano@globe.com.ph','1','2025-12-05 16:05:05');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('42','Strategic Verticals - Supply Chain 3','6','rgserrano@globe.com.ph','1','2025-12-05 16:05:20');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('43','Strategic Verticals - GEO VisMin','6','rgserrano@globe.com.ph','1','2025-12-05 16:05:28');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('44','Geo & OMNI - SL','6','rgserrano@globe.com.ph','1','2025-12-05 16:07:27');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('45','Geo & OMNI - SGMA 1','6','rgserrano@globe.com.ph','1','2025-12-05 16:08:00');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('47','Geo & OMNI - VIS 1','6','rgserrano@globe.com.ph','1','2025-12-05 16:08:30');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('48','Geo & OMNI - MIN','6','rgserrano@globe.com.ph','1','2025-12-05 16:09:14');
INSERT INTO `routing_rules` (`id`,`ticket_type`,`assigned_lawyer`,`cc_emails`,`active`,`created_at`) VALUES ('49','Government','6','rgserrano@globe.com.ph','1','2025-12-05 16:09:22');


-- ----------------------------
-- Table structure for `ticket_alert_ack`
-- ----------------------------
DROP TABLE IF EXISTS `ticket_alert_ack`;
CREATE TABLE `ticket_alert_ack` (
  `ticket_id` int(11) NOT NULL,
  `lawyer_id` int(11) NOT NULL,
  `alert_type` enum('pre_overdue_24h') NOT NULL,
  `acknowledged_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ticket_id`,`lawyer_id`,`alert_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of `ticket_alert_ack`
-- ----------------------------
INSERT INTO `ticket_alert_ack` (`ticket_id`,`lawyer_id`,`alert_type`,`acknowledged_at`) VALUES ('6','1','pre_overdue_24h','2025-10-29 08:26:42');
INSERT INTO `ticket_alert_ack` (`ticket_id`,`lawyer_id`,`alert_type`,`acknowledged_at`) VALUES ('321','1','pre_overdue_24h','2025-11-25 02:31:11');


-- ----------------------------
-- Table structure for `ticket_files`
-- ----------------------------
DROP TABLE IF EXISTS `ticket_files`;
CREATE TABLE `ticket_files` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint(20) unsigned NOT NULL,
  `original` varchar(255) NOT NULL,
  `saved_as` varchar(255) NOT NULL,
  `mime` varchar(120) DEFAULT NULL,
  `size_bytes` bigint(20) unsigned DEFAULT NULL,
  `uploaded_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_files_ticket` (`ticket_id`),
  CONSTRAINT `fk_files_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of `ticket_files`
-- ----------------------------
INSERT INTO `ticket_files` (`id`,`ticket_id`,`original`,`saved_as`,`mime`,`size_bytes`,`uploaded_at`) VALUES ('9','9','HOUSE RENT AUDIT.xlsx','9_d1c6b1f87d.xlsx','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','8839','2025-10-27 12:45:02');
INSERT INTO `ticket_files` (`id`,`ticket_id`,`original`,`saved_as`,`mime`,`size_bytes`,`uploaded_at`) VALUES ('10','10','RESUME PEREZ.pdf','10_9654be4228.pdf','application/pdf','208557','2025-10-27 12:52:17');
INSERT INTO `ticket_files` (`id`,`ticket_id`,`original`,`saved_as`,`mime`,`size_bytes`,`uploaded_at`) VALUES ('11','11','HOUSE RENT AUDIT.xlsx','11_c5ed015312.xlsx','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','8839','2025-10-28 05:21:23');
INSERT INTO `ticket_files` (`id`,`ticket_id`,`original`,`saved_as`,`mime`,`size_bytes`,`uploaded_at`) VALUES ('12','12','ERD.png','12_7509c8eaa4.png','image/png','266318','2025-11-08 17:28:52');
INSERT INTO `ticket_files` (`id`,`ticket_id`,`original`,`saved_as`,`mime`,`size_bytes`,`uploaded_at`) VALUES ('13','14','ERD.png','14_3c80ef1b3f.png','image/png','266318','2025-11-08 17:30:18');
INSERT INTO `ticket_files` (`id`,`ticket_id`,`original`,`saved_as`,`mime`,`size_bytes`,`uploaded_at`) VALUES ('14','15','ERD.png','15_277ed34365.png','image/png','266318','2025-11-08 17:39:21');
INSERT INTO `ticket_files` (`id`,`ticket_id`,`original`,`saved_as`,`mime`,`size_bytes`,`uploaded_at`) VALUES ('15','16','Game.jpg','16_76e77b8990.jpg','image/jpeg','262284','2025-11-12 13:46:12');
INSERT INTO `ticket_files` (`id`,`ticket_id`,`original`,`saved_as`,`mime`,`size_bytes`,`uploaded_at`) VALUES ('16','23','Proof 2.png','23_adf854e1f3.png','image/png','305189','2025-11-20 11:26:25');
INSERT INTO `ticket_files` (`id`,`ticket_id`,`original`,`saved_as`,`mime`,`size_bytes`,`uploaded_at`) VALUES ('17','24','PROJECT 1.jpg','24_a307d2fc75.jpg','image/jpeg','731980','2025-11-21 02:06:04');
INSERT INTO `ticket_files` (`id`,`ticket_id`,`original`,`saved_as`,`mime`,`size_bytes`,`uploaded_at`) VALUES ('18','25','FOR UPWORK.pdf','25_56cbd5a9ba.pdf','application/pdf','494253','2025-11-24 21:25:10');
INSERT INTO `ticket_files` (`id`,`ticket_id`,`original`,`saved_as`,`mime`,`size_bytes`,`uploaded_at`) VALUES ('19','26','FOR UPWORK.pdf','26_ef9fd132ae.pdf','application/pdf','494253','2025-11-24 21:27:19');
INSERT INTO `ticket_files` (`id`,`ticket_id`,`original`,`saved_as`,`mime`,`size_bytes`,`uploaded_at`) VALUES ('22','29','PROJECT 1.jpg','29_c2bbb59e8a.jpg','image/jpeg','731980','2025-11-24 21:57:32');
INSERT INTO `ticket_files` (`id`,`ticket_id`,`original`,`saved_as`,`mime`,`size_bytes`,`uploaded_at`) VALUES ('23','30','FOR UPWORK.pdf','30_f9259fe9fc.pdf','application/pdf','494253','2025-11-25 09:43:18');
INSERT INTO `ticket_files` (`id`,`ticket_id`,`original`,`saved_as`,`mime`,`size_bytes`,`uploaded_at`) VALUES ('24','31','Chapter 4 Drafts.xlsx','31_4ba219877d.xlsx','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','15584','2025-12-04 20:00:09');
INSERT INTO `ticket_files` (`id`,`ticket_id`,`original`,`saved_as`,`mime`,`size_bytes`,`uploaded_at`) VALUES ('25','32','ARISE ORG CHART-CREATIVES DEPARTMENT.jpg','32_fd7300f6a9.jpg','image/jpeg','58681','2025-12-05 13:55:39');


-- ----------------------------
-- Table structure for `tickets`
-- ----------------------------
DROP TABLE IF EXISTS `tickets`;
CREATE TABLE `tickets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
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
  `status` enum('Pending','In Review','For Revisions','Completed') NOT NULL DEFAULT 'Pending',
  `remarks` text DEFAULT NULL,
  `email_24h_sent` tinyint(1) NOT NULL DEFAULT 0,
  `email_24h_sent_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of `tickets`
-- ----------------------------
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('9','GDA-159140','2025-10-27 12:45:02','Jerry S. Paje','jerrypaje045@gmail.com','Normal','2025-11-03',NULL,'STT|FRANCINE','','Atty. Francine Turo <ksperez.degullado@gmail.com>','ksperez.degullado@gmail.com','wqefqfq3f','Data Sharing Agreement (DSA)','','qwefqawefqa','qwefqwef','Both parties share/process data','','qwefqwfe','N/A','Pending',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('10','GDA-486374','2025-10-27 12:52:17','John Lexter Ilao','kentperez30@gmail.com','Normal','2025-11-03','2025-10-28 05:22:54','OSMCX|FRANCINE','','Atty. Francine Turo <ksperez.degullado@gmail.com>','ksperez.degullado@gmail.com','wfqaewfq','Non-Disclosure Agreement (NDA)','','qwefqwef','ewqfqefq','Partner processes Globe data','','qqwefqwe','N/A','Completed',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('11','GDA-093024','2025-10-28 05:21:23','John Wick','jerrypaje045@gmail.com','Normal','2025-11-04',NULL,'B2B','','Atty. Francine Turo <ksperez.degullado@gmail.com>','kentperez30@gmail.com','lorem ipsum lorem ipsum','Non-Disclosure Agreement (NDA)','','Customer','Vendor','Both parties share/process data','','Lorem Ipsum','N/A','Pending',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('12','GDA-944185','2025-11-08 17:28:52','John Lexter Ilao','kentperez30@gmail.com','Normal','2025-11-14',NULL,'OSMCX|FRANCINE','','Atty. Francine Turo <ksperez.degullado@gmail.com>','ksperez.degullado@gmail.com','wfqaewfq','Non-Disclosure Agreement (NDA)','','qwefqwef','ewqfqefq','Both parties share/process data','','qqwefqwe','N/A','Pending',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('13','GDA-247273','2025-11-08 17:29:26','John Lexter Ilao','kentperez30@gmail.com','Normal','2025-11-14',NULL,'OSMCX|FRANCINE','','Atty. Francine Turo <ksperez.degullado@gmail.com>','ksperez.degullado@gmail.com','wfqaewfq','Non-Disclosure Agreement (NDA)','','qwefqwef','ewqfqefq','Partner processes Globe data','','qqwefqwe','N/A','Pending',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('14','GDA-645802','2025-11-08 17:30:18','Lod Idle Bautista','kentnotcant@gmail.com','Normal','2025-11-14',NULL,'MKT|FRANCINE','','Atty. Francine Turo <ksperez.degullado@gmail.com>','kentperez30@gmail.com','asdfwefwef','Non-Disclosure Agreement (NDA)','','wefwef','wdfwsds','Both parties share/process data','','wqefwefwaq','N/A','Pending',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('15','GDA-062875','2025-11-08 17:39:21','Lod Idle Bautista','kentnotcant@gmail.com','Normal','2025-11-14',NULL,'MKT|FRANCINE','','Atty. Francine Turo <ksperez.degullado@gmail.com>','kentperez30@gmail.com','asdfwefwef','Non-Disclosure Agreement (NDA)','','wefwef','wdfwsds','Both parties share/process data','','wqefwefwaq','N/A','Pending',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('16','GDA-327414','2025-11-12 13:46:12','John Dela Cruz','kentnotcant@gmail.com','Normal','2025-11-19','2025-11-25 09:50:47','OSMCX|FRANCINE','','Atty. Francine Turo <ksperez.degullado@gmail.com>','ksperez.degullado@gmail.com','awsdfaewrferfqaefqa','Data Sharing Agreement (DSA)','','ewrfaedfaw','aqdfaqerf','Partner processes Globe data','','qerfdfgsf','N/A','Completed',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('17','GDA-971073','2025-11-12 13:52:13','John Lexter Ilao','kentperez30@gmail.com','Normal','2025-11-19',NULL,'OSMCX|FRANCINE','','Atty. Francine Turo <ksperez.degullado@gmail.com>','ksperez.degullado@gmail.com','wfqaewfq','Non-Disclosure Agreement (NDA)','','qwefqwef','ewqfqefq','Partner processes Globe data','','qqwefqwe','N/A','For Revisions',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('18','GDA-667301','2025-11-12 13:54:50','John Lexter Ilao','kentperez30@gmail.com','Normal','2025-11-19',NULL,'OSMCX|FRANCINE','','Atty. Francine Turo <ksperez.degullado@gmail.com>','ksperez.degullado@gmail.com','wfqaewfq','Non-Disclosure Agreement (NDA)','','qwefqwef','ewqfqefq','Partner processes Globe data','','qqwefqwe','N/A','For Revisions',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('19','GDA-532951','2025-11-12 13:56:01','John Lexter Ilao','kentperez30@gmail.com','Normal','2025-11-19','2025-11-12 14:21:51','OSMCX|FRANCINE','','Atty. Francine Turo <ksperez.degullado@gmail.com>','ksperez.degullado@gmail.com','wfqaewfq','Non-Disclosure Agreement (NDA)','','qwefqwef','ewqfqefq','Partner processes Globe data','','qqwefqwe','N/A','Completed',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('20','GDA-316823','2025-11-12 13:57:46','John Lexter Ilao','kentperez30@gmail.com','Normal','2025-11-19',NULL,'OSMCX|FRANCINE','','Atty. Francine Turo <ksperez.degullado@gmail.com>','ksperez.degullado@gmail.com','wfqaewfq','Non-Disclosure Agreement (NDA)','','qwefqwef','ewqfqefq','Partner processes Globe data','','qqwefqwe','N/A','For Revisions',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('21','GDA-291394','2025-11-12 14:10:15','John Lexter Ilao','kentperez30@gmail.com','Normal','2025-11-19','2025-11-12 14:21:47','OSMCX|FRANCINE','','','','wfqaewfq','Non-Disclosure Agreement (NDA)','','qwefqwef','ewqfqefq','Partner processes Globe data','','qqwefqwe','N/A','Completed',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('22','GDA-843322','2025-11-12 14:10:33','John Lexter Ilao','kentperez30@gmail.com','Normal','2025-11-19',NULL,'OSMCX|FRANCINE','','Atty. Francine Turo <ksperez.degullado@gmail.com>','ksperez.degullado@gmail.com','wfqaewfq','Non-Disclosure Agreement (NDA)','','qwefqwef','ewqfqefq','Partner processes Globe data','','qqwefqwe','N/A','For Revisions',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('23','GDA-237342','2025-11-20 11:26:25','John Lexter Ilao','kentperez30@gmail.com','Normal','2025-11-27',NULL,'OSMCX|FRANCINE','','Atty. Francine Turo <ksperez.degullado@gmail.com>','ksperez.degullado@gmail.com','wfqaewfq','Non-Disclosure Agreement (NDA)','','qwefqwef','ewqfqefq','Partner processes Globe data','','qqwefqwe','N/A','Pending',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('24','GDA-722603','2025-11-21 02:06:04','Kim Dominic Valenzuela','ksperez.degullado@gmail.com','Normal','2025-11-28','2025-12-05 14:11:33','CMB|FRANCINE','','Atty. Francine Turo <ksperez.degullado@gmail.com>','kentperez30@gmail.com','erfqeafw2ef','Non-Disclosure Agreement (NDA)','','regfwaf','qewrfgefvgweas','OTHER','werqfqaewfaqwergfewrgv','wfwqerdfgwergf','N/A','Completed',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('25','GDA-792654','2025-11-24 21:25:10','Jio Kein Siku','kentnotcant@gmail.com','Normal',NULL,NULL,'CorpComm|ALEX','','Atty. Alex Austria <kentnotcant@gmail.com>','kentnotcant@gmail.com','ewrgfwesrgwsergwseg','Data Sharing Agreement (DSA)','','wergwergwe','wergwerg','Globe processes partner data','','wergwergwergwefrg','N/A','For Revisions',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('26','GDA-219315','2025-11-24 21:27:19','John Lexter Ilao','kentperez30@gmail.com','Normal',NULL,'2025-11-25 09:11:55','OSMCX|FRANCINE','','Atty. Francine Turo <ksperez.degullado@gmail.com>','ksperez.degullado@gmail.com','wfqaewfq','Non-Disclosure Agreement (NDA)','','qwefqwef','ewqfqefq','Partner processes Globe data','','qqwefqwe','N/A','Completed',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('29','GDA-391453','2025-11-24 21:57:32','John Lexter Ilao','kentperez30@gmail.com','Normal','2025-12-01','2025-11-25 00:30:30','OSMCX|FRANCINE','','Atty. Francine Turo <ksperez.degullado@gmail.com>','ksperez.degullado@gmail.com','wfqaewfq','Non-Disclosure Agreement (NDA)','','qwefqwef','ewqfqefq','Partner processes Globe data','','qqwefqwe','N/A','Completed',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('30','GDA-652108','2025-11-25 09:43:18','Kent Steven Perez','kentnotcant@gmail.com','Normal','2025-12-02','2025-12-05 14:11:59','CMG|FRANCINE','','Atty. Francine Turo <ksperez.degullado@gmail.com>','kentperez30@gmail.com','Lorem Ipsum','Data Sharing Agreement (DSA)','','Lorem','Ipsum','Both parties share/process data','','qqwefqwe','N/A','Completed',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('31','GDA-957681','2025-12-04 20:00:09','Kent Steven Perez','kentperez30@gmail.com','Normal','2025-12-12','2025-12-04 20:20:58','Product Engineering and Digital Growth (PEDG)',NULL,'ksperez.degullado@gmail.com','ksperez.degullado@gmail.com','gerfwagewrgw','Data Sharing Agreement (DSA)','','ergwerg','ewrgfewg','Globe processes partner data','','ewgfgwes','N/A','Completed',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('32','GDA-039985','2025-12-05 13:55:39','John Lexter Ilao','kentnotcant@gmail.com','Normal','2025-12-15',NULL,'Product Engineering and Digital Growth (PEDG)',NULL,'ksperez.degullado@gmail.com','kent.perez@globe.com.ph','sddefwqafqwefq','Data Sharing Agreement (DSA)','','qewfqwef','dgfw4e5tyrefwhtb','Partner processes Globe data','','qergwqregw','N/A','Pending',NULL,'0',NULL);


-- ----------------------------
-- Table structure for `users`
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `name` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of `users`
-- ----------------------------

SET foreign_key_checks = 1;
