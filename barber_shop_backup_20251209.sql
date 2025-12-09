-- MySQL dump 10.13  Distrib 8.0.30, for macos12 (arm64)
--
-- Host: 127.0.0.1    Database: barber_shop
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `appointments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_id` int NOT NULL,
  `barber_id` int NOT NULL,
  `procedure_id` int NOT NULL,
  `date` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `duration` int NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_added` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `date_last_update` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `date_canceled` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `cancellation_reason` longtext COLLATE utf8mb4_unicode_ci,
  `notes` longtext COLLATE utf8mb4_unicode_ci,
  `confirmation_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `confirmed_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_6A41727AC05FB297` (`confirmation_token`),
  KEY `IDX_6A41727A19EB6921` (`client_id`),
  KEY `IDX_6A41727ABFF2FEF2` (`barber_id`),
  KEY `IDX_6A41727A1624BCD2` (`procedure_id`),
  KEY `idx_appointments_date` (`date`),
  KEY `idx_appointments_barber_date` (`barber_id`,`date`),
  KEY `idx_appointments_client_date` (`client_id`,`date`),
  KEY `idx_appointments_status` (`status`),
  CONSTRAINT `FK_6A41727A1624BCD2` FOREIGN KEY (`procedure_id`) REFERENCES `procedures` (`id`),
  CONSTRAINT `FK_6A41727A19EB6921` FOREIGN KEY (`client_id`) REFERENCES `user` (`id`),
  CONSTRAINT `FK_6A41727ABFF2FEF2` FOREIGN KEY (`barber_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointments`
--

LOCK TABLES `appointments` WRITE;
/*!40000 ALTER TABLE `appointments` DISABLE KEYS */;
INSERT INTO `appointments` VALUES (1,5,4,16,'2025-12-05 15:00:00',45,'cancelled','2025-12-03 13:51:10','2025-12-03 14:36:43','2025-12-03 14:36:43','Отменен за промяна на час',NULL,NULL,NULL),(2,5,5,15,'2025-12-06 09:00:00',15,'cancelled','2025-12-03 13:51:36','2025-12-03 13:52:24','2025-12-03 13:52:24','Отменен от бръснар',NULL,NULL,NULL),(3,1,5,1,'2025-12-01 13:00:00',50,'confirmed','2025-12-03 13:55:29','2025-12-03 14:02:12','2025-12-03 13:55:00',NULL,NULL,NULL,NULL),(4,2,5,6,'2025-12-03 15:00:00',50,'completed','2025-12-03 14:44:10','2025-12-04 19:41:01',NULL,NULL,'',NULL,NULL),(5,2,5,6,'2025-12-03 13:00:00',50,'completed','2025-12-03 14:55:56','2025-12-04 21:35:32',NULL,NULL,'',NULL,NULL),(6,4,4,4,'2025-12-05 10:00:00',25,'completed','2025-12-04 21:36:35','2025-12-05 10:02:02',NULL,NULL,NULL,NULL,NULL),(7,4,5,4,'2025-12-05 17:00:00',25,'confirmed','2025-12-05 08:53:34','2025-12-05 08:53:34',NULL,NULL,NULL,NULL,NULL),(8,8,4,1,'2025-12-05 15:00:00',40,'confirmed','2025-12-05 14:49:53','2025-12-05 14:49:53',NULL,NULL,NULL,NULL,NULL),(9,8,4,2,'2025-12-08 10:30:00',25,'cancelled','2025-12-05 21:32:32','2025-12-05 21:39:49','2025-12-05 21:39:49','Отменен за промяна на час',NULL,NULL,NULL),(10,8,4,1,'2025-12-12 12:30:00',40,'cancelled','2025-12-05 21:38:34','2025-12-05 21:47:06','2025-12-05 21:47:06','Отменен за промяна на час',NULL,NULL,NULL),(11,8,5,4,'2025-12-09 13:30:00',25,'cancelled','2025-12-05 21:40:04','2025-12-05 21:40:41','2025-12-05 21:40:41','Отменен от клиент',NULL,NULL,NULL),(12,8,5,1,'2025-12-10 14:00:00',40,'cancelled','2025-12-05 21:43:36','2025-12-05 21:43:59','2025-12-05 21:43:59','Отменен за промяна на час',NULL,NULL,NULL),(13,8,4,4,'2025-12-11 13:30:00',25,'cancelled','2025-12-05 21:44:16','2025-12-05 21:44:46','2025-12-05 21:44:46','Отменен от клиент',NULL,NULL,NULL),(14,8,4,4,'2025-12-18 17:00:00',25,'confirmed','2025-12-05 21:46:46','2025-12-05 21:46:46',NULL,NULL,NULL,NULL,NULL),(15,8,4,4,'2025-12-22 13:30:00',25,'confirmed','2025-12-05 21:47:20','2025-12-05 21:47:20',NULL,NULL,NULL,NULL,NULL),(16,2,4,2,'2025-12-09 11:00:00',25,'confirmed','2025-12-08 08:38:38','2025-12-08 08:38:38',NULL,NULL,NULL,NULL,NULL),(17,10,4,1,'2025-12-10 14:00:00',40,'pending_confirmation','2025-12-09 16:22:06','2025-12-09 16:22:06',NULL,NULL,NULL,'db7756c39f075eb921de3c71f10fbe6fc32fb7864fe009993e3fd0bfc77517aa',NULL),(18,11,4,1,'2025-12-10 14:00:00',40,'confirmed','2025-12-09 16:22:30','2025-12-09 16:23:18',NULL,NULL,NULL,'1518e7fc2c1f9885ffd43414aa37b25120f1ed4d8a6bd412d6a4b3072ed879a6','2025-12-09 16:23:18'),(19,12,4,2,'2025-12-09 17:30:00',25,'confirmed','2025-12-09 17:04:36','2025-12-09 17:05:06',NULL,NULL,NULL,'315bd4118345ce9cfa5c4cb7a49456c24a87ae07e197c239f60be519f3b54423','2025-12-09 17:05:06'),(20,12,4,1,'2025-12-10 15:00:00',40,'cancelled','2025-12-09 17:07:37','2025-12-09 17:07:59','2025-12-09 17:07:59','Отказана от клиента',NULL,'ea080548c14c661839b37225c92a4103cd279de393f2a97caa3b80efece32403',NULL),(21,13,5,2,'2025-12-19 15:00:00',25,'confirmed','2025-12-09 17:35:55','2025-12-09 17:36:26',NULL,NULL,NULL,'cb9cf6ccb3012beee70a2835d56705242e4ac4db0ce73f945d109cf264ec1f67','2025-12-09 17:36:26');
/*!40000 ALTER TABLE `appointments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `barber_procedure`
--

DROP TABLE IF EXISTS `barber_procedure`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `barber_procedure` (
  `id` int NOT NULL AUTO_INCREMENT,
  `barber_id` int NOT NULL,
  `procedure_id` int NOT NULL,
  `can_perform` tinyint(1) NOT NULL DEFAULT '1',
  `valid_from` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `valid_until` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_E8A4E019BFF2FEF2` (`barber_id`),
  KEY `IDX_E8A4E0191624BCD2` (`procedure_id`),
  KEY `idx_barber_procedure` (`barber_id`,`procedure_id`),
  CONSTRAINT `FK_E8A4E0191624BCD2` FOREIGN KEY (`procedure_id`) REFERENCES `procedures` (`id`),
  CONSTRAINT `FK_E8A4E019BFF2FEF2` FOREIGN KEY (`barber_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `barber_procedure`
--

LOCK TABLES `barber_procedure` WRITE;
/*!40000 ALTER TABLE `barber_procedure` DISABLE KEYS */;
INSERT INTO `barber_procedure` VALUES (1,4,1,1,'2025-12-03 13:37:10',NULL),(2,4,2,1,'2025-12-03 13:37:10',NULL),(3,4,4,1,'2025-12-03 13:37:10',NULL),(4,4,7,1,'2025-12-03 13:37:10',NULL),(5,4,9,1,'2025-12-03 13:37:10',NULL),(6,5,1,1,'2025-12-03 13:37:50',NULL),(7,5,2,1,'2025-12-03 13:37:50',NULL),(8,5,4,1,'2025-12-03 13:37:50',NULL),(9,5,5,1,'2025-12-03 13:37:50',NULL),(10,5,6,1,'2025-12-03 13:37:50',NULL),(11,5,7,1,'2025-12-03 13:37:50',NULL),(12,5,8,1,'2025-12-03 13:37:50',NULL),(13,5,9,1,'2025-12-03 13:37:50',NULL),(14,5,15,1,'2025-12-03 13:37:50',NULL),(15,4,16,1,'2025-12-03 13:50:24',NULL);
/*!40000 ALTER TABLE `barber_procedure` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `barber_schedule`
--

DROP TABLE IF EXISTS `barber_schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `barber_schedule` (
  `id` int NOT NULL AUTO_INCREMENT,
  `barber_id` int NOT NULL,
  `schedule_data` json NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `updated_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_73B68D78BFF2FEF2` (`barber_id`),
  CONSTRAINT `FK_73B68D78BFF2FEF2` FOREIGN KEY (`barber_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `barber_schedule`
--

LOCK TABLES `barber_schedule` WRITE;
/*!40000 ALTER TABLE `barber_schedule` DISABLE KEYS */;
INSERT INTO `barber_schedule` VALUES (1,4,'[{\"working\": false}, {\"end\": \"18:00\", \"start\": \"09:00\", \"working\": true}, {\"end\": \"18:00\", \"start\": \"09:00\", \"working\": true}, {\"end\": \"18:00\", \"start\": \"09:00\", \"working\": true}, {\"end\": \"18:00\", \"start\": \"09:00\", \"working\": true}, {\"end\": \"18:00\", \"start\": \"09:00\", \"working\": true}, {\"end\": \"13:00\", \"start\": \"09:00\", \"working\": true}]','2025-11-27 13:40:40',NULL),(2,5,'[{\"working\": false}, {\"end\": \"18:00\", \"start\": \"09:00\", \"working\": true}, {\"end\": \"18:00\", \"start\": \"09:00\", \"working\": true}, {\"end\": \"18:00\", \"start\": \"09:00\", \"working\": true}, {\"end\": \"18:00\", \"start\": \"09:00\", \"working\": true}, {\"end\": \"18:00\", \"start\": \"09:00\", \"working\": true}, {\"end\": \"13:00\", \"start\": \"09:00\", \"working\": true}]','2025-11-30 07:53:32',NULL);
/*!40000 ALTER TABLE `barber_schedule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `barber_schedule_exception`
--

DROP TABLE IF EXISTS `barber_schedule_exception`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `barber_schedule_exception` (
  `id` int NOT NULL AUTO_INCREMENT,
  `barber_id` int NOT NULL,
  `created_by` int DEFAULT NULL,
  `date` date NOT NULL COMMENT '(DC2Type:date_immutable)',
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `excluded_slots` json DEFAULT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_8DC81892BFF2FEF2` (`barber_id`),
  KEY `IDX_8DC81892DE12AB56` (`created_by`),
  KEY `idx_barber_date` (`barber_id`,`date`),
  CONSTRAINT `FK_8DC81892BFF2FEF2` FOREIGN KEY (`barber_id`) REFERENCES `user` (`id`),
  CONSTRAINT `FK_8DC81892DE12AB56` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `barber_schedule_exception`
--

LOCK TABLES `barber_schedule_exception` WRITE;
/*!40000 ALTER TABLE `barber_schedule_exception` DISABLE KEYS */;
INSERT INTO `barber_schedule_exception` VALUES (1,4,4,'2025-11-28',1,'09:00:00','18:00:00','[\"10:00\", \"10:30\"]',NULL,'2025-11-27 13:44:13');
/*!40000 ALTER TABLE `barber_schedule_exception` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `business_hours`
--

DROP TABLE IF EXISTS `business_hours`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `business_hours` (
  `id` int NOT NULL AUTO_INCREMENT,
  `day_of_week` smallint NOT NULL,
  `open_time` time NOT NULL,
  `close_time` time NOT NULL,
  `is_closed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `business_hours`
--

LOCK TABLES `business_hours` WRITE;
/*!40000 ALTER TABLE `business_hours` DISABLE KEYS */;
/*!40000 ALTER TABLE `business_hours` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `business_hours_exception`
--

DROP TABLE IF EXISTS `business_hours_exception`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `business_hours_exception` (
  `id` int NOT NULL AUTO_INCREMENT,
  `barber_id` int DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `date` date NOT NULL COMMENT '(DC2Type:date_immutable)',
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_closed` tinyint(1) NOT NULL DEFAULT '1',
  `open_time` time DEFAULT NULL,
  `close_time` time DEFAULT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_8B1FA04BBFF2FEF2` (`barber_id`),
  KEY `IDX_8B1FA04BDE12AB56` (`created_by`),
  CONSTRAINT `FK_8B1FA04BBFF2FEF2` FOREIGN KEY (`barber_id`) REFERENCES `user` (`id`),
  CONSTRAINT `FK_8B1FA04BDE12AB56` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `business_hours_exception`
--

LOCK TABLES `business_hours_exception` WRITE;
/*!40000 ALTER TABLE `business_hours_exception` DISABLE KEYS */;
/*!40000 ALTER TABLE `business_hours_exception` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8mb3_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctrine_migration_versions`
--

LOCK TABLES `doctrine_migration_versions` WRITE;
/*!40000 ALTER TABLE `doctrine_migration_versions` DISABLE KEYS */;
INSERT INTO `doctrine_migration_versions` VALUES ('DoctrineMigrations\\Version20251126083550',NULL,NULL),('DoctrineMigrations\\Version20251126084039',NULL,NULL),('DoctrineMigrations\\Version20251126152116',NULL,NULL),('DoctrineMigrations\\Version20251203072702','2025-12-03 07:41:39',60),('DoctrineMigrations\\Version20251209141020','2025-12-09 14:10:52',82),('DoctrineMigrations\\Version20251209141316','2025-12-09 14:13:40',35),('DoctrineMigrations\\Version20251209141952','2025-12-09 14:20:08',59);
/*!40000 ALTER TABLE `doctrine_migration_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `procedures`
--

DROP TABLE IF EXISTS `procedures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procedures` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price_master` decimal(10,2) NOT NULL,
  `price_junior` decimal(10,2) NOT NULL,
  `duration_master` int NOT NULL,
  `duration_junior` int NOT NULL,
  `available` tinyint(1) NOT NULL DEFAULT '1',
  `date_added` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `date_last_update` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `date_stopped` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `procedures`
--

LOCK TABLES `procedures` WRITE;
/*!40000 ALTER TABLE `procedures` DISABLE KEYS */;
INSERT INTO `procedures` VALUES (1,'Haircut - Men',25.00,20.00,30,40,1,'2025-12-03 11:54:06',NULL,NULL),(2,'Haircut - Kids',15.00,12.00,20,25,1,'2025-12-03 11:54:06',NULL,NULL),(3,'Haircut - Senior',20.00,18.00,30,40,0,'2025-12-03 11:54:06',NULL,NULL),(4,'Beard Trim',18.00,15.00,20,25,1,'2025-12-03 11:54:06','2025-12-03 13:49:37',NULL),(5,'Beard Shaping',18.00,15.00,25,30,1,'2025-12-03 11:54:06',NULL,NULL),(6,'Hot Towel Shave',30.00,25.00,40,50,1,'2025-12-03 11:54:06',NULL,NULL),(7,'Haircut + Beard Trim',35.00,28.00,45,60,1,'2025-12-03 11:54:06',NULL,NULL),(8,'Haircut + Beard Shaping',38.00,32.00,50,65,1,'2025-12-03 11:54:06',NULL,NULL),(9,'Haircut + Hot Towel Shave',50.00,42.00,60,75,1,'2025-12-03 11:54:06',NULL,NULL),(10,'Premium Haircut + Styling',40.00,35.00,45,55,0,'2025-12-03 11:54:06',NULL,NULL),(11,'Head Massage',20.00,15.00,20,25,0,'2025-12-03 11:54:06',NULL,NULL),(12,'Hair Coloring',50.00,45.00,90,120,0,'2025-12-03 11:54:06',NULL,NULL),(13,'Highlights',60.00,55.00,120,150,0,'2025-12-03 11:54:06',NULL,NULL),(14,'Wedding/Event Styling',80.00,70.00,90,120,0,'2025-12-03 11:54:06',NULL,NULL),(15,'Consultation',0.00,0.00,15,15,1,'2025-12-03 11:54:06',NULL,NULL),(16,'Test procedure',20.00,15.00,35,45,1,'2025-12-03 13:50:09',NULL,NULL);
/*!40000 ALTER TABLE `procedures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nick_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_added` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `date_banned` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `date_last_update` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_banned` tinyint(1) NOT NULL DEFAULT '0',
  `confirmation_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_expires_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'superadmin@abv.bg','[\"ROLE_SUPER_ADMIN\"]','$2y$13$LVQe5U5gu3IRJoNZps/OruwBC3EieX6o.Mo4Nba0LGkxACe9kipzS','Super','Admin',NULL,'0888888888','2025-11-26 16:05:19',NULL,'2025-12-03 16:36:32',1,0,NULL,NULL),(2,'john.smith@abv.bg','[\"ROLE_CLIENT\"]','$2y$13$LVQe5U5gu3IRJoNZps/OruwBC3EieX6o.Mo4Nba0LGkxACe9kipzS','John','Smith','Little John','0897123456','2025-11-26 16:09:45','2025-12-03 18:22:32','2025-12-03 18:22:33',1,0,NULL,NULL),(3,'maria.petrova@abv.bg','[\"ROLE_CLIENT\"]','$2y$13$LVQe5U5gu3IRJoNZps/OruwBC3EieX6o.Mo4Nba0LGkxACe9kipzS','Maria','Petrova',NULL,'0898234567','2025-11-26 16:09:48',NULL,'2025-11-26 16:09:48',1,0,NULL,NULL),(4,'b_peter@abv.bg','[\"ROLE_BARBER_SENIOR\", \"ROLE_BARBER\", \"ROLE_BARBER_JUNIOR\", \"ROLE_CLIENT\"]','$2y$13$LVQe5U5gu3IRJoNZps/OruwBC3EieX6o.Mo4Nba0LGkxACe9kipzS','Peter','Ivanov',NULL,'0899345678','2025-11-26 16:09:51',NULL,'2025-11-26 16:09:51',1,0,NULL,NULL),(5,'b_georgi@abv.bg','[\"ROLE_BARBER\", \"ROLE_BARBER_JUNIOR\", \"ROLE_CLIENT\"]','$2y$13$LVQe5U5gu3IRJoNZps/OruwBC3EieX6o.Mo4Nba0LGkxACe9kipzS','Georgi','Dimitrov',NULL,'0897456789','2025-11-26 16:09:54',NULL,'2025-11-26 16:09:54',1,0,NULL,NULL),(6,'manager@abv.bg','[\"ROLE_MANAGER\"]','$2y$13$LVQe5U5gu3IRJoNZps/OruwBC3EieX6o.Mo4Nba0LGkxACe9kipzS','Manager','Managerov','Manager','0888123456','2025-12-01 11:06:17',NULL,NULL,1,0,NULL,NULL),(7,'admin@abv.bg','[\"ROLE_ADMIN\"]','$2y$13$ZCpJqS8hvGRNFq7/a6gqpuEZLceumJ/b6R9nFbPLDGcgSb3WFjCwq','Admin','Adminov','Admincho','0898765432','2025-12-03 16:30:46',NULL,'2025-12-03 16:30:46',1,0,NULL,NULL),(8,'as@abv.bg','[\"ROLE_CLIENT\"]','$2y$13$N6V7pW.cCPXMWHQhun1UAesa8Sb4NXNU7Pk5ba4S5UC91MoM88LqC',NULL,NULL,'Asko',NULL,'2025-12-05 10:51:40',NULL,'2025-12-05 15:43:31',1,0,NULL,NULL),(9,'test_guest_1765290099@example.com','[\"ROLE_CLIENT\"]',NULL,'Test','Guest',NULL,'+359888123456','2025-12-09 16:21:39',NULL,NULL,0,0,NULL,NULL),(10,'test_guest_1765290126@example.com','[\"ROLE_CLIENT\"]',NULL,'Test','Guest',NULL,'+359888123456','2025-12-09 16:22:06',NULL,NULL,0,0,NULL,NULL),(11,'test_guest_1765290150@example.com','[\"ROLE_CLIENT\"]',NULL,'Test','Guest',NULL,'+359888123456','2025-12-09 16:22:30',NULL,NULL,0,0,NULL,NULL),(12,'abv@abv.bg','[\"ROLE_CLIENT\"]',NULL,'Ижан','ИЖАНОВ',NULL,'0899112233','2025-12-09 17:04:36',NULL,NULL,0,0,NULL,NULL),(13,'petar_new@abv.bg','[\"ROLE_CLIENT\"]','$2y$13$fC4RrbBFUJQ.NMxVslzgv.Gq4B0pZ2i/o7TXVS.NiRMZkZmeRMSOq','Petar','Petrow',NULL,'0899119919','2025-12-09 17:35:55',NULL,'2025-12-09 17:58:28',1,0,NULL,NULL);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-09 20:27:13
