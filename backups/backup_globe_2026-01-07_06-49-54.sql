-- Backup of globe
-- Generated: 2026-01-07T06:49:54+01:00

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
INSERT INTO `lawyers` (`id`,`name`,`email`,`pass_hash`,`role`,`created_at`,`archived_at`,`archived_by`) VALUES ('2','Kent Steven D. Perez','ksperez.degullado@gmail.com','$2y$10$DvSO/5eH/RwToYt5ApfUT.q0zTsPpWr5wXvhio/DxvMfoyQ8QLLne','lawyer','2025-11-11 11:10:14',NULL,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of `ticket_files`
-- ----------------------------
INSERT INTO `ticket_files` (`id`,`ticket_id`,`original`,`saved_as`,`mime`,`size_bytes`,`uploaded_at`) VALUES ('1','33','CH-1-5-DEFENDED-REVISIONS-FINAL.pdf','33_a096cc386d.pdf','application/pdf','16295879','2026-01-07 11:24:43');
INSERT INTO `ticket_files` (`id`,`ticket_id`,`original`,`saved_as`,`mime`,`size_bytes`,`uploaded_at`) VALUES ('2','34','CH-1-5-DEFENDED-REVISIONS-FINAL.pdf','34_e86a4c0a52.pdf','application/pdf','16295879','2026-01-07 11:30:05');
INSERT INTO `ticket_files` (`id`,`ticket_id`,`original`,`saved_as`,`mime`,`size_bytes`,`uploaded_at`) VALUES ('3','35','CH-1-5-DEFENDED-REVISIONS-FINAL.pdf','35_dcc1a4ccc9.pdf','application/pdf','16295879','2026-01-07 11:34:41');
INSERT INTO `ticket_files` (`id`,`ticket_id`,`original`,`saved_as`,`mime`,`size_bytes`,`uploaded_at`) VALUES ('4','36','CH-1-5-DEFENDED-REVISIONS-FINAL.pdf','36_929cb7cd85.pdf','application/pdf','16295879','2026-01-07 11:35:11');
INSERT INTO `ticket_files` (`id`,`ticket_id`,`original`,`saved_as`,`mime`,`size_bytes`,`uploaded_at`) VALUES ('5','37','CH-1-5-DEFENDED-REVISIONS-FINAL.pdf','37_f9d1727580.pdf','application/pdf','16295879','2026-01-07 11:38:06');
INSERT INTO `ticket_files` (`id`,`ticket_id`,`original`,`saved_as`,`mime`,`size_bytes`,`uploaded_at`) VALUES ('6','38','CH-1-5-DEFENDED-REVISIONS-FINAL.pdf','38_b08c4122ae.pdf','application/pdf','16295879','2026-01-07 11:39:48');
INSERT INTO `ticket_files` (`id`,`ticket_id`,`original`,`saved_as`,`mime`,`size_bytes`,`uploaded_at`) VALUES ('7','39','ARISE LETTER.pdf','39_64c66bff9c.pdf','application/pdf','539003','2026-01-07 11:41:52');
INSERT INTO `ticket_files` (`id`,`ticket_id`,`original`,`saved_as`,`mime`,`size_bytes`,`uploaded_at`) VALUES ('8','40','ARISE LETTER.pdf','40_9aa09ae7e6.pdf','application/pdf','539003','2026-01-07 11:59:33');
INSERT INTO `ticket_files` (`id`,`ticket_id`,`original`,`saved_as`,`mime`,`size_bytes`,`uploaded_at`) VALUES ('9','41','ARISE LETTER.pdf','41_fe8e05fef7.pdf','application/pdf','539003','2026-01-07 12:07:48');
INSERT INTO `ticket_files` (`id`,`ticket_id`,`original`,`saved_as`,`mime`,`size_bytes`,`uploaded_at`) VALUES ('10','42','ARISE LETTER.pdf','42_055e35b6bb.pdf','application/pdf','539003','2026-01-07 12:08:12');
INSERT INTO `ticket_files` (`id`,`ticket_id`,`original`,`saved_as`,`mime`,`size_bytes`,`uploaded_at`) VALUES ('11','43','ARISE LETTER.pdf','43_88d33c3260.pdf','application/pdf','539003','2026-01-07 12:46:40');


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
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of `tickets`
-- ----------------------------
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('33','GDA-511852','2026-01-07 11:24:43','Reign Gel Ogma','kentnotcant@gmail.com','Normal','2026-01-14',NULL,'Product Engineering and Digital Growth (PEDG)',NULL,'ksperez.degullado@gmail.com','kent.perez@globe.com.ph','lorem ipsum','Non-Disclosure Agreement (NDA)','','sfdferfe','erertwew4r','Globe processes partner data','','rehehhert','N/A','Pending',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('34','GDA-681523','2026-01-07 11:30:05','Reign Gel Ogma','kentnotcant@gmail.com','Normal','2026-01-14',NULL,'Product Engineering and Digital Growth (PEDG)',NULL,'ksperez.degullado@gmail.com','kent.perez@globe.com.ph','fewfwdsf','Non-Disclosure Agreement (NDA)','','dssfew','dsffwefw','Partner processes Globe data','','dsfrefw','N/A','Pending',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('35','GDA-028625','2026-01-07 11:34:41','Reign Gel Ogma','kentnotcant@gmail.com','Normal','2026-01-14',NULL,'Product Engineering and Digital Growth (PEDG)',NULL,'ksperez.degullado@gmail.com','kent.perez@globe.com.ph','fewfwdsf','Non-Disclosure Agreement (NDA)','','dssfew','dsffwefw','Partner processes Globe data','','dsfrefw','N/A','Pending',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('36','GDA-375236','2026-01-07 11:35:11','Reign Gel Ogma','kentnotcant@gmail.com','Normal','2026-01-14',NULL,'Product Engineering and Digital Growth (PEDG)',NULL,'ksperez.degullado@gmail.com','kent.perez@globe.com.ph','fewfwdsf','Non-Disclosure Agreement (NDA)','','dssfew','dsffwefw','Partner processes Globe data','','dsfrefw','N/A','Pending',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('37','GDA-372074','2026-01-07 11:38:06','Jerry S. Paje','ksd.perez13@gmail.com','Normal','2026-01-14',NULL,'Product Engineering and Digital Growth (PEDG)',NULL,'ksperez.degullado@gmail.com','kent.perez@globe.com.ph','dbdfebe','Data Sharing Agreement (DSA)','','rgerge','gfergeg','Both parties share/process data','','fgrgrwg','N/A','Pending',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('38','GDA-472849','2026-01-07 11:39:48','Jerry S. Paje','ksd.perez13@gmail.com','Normal','2026-01-14',NULL,'Product Engineering and Digital Growth (PEDG)','Key Accounts - Conglo 3','ksperez.degullado@gmail.com','kent.perez@globe.com.ph','dbdfebe','Data Sharing Agreement (DSA)','','rgerge','gfergeg','Both parties share/process data','','fgrgrwg','N/A','Pending',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('39','GDA-688130','2026-01-07 11:41:52','Lod Idle Bautista','kentnotcant@gmail.com','Normal','2026-01-14',NULL,'Product Engineering and Digital Growth (PEDG)',NULL,'ksperez.degullado@gmail.com','kent.perez@globe.com.ph','cv cv cvdfgd','Data Sharing Agreement (DSA)','','fgfgr','dsggsgwr','Both parties share/process data','','sfergerg','N/A','Pending',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('40','GDA-970808','2026-01-07 11:59:33','Lirerio Jonathan','kentnotcant@gmail.com','Normal','2026-01-14',NULL,'Product Engineering and Digital Growth (PEDG)',NULL,'ksperez.degullado@gmail.com','kent.perez@globe.com.ph','ffaarefaer','Data Sharing Agreement (DSA)','','dfgst','fdgsreg','Partner processes Globe data','','vaergea','N/A','Pending',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('41','GDA-771330','2026-01-07 12:07:48','Lirerio Jonathan','kentnotcant@gmail.com','Normal','2026-01-14',NULL,'Product Engineering and Digital Growth (PEDG)',NULL,'ksperez.degullado@gmail.com','kent.perez@globe.com.ph','ffaarefaer','Data Sharing Agreement (DSA)','','dfgst','fdgsreg','Partner processes Globe data','','vaergea','N/A','Pending',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('42','GDA-911716','2026-01-07 12:08:12','Lirerio Jonathan','kentnotcant@gmail.com','Normal','2026-01-14',NULL,'Product Engineering and Digital Growth (PEDG)',NULL,'ksperez.degullado@gmail.com','kent.perez@globe.com.ph','ffaarefaer','Data Sharing Agreement (DSA)','','dfgst','fdgsreg','Partner processes Globe data','','vaergea','N/A','Pending',NULL,'0',NULL);
INSERT INTO `tickets` (`id`,`ticket_code`,`created_at`,`full_name`,`email`,`priority`,`due_date`,`completed_at`,`grp`,`tribe`,`assigned_lawyer`,`cc_emails`,`summary`,`contract_type`,`contract_other`,`customer`,`vendor`,`pd_nature`,`pd_other_text`,`clauses`,`doc_link`,`status`,`remarks`,`email_24h_sent`,`email_24h_sent_at`) VALUES ('43','GDA-261331','2026-01-07 12:46:40','Lirerio Jonathan','kentnotcant@gmail.com','Normal','2026-01-14',NULL,'Product Engineering and Digital Growth (PEDG)',NULL,'ksperez.degullado@gmail.com','kent.perez@globe.com.ph','ffaarefaer','Data Sharing Agreement (DSA)','','dfgst','fdgsreg','Partner processes Globe data','','vaergea','N/A','For Revisions','Not done yet...','0',NULL);


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
