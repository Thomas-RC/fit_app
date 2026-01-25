-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: fit_ai
-- ------------------------------------------------------
-- Server version	8.0.45

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
-- Table structure for table `app_settings`
--

DROP TABLE IF EXISTS `app_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `app_settings_key_unique` (`key`),
  KEY `app_settings_key_index` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_settings`
--

LOCK TABLES `app_settings` WRITE;
/*!40000 ALTER TABLE `app_settings` DISABLE KEYS */;
INSERT INTO `app_settings` VALUES (1,'spoonacular_api_key','eyJpdiI6IjFEL3kyUW5FRFgrN3NyL2pPYk5RM0E9PSIsInZhbHVlIjoiRElHNnVBRnc5ZFN2Tzg3ditDYW9tbVMrRW9zdWpTeXBOV1dtZ3g2V1FMK0w5Qkw4MzExK0JGNzhMUTBWRlhQdyIsIm1hYyI6ImFlZDJiZTRlMGQxYjRjNzE0NjE0NTlhYTFlNjhkNTgzNGJkYzg2OWFlOWU2YmU2ZmJjNGZhNjc1NzJjNmMyZjgiLCJ0YWciOiIifQ==',NULL,'2026-01-25 00:50:51','2026-01-25 02:36:06'),(2,'vertex_ai_credentials','eyJpdiI6IlQyeU5zMDd0SmlhVE5FNzZRQytTdkE9PSIsInZhbHVlIjoidEZpYmIxM1RJWHYreFRDOHN2MWNsayt3OFZweHVqRkQ0TzNObXFKT3NTMmhHcGFLLzUwZVhyUExEYkVtS1dqb1FJREhLV1ZHM3FHQ1VWUTVLYStHUnJ5YXhZS3NJZzVsUVAvRks3LzhvbmxuMTlLdDJwSzFNS0tnc0FFT2dWNnV1TXE3Q3dYekwvLzhTOFNZclV6M0E1V3ovcmY5bFV6anNHbDNmdkd3dm1QQmdYdEZKQmhkVUVxcHVRS21mU3oxMThhWlpTWW9HbEJqclREd1h3cXhCd00vcnVhanM5cm9FaXFvVnUrUmoyN0IwSzViQlRrdk1pUEErbFBnZ0duYjdkaHNpYnFweW9pdUR3UDJpMVpJWHZRYWtsRzRhWWxyU3hZN2E0M0dKTS9IMW5aVU0yaTdOMDJCZ2VKbDd0WDVKek1iaCtEUGhnY0huUEsxVXRFYXQrZ240bnM4S3lRUGRNK0F2V0l5MXFHN1h1blMyYnRxSWM1VVc2cGpTSlE0Nlkwc3NITnJyUTFtdk9rMWRNdGRCMFVxUS9KSHFkeW83ak8wQ3dWUkUxU29ESmFuekNpdDg3bGtYMTc0WmRCcTgveWt6K3ZTQk5BTHpHTWJUUlkxQWd1cXJodzRTN3VvVkJqditPbzgvaEVacmRMS3NhQWtLbnEvWDg1dm1PYm9CbjN0MUJJSklJTEJqVTVDQVhZOHNicTdVbHh4elVFQ1Z3cGk0VTRoNERkMzM0MlZoM1ZWNnRTeDZBRnhEYzNtUlR2RWI5eTZEYVFZOUcyNGh3SFZqNWxpUG1XS3ZsQlJUU1JBQzAreDlRRDlrNDRzMCt5bFpYTDhpbDVmSGV1R2ZFMmJjMWFBUUdOcnhTaW1zRVFTUXF1NzFZMG5zWDdzTU4zRXB1UjB6TUNwK29rTzgydk5rR3dPSWdkV0NRb0dVcEgrT0R3RkJubFR3a1ZEbk9Va2d0eUVmNFZvS1RWd3FEWlB0UlFDZVV5aXBISU5UVVVFV2F4blM4aysxeUY4NkRUWG9XTDBOTWIrMzNzbzZ6cXBITnFId3ozcFMzS2JvOVM3ODM0WWlIUWo0MmR5WGFwelBsSHdudHpmWUZieVpqZU1rRThRaFJSbjU3MTNINnE0N0E4SktselQ1VUV4RHAxcWxrMzNsR3V4d0NtSEk2ZE81VzUvVlhJOXZxaHZWNkc1WGZ3RTRYKzZUdlRzQTZZTGJZV0IxS082NXRGLzlXUGc0MEZkNStreDN4Qy9pa2ducC9mR2Y3d2dCNEE5OHA5Tjd5QSswUVB0QklGUDB4N1dOSCtyNzFpc1FEWWlqQnpXZWI4MDFDS3JHdmxpK2VoaEdzWXlBRGh2ZzNjWk5HcnpjdUM1ZHJxeFNOaXJRWGV4V1dBNlR6VWhWVENWQm4vRU00TzJFT0o4TURoZkk2OE9MbUxsYWlqM3FRR1B4QXo3a2JLSWlFODgrUndLUnN1OExndExRYlhxck5yQklGZjJzR0FRaFExbENTRmhab0xtd2hJRTQ0clZxcjhWcXhuQ3E0clVra0JRMHVWL2JGRjM4dk9vQVNHZll0enNhNTVLZ1FXTmJ1QUVZR0w4eC9lVUFMeStnUVZHc2l4WFJjUnY1V2tEM0VPQ3BpaGFTVlNDQ0NsSFhaV0REa0l3ZkVtZkN6ZkI5S0RoMGlTR1doS0RzcUpmUU9vUDZndFFCQm84WDkxOEduSnYzVmtFRFU0MWQ1eEg0R1IxNWJOczVMcXdlMDg1K0d2THRqUzNSdFF4KzM3VUhRdzZIQUt3bE85NjkzOFlrZlBuNEVrRW8xZGx6VTcxYWdrRXZvSFd5TUF2alFmUDd2ZEJKVHUrVHIxWnFJOHFIWGtQcUFqUDBRcFBLMi9SV2NQREwrTUp4L2sxcC9TRHNRWnMrVkZUS1REWndGa2U4YVlJYzk0M0ZjRmM5MERPeEpGK1VPNnVxWHZHZFJIWlFNZWgzdTdVN09pVmxFcjFtQmdHVGJKb3ZHcTg0NzM2MTcrNmNYWTZyQ0paZGdOdWZxa0FZbjl5bzNwK0F0OHZ3TTcwbDVsUFhEQ0JxNjRnaWd6N3JMcm4zSGllNzRKZTFQd3h0aG81RzA2bVB1ZlFKMm8raUJ2UzZCQkhYbWMyNEpxUVorejNyQ3R3bEZkMGJPTHQ1eVpqdkRKdVVmNHdxWks1SDk0V0JMdHAxMWlNemM5T3psZWhyVUtSZ2U5b0M4dkloNEVoeVBWRjdyOHpYWnRIVm1CWGJrVUlLa1ZBcDdHU0hYUHF1dklkWUNPZTk4ZGJ3ZDBlRWxVM21DdTlDeVNHQml1NFhpcVBpQjhJRWlkdVdrak9kaGhOUlJyRWNpTDJoZ1pLTmN6Q1IxRkc5ekk5d3Y1YStiMlFLYy9ueXBQYmRkU3c4MldaaEd4aENrUEVFTlgrSThzLzBLQm9sbVAySFk1L0kvYTlGMnJwWW02Y05RWU9DTHFLSXZYLzM0ZGNFNjYvdFpNQTJrdVZhWEVCa2FBazZQdWorM2t1VmZOYXpzU1VmYitOZFlyRXVwZTZsTXBwQ3ZHdysvT2d5UERDZGFCdGV0QjlTU3RaZjA3aEhIQmtzWnVZMXpJYS9pRW1nY1VaV096MGFnQ3YyNHNDb3BKRXh4WUM4MGQ4ZXBGdUloS2kyMktwdTc0Q01CMW9PeHBxK1NWMVl2QXl5NE9KRzhramNOREZZemF6UzFwQ1JMa0lybkVTZjRhODdoUlljb2l0bkhidEpBU2VEWlhBOXZzdFB2akFOM3dRS253MUdzeTBZbXRIYlJvanBscXBtT3QrQjZ3dUY3R09HTmx4Q3hTSEFGbks3dm5wNzdGRFBGa2V5bEVDUGlneHdlZytUZFgzYTFTU21wRm80eDBtSG5aQVRqTVVDY2QzY0NVOWdtUktqYVRieUZOZDFTaTFvS2JuWmwzQnBKc0VUbDJpaUExMUFwSzhnWUJmYnpDMW9XY3hvdXY1QlIvNVUzZkk1QTQzUWpvbnV5Q3FFdXBvVXRscEhMbG9UOUtTZ3VtTzBBYS9IRUorNk5OTUV1VG1TdWhUVzlueGJiRGRmNWdGaVFlbktNd0pQQzhLUmtFRHludHpSQzRDQytPMmJKKzNJVmNoVHkrdGllQkFidks3cDlEUlpXSk5WTFRiN2g3aCtXSDBydEU0Mm9uM3JmZWgyWEpNUno5b09GREFLSStjdTlTV0pDNTZobTVYR0s3YWYvMFRXWGg3VnRFQ2g0VCt0VzgxTFRVVjF1eDJBbTRlajZLajdLQ1M1VFFidUJ3cHVZYzBTazd6VzltOElDVDhGa0ZPTTg3dXFvcGhJb2RWZDlvZzRZZ2xtNEd0Q2tmMVpqUjZPM0NMQ21RcFpBQm8vanFFQ1ZkMTJOd1hFM1ZmWEh0YStCWHkwVUdmUitkUUdwUzQ2a0Fhc2QwRmtSczNJWDNFM1BSZkJkVENZLyt4MER6MjI0OW15bHd4UUQ0M3JQcyt3SE9aWWJxbEdTMm5zZGlSaU5xTm1NRDN6MktWcjRRRnJ0dE56dUxkdHJFYjROSU96N2llbXFRZkRGajlqK0wyUUVBVm5iSTU4R2NrdUhwYXlUZElMWlJNS0h5WUxTWTV1d1AwTFdicWdwTUpJOFVoTEJyNVdmcmIyZlhDbjVtU28vTDMvU3h5bXlDV2t2eWRLaGdTYlEwQTlsc0VXWkN6ckhQaGRhVXJocyszaUpIVkJ1TkY2U0dNbllWZ1JzcXE3Y2RFTkUwOVRFTEY5OFMwWndBbzJtTkFaODdUVGxmd3RBcHNlT0lVbVNodjRTR1JwN1o1NmUrbVBEWVI2UlU3ZHlWajdYTzdEbVdjRlQ5aGVnaERDN1NWaURuYXdsd0RQWklYQjdhOFJBZUtMKytvL3p3Wmg5dkp6OTkzRHFaaTcyUHlJNzJrdlFSRmlheWEyNDJTbzNKekhQUU9UQ1pOMWE1SlBHaGlLOWpBTmtwT2pOczJ5QlNLb1RHYXZXdkN6dUorOE1WeVpNUy9wUURUS1dnY3BkSzk3dWJJN2pPcDJYajhoaS9tc0JxTnRGaFVHa0JTWmExVWt4d0owM1NjRUYwTzJ0QWR1RWE4cWUvNXpzUFZPYVBYb3pvTm5SM3VpdVhFck1tUHY4NXl0WmdZN1RWcHB1dnpNR0praE91SEpSbjVSRjRGR3N4b3l3dy9jcnFCcHJiaDFZTGtGTGZwN3BqN1hjUDNsSmM9IiwibWFjIjoiMjBjMDM0OGM1ZGRkMDAwNDUwYzhiODc5YTgxZmVmNzBiYTk5ODY2NjI0NDU2NzQzYTdlNDljZWFkOTZlNjQ2OSIsInRhZyI6IiJ9',NULL,'2026-01-25 01:19:08','2026-01-25 02:36:06'),(3,'vertex_ai_project_id','eyJpdiI6Iml0K3k1M2JoaXR1TS93Um5FVG5PR3c9PSIsInZhbHVlIjoiZDZwdXVYQktWanRmN2kxL0lXb0ZFTUpJTFpENUdqTDVWRzFmUUhtVFVubz0iLCJtYWMiOiJiNjk1ZDA4OTk0YjZkODY2ZmUyNzQwNzVlYTBmZjE5YjEwZDY5ODhiZGIwMGZhNzg0NGQxNGViYTZjODJlMTg4IiwidGFnIjoiIn0=',NULL,'2026-01-25 01:19:08','2026-01-25 02:36:06');
/*!40000 ALTER TABLE `app_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custom_dishes`
--

DROP TABLE IF EXISTS `custom_dishes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `custom_dishes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ingredients` json NOT NULL,
  `instructions` text COLLATE utf8mb4_unicode_ci,
  `calories` int unsigned NOT NULL,
  `protein` decimal(8,2) DEFAULT NULL,
  `carbs` decimal(8,2) DEFAULT NULL,
  `fat` decimal(8,2) DEFAULT NULL,
  `image_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `custom_dishes_user_id_index` (`user_id`),
  KEY `custom_dishes_created_at_index` (`created_at`),
  CONSTRAINT `custom_dishes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_dishes`
--

LOCK TABLES `custom_dishes` WRITE;
/*!40000 ALTER TABLE `custom_dishes` DISABLE KEYS */;
/*!40000 ALTER TABLE `custom_dishes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fridge_items`
--

DROP TABLE IF EXISTS `fridge_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fridge_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(8,2) DEFAULT NULL,
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `added_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fridge_items_user_id_index` (`user_id`),
  KEY `fridge_items_added_at_index` (`added_at`),
  CONSTRAINT `fridge_items_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fridge_items`
--

LOCK TABLES `fridge_items` WRITE;
/*!40000 ALTER TABLE `fridge_items` DISABLE KEYS */;
INSERT INTO `fridge_items` VALUES (1,1,'Milk',NULL,'l','2026-01-25 02:12:20',NULL,'2026-01-25 02:12:20','2026-01-25 02:12:20'),(2,1,'Cheese',NULL,'g','2026-01-25 02:12:20',NULL,'2026-01-25 02:12:20','2026-01-25 02:12:20'),(4,1,'Bell Pepper',NULL,'szt','2026-01-25 02:12:20',NULL,'2026-01-25 02:12:20','2026-01-25 02:12:20'),(5,1,'Onion',NULL,'szt','2026-01-25 02:12:20',NULL,'2026-01-25 02:12:20','2026-01-25 02:12:20'),(6,1,'Lettuce',NULL,'szt','2026-01-25 02:12:20',NULL,'2026-01-25 02:12:20','2026-01-25 02:12:20'),(7,1,'Eggplant',NULL,'szt','2026-01-25 02:12:20',NULL,'2026-01-25 02:12:20','2026-01-25 02:12:20'),(8,1,'Garlic',NULL,'szt','2026-01-25 02:12:20',NULL,'2026-01-25 02:12:20','2026-01-25 02:12:20'),(9,1,'Dill',NULL,'g','2026-01-25 02:12:20',NULL,'2026-01-25 02:12:20','2026-01-25 02:12:20'),(10,1,'Banana',NULL,'szt','2026-01-25 02:12:20',NULL,'2026-01-25 02:12:20','2026-01-25 02:12:20'),(11,1,'Apple',NULL,'szt','2026-01-25 02:12:20',NULL,'2026-01-25 02:12:20','2026-01-25 02:12:20'),(12,1,'Plum',NULL,'szt','2026-01-25 02:12:20',NULL,'2026-01-25 02:12:20','2026-01-25 02:12:20'),(13,1,'Coconut',NULL,'szt','2026-01-25 02:12:20',NULL,'2026-01-25 02:12:20','2026-01-25 02:12:20'),(14,1,'Persimmon',NULL,'szt','2026-01-25 02:12:20',NULL,'2026-01-25 02:12:20','2026-01-25 02:12:20'),(15,1,'Pear',NULL,'szt','2026-01-25 02:12:20',NULL,'2026-01-25 02:12:20','2026-01-25 02:12:20'),(16,1,'Mixed Vegetables',NULL,'g','2026-01-25 02:12:20',NULL,'2026-01-25 02:12:20','2026-01-25 02:12:20'),(17,1,'Sausage',NULL,'g','2026-01-25 02:12:20',NULL,'2026-01-25 02:12:20','2026-01-25 02:12:20');
/*!40000 ALTER TABLE `fridge_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meal_plan_recipes`
--

DROP TABLE IF EXISTS `meal_plan_recipes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `meal_plan_recipes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `meal_plan_id` bigint unsigned NOT NULL,
  `spoonacular_recipe_id` int unsigned NOT NULL,
  `meal_type` enum('breakfast','lunch','dinner','snack') COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipe_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `calories` int unsigned NOT NULL,
  `recipe_data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `meal_plan_recipes_meal_plan_id_index` (`meal_plan_id`),
  KEY `meal_plan_recipes_spoonacular_recipe_id_index` (`spoonacular_recipe_id`),
  KEY `meal_plan_recipes_meal_type_index` (`meal_type`),
  CONSTRAINT `meal_plan_recipes_meal_plan_id_foreign` FOREIGN KEY (`meal_plan_id`) REFERENCES `meal_plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meal_plan_recipes`
--

LOCK TABLES `meal_plan_recipes` WRITE;
/*!40000 ALTER TABLE `meal_plan_recipes` DISABLE KEYS */;
/*!40000 ALTER TABLE `meal_plan_recipes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meal_plans`
--

DROP TABLE IF EXISTS `meal_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `meal_plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `total_calories` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `meal_plans_user_id_date_index` (`user_id`,`date`),
  KEY `meal_plans_created_at_index` (`created_at`),
  CONSTRAINT `meal_plans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meal_plans`
--

LOCK TABLES `meal_plans` WRITE;
/*!40000 ALTER TABLE `meal_plans` DISABLE KEYS */;
/*!40000 ALTER TABLE `meal_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_01_24_225640_create_user_preferences_table',2),(5,'2026_01_24_225644_create_fridge_items_table',2),(6,'2026_01_24_225648_create_meal_plans_table',2),(7,'2026_01_24_225653_create_meal_plan_recipes_table',2),(8,'2026_01_24_225657_create_custom_dishes_table',2),(9,'2026_01_24_225701_create_app_settings_table',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('26GLK8zGgyxDeNgiX9DOJtnWka0KlpcwUfQ0vMZq',NULL,'172.19.0.1','curl/8.5.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiOE5SbUZDQWNkb2tObUpsZ1lxUnRabXkyVjFIejM3cjVHTzR5TGlHRyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1769333596),('47vUkEPc6iU9PWSsr38SLZKlM4NKc3nbWcz0x6CH',NULL,'172.19.0.1','curl/8.5.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiSXBNd2VzaHA1dWdqdnc2MmY1VlFyYmlUWlBKd0JuM240eVFjRXZqayI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1769303541),('BuNdaA1mz8gptL7Z2UpFJzx07lQuDVOwBe0BQnUD',NULL,'172.19.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','YToyOntzOjY6Il90b2tlbiI7czo0MDoiZUxTcjRVOTFnRFdvVjczWUUwSWFOMEhQMm1ONlE1MmNwcFV2ZEF3SSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1769303217),('fRJrxfrK557mE7gBw1q9l6qkZQhEG21kx4OwwG7P',NULL,'172.19.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUXNkWTJRb09YSklzcG5iQ0VObDQ0SjQ0dXV6OVJkSllPeUJkMWh2TyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1769303140),('OGbMdR35HJV5MzlXhCrKWCV6VXsddd8AWRcGKy7a',1,'172.19.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWnVlTXNDM2hXZFNyb2JmVmZRWXZRcEdSZWM5YWl6eWFQWkQ4NXBlVSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9kYXNoYm9hcmQiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=',1769303115),('OwAFNQPwJPZGGBk2dYFJLH2Xq2H10r55KEJEeIFh',1,'172.19.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiTW1kd1B1Y2t6N3NMYW5qYTNjZU1qN0ZFSnMxNUxBU2dQRTVEb09RbiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9kYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=',1769303561),('UJIVyoqKNiVHdKtbY5RzlWkabJEBtAjIIQQk2zgl',1,'172.19.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiNDNiVjdsRnZTdDAzNW9nc3pPdlRyVGcyZUR6YkJXbldiR1pxa3gwTCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzI6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9tZWFsLXBsYW5zIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9',1769310351);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_preferences`
--

DROP TABLE IF EXISTS `user_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_preferences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `diet_type` enum('omnivore','vegetarian','vegan','keto') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'omnivore',
  `daily_calories` int unsigned NOT NULL DEFAULT '2000',
  `allergies` json DEFAULT NULL,
  `exclude_ingredients` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_preferences_user_id_unique` (`user_id`),
  KEY `user_preferences_diet_type_index` (`diet_type`),
  CONSTRAINT `user_preferences_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_preferences`
--

LOCK TABLES `user_preferences` WRITE;
/*!40000 ALTER TABLE `user_preferences` DISABLE KEYS */;
INSERT INTO `user_preferences` VALUES (1,1,'omnivore',2500,NULL,NULL,'2026-01-24 23:19:04','2026-01-25 03:00:46');
/*!40000 ALTER TABLE `user_preferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `google_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_google_id_unique` (`google_id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_email_index` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'108019326953304831117','Tomek Rosik','tomekrosik@gmail.com','https://lh3.googleusercontent.com/a/ACg8ocIHPvq0WvVr92AbkX06fo3387hjhuWumM3LXVxTidOtI3nt7w=s96-c','2026-01-24 23:19:04','2026-01-24 23:19:04');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-25  9:33:23
