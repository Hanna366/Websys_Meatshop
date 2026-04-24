-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: meatshop_pos_preview
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
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
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` varchar(255) NOT NULL,
  `customer_code` varchar(255) NOT NULL,
  `personal_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`personal_info`)),
  `address` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`address`)),
  `preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`preferences`)),
  `loyalty` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`loyalty`)),
  `purchasing_history` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`purchasing_history`)),
  `payment_methods` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_methods`)),
  `special_requirements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`special_requirements`)),
  `business_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`business_info`)),
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_tenant_id_customer_code_unique` (`tenant_id`,`customer_code`),
  UNIQUE KEY `customers_customer_code_unique` (`customer_code`),
  KEY `customers_tenant_id_status_index` (`tenant_id`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (1,'TENR3P2TBO5','CUST000101','{\"first_name\":\"Robert\",\"last_name\":\"Johnson\",\"email\":\"robert.johnson@email.com\",\"phone\":\"+1-555-1001\",\"address\":{\"street\":\"123 Main St\",\"city\":\"Meatville\",\"state\":\"TX\",\"zip_code\":\"75001\",\"country\":\"US\"}}',NULL,'{\"preferred_contact_method\":\"email\",\"marketing_consent\":{\"email\":true,\"sms\":false}}','{\"tier\":\"bronze\",\"points_balance\":150,\"total_spent\":2500,\"join_date\":\"2025-08-19T15:47:06.407529Z\"}','{\"total_orders\":12,\"total_spent\":2500,\"average_order_value\":208.33,\"last_purchase_date\":\"2026-02-14T15:47:06.407596Z\"}',NULL,NULL,NULL,'active','2026-02-19 07:47:06','2026-02-19 07:47:06',NULL),(2,'TENR3P2TBO5','CUST000102','{\"first_name\":\"Maria\",\"last_name\":\"Garcia\",\"email\":\"maria.garcia@email.com\",\"phone\":\"+1-555-1002\",\"address\":{\"street\":\"456 Oak Ave\",\"city\":\"Butchertown\",\"state\":\"CA\",\"zip_code\":\"90210\",\"country\":\"US\"}}',NULL,'{\"preferred_contact_method\":\"sms\",\"marketing_consent\":{\"email\":true,\"sms\":true}}','{\"tier\":\"silver\",\"points_balance\":850,\"total_spent\":8500,\"join_date\":\"2025-02-19T15:47:06.407627Z\"}','{\"total_orders\":35,\"total_spent\":8500,\"average_order_value\":242.86,\"last_purchase_date\":\"2026-02-17T15:47:06.407648Z\"}',NULL,NULL,NULL,'active','2026-02-19 07:47:06','2026-02-19 07:47:06',NULL),(3,'TENR3P2TBO5','CUST000103','{\"first_name\":\"James\",\"last_name\":\"Wilson\",\"email\":\"james.wilson@email.com\",\"phone\":\"+1-555-1003\",\"address\":{\"street\":\"789 Pine Rd\",\"city\":\"Steak City\",\"state\":\"NY\",\"zip_code\":\"10001\",\"country\":\"US\"}}',NULL,'{\"preferred_contact_method\":\"email\",\"marketing_consent\":{\"email\":false,\"sms\":false}}','{\"tier\":\"gold\",\"points_balance\":2200,\"total_spent\":15000,\"join_date\":\"2024-02-19T15:47:06.407670Z\"}','{\"total_orders\":68,\"total_spent\":15000,\"average_order_value\":220.59,\"last_purchase_date\":\"2026-02-18T15:47:06.407684Z\"}',NULL,NULL,NULL,'active','2026-02-19 07:47:06','2026-02-19 07:47:06',NULL),(4,'TENR3P2TBO5','CUST000104','{\"first_name\":\"Patricia\",\"last_name\":\"Brown\",\"email\":\"patricia.brown@email.com\",\"phone\":\"+1-555-1004\",\"address\":{\"street\":\"321 Elm St\",\"city\":\"Grill Town\",\"state\":\"FL\",\"zip_code\":\"33101\",\"country\":\"US\"}}',NULL,'{\"preferred_contact_method\":\"email\",\"marketing_consent\":{\"email\":true,\"sms\":false}}','{\"tier\":\"platinum\",\"points_balance\":5500,\"total_spent\":35000,\"join_date\":\"2023-02-19T15:47:06.407703Z\"}','{\"total_orders\":145,\"total_spent\":35000,\"average_order_value\":241.38,\"last_purchase_date\":\"2026-02-19T12:47:06.407717Z\"}',NULL,NULL,NULL,'active','2026-02-19 07:47:06','2026-02-19 07:47:06',NULL),(5,'TENR3P2TBO5','CUST000105','{\"first_name\":\"David\",\"last_name\":\"Lee\",\"email\":\"david.lee@email.com\",\"phone\":\"+1-555-1005\",\"address\":{\"street\":\"654 Maple Dr\",\"city\":\"BBQ Heights\",\"state\":\"TX\",\"zip_code\":\"75201\",\"country\":\"US\"}}',NULL,'{\"preferred_contact_method\":\"sms\",\"marketing_consent\":{\"email\":true,\"sms\":true}}','{\"tier\":\"bronze\",\"points_balance\":75,\"total_spent\":1200,\"join_date\":\"2025-11-19T15:47:06.407740Z\"}','{\"total_orders\":6,\"total_spent\":1200,\"average_order_value\":200,\"last_purchase_date\":\"2026-02-05T15:47:06.407755Z\"}',NULL,NULL,NULL,'active','2026-02-19 07:47:06','2026-02-19 07:47:06',NULL),(6,'TENZIJMZJAJ','CUST000201','{\"first_name\":\"Robert\",\"last_name\":\"Johnson\",\"email\":\"robert.johnson@email.com\",\"phone\":\"+1-555-1001\",\"address\":{\"street\":\"123 Main St\",\"city\":\"Meatville\",\"state\":\"TX\",\"zip_code\":\"75001\",\"country\":\"US\"}}',NULL,'{\"preferred_contact_method\":\"email\",\"marketing_consent\":{\"email\":true,\"sms\":false}}','{\"tier\":\"bronze\",\"points_balance\":150,\"total_spent\":2500,\"join_date\":\"2025-08-19T15:47:06.422271Z\"}','{\"total_orders\":12,\"total_spent\":2500,\"average_order_value\":208.33,\"last_purchase_date\":\"2026-02-14T15:47:06.422325Z\"}',NULL,NULL,NULL,'active','2026-02-19 07:47:06','2026-02-19 07:47:06',NULL),(7,'TENZIJMZJAJ','CUST000202','{\"first_name\":\"Maria\",\"last_name\":\"Garcia\",\"email\":\"maria.garcia@email.com\",\"phone\":\"+1-555-1002\",\"address\":{\"street\":\"456 Oak Ave\",\"city\":\"Butchertown\",\"state\":\"CA\",\"zip_code\":\"90210\",\"country\":\"US\"}}',NULL,'{\"preferred_contact_method\":\"sms\",\"marketing_consent\":{\"email\":true,\"sms\":true}}','{\"tier\":\"silver\",\"points_balance\":850,\"total_spent\":8500,\"join_date\":\"2025-02-19T15:47:06.422354Z\"}','{\"total_orders\":35,\"total_spent\":8500,\"average_order_value\":242.86,\"last_purchase_date\":\"2026-02-17T15:47:06.422373Z\"}',NULL,NULL,NULL,'active','2026-02-19 07:47:06','2026-02-19 07:47:06',NULL),(8,'TENZIJMZJAJ','CUST000203','{\"first_name\":\"James\",\"last_name\":\"Wilson\",\"email\":\"james.wilson@email.com\",\"phone\":\"+1-555-1003\",\"address\":{\"street\":\"789 Pine Rd\",\"city\":\"Steak City\",\"state\":\"NY\",\"zip_code\":\"10001\",\"country\":\"US\"}}',NULL,'{\"preferred_contact_method\":\"email\",\"marketing_consent\":{\"email\":false,\"sms\":false}}','{\"tier\":\"gold\",\"points_balance\":2200,\"total_spent\":15000,\"join_date\":\"2024-02-19T15:47:06.422394Z\"}','{\"total_orders\":68,\"total_spent\":15000,\"average_order_value\":220.59,\"last_purchase_date\":\"2026-02-18T15:47:06.422409Z\"}',NULL,NULL,NULL,'active','2026-02-19 07:47:06','2026-02-19 07:47:06',NULL),(9,'TENZIJMZJAJ','CUST000204','{\"first_name\":\"Patricia\",\"last_name\":\"Brown\",\"email\":\"patricia.brown@email.com\",\"phone\":\"+1-555-1004\",\"address\":{\"street\":\"321 Elm St\",\"city\":\"Grill Town\",\"state\":\"FL\",\"zip_code\":\"33101\",\"country\":\"US\"}}',NULL,'{\"preferred_contact_method\":\"email\",\"marketing_consent\":{\"email\":true,\"sms\":false}}','{\"tier\":\"platinum\",\"points_balance\":5500,\"total_spent\":35000,\"join_date\":\"2023-02-19T15:47:06.422429Z\"}','{\"total_orders\":145,\"total_spent\":35000,\"average_order_value\":241.38,\"last_purchase_date\":\"2026-02-19T12:47:06.422443Z\"}',NULL,NULL,NULL,'active','2026-02-19 07:47:06','2026-02-19 07:47:06',NULL),(10,'TENZIJMZJAJ','CUST000205','{\"first_name\":\"David\",\"last_name\":\"Lee\",\"email\":\"david.lee@email.com\",\"phone\":\"+1-555-1005\",\"address\":{\"street\":\"654 Maple Dr\",\"city\":\"BBQ Heights\",\"state\":\"TX\",\"zip_code\":\"75201\",\"country\":\"US\"}}',NULL,'{\"preferred_contact_method\":\"sms\",\"marketing_consent\":{\"email\":true,\"sms\":true}}','{\"tier\":\"bronze\",\"points_balance\":75,\"total_spent\":1200,\"join_date\":\"2025-11-19T15:47:06.422463Z\"}','{\"total_orders\":6,\"total_spent\":1200,\"average_order_value\":200,\"last_purchase_date\":\"2026-02-05T15:47:06.422477Z\"}',NULL,NULL,NULL,'active','2026-02-19 07:47:06','2026-02-19 07:47:06',NULL);
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `domains`
--

DROP TABLE IF EXISTS `domains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `domains` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) NOT NULL,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `domains_domain_unique` (`domain`),
  KEY `domains_tenant_id_foreign` (`tenant_id`),
  CONSTRAINT `domains_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `domains`
--

LOCK TABLES `domains` WRITE;
/*!40000 ALTER TABLE `domains` DISABLE KEYS */;
INSERT INTO `domains` VALUES (2,'ramcar.localhost',4,'2026-03-21 08:13:44','2026-03-22 05:37:13'),(3,'mmfoods.localhost',5,'2026-03-30 16:53:07','2026-03-30 16:53:07'),(4,'monterey.locathost.localhost',6,'2026-03-31 18:54:14','2026-03-31 18:54:14'),(5,'email-test-20260401040804.localhost',8,'2026-03-31 20:08:04','2026-03-31 20:08:04'),(6,'gourmet.localhost',9,'2026-03-31 20:29:38','2026-03-31 20:29:38'),(7,'3ms.localhost',10,'2026-03-31 20:33:42','2026-03-31 20:33:42'),(8,'bauyan.localhost',11,'2026-04-06 00:29:23','2026-04-06 00:29:23'),(9,'berts-meatshop.localhost',12,'2026-04-06 00:56:17','2026-04-06 00:56:17'),(10,'premium-meatshop.localhost',13,'2026-04-06 07:35:50','2026-04-06 07:35:50'),(11,'rmcc.localhost',14,'2026-04-06 16:11:10','2026-04-06 16:11:10'),(12,'lrj-meatshop.localhost',15,'2026-04-06 16:19:36','2026-04-06 16:19:36'),(13,'princessmeatshop.localhost',16,'2026-04-06 16:27:45','2026-04-06 16:27:45'),(15,'caberto.localhost',19,'2026-04-11 05:42:16','2026-04-11 05:42:16'),(16,'pampi.localhost',20,'2026-04-11 20:53:57','2026-04-11 20:53:57'),(17,'jams.localhost',21,'2026-04-11 21:47:37','2026-04-11 21:47:37'),(18,'chop.localhost',22,'2026-04-12 06:34:25','2026-04-12 06:34:25'),(19,'bong.localhost',23,'2026-04-19 07:02:27','2026-04-19 07:02:27'),(20,'bridge.localhost',24,'2026-04-19 08:19:54','2026-04-19 08:19:54'),(21,'san.localhost',25,'2026-04-19 08:29:04','2026-04-19 08:29:04'),(22,'s-jane-meatshop.localhost',26,'2026-04-19 20:35:49','2026-04-19 20:35:49'),(23,'tender.localhost',27,'2026-04-20 09:45:08','2026-04-20 09:45:08');
/*!40000 ALTER TABLE `domains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
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
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
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
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2019_12_14_000001_create_personal_access_tokens_table',1),(2,'2024_01_01_000001_create_tenants_table',1),(3,'2024_01_01_000002_create_users_table',1),(4,'2024_01_01_000003_create_products_table',1),(5,'2024_01_01_000004_create_customers_table',1),(6,'2026_02_23_045437_create_users_table',1),(7,'2026_02_23_045403_create_subscriptions_table',2),(8,'2026_03_16_125425_update_users_table_tenant_id_nullable',2),(9,'2026_03_16_131556_update_users_table_make_tenant_id_nullable',2),(10,'2026_03_17_000000_add_tenancy_fields_to_tenants_table',3),(11,'2026_03_17_200000_add_tenant_lifecycle_and_domain_fields',3),(12,'2026_03_17_210000_create_domains_table',4),(13,'2026_03_17_220000_backfill_domains_from_tenants_table',5),(14,'2026_03_22_000001_create_password_reset_tokens_table',6),(15,'2026_03_28_000002_create_permission_tables',7),(16,'2026_03_25_000100_create_pricing_master_tables',8),(17,'2026_03_31_162734_add_logo_path_to_tenants_table',8),(18,'2026_04_06_223500_add_expires_at_to_password_reset_tokens_table',9),(19,'2026_04_11_000000_add_disabled_message_to_tenants',10),(20,'2026_04_12_000000_create_subscription_requests_table',11),(21,'2024_04_08_000001_create_versions_table',12),(22,'2024_04_08_000002_create_update_logs_table',12),(23,'2026_04_08_023808_create_sessions_table',13),(24,'2026_04_12_000002_add_token_encrypted_to_password_reset_tokens',13),(25,'2026_04_19_000000_add_recovery_email_to_users',14),(26,'2026_04_20_045407_create_jobs_table',15),(27,'2026_04_20_120000_create_versions_table',16),(28,'2026_04_20_120100_create_system_updates_table',16);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `token_encrypted` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
INSERT INTO `password_reset_tokens` VALUES ('areshanna088@gmail.com','$2y$12$.IGXfpm5O7lTe42akyCMdexgPAvx7Bf1cII1cTzGqa/NY1UlPGuA2','eyJpdiI6IlhqVi9UZHRraHlSWFlIbDJZRFJLb1E9PSIsInZhbHVlIjoieHVaZzJxNFltWHZMRmpwUFprS0NBQU9KdVFoR2pwV3NIRkxXWEdhTmdRcVNOVWpQaHV5NG5QS1NuekRsQjliMXZDK2FNRXR1ZnZRNnA1eE9pZEdIRUY3R3RnVHJ6cnp6WnRQM0hZVHR3ZFU9IiwibWFjIjoiYjRlN2ViMjZkZDlmY2QwZDEyYmZkNjFjYjQ1MDJiOGFkZTJjZTFjN2Y4NjExMGRkZWMxMDNhNDkyYmNkZGY0YiIsInRhZyI6IiJ9','2026-04-19 08:31:48',NULL),('areshanna088+j@gmail.com','$2y$12$HEPNB1AXVm.e2D.vLSRKFOB17BbRyFffomz7U6NstpDTnCW.RgZSq',NULL,'2026-04-12 06:11:17',NULL);
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'pos.access','web','2026-03-27 19:20:18','2026-03-27 19:20:18'),(2,'products.manage','web','2026-03-27 19:20:18','2026-03-27 19:20:18'),(3,'inventory.manage','web','2026-03-27 19:20:18','2026-03-27 19:20:18'),(4,'customers.manage','web','2026-03-27 19:20:18','2026-03-27 19:20:18'),(5,'suppliers.manage','web','2026-03-27 19:20:18','2026-03-27 19:20:18'),(6,'reports.view','web','2026-03-27 19:20:18','2026-03-27 19:20:18'),(7,'users.manage','web','2026-03-27 19:20:18','2026-03-27 19:20:18'),(8,'settings.manage','web','2026-03-27 19:20:18','2026-03-27 19:20:18');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `price_list_items`
--

DROP TABLE IF EXISTS `price_list_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `price_list_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` varchar(255) DEFAULT NULL,
  `price_list_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `min_qty` decimal(12,3) DEFAULT NULL,
  `max_qty` decimal(12,3) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pli_unique` (`price_list_id`,`product_id`,`min_qty`,`max_qty`),
  KEY `price_list_items_product_id_foreign` (`product_id`),
  KEY `price_list_items_tenant_id_product_id_index` (`tenant_id`,`product_id`),
  CONSTRAINT `price_list_items_price_list_id_foreign` FOREIGN KEY (`price_list_id`) REFERENCES `price_lists` (`id`) ON DELETE CASCADE,
  CONSTRAINT `price_list_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `price_list_items`
--

LOCK TABLES `price_list_items` WRITE;
/*!40000 ALTER TABLE `price_list_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `price_list_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `price_lists`
--

DROP TABLE IF EXISTS `price_lists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `price_lists` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` varchar(255) DEFAULT NULL,
  `code` varchar(100) NOT NULL,
  `name` varchar(150) NOT NULL,
  `channel` varchar(50) NOT NULL DEFAULT 'retail',
  `currency` varchar(3) NOT NULL DEFAULT 'PHP',
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `effective_from` datetime NOT NULL,
  `effective_to` datetime DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `published_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `price_lists_tenant_id_code_unique` (`tenant_id`,`code`),
  KEY `price_lists_tenant_id_channel_status_effective_from_index` (`tenant_id`,`channel`,`status`,`effective_from`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `price_lists`
--

LOCK TABLES `price_lists` WRITE;
/*!40000 ALTER TABLE `price_lists` DISABLE KEYS */;
/*!40000 ALTER TABLE `price_lists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_categories`
--

DROP TABLE IF EXISTS `product_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` varchar(255) DEFAULT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_categories_tenant_id_code_unique` (`tenant_id`,`code`),
  KEY `product_categories_tenant_id_is_active_index` (`tenant_id`,`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_categories`
--

LOCK TABLES `product_categories` WRITE;
/*!40000 ALTER TABLE `product_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` varchar(255) NOT NULL,
  `product_code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` enum('beef','pork','chicken','lamb','seafood','processed','other','byproduct') NOT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `subcategory` varchar(255) DEFAULT NULL,
  `uom_id` bigint(20) unsigned DEFAULT NULL,
  `pricing` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`pricing`)),
  `inventory` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`inventory`)),
  `batch_tracking` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`batch_tracking`)),
  `physical_attributes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`physical_attributes`)),
  `supplier_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`supplier_info`)),
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `barcode` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_tenant_id_product_code_unique` (`tenant_id`,`product_code`),
  UNIQUE KEY `products_barcode_unique` (`barcode`),
  KEY `products_tenant_id_category_index` (`tenant_id`,`category`),
  KEY `products_tenant_id_status_index` (`tenant_id`,`status`),
  KEY `products_category_id_foreign` (`category_id`),
  KEY `products_uom_id_foreign` (`uom_id`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`),
  CONSTRAINT `products_uom_id_foreign` FOREIGN KEY (`uom_id`) REFERENCES `units_of_measure` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'TENR3P2TBO5','PRIME_RIB_STEAK','Prime Rib Steak','Premium Wagyu Prime Rib Steak - Grade 1','beef',NULL,'prime',NULL,'{\"price_per_unit\":2870,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":2500}','{\"current_stock\":50,\"reorder_level\":10,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}','{\"weight_range\":\"300-500g\",\"cut_thickness\":\"2.5cm\",\"marbling_score\":\"A5\"}',NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(2,'TENR3P2TBO5','PRIME_RIBEYE','Ribeye','Premium Wagyu Ribeye - Grade 1','beef',NULL,'prime',NULL,'{\"price_per_unit\":3570,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":3200}','{\"current_stock\":45,\"reorder_level\":10,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}','{\"weight_range\":\"250-400g\",\"cut_thickness\":\"3cm\",\"marbling_score\":\"A5\"}',NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(3,'TENR3P2TBO5','PRIME_SHORTLOIN','Shortloin Slab','Premium Wagyu Shortloin Slab - Grade 1','beef',NULL,'prime',NULL,'{\"price_per_unit\":2670,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":2400}','{\"current_stock\":30,\"reorder_level\":8,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}',NULL,NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(4,'TENR3P2TBO5','PRIME_TENDERLOIN','Tenderloin','Premium Wagyu Tenderloin - Grade 1','beef',NULL,'prime',NULL,'{\"price_per_unit\":4020,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":3600}','{\"current_stock\":25,\"reorder_level\":5,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}','{\"weight_range\":\"200-300g\",\"marbling_score\":\"A5\"}',NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(5,'TENR3P2TBO5','PRIME_STRIPLOIN','Striploin','Premium Wagyu Striploin - Grade 1','beef',NULL,'prime',NULL,'{\"price_per_unit\":2870,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":2600}','{\"current_stock\":35,\"reorder_level\":8,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}',NULL,NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(6,'TENR3P2TBO5','PRIME_PORTERHOUSE','Porterhouse','Premium Wagyu Porterhouse - Grade 1','beef',NULL,'prime',NULL,'{\"price_per_unit\":2670,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":2400}','{\"current_stock\":20,\"reorder_level\":5,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}','{\"weight_range\":\"500-700g\",\"marbling_score\":\"A5\"}',NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(7,'TENR3P2TBO5','PRIME_TBONE','T-Bone','Premium Wagyu T-Bone Steak - Grade 1','beef',NULL,'prime',NULL,'{\"price_per_unit\":2470,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":2200}','{\"current_stock\":22,\"reorder_level\":6,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}','{\"weight_range\":\"450-600g\",\"marbling_score\":\"A5\"}',NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(8,'TENR3P2TBO5','PREM_OYSTER_BLADE','Oyster blade','Wagyu Oyster Blade - Premium Grade','beef',NULL,'premium',NULL,'{\"price_per_unit\":1720,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":1500}','{\"current_stock\":40,\"reorder_level\":10,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}',NULL,NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(9,'TENR3P2TBO5','PREM_FLAT_IRON','Flat iron steak','Wagyu Flat Iron Steak - Premium Grade','beef',NULL,'premium',NULL,'{\"price_per_unit\":2120,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":1900}','{\"current_stock\":35,\"reorder_level\":8,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}',NULL,NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(10,'TENR3P2TBO5','PREM_BRISKET','Brisket','Wagyu Brisket - Premium Grade','beef',NULL,'premium',NULL,'{\"price_per_unit\":980,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":850}','{\"current_stock\":60,\"reorder_level\":15,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}',NULL,NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(11,'TENR3P2TBO5','PREM_CHUCK_ROLL','Chuck Roll','Wagyu Chuck Roll - Premium Grade','beef',NULL,'premium',NULL,'{\"price_per_unit\":1870,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":1650}','{\"current_stock\":45,\"reorder_level\":12,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}',NULL,NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(12,'TENR3P2TBO5','PREM_SHORT_PLATE','SHORT PLATE','Wagyu Short Plate - Premium Grade','beef',NULL,'premium',NULL,'{\"price_per_unit\":1020,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":900}','{\"current_stock\":50,\"reorder_level\":12,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}',NULL,NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(13,'TENR3P2TBO5','PREM_BONELESS_SHORT_PLATE','Boneless Short Plate','Wagyu Boneless Short Plate - Premium Grade','beef',NULL,'premium',NULL,'{\"price_per_unit\":1270,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":1100}','{\"current_stock\":38,\"reorder_level\":10,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}',NULL,NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(14,'TENR3P2TBO5','PREM_TENDERLOIN_TIP','Tenderloin Tip','Wagyu Tenderloin Tip - Premium Grade','beef',NULL,'premium',NULL,'{\"price_per_unit\":1920,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":1700}','{\"current_stock\":25,\"reorder_level\":6,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}',NULL,NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(15,'TENR3P2TBO5','PREM_SIRLOIN','Sirloin','Wagyu Sirloin - Premium Grade','beef',NULL,'premium',NULL,'{\"price_per_unit\":1720,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":1500}','{\"current_stock\":42,\"reorder_level\":10,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}',NULL,NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(16,'TENR3P2TBO5','PREM_TRI_TIP','Tri-tip','Wagyu Tri-tip - Premium Grade','beef',NULL,'premium',NULL,'{\"price_per_unit\":1720,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":1500}','{\"current_stock\":30,\"reorder_level\":8,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}',NULL,NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(17,'TENR3P2TBO5','PREM_FLANK_STEAK','Flank Steak','Wagyu Flank Steak - Premium Grade','beef',NULL,'premium',NULL,'{\"price_per_unit\":1870,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":1650}','{\"current_stock\":28,\"reorder_level\":7,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}',NULL,NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(18,'TENR3P2TBO5','PREM_FLANK_WHOLE','Flank Whole','Wagyu Whole Flank - Premium Grade','beef',NULL,'premium',NULL,'{\"price_per_unit\":885,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":750}','{\"current_stock\":35,\"reorder_level\":9,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}',NULL,NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(19,'TENR3P2TBO5','SEL_CHUCK_TENDER','Chuck Tender','Wagyu Chuck Tender - Select Grade','beef',NULL,'select',NULL,'{\"price_per_unit\":770,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":650}','{\"current_stock\":55,\"reorder_level\":15,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}',NULL,NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(20,'TENR3P2TBO5','SEL_BOLAR_BLADE','Bolar Blade','Wagyu Bolar Blade - Select Grade','beef',NULL,'select',NULL,'{\"price_per_unit\":1060,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":900}','{\"current_stock\":48,\"reorder_level\":12,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}',NULL,NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(21,'TENR3P2TBO5','SEL_SHORT_RIBS','Short Ribs','Wagyu Short Ribs - Select Grade','beef',NULL,'select',NULL,'{\"price_per_unit\":855,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":720}','{\"current_stock\":40,\"reorder_level\":10,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}',NULL,NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(22,'TENR3P2TBO5','SEL_BONELESS_SHORT_RIB','Boneless Short Rib','Wagyu Boneless Short Rib - Select Grade','beef',NULL,'select',NULL,'{\"price_per_unit\":1050,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":900}','{\"current_stock\":32,\"reorder_level\":8,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}',NULL,NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(23,'TENR3P2TBO5','SEL_SIRLOIN_TIP','Sirloin Tip','Wagyu Sirloin Tip - Select Grade','beef',NULL,'select',NULL,'{\"price_per_unit\":970,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":820}','{\"current_stock\":38,\"reorder_level\":10,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}',NULL,NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(24,'TENR3P2TBO5','SEL_TOP_ROUND','Top Round','Wagyu Top Round - Select Grade','beef',NULL,'select',NULL,'{\"price_per_unit\":960,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":810}','{\"current_stock\":45,\"reorder_level\":12,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}',NULL,NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(25,'TENR3P2TBO5','SEL_SILVERSIDE','Silverside','Wagyu Silverside - Select Grade','beef',NULL,'select',NULL,'{\"price_per_unit\":880,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":740}','{\"current_stock\":42,\"reorder_level\":11,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}',NULL,NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(26,'TENR3P2TBO5','CHO_NECK_MEAT','Neck Meat','Wagyu Neck Meat - Choice Grade','beef',NULL,'choice',NULL,'{\"price_per_unit\":770,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":650}','{\"current_stock\":60,\"reorder_level\":15,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}',NULL,NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(27,'TENR3P2TBO5','CHO_HUMP_ROAST','Hump Roast','Wagyu Hump Roast - Choice Grade','beef',NULL,'choice',NULL,'{\"price_per_unit\":770,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":650}','{\"current_stock\":35,\"reorder_level\":10,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}',NULL,NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(28,'TENR3P2TBO5','CHO_SHANK_BI','Shank BI','Wagyu Shank BI - Choice Grade','beef',NULL,'choice',NULL,'{\"price_per_unit\":620,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":520}','{\"current_stock\":50,\"reorder_level\":12,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}',NULL,NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(29,'TENR3P2TBO5','CHO_EYE_ROUND','Eye Round','Wagyu Eye Round - Choice Grade','beef',NULL,'choice',NULL,'{\"price_per_unit\":770,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":650}','{\"current_stock\":40,\"reorder_level\":10,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}',NULL,NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(30,'TENR3P2TBO5','CHO_SHIN_SHANK','Shin/Shank Boneless','Wagyu Shin/Shank Boneless - Choice Grade','beef',NULL,'choice',NULL,'{\"price_per_unit\":670,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":560}','{\"current_stock\":45,\"reorder_level\":11,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}',NULL,NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(31,'TENR3P2TBO5','BY_NECK_BONES','Neck Bones','Wagyu Neck Bones','byproduct',NULL,'bones',NULL,'{\"price_per_unit\":410,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":350}','{\"current_stock\":80,\"reorder_level\":20,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}',NULL,NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(32,'TENR3P2TBO5','BY_SOUP_BONES','Soup Bones','Wagyu Soup Bones','byproduct',NULL,'bones',NULL,'{\"price_per_unit\":220,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":180}','{\"current_stock\":100,\"reorder_level\":25,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}',NULL,NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(33,'TENR3P2TBO5','BY_BONE_MARROW','Bone Marrow','Wagyu Bone Marrow','byproduct',NULL,'marrow',NULL,'{\"price_per_unit\":440,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":380}','{\"current_stock\":30,\"reorder_level\":8,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}',NULL,NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06'),(34,'TENR3P2TBO5','BY_FATS','Fats','Wagyu Beef Fat','byproduct',NULL,'fat',NULL,'{\"price_per_unit\":340,\"unit_type\":\"kg\",\"tax_rate\":8.25,\"cost_per_unit\":290}','{\"current_stock\":70,\"reorder_level\":18,\"unit_of_measure\":\"kg\"}','{\"enabled\":true,\"track_expiry\":true,\"track_temperature\":true}',NULL,NULL,NULL,NULL,NULL,NULL,'active',1,NULL,NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,1),(1,2),(1,3),(1,4),(2,1),(2,3),(3,1),(3,3),(4,1),(4,3),(5,1),(5,3),(6,1),(6,3),(7,1),(7,3),(8,1),(8,3);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Owner','web','2026-03-27 19:20:18','2026-03-27 19:20:18'),(2,'Staff','web','2026-03-27 19:20:18','2026-03-27 19:20:18'),(3,'Administrator','web','2026-03-27 19:50:37','2026-03-27 19:50:37'),(4,'Cashier','web','2026-03-27 19:50:37','2026-03-27 19:50:37');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
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
INSERT INTO `sessions` VALUES ('F4VbQ783p3CHWNNvRB9kMNkeeugaM05O1WK8qrpb',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoib2plQTI3NTNlTE5PQWJaRTJselNkOEZSOU5uVUduQnRQcUlKWnl3cCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czoxMDoibG9naW4uZm9ybSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1771563153),('mlCCip2z64MTG3cGXa3pBjAhuj9NG4fojrHWmFmL',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRExIZzJMTXQ2UVZnWXFIa29sanFIZ1JuNmRtaDV3VWVpd1dwSm1sbyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1771516210);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscription_requests`
--

DROP TABLE IF EXISTS `subscription_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscription_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` varchar(255) NOT NULL,
  `requested_plan` varchar(255) NOT NULL,
  `payment_method` varchar(255) DEFAULT NULL,
  `payment_reference` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subscription_requests_tenant_id_index` (`tenant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscription_requests`
--

LOCK TABLES `subscription_requests` WRITE;
/*!40000 ALTER TABLE `subscription_requests` DISABLE KEYS */;
INSERT INTO `subscription_requests` VALUES (1,'9999164e-1a01-4475-89e1-7ad8042d8606','premium',NULL,NULL,149.00,'rejected','[]','2026-04-11 19:05:08','2026-04-11 20:17:57'),(2,'9999164e-1a01-4475-89e1-7ad8042d8606','premium',NULL,NULL,149.00,'approved','[]','2026-04-11 19:18:11','2026-04-11 20:18:00'),(3,'3599909b-405c-4a89-a279-8c07a378fc81','premium',NULL,NULL,149.00,'approved','[]','2026-04-11 20:59:32','2026-04-11 20:59:42'),(4,'74f32b47-e354-40de-8364-967824f4afbb','premium',NULL,NULL,149.00,'approved','[]','2026-04-11 21:50:19','2026-04-11 21:50:29'),(5,'970dd2aa-68b3-4841-813a-582aeacd7a59','premium',NULL,NULL,149.00,'approved','[]','2026-04-19 08:12:31','2026-04-19 08:12:39'),(6,'b63c44ec-e899-4f42-9725-c1d32c1db355','premium',NULL,NULL,149.00,'approved','[]','2026-04-19 21:01:12','2026-04-19 21:01:26'),(7,'8e0d30c6-406b-467d-84e6-140fa6a0e8d0','premium',NULL,NULL,149.00,'approved','[]','2026-04-20 09:47:41','2026-04-20 09:47:54');
/*!40000 ALTER TABLE `subscription_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) NOT NULL,
  `plan` varchar(255) NOT NULL,
  `price` decimal(8,2) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `starts_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL,
  `payment_method` varchar(255) NOT NULL,
  `last_payment_at` timestamp NULL DEFAULT NULL,
  `next_billing_at` timestamp NULL DEFAULT NULL,
  `auto_renew` tinyint(1) NOT NULL DEFAULT 1,
  `features_used` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features_used`)),
  `subscription_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subscriptions_user_id_status_index` (`user_id`,`status`),
  KEY `subscriptions_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscriptions`
--

LOCK TABLES `subscriptions` WRITE;
/*!40000 ALTER TABLE `subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` varchar(255) NOT NULL,
  `supplier_code` varchar(255) NOT NULL,
  `business_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`business_info`)),
  `address` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`address`)),
  `business_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`business_details`)),
  `product_categories` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`product_categories`)),
  `payment_terms` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`payment_terms`)),
  `delivery_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`delivery_info`)),
  `quality_standards` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`quality_standards`)),
  `performance_metrics` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`performance_metrics`)),
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `suppliers_tenant_id_supplier_code_unique` (`tenant_id`,`supplier_code`),
  UNIQUE KEY `suppliers_supplier_code_unique` (`supplier_code`),
  KEY `suppliers_tenant_id_status_index` (`tenant_id`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES (1,'TENR3P2TBO5','SUP000101','{\"name\":\"Premium Wagyu Farms\",\"contact_person\":\"John Smith\",\"email\":\"john@premiumwagyu.com\",\"phone\":\"+1-555-2001\",\"fax\":\"+1-555-2002\",\"website\":\"https:\\/\\/premiumwagyu.com\"}','{\"street\":\"1000 Ranch Road\",\"city\":\"Wagyu Valley\",\"state\":\"TX\",\"zip_code\":\"75001\",\"country\":\"US\"}','{\"tax_id\":\"TX-123456789\",\"business_license\":\"BL-2024-001\",\"years_in_business\":15,\"certifications\":[\"USDA Organic\",\"Halal Certified\",\"Animal Welfare Approved\"]}','[\"beef\"]','{\"method\":\"net_30\",\"credit_limit\":50000,\"due_days\":30}','{\"delivery_days\":[\"Monday\",\"Wednesday\",\"Friday\"],\"minimum_order\":100,\"delivery_fee\":50,\"delivery_radius\":200}','{\"grade_requirements\":[\"A5\",\"A4\"],\"inspection_required\":true,\"temperature_control\":true,\"traceability\":true}','{\"quality_score\":95,\"delivery_performance\":98,\"price_competitiveness\":85,\"reliability\":92,\"last_updated\":\"2026-02-19T15:50:01.531548Z\"}','active','2026-02-19 07:50:01','2026-02-19 07:50:01',NULL),(2,'TENR3P2TBO5','SUP000102','{\"name\":\"Local Beef Co-op\",\"contact_person\":\"Maria Rodriguez\",\"email\":\"maria@localbeef.com\",\"phone\":\"+1-555-2003\",\"fax\":\"+1-555-2004\",\"website\":\"https:\\/\\/localbeef.com\"}','{\"street\":\"500 Farm Lane\",\"city\":\"Cattle Town\",\"state\":\"CA\",\"zip_code\":\"90210\",\"country\":\"US\"}','{\"tax_id\":\"CA-987654321\",\"business_license\":\"BL-2023-045\",\"years_in_business\":25,\"certifications\":[\"USDA Prime\",\"Grass Fed Certified\",\"Local Farm Verified\"]}','[\"beef\"]','{\"method\":\"net_15\",\"credit_limit\":25000,\"due_days\":15}','{\"delivery_days\":[\"Tuesday\",\"Thursday\"],\"minimum_order\":50,\"delivery_fee\":25,\"delivery_radius\":100}','{\"grade_requirements\":[\"Prime\",\"Choice\"],\"inspection_required\":true,\"temperature_control\":true,\"traceability\":true}','{\"quality_score\":88,\"delivery_performance\":95,\"price_competitiveness\":92,\"reliability\":90,\"last_updated\":\"2026-02-19T15:50:01.531567Z\"}','active','2026-02-19 07:50:01','2026-02-19 07:50:01',NULL),(3,'TENR3P2TBO5','SUP000103','{\"name\":\"International Meat Imports\",\"contact_person\":\"Chen Wei\",\"email\":\"chen@internationalmeat.com\",\"phone\":\"+1-555-2005\",\"fax\":\"+1-555-2006\",\"website\":\"https:\\/\\/internationalmeat.com\"}','{\"street\":\"2500 Export Blvd\",\"city\":\"Port City\",\"state\":\"NY\",\"zip_code\":\"10001\",\"country\":\"US\"}','{\"tax_id\":\"NY-456789123\",\"business_license\":\"BL-2022-089\",\"years_in_business\":30,\"certifications\":[\"HACCP Certified\",\"ISO 9001\",\"Global Food Safety\"]}','[\"beef\",\"pork\",\"chicken\"]','{\"method\":\"net_45\",\"credit_limit\":100000,\"due_days\":45}','{\"delivery_days\":[\"Monday\",\"Tuesday\",\"Wednesday\",\"Thursday\",\"Friday\"],\"minimum_order\":500,\"delivery_fee\":100,\"delivery_radius\":500}','{\"grade_requirements\":[\"All Grades\"],\"inspection_required\":true,\"temperature_control\":true,\"traceability\":true}','{\"quality_score\":82,\"delivery_performance\":88,\"price_competitiveness\":78,\"reliability\":85,\"last_updated\":\"2026-02-19T15:50:01.531578Z\"}','active','2026-02-19 07:50:01','2026-02-19 07:50:01',NULL),(4,'TENR3P2TBO5','SUP000104','{\"name\":\"Organic Ranch Supply\",\"contact_person\":\"Sarah Green\",\"email\":\"sarah@organicranch.com\",\"phone\":\"+1-555-2007\",\"fax\":\"+1-555-2008\",\"website\":\"https:\\/\\/organicranch.com\"}','{\"street\":\"750 Green Acres\",\"city\":\"Organic Valley\",\"state\":\"CO\",\"zip_code\":\"80201\",\"country\":\"US\"}','{\"tax_id\":\"CO-789123456\",\"business_license\":\"BL-2021-067\",\"years_in_business\":12,\"certifications\":[\"USDA Organic\",\"Non-GMO Project\",\"Certified Humane\"]}','[\"beef\",\"lamb\"]','{\"method\":\"net_30\",\"credit_limit\":30000,\"due_days\":30}','{\"delivery_days\":[\"Wednesday\",\"Saturday\"],\"minimum_order\":75,\"delivery_fee\":40,\"delivery_radius\":150}','{\"grade_requirements\":[\"Choice\",\"Select\"],\"inspection_required\":true,\"temperature_control\":true,\"traceability\":true}','{\"quality_score\":90,\"delivery_performance\":92,\"price_competitiveness\":88,\"reliability\":89,\"last_updated\":\"2026-02-19T15:50:01.531588Z\"}','active','2026-02-19 07:50:01','2026-02-19 07:50:01',NULL),(5,'TENR3P2TBO5','SUP000105','{\"name\":\"Regional Distribution Center\",\"contact_person\":\"Mike Johnson\",\"email\":\"mike@regionaldist.com\",\"phone\":\"+1-555-2009\",\"fax\":\"+1-555-2010\",\"website\":\"https:\\/\\/regionaldist.com\"}','{\"street\":\"1500 Warehouse Way\",\"city\":\"Distribution City\",\"state\":\"IL\",\"zip_code\":\"60601\",\"country\":\"US\"}','{\"tax_id\":\"IL-321654987\",\"business_license\":\"BL-2020-123\",\"years_in_business\":20,\"certifications\":[\"FDA Registered\",\"Cold Chain Certified\",\"Food Safety Modernization Act\"]}','[\"beef\",\"pork\",\"chicken\",\"lamb\",\"seafood\"]','{\"method\":\"net_60\",\"credit_limit\":75000,\"due_days\":60}','{\"delivery_days\":[\"Daily\"],\"minimum_order\":200,\"delivery_fee\":75,\"delivery_radius\":300}','{\"grade_requirements\":[\"All Grades\"],\"inspection_required\":true,\"temperature_control\":true,\"traceability\":true}','{\"quality_score\":85,\"delivery_performance\":94,\"price_competitiveness\":80,\"reliability\":87,\"last_updated\":\"2026-02-19T15:50:01.531596Z\"}','active','2026-02-19 07:50:01','2026-02-19 07:50:01',NULL),(6,'TENZIJMZJAJ','SUP000201','{\"name\":\"Premium Wagyu Farms\",\"contact_person\":\"John Smith\",\"email\":\"john@premiumwagyu.com\",\"phone\":\"+1-555-2001\",\"fax\":\"+1-555-2002\",\"website\":\"https:\\/\\/premiumwagyu.com\"}','{\"street\":\"1000 Ranch Road\",\"city\":\"Wagyu Valley\",\"state\":\"TX\",\"zip_code\":\"75001\",\"country\":\"US\"}','{\"tax_id\":\"TX-123456789\",\"business_license\":\"BL-2024-001\",\"years_in_business\":15,\"certifications\":[\"USDA Organic\",\"Halal Certified\",\"Animal Welfare Approved\"]}','[\"beef\"]','{\"method\":\"net_30\",\"credit_limit\":50000,\"due_days\":30}','{\"delivery_days\":[\"Monday\",\"Wednesday\",\"Friday\"],\"minimum_order\":100,\"delivery_fee\":50,\"delivery_radius\":200}','{\"grade_requirements\":[\"A5\",\"A4\"],\"inspection_required\":true,\"temperature_control\":true,\"traceability\":true}','{\"quality_score\":95,\"delivery_performance\":98,\"price_competitiveness\":85,\"reliability\":92,\"last_updated\":\"2026-02-19T15:50:01.551059Z\"}','active','2026-02-19 07:50:01','2026-02-19 07:50:01',NULL),(7,'TENZIJMZJAJ','SUP000202','{\"name\":\"Local Beef Co-op\",\"contact_person\":\"Maria Rodriguez\",\"email\":\"maria@localbeef.com\",\"phone\":\"+1-555-2003\",\"fax\":\"+1-555-2004\",\"website\":\"https:\\/\\/localbeef.com\"}','{\"street\":\"500 Farm Lane\",\"city\":\"Cattle Town\",\"state\":\"CA\",\"zip_code\":\"90210\",\"country\":\"US\"}','{\"tax_id\":\"CA-987654321\",\"business_license\":\"BL-2023-045\",\"years_in_business\":25,\"certifications\":[\"USDA Prime\",\"Grass Fed Certified\",\"Local Farm Verified\"]}','[\"beef\"]','{\"method\":\"net_15\",\"credit_limit\":25000,\"due_days\":15}','{\"delivery_days\":[\"Tuesday\",\"Thursday\"],\"minimum_order\":50,\"delivery_fee\":25,\"delivery_radius\":100}','{\"grade_requirements\":[\"Prime\",\"Choice\"],\"inspection_required\":true,\"temperature_control\":true,\"traceability\":true}','{\"quality_score\":88,\"delivery_performance\":95,\"price_competitiveness\":92,\"reliability\":90,\"last_updated\":\"2026-02-19T15:50:01.551074Z\"}','active','2026-02-19 07:50:01','2026-02-19 07:50:01',NULL),(8,'TENZIJMZJAJ','SUP000203','{\"name\":\"International Meat Imports\",\"contact_person\":\"Chen Wei\",\"email\":\"chen@internationalmeat.com\",\"phone\":\"+1-555-2005\",\"fax\":\"+1-555-2006\",\"website\":\"https:\\/\\/internationalmeat.com\"}','{\"street\":\"2500 Export Blvd\",\"city\":\"Port City\",\"state\":\"NY\",\"zip_code\":\"10001\",\"country\":\"US\"}','{\"tax_id\":\"NY-456789123\",\"business_license\":\"BL-2022-089\",\"years_in_business\":30,\"certifications\":[\"HACCP Certified\",\"ISO 9001\",\"Global Food Safety\"]}','[\"beef\",\"pork\",\"chicken\"]','{\"method\":\"net_45\",\"credit_limit\":100000,\"due_days\":45}','{\"delivery_days\":[\"Monday\",\"Tuesday\",\"Wednesday\",\"Thursday\",\"Friday\"],\"minimum_order\":500,\"delivery_fee\":100,\"delivery_radius\":500}','{\"grade_requirements\":[\"All Grades\"],\"inspection_required\":true,\"temperature_control\":true,\"traceability\":true}','{\"quality_score\":82,\"delivery_performance\":88,\"price_competitiveness\":78,\"reliability\":85,\"last_updated\":\"2026-02-19T15:50:01.551084Z\"}','active','2026-02-19 07:50:01','2026-02-19 07:50:01',NULL),(9,'TENZIJMZJAJ','SUP000204','{\"name\":\"Organic Ranch Supply\",\"contact_person\":\"Sarah Green\",\"email\":\"sarah@organicranch.com\",\"phone\":\"+1-555-2007\",\"fax\":\"+1-555-2008\",\"website\":\"https:\\/\\/organicranch.com\"}','{\"street\":\"750 Green Acres\",\"city\":\"Organic Valley\",\"state\":\"CO\",\"zip_code\":\"80201\",\"country\":\"US\"}','{\"tax_id\":\"CO-789123456\",\"business_license\":\"BL-2021-067\",\"years_in_business\":12,\"certifications\":[\"USDA Organic\",\"Non-GMO Project\",\"Certified Humane\"]}','[\"beef\",\"lamb\"]','{\"method\":\"net_30\",\"credit_limit\":30000,\"due_days\":30}','{\"delivery_days\":[\"Wednesday\",\"Saturday\"],\"minimum_order\":75,\"delivery_fee\":40,\"delivery_radius\":150}','{\"grade_requirements\":[\"Choice\",\"Select\"],\"inspection_required\":true,\"temperature_control\":true,\"traceability\":true}','{\"quality_score\":90,\"delivery_performance\":92,\"price_competitiveness\":88,\"reliability\":89,\"last_updated\":\"2026-02-19T15:50:01.551093Z\"}','active','2026-02-19 07:50:01','2026-02-19 07:50:01',NULL),(10,'TENZIJMZJAJ','SUP000205','{\"name\":\"Regional Distribution Center\",\"contact_person\":\"Mike Johnson\",\"email\":\"mike@regionaldist.com\",\"phone\":\"+1-555-2009\",\"fax\":\"+1-555-2010\",\"website\":\"https:\\/\\/regionaldist.com\"}','{\"street\":\"1500 Warehouse Way\",\"city\":\"Distribution City\",\"state\":\"IL\",\"zip_code\":\"60601\",\"country\":\"US\"}','{\"tax_id\":\"IL-321654987\",\"business_license\":\"BL-2020-123\",\"years_in_business\":20,\"certifications\":[\"FDA Registered\",\"Cold Chain Certified\",\"Food Safety Modernization Act\"]}','[\"beef\",\"pork\",\"chicken\",\"lamb\",\"seafood\"]','{\"method\":\"net_60\",\"credit_limit\":75000,\"due_days\":60}','{\"delivery_days\":[\"Daily\"],\"minimum_order\":200,\"delivery_fee\":75,\"delivery_radius\":300}','{\"grade_requirements\":[\"All Grades\"],\"inspection_required\":true,\"temperature_control\":true,\"traceability\":true}','{\"quality_score\":85,\"delivery_performance\":94,\"price_competitiveness\":80,\"reliability\":87,\"last_updated\":\"2026-02-19T15:50:01.551100Z\"}','active','2026-02-19 07:50:01','2026-02-19 07:50:01',NULL);
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_updates`
--

DROP TABLE IF EXISTS `system_updates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_updates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) DEFAULT NULL,
  `source` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_updates`
--

LOCK TABLES `system_updates` WRITE;
/*!40000 ALTER TABLE `system_updates` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_updates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenants`
--

DROP TABLE IF EXISTS `tenants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tenants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` varchar(255) NOT NULL,
  `domain` varchar(255) DEFAULT NULL,
  `db_name` varchar(255) DEFAULT NULL,
  `db_username` varchar(255) DEFAULT NULL,
  `db_password` text DEFAULT NULL,
  `business_name` varchar(255) NOT NULL,
  `business_email` varchar(255) NOT NULL,
  `admin_name` varchar(255) DEFAULT NULL,
  `admin_email` varchar(255) DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `business_phone` varchar(255) NOT NULL,
  `business_address` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`business_address`)),
  `subscription` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`subscription`)),
  `plan` varchar(255) NOT NULL DEFAULT 'basic',
  `plan_started_at` timestamp NULL DEFAULT NULL,
  `plan_ends_at` timestamp NULL DEFAULT NULL,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`settings`)),
  `usage` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`usage`)),
  `limits` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`limits`)),
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `payment_status` varchar(255) NOT NULL DEFAULT 'paid',
  `disabled_message` varchar(255) DEFAULT NULL,
  `suspended_message` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenants_tenant_id_unique` (`tenant_id`),
  UNIQUE KEY `tenants_business_email_unique` (`business_email`),
  UNIQUE KEY `tenants_domain_unique` (`domain`),
  UNIQUE KEY `tenants_db_name_unique` (`db_name`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenants`
--

LOCK TABLES `tenants` WRITE;
/*!40000 ALTER TABLE `tenants` DISABLE KEYS */;
INSERT INTO `tenants` VALUES (1,'TENR3P2TBO5',NULL,NULL,NULL,NULL,'Premium Meats Inc.','info@premiummeats.com',NULL,NULL,NULL,'+1-555-0123','{\"street\":\"123 Main St\",\"city\":\"Meatville\",\"state\":\"TX\",\"zip_code\":\"75001\",\"country\":\"US\"}','{\"plan\":\"premium\",\"status\":\"active\",\"start_date\":\"2026-02-19T15:47:04.883595Z\",\"end_date\":\"2026-03-19T15:47:04.883605Z\",\"monthly_price\":149,\"features\":[{\"name\":\"inventory_tracking\",\"enabled\":true},{\"name\":\"pos_system\",\"enabled\":true},{\"name\":\"supplier_management\",\"enabled\":true},{\"name\":\"customer_management\",\"enabled\":true},{\"name\":\"advanced_reporting\",\"enabled\":true},{\"name\":\"api_access\",\"enabled\":true},{\"name\":\"batch_operations\",\"enabled\":true},{\"name\":\"data_export\",\"enabled\":true}]}','basic',NULL,NULL,'{\"currency\":\"USD\",\"weight_unit\":\"lb\",\"tax_rate\":8.25,\"low_stock_threshold\":10,\"expiry_warning_days\":7,\"enable_sms_notifications\":true,\"enable_email_notifications\":true}','{\"users_count\":4,\"products_count\":34,\"storage_used\":0,\"api_calls_this_month\":0}','{\"max_users\":-1,\"max_products\":-1,\"max_storage_mb\":20000,\"max_api_calls_per_month\":50000}','active','paid',NULL,NULL,'2026-02-19 07:47:04','2026-03-22 05:37:13','2026-03-21 07:17:05'),(2,'TENZIJMZJAJ',NULL,NULL,NULL,NULL,'Local Butcher Shop','contact@localbutcher.com',NULL,NULL,NULL,'+1-555-0456','{\"street\":\"456 Oak Ave\",\"city\":\"Butchertown\",\"state\":\"CA\",\"zip_code\":\"90210\",\"country\":\"US\"}','{\"plan\":\"standard\",\"status\":\"trial\",\"start_date\":\"2026-02-19T15:47:04.899910Z\",\"end_date\":\"2026-03-21T15:47:04.899921Z\",\"monthly_price\":79,\"features\":[{\"name\":\"inventory_tracking\",\"enabled\":true},{\"name\":\"pos_system\",\"enabled\":true},{\"name\":\"supplier_management\",\"enabled\":true},{\"name\":\"customer_management\",\"enabled\":true},{\"name\":\"basic_reporting\",\"enabled\":true}]}','basic',NULL,NULL,'{\"currency\":\"USD\",\"weight_unit\":\"lb\",\"tax_rate\":7.5,\"low_stock_threshold\":5,\"expiry_warning_days\":5,\"enable_sms_notifications\":false,\"enable_email_notifications\":true}','{\"users_count\":3,\"products_count\":0,\"storage_used\":0,\"api_calls_this_month\":0}','{\"max_users\":3,\"max_products\":-1,\"max_storage_mb\":5000,\"max_api_calls_per_month\":10000}','active','paid',NULL,NULL,'2026-02-19 07:47:04','2026-03-21 07:17:05','2026-03-21 07:17:05'),(3,'TENGOGSY9AZ',NULL,NULL,NULL,NULL,'buksu','annahares61@gmail.com',NULL,NULL,NULL,'09954630525','{\"street\":\"Dangcagan, Bukidnon\",\"city\":\"Lunsayan St. Sumpong Malaybalay City\",\"state\":\"Cebu\",\"zip_code\":\"8716\",\"country\":\"US\"}','{\"plan\":\"basic\",\"status\":\"active\",\"start_date\":\"2026-02-20T04:36:19.699229Z\",\"end_date\":\"2026-03-20T04:36:19.699246Z\",\"monthly_price\":29,\"features\":[{\"name\":\"inventory_tracking\",\"enabled\":true},{\"name\":\"pos_system\",\"enabled\":true},{\"name\":\"basic_reporting\",\"enabled\":true}]}','basic',NULL,NULL,'{\"currency\":\"USD\",\"weight_unit\":\"lb\",\"tax_rate\":8.25,\"low_stock_threshold\":10,\"expiry_warning_days\":7,\"enable_sms_notifications\":true,\"enable_email_notifications\":true}','{\"users_count\":0,\"products_count\":0,\"storage_used\":0,\"api_calls_this_month\":0}','{\"max_users\":3,\"max_products\":100,\"max_storage_mb\":1000,\"max_api_calls_per_month\":1000}','active','paid',NULL,NULL,'2026-02-19 20:36:19','2026-03-21 07:17:05','2026-03-21 07:17:05'),(4,'9ac55e34-20dd-435d-9509-20e58dc9d5cc','ramcar.localhost','tenant_d378fa7ceaa4','tenant_d378fa7cea','eyJpdiI6IllOeVZNY3ZtbGVTbHNobWJiYjBxTnc9PSIsInZhbHVlIjoiam5hTGpQTk5za0F5TWxvbWdlTTUwSlJZVDE4Q29tcmpiaUliNGVlVWFvUkJGNnBrQzR0ajQ5T0RZcFZIT2t4ayIsIm1hYyI6IjVmOTBhZDJmMmFhMWFkY2VmMmQxMTlmODk3MGQ4ZmRmNTY1ZDdkOWRkMjMwZWUwYjY2ZWIwNmZmM2M4NmM4ZTAiLCJ0YWciOiIifQ==','Ramcar','areshanna088@gmail.com','Hanna','areshanna088@gmail.com',NULL,'09954277538','\"Libona\"','{\"plan\":\"premium\",\"status\":\"active\"}','premium','2026-03-21 08:13:44','2026-04-21 08:13:44','[]','[]','[]','active','paid','Please contact your administrator.','Please contact your administrator.','2026-03-21 08:13:44','2026-03-22 05:37:13',NULL),(5,'48246437-bb4d-4abf-87db-c554f63f9591','mmfoods.localhost','tenant_8ee6d14757f1','root','eyJpdiI6Im9xNnY3M2xWcmJUUFBiendIYk1ndEE9PSIsInZhbHVlIjoid3o4VXBPdXVvWjRZRWM3S0hjR1Bxdz09IiwibWFjIjoiNzE5ZTMyOWZmMTUwYjM2ZDA0OGFkYzk1ZTQ5OGQzN2JhYmE3YmVkMjY0MDQwYWU0ZmNjZDgxYjc2NDI0NDMyMiIsInRhZyI6IiJ9','mmfoods','areshanna088+mmfoods@gmail.com','Juan Dela cruz','areshanna088+mmfoods@gmail.com',NULL,'09954277538','\"8QVJ+96F\"','{\"plan\":\"premium\",\"status\":\"active\",\"billing_cycle\":\"monthly\",\"current_period_start\":\"2026-03-31\",\"current_period_end\":\"2026-05-01\"}','premium','2026-03-30 16:53:07','2026-04-30 16:53:07','[]','[]','{\"max_branches\":5,\"max_users\":null,\"max_products\":999999,\"max_monthly_transactions\":60000,\"max_api_calls_per_month\":100000,\"max_storage_mb\":20480,\"data_retention_months\":60}','active','overdue','Please contact your administrator.','Please contact your administrator.','2026-03-30 16:53:07','2026-04-11 07:40:50',NULL),(6,'4ade206e-83ec-4ce5-b9f7-8ca4101fdcb6','monterey.locathost.localhost','tenant_d7267264cedc','root','eyJpdiI6InNBZ0xtVjFxaTZnZERQUkpCRGFaQVE9PSIsInZhbHVlIjoiWVJKUVlzcXZpZ1luVk4zNzYzLzFuZz09IiwibWFjIjoiMTIxZTdkZmVlZDZjNDNhYTM0OGYyZWQxZWUwNWE3NGFlMDlkZjI4ZTM2MjdkOGVlYmFiM2M4MmFjNTgwYTRlOCIsInRhZyI6IiJ9','Monterey Meatshop','areshanna088+monterey@gmail.com','Danica Pahanggin','areshanna088+monterey@gmail.com',NULL,'09954277538','\"Don Carlos\"','{\"plan\":\"premium\",\"status\":\"active\",\"billing_cycle\":\"monthly\",\"current_period_start\":\"2026-04-01\",\"current_period_end\":\"2026-05-01\"}','premium','2026-03-31 18:54:14','2026-04-30 18:54:14','[]','[]','{\"max_branches\":5,\"max_users\":null,\"max_products\":999999,\"max_monthly_transactions\":60000,\"max_api_calls_per_month\":100000,\"max_storage_mb\":20480,\"data_retention_months\":60}','active','paid','Please contact your administrator.','Please contact your administrator.','2026-03-31 18:54:14','2026-03-31 18:54:14',NULL),(8,'7c9277a7-4c6e-4989-ba64-a5e65a750521','email-test-20260401040804.localhost','tenant_8463914dda73','root','eyJpdiI6Ikd0a2U4T3k2c0xKREwvcE9jMkl1N1E9PSIsInZhbHVlIjoiOXFzT0I2Z201cUpCdWxWa0dydHordz09IiwibWFjIjoiYTJlMjk0YzFjYTk1ODAxODZkOGRiOTM2NjIzMmIzMGZiZWIzOGM2NjkyZDk4MTY5MDM2ZGQ2MGU0ZmMyNmJkMyIsInRhZyI6IiJ9','Email Test 20260401040804','tenant.test.20260401040804@gmail.com','Email Test Admin','areshanna088@gmail.com',NULL,'09999999999','\"Test Address\"','{\"plan\":\"basic\",\"status\":\"active\",\"billing_cycle\":\"monthly\",\"current_period_start\":\"2026-04-01\",\"current_period_end\":\"2026-05-01\"}','basic','2026-03-31 20:08:04','2026-04-30 20:08:04','[]','[]','{\"max_branches\":1,\"max_users\":1,\"max_products\":100,\"max_monthly_transactions\":3000,\"max_api_calls_per_month\":0,\"max_storage_mb\":1024,\"data_retention_months\":6}','active','paid','Please contact your administrator.','Please contact your administrator.','2026-03-31 20:08:04','2026-03-31 20:08:04',NULL),(9,'b34f4ade-4193-4dd3-b5fe-3de4f8a82950','gourmet.localhost','tenant_76e11c2ffd3a','root','eyJpdiI6InpwUWlqeGRSR0loV1hCYVZpakVTd3c9PSIsInZhbHVlIjoiUWtDV2NyNzRaZzROdEtEU3ZpOUM4dz09IiwibWFjIjoiNTRiOThmYzVlMGU0NTI5N2VlNGM0YWI0ZGI5ZDVlMGE5NDE1YzZiYWFhOThiZDNkNzhlNWYzNmI2MzcxYWI2MSIsInRhZyI6IiJ9','Gourmet','areshanna088+gourmet@gmail.com','Loraigne','areshanna088+gourmet@gmail.com',NULL,'09954277538','\"Valencia\"','{\"plan\":\"standard\",\"status\":\"active\",\"billing_cycle\":\"monthly\",\"current_period_start\":\"2026-04-01\",\"current_period_end\":\"2026-05-01\"}','standard','2026-03-31 20:29:37','2026-04-30 20:29:37','[]','[]','{\"max_branches\":1,\"max_users\":3,\"max_products\":999999,\"max_monthly_transactions\":15000,\"max_api_calls_per_month\":0,\"max_storage_mb\":5120,\"data_retention_months\":24}','active','paid','Please contact your administrator.','Please contact your administrator.','2026-03-31 20:29:38','2026-03-31 20:29:38',NULL),(10,'838ea43d-ae1c-40a6-a7b3-2d297b0c7e76','3MS.localhost','tenant_2e6d14f1e632','root','eyJpdiI6InE1c2dUbWhOTng5ekFhV3BGczJqeWc9PSIsInZhbHVlIjoieEJMUEgzcFJJcVJnVTB1cmdPRGNMZz09IiwibWFjIjoiMzNiNGE0ZThkYTBhYzZiY2E0MDE1ODgyYWVhY2Q4Y2U5ODNhYjlmOWYwNmYzZWY1MzRiNjUyNWNjMjA1Y2ZjNSIsInRhZyI6IiJ9','3MS','areshanna088+3MS@gmail.com','Loraigne','areshanna088+gourmet@gmail.com',NULL,'09954277538','\"Valencia\"','{\"plan\":\"standard\",\"status\":\"active\",\"billing_cycle\":\"monthly\",\"current_period_start\":\"2026-04-01\",\"current_period_end\":\"2026-05-01\"}','standard','2026-03-31 20:33:42','2026-04-30 20:33:42','[]','[]','{\"max_branches\":1,\"max_users\":3,\"max_products\":999999,\"max_monthly_transactions\":15000,\"max_api_calls_per_month\":0,\"max_storage_mb\":5120,\"data_retention_months\":24}','active','paid','Please contact your administrator.','Please contact your administrator.','2026-03-31 20:33:42','2026-03-31 20:33:42',NULL),(11,'b97baebf-2350-41c4-9688-ee9caa77da85','bauyan.localhost','tenant_363b9211fb10','tenant_363b9211fb','eyJpdiI6IkIrbHdWTW9maDQxWlErbXRMaGN2WWc9PSIsInZhbHVlIjoiL0FycUpIcysvbk0rZWpQemZmdWtHbi9GYjJGNWRRaERLV2ZuODgxMkY2QmpPMURwcy9hWjVvaUg2RzlrbmcvNCIsIm1hYyI6Ijk5Y2EwZWVlNzMxZmUxZGUzMmNkMjMxMTA1NmQyNjI3ZThjODNhNzcyYTU2YTM3OWUzNzk4NjRlNmY3MjY3MDgiLCJ0YWciOiIifQ==','Bauyan Meatshop','areshanna088+testing@gmail.com','Loraigne','areshanna088+testing@gmail.com',NULL,'09954277538','\"8QVJ+96F\"','{\"plan\":\"standard\",\"status\":\"active\",\"billing_cycle\":\"monthly\",\"current_period_start\":\"2026-04-06\",\"current_period_end\":\"2026-05-06\"}','standard','2026-04-06 00:29:23','2026-05-06 00:29:23','[]','[]','{\"max_branches\":1,\"max_users\":3,\"max_products\":999999,\"max_monthly_transactions\":15000,\"max_api_calls_per_month\":0,\"max_storage_mb\":5120,\"data_retention_months\":24}','active','paid','Please contact your administrator.','Please contact your administrator.','2026-04-06 00:29:23','2026-04-06 00:29:23',NULL),(12,'9999164e-1a01-4475-89e1-7ad8042d8606','berts-meatshop.localhost','tenant_3d4bc7740e6e','tenant_3d4bc7740e','eyJpdiI6IlZYM0FLTVdDTWNGYU8xWlRiaGc3MlE9PSIsInZhbHVlIjoiRU9rTHZYTkg0UE1MRHRseXZxVVdGd0Fkazl0c2JuL1VzOWF2UHhrV0JkV3lybzdpWElhVkhVUjNpYldOWmNJRSIsIm1hYyI6IjE4YzI2NWYzZGY4ZTQ5N2M1MmE4M2Q2MjAwNDM4OGYyZDAyYzE1ZTdkNTRlMmM3NGY2NjM0ODQ2ZWUyYjFkZDgiLCJ0YWciOiIifQ==','Berts Meatshop','areshanna088+tab@gmail.com','Danica','areshanna088+tabs@gmail.com',NULL,'','\"Don Carlos\"','{\"plan\":\"premium\",\"status\":\"active\",\"billing_cycle\":\"monthly\",\"current_period_start\":\"2026-04-12\",\"current_period_end\":\"2026-05-12\"}','premium','2026-04-06 00:56:17','2026-05-06 00:56:17','[]','[]','{\"max_branches\":1,\"max_users\":1,\"max_products\":100,\"max_monthly_transactions\":3000,\"max_api_calls_per_month\":0,\"max_storage_mb\":1024,\"data_retention_months\":6}','active','paid','Please contact your administrator.','Please contact your administrator.','2026-04-06 00:56:17','2026-04-11 20:18:00',NULL),(13,'59fa5cd7-c18b-42ab-89c9-0dad83daee24','premium-meatshop.localhost','tenant_4d34b7e0e131','tenant_4d34b7e0e1','eyJpdiI6IlJqSkp0eHNoajltM1pzTWI5RVFDc1E9PSIsInZhbHVlIjoieVZieVcyazRMWkJZVURwZDFvOXpTQ2t0QkN0MWxWYUI4TEJNSFBRQlRSTitxL3pNRVppUS94SnBDTVkrK1dpcCIsIm1hYyI6ImVlMjgwZGUwZTNlZDdmMTlmZTc2NzNhZDQwMDk5MjBiMWYxMzdhZDAwOTc5ZmZiYmRkNmY4YTY3YzFmOTY2ZDciLCJ0YWciOiIifQ==','Premium Meatshop','areshanna088+prem@gmail.com','Maria Mercedes','areshanna088+prem@gmail.com',NULL,'','\"Valencia City\"','{\"plan\":\"premium\",\"status\":\"active\",\"billing_cycle\":\"monthly\",\"current_period_start\":\"2026-04-06\",\"current_period_end\":\"2026-05-06\"}','premium','2026-04-06 07:35:50','2026-05-06 07:35:50','[]','[]','{\"max_branches\":5,\"max_users\":null,\"max_products\":999999,\"max_monthly_transactions\":60000,\"max_api_calls_per_month\":100000,\"max_storage_mb\":20480,\"data_retention_months\":60}','active','paid','Please contact your administrator.','Please contact your administrator.','2026-04-06 07:35:50','2026-04-06 07:35:50',NULL),(14,'e37f6781-d570-4101-a6b9-f96c038f89d2','rmcc.localhost','tenant_35327d94354c','tenant_35327d9435','eyJpdiI6InhMN2syODNYVTZaMVdXanlJc2dvN1E9PSIsInZhbHVlIjoiUDhEb1YxV3oweXh0cFBzYlZBZHptYjNRbXF2WHU1TWRXVEpzUHFmamFmYVVGWXpZb25KM1o2bjh5QU1aQWlVUSIsIm1hYyI6IjY5ZTgyYjdmYzU2YzQyNjY5NWM5MGQ2YmQzM2EzNjExZjU2MmZmYWQ5NjhlOTA4Y2QyNjRhNjIwYjE4MzFmYWIiLCJ0YWciOiIifQ==','RMCC','areshanna088+rmcc@gmail.com','Rusty','areshanna088+rmcc@gmail.com',NULL,'09542518516','\"Dangcagan, Bukidnon\"','{\"plan\":\"premium\",\"status\":\"active\",\"billing_cycle\":\"monthly\",\"current_period_start\":\"2026-04-07\",\"current_period_end\":\"2026-05-07\"}','premium','2026-04-06 16:11:10','2026-05-06 16:11:10','[]','[]','{\"max_branches\":5,\"max_users\":null,\"max_products\":999999,\"max_monthly_transactions\":60000,\"max_api_calls_per_month\":100000,\"max_storage_mb\":20480,\"data_retention_months\":60}','active','paid','Please contact your administrator.','Please contact your administrator.','2026-04-06 16:11:10','2026-04-06 16:11:10',NULL),(15,'492ca180-643b-4615-8042-d697c6425cd5','lrj-meatshop.localhost','tenant_4fb538797678','tenant_4fb5387976','eyJpdiI6ImZVUnkwUXRsVDhhaVVReFFqNUo1MlE9PSIsInZhbHVlIjoiYnhLQlRYM2Z2TXBZT1Z5cnFLV1NjNmpPamRoeXNyYmVWNVFSV2FqbjYzRVVBYjRmY3BISW5NTUpzN2pNWjNNNCIsIm1hYyI6ImI5NDEzZThiMDU5YjcyMmRkYTk3NGYzMjM0NDNkZjMxZTIxZGY0YTFmY2JmZThhODdjMmRlYjdmZmZjZmI1MWIiLCJ0YWciOiIifQ==','LRJ_Meatshop','rustycamuro071605+lrj-ms@gmail.com','Rusty','rustycamuro071605@gmail.com',NULL,'09542518516','\"Valencia City\"','{\"plan\":\"standard\",\"status\":\"active\",\"billing_cycle\":\"monthly\",\"current_period_start\":\"2026-04-07\",\"current_period_end\":\"2026-05-07\"}','standard','2026-04-06 16:19:36','2026-05-06 16:19:36','[]','[]','{\"max_branches\":1,\"max_users\":3,\"max_products\":999999,\"max_monthly_transactions\":15000,\"max_api_calls_per_month\":0,\"max_storage_mb\":5120,\"data_retention_months\":24}','active','paid','Please contact your administrator.','Please contact your administrator.','2026-04-06 16:19:36','2026-04-06 16:19:36',NULL),(16,'8b5b7617-a3de-4cbe-8c48-1e7fc5761dba','princessmeatshop.localhost','tenant_e076b79539dc','tenant_e076b79539','eyJpdiI6InFJbVk5a0hTcVFkcXVxLzE5YlRQMUE9PSIsInZhbHVlIjoicjVFUTVuczZ6RVBtVHJQRlBDY1lTbi9zc0YvdHd1YjE1Rkg5dmx1aG9WNlpYSThldTAvdTZKcGVJOHhucUNLTiIsIm1hYyI6ImNlMWUzYmM3YjY0M2Y2NWI1NmJhODgxYzMzMmQ5NWJhODNmMGE2ODUyZDEzNDA4NjAwMDUzZWE2NDlkOTA1ZTUiLCJ0YWciOiIifQ==','princessmeatshop','areshanna088+princess@gmail.com','Princess','areshanna088@gmail.com',NULL,'09542518516','\"Malaybalay City\"','{\"plan\":\"premium\",\"status\":\"active\",\"billing_cycle\":\"monthly\",\"current_period_start\":\"2026-04-07\",\"current_period_end\":\"2026-05-07\"}','premium','2026-04-06 16:27:44','2026-05-06 16:27:44','[]','[]','{\"max_branches\":5,\"max_users\":null,\"max_products\":999999,\"max_monthly_transactions\":60000,\"max_api_calls_per_month\":100000,\"max_storage_mb\":20480,\"data_retention_months\":60}','active','paid','Please contact your administrator.','Please contact your administrator.','2026-04-06 16:27:44','2026-04-06 16:32:07',NULL),(19,'a357938d-cf50-44e3-b021-f461ab660eee','caberto.localhost','tenant_d54d990eea30','root','eyJpdiI6IjdvcFZXM3YwaDNHdWJJakdCYmhHSFE9PSIsInZhbHVlIjoieGVrUWxjYmtKWXJRaWo1cS96SmlrZz09IiwibWFjIjoiYjAwM2U0ODFjYTZmZDVmOTg4OTMxMWI1ODZhNzQwOWYzM2NhMmVjYWViNTFkNWY3ZWViNzlmMGEzOGJlOTQzYSIsInRhZyI6IiJ9','Caberto','areshanna088+cab@gmail.com','Caberto','areshanna088+cab@gmail.com',NULL,'','[]','{\"plan\":\"premium\",\"status\":\"active\",\"current_period_start\":\"2026-04-11\",\"current_period_end\":\"2026-05-11\"}','premium',NULL,NULL,'[]','[]','[]','active','paid','Please contact your administrator.','Please contact your administrator.','2026-04-11 05:10:43','2026-04-11 06:02:20',NULL),(20,'3599909b-405c-4a89-a279-8c07a378fc81','pampi.localhost','tenant_24954cbf4f9a','root','eyJpdiI6IlUreHM0VnZCRWxkSFhrZmVyUVNWN1E9PSIsInZhbHVlIjoiNjdMTGFla1RDNzhKZjFvVFVRVi83Zz09IiwibWFjIjoiNTE0N2E5MWE2OWNiZWQ4NzY2ZDhmMTg1MzlkY2QzYWFlNTk4NmViZTA2MzAzNjEwYjdmMjZlMDQyNmYwMDc3NiIsInRhZyI6IiJ9','Pampi','areshanna088+el@gmail.com','Stephanie','areshanna088+el@gmail.com',NULL,'','\"Canyon\"','{\"plan\":\"premium\",\"status\":\"active\",\"current_period_start\":\"2026-04-12\",\"current_period_end\":\"2026-05-12\"}','premium',NULL,NULL,'[]','[]','[]','active','paid','Please contact your administrator.','Please contact your administrator.','2026-04-11 20:52:00','2026-04-11 20:59:42',NULL),(21,'74f32b47-e354-40de-8364-967824f4afbb','jams.localhost','tenant_c9ba16171362','root','eyJpdiI6IkM0Wjk2K2R0NHdCZndsUDgvNTBsQkE9PSIsInZhbHVlIjoiSnZrTlEvTnhwaldUamJEcnVIdTkxQT09IiwibWFjIjoiMGQ5Yzc2M2IzNDMxMmQwNTQwZTJmMTJmOTNlYWNjNTMzZjEzYjUzNjlhMzMyYjk1YzE1YWM5MTQ2ZWQxYzU4NSIsInRhZyI6IiJ9','Jams','areshanna088+j@gmail.com','Jamaica','areshanna088+j@gmail.com',NULL,'09954277538','\"Valencia City\"','{\"plan\":\"premium\",\"status\":\"active\",\"current_period_start\":\"2026-04-12\",\"current_period_end\":\"2026-05-12\"}','premium',NULL,NULL,'[]','[]','[]','active','paid','Please contact your administrator.','Please contact your administrator.','2026-04-11 21:47:36','2026-04-11 21:50:29',NULL),(22,'1b8f272f-ea6e-41d0-91be-117e6bc32b92','chop.localhost','tenant_42773bcec7e4','root','eyJpdiI6InJ4YXU4bWxMUTdRaVd4U0U5eWI2RWc9PSIsInZhbHVlIjoiM1VvWUlhRjZJK1RrWkFDaHVuUEtmZz09IiwibWFjIjoiMTRmY2MxMDczYjZjN2E1OTM2M2M5NzA0ODBlMjA5MDAwOTE0Yzg1Y2M1MmFjZWZhMDI4NWIzOWRkNzI5NzRlYiIsInRhZyI6IiJ9','chop','areshanna088+h@gmail.com','HANNA','areshanna088+h@gmail.com',NULL,'09954277538','\"Libona\"','{\"plan\":\"basic\",\"status\":\"active\",\"current_period_start\":\"2026-04-12\",\"current_period_end\":\"2026-05-12\"}','basic',NULL,NULL,'[]','[]','[]','active','paid','Please contact your administrator.','Please contact your administrator.','2026-04-12 06:34:25','2026-04-12 06:36:07',NULL),(23,'970dd2aa-68b3-4841-813a-582aeacd7a59','bong.localhost','tenant_41a99cc297ab','root','eyJpdiI6IjVTc0RvOURubXZYWEZhRTdXM2pwTHc9PSIsInZhbHVlIjoiNUp5eUFTWTBwOC9QYmI4d2FNRzd5dz09IiwibWFjIjoiNDNkMWFmNTIwZWVlMjRhOTNhNThmODgwNjJhOTQxMmY3ZTRlMjI0MDc2MzhjN2E3N2E3ZWZkNjFkZjJhZWMzMiIsInRhZyI6IiJ9','Bong','areshanna088+bong@gmail.com','Bonggo','areshanna088+bong@gmail.com',NULL,'09954477568','\"Manila\"','{\"plan\":\"premium\",\"status\":\"active\",\"current_period_start\":\"2026-04-19\",\"current_period_end\":\"2026-05-19\"}','premium',NULL,NULL,'[]','[]','[]','active','paid','Please contact your administrator.','Please contact your administrator.','2026-04-19 07:02:26','2026-04-19 08:12:39',NULL),(24,'d61a00b5-cf44-4654-8935-e891e511f559','bridge.localhost',NULL,NULL,NULL,'Bridge','areshanna088+b@gmail.com','Anthony','areshanna088+b@gmail.com',NULL,'09954277538','\"Cavite\"','{\"plan\":\"basic\",\"status\":\"pending\"}','basic',NULL,NULL,'[]','[]','[]','pending','paid','Please contact your administrator.','Please contact your administrator.','2026-04-19 08:19:54','2026-04-19 08:19:54',NULL),(25,'b63c44ec-e899-4f42-9725-c1d32c1db355','san.localhost','tenant_00f6a9008849','root','eyJpdiI6IlN4UTRNOXJqTy9ybUpvemlKNEdUeHc9PSIsInZhbHVlIjoiYUZEYnZWak5ZMlE5Ylc0eWErY0dWZz09IiwibWFjIjoiMzBhNDUwMzFmZTc2YjVlMDIyNmQzZTMwMjU5OWI3YjY3ZDQxZTAyNmFmMmQ5YThhNTg2ZmU4MzU5N2E5MTUyMyIsInRhZyI6IiJ9','San','areshanna088+hh@gmail.com','Hanna','areshanna088+hh@gmail.com',NULL,'09542518516','\"Santa Fe\"','{\"plan\":\"premium\",\"status\":\"active\",\"current_period_start\":\"2026-04-20\",\"current_period_end\":\"2026-05-20\"}','premium',NULL,NULL,'[]','[]','[]','active','paid','Please contact your administrator.','Please contact your administrator.','2026-04-19 08:29:04','2026-04-19 21:01:26',NULL),(26,'96e8190d-3ca7-4261-b620-98dec6970a94','s-jane-meatshop.localhost','tenant_0c47e8ff57e1','root','eyJpdiI6ImZ1U2ozK0p6bml2OEUxakIzTVZuUXc9PSIsInZhbHVlIjoiQjBWYkY4aE5ueXlLaVFIM2h6dmVZdz09IiwibWFjIjoiZWNlMGIyODA4MDI1OWNkMGFmN2VjY2E5N2EwNmU3NGE1OGVkM2YwZWZhY2Q0OWUyNTJlOTM5NDllOGJhOWZlMSIsInRhZyI6IiJ9','S-Jane Meatshop','areshanna088+eleccion@gmail.com','Stephanie Jane Eleccion','areshanna088+eleccion@gmail.com',NULL,'09954477568','\"Valencia City\"','{\"plan\":\"premium\",\"status\":\"active\",\"current_period_start\":\"2026-04-20\",\"current_period_end\":\"2026-05-20\"}','premium',NULL,NULL,'[]','[]','[]','active','paid','Please contact your administrator.','Please contact your administrator.','2026-04-19 20:35:48','2026-04-19 20:48:12',NULL),(27,'8e0d30c6-406b-467d-84e6-140fa6a0e8d0','tender.localhost','tenant_cdfc6312b27e','root','eyJpdiI6IjBOUHRlblZZL0xCVEZHbWxyRkNVc2c9PSIsInZhbHVlIjoiNGduVklGZGh4U203ck5LbStGZEVLZz09IiwibWFjIjoiOWZiYmI3OThjMjg3MjU0NTM5MjAyNmYzODExODg4YTczNDg3ZTYzOTM5NzAwYzBjMjE3ODI0Y2ZlZjQ5NDZmMiIsInRhZyI6IiJ9','Tender','areshanna088+t@gmail.com','Laslo','areshanna088+t@gmail.com',NULL,'09954277538','\"Manila\"','{\"plan\":\"premium\",\"status\":\"active\",\"current_period_start\":\"2026-04-20\",\"current_period_end\":\"2026-05-20\"}','premium',NULL,NULL,'[]','[]','[]','active','paid','Please contact your administrator.','Please contact your administrator.','2026-04-20 09:45:07','2026-04-20 09:47:54',NULL);
/*!40000 ALTER TABLE `tenants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `units_of_measure`
--

DROP TABLE IF EXISTS `units_of_measure`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `units_of_measure` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `precision` tinyint(3) unsigned NOT NULL DEFAULT 3,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `units_of_measure_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `units_of_measure`
--

LOCK TABLES `units_of_measure` WRITE;
/*!40000 ALTER TABLE `units_of_measure` DISABLE KEYS */;
/*!40000 ALTER TABLE `units_of_measure` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `update_logs`
--

DROP TABLE IF EXISTS `update_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `update_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `from_version` varchar(255) NOT NULL,
  `to_version` varchar(255) NOT NULL,
  `status` enum('pending','downloading','installing','completed','failed') NOT NULL,
  `error_message` text DEFAULT NULL,
  `update_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`update_data`)),
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `update_logs_tenant_id_status_index` (`tenant_id`,`status`),
  KEY `update_logs_status_created_at_index` (`status`,`created_at`),
  CONSTRAINT `update_logs_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `update_logs`
--

LOCK TABLES `update_logs` WRITE;
/*!40000 ALTER TABLE `update_logs` DISABLE KEYS */;
INSERT INTO `update_logs` VALUES (1,NULL,'1.0.0','1.0.6','completed',NULL,'{\"file_path\":\"updates\\/update-1.0.6.zip\",\"source\":\"local\"}','2026-04-20 17:31:56','2026-04-20 17:31:56','2026-04-20 09:31:56','2026-04-20 09:31:56'),(2,NULL,'1.0.0','1.0.6','completed',NULL,'{\"file_path\":\"updates\\/update-1.0.6.zip\",\"source\":\"local\"}','2026-04-20 17:32:03','2026-04-20 17:32:03','2026-04-20 09:32:03','2026-04-20 09:32:03');
/*!40000 ALTER TABLE `update_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` varchar(255) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `recovery_email` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `profile` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`profile`)),
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`permissions`)),
  `preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferences`)),
  `last_login` timestamp NULL DEFAULT NULL,
  `login_attempts` int(11) NOT NULL DEFAULT 0,
  `lock_until` timestamp NULL DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_tenant_id_email_index` (`tenant_id`,`email`),
  KEY `users_tenant_id_role_index` (`tenant_id`,`role`),
  KEY `users_status_index` (`status`),
  KEY `users_recovery_email_index` (`recovery_email`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'TENR3P2TBO5','owner@premiummeatsinc..com','John Owner','owner@premiummeatsinc..com',NULL,NULL,'$2y$12$aS04wIvImi5S1di4Ob.otuX5TgUip0J8N9.5x0SpO66d1EqOvNGzW','owner','{\"first_name\":\"John\",\"last_name\":\"Owner\",\"phone\":\"+1-555-0001\",\"address\":{\"street\":\"123 Main St\",\"city\":\"Meatville\",\"state\":\"TX\",\"zip_code\":\"75001\",\"country\":\"US\"}}','{\"can_manage_users\":true,\"can_manage_inventory\":true,\"can_process_sales\":true,\"can_view_reports\":true,\"can_manage_suppliers\":true,\"can_manage_customers\":true,\"can_export_data\":true,\"can_access_api\":true}','{\"language\":\"en\",\"timezone\":\"America\\/New_York\",\"theme\":\"light\",\"email_notifications\":true,\"sms_notifications\":false}',NULL,0,NULL,'active',NULL,'2026-02-19 07:47:05','2026-02-19 07:47:05',NULL),(2,'TENR3P2TBO5','manager@premiummeatsinc..com','Jane Manager','manager@premiummeatsinc..com',NULL,NULL,'$2y$12$.9owwoFc6sd4RVCybKHnyugzeCsJkjg9w/udR27WP9YAsBdEFLDNu','manager','{\"first_name\":\"Jane\",\"last_name\":\"Manager\",\"phone\":\"+1-555-0002\",\"address\":{\"street\":\"123 Main St\",\"city\":\"Meatville\",\"state\":\"TX\",\"zip_code\":\"75001\",\"country\":\"US\"}}','{\"can_manage_users\":false,\"can_manage_inventory\":true,\"can_process_sales\":true,\"can_view_reports\":true,\"can_manage_suppliers\":true,\"can_manage_customers\":true,\"can_export_data\":true,\"can_access_api\":false}','{\"language\":\"en\",\"timezone\":\"America\\/New_York\",\"theme\":\"light\",\"email_notifications\":true,\"sms_notifications\":true}',NULL,0,NULL,'active',NULL,'2026-02-19 07:47:05','2026-02-19 07:47:05',NULL),(3,'TENR3P2TBO5','cashier@premiummeatsinc..com','Mike Cashier','cashier@premiummeatsinc..com',NULL,NULL,'$2y$12$2Pid7eCEkEzvtXMNwSFZRucHTeGCHyiEr7RPnZfNKbrrgOcAsmrz6','cashier','{\"first_name\":\"Mike\",\"last_name\":\"Cashier\",\"phone\":\"+1-555-0003\",\"address\":{\"street\":\"123 Main St\",\"city\":\"Meatville\",\"state\":\"TX\",\"zip_code\":\"75001\",\"country\":\"US\"}}','{\"can_manage_users\":false,\"can_manage_inventory\":false,\"can_process_sales\":true,\"can_view_reports\":false,\"can_manage_suppliers\":false,\"can_manage_customers\":true,\"can_export_data\":false,\"can_access_api\":false}','{\"language\":\"en\",\"timezone\":\"America\\/New_York\",\"theme\":\"light\",\"email_notifications\":false,\"sms_notifications\":false}',NULL,0,NULL,'active',NULL,'2026-02-19 07:47:05','2026-02-19 07:47:05',NULL),(4,'TENR3P2TBO5','inventory@premiummeatsinc..com','Sarah Inventory','inventory@premiummeatsinc..com',NULL,NULL,'$2y$12$jdhGZrc6Y7M2jnYGUHNWf.FPGWUl9zyFzx/S/4aVXsLDVeLKbZqni','inventory_staff','{\"first_name\":\"Sarah\",\"last_name\":\"Inventory\",\"phone\":\"+1-555-0004\",\"address\":{\"street\":\"123 Main St\",\"city\":\"Meatville\",\"state\":\"TX\",\"zip_code\":\"75001\",\"country\":\"US\"}}','{\"can_manage_users\":false,\"can_manage_inventory\":true,\"can_process_sales\":false,\"can_view_reports\":false,\"can_manage_suppliers\":false,\"can_manage_customers\":false,\"can_export_data\":false,\"can_access_api\":false}','{\"language\":\"en\",\"timezone\":\"America\\/New_York\",\"theme\":\"light\",\"email_notifications\":true,\"sms_notifications\":false}',NULL,0,NULL,'active',NULL,'2026-02-19 07:47:05','2026-02-19 07:47:05',NULL),(5,'TENZIJMZJAJ','owner@localbutchershop.com','John Owner','owner@localbutchershop.com',NULL,NULL,'$2y$12$mWl11UIrxNTnXreJhkcaPemS/LlpQbl9iOs0VoU2I8zdD8cclFoyu','owner','{\"first_name\":\"John\",\"last_name\":\"Owner\",\"phone\":\"+1-555-0001\",\"address\":{\"street\":\"456 Oak Ave\",\"city\":\"Butchertown\",\"state\":\"CA\",\"zip_code\":\"90210\",\"country\":\"US\"}}','{\"can_manage_users\":true,\"can_manage_inventory\":true,\"can_process_sales\":true,\"can_view_reports\":true,\"can_manage_suppliers\":true,\"can_manage_customers\":true,\"can_export_data\":true,\"can_access_api\":true}','{\"language\":\"en\",\"timezone\":\"America\\/New_York\",\"theme\":\"light\",\"email_notifications\":true,\"sms_notifications\":false}',NULL,0,NULL,'active',NULL,'2026-02-19 07:47:05','2026-02-19 07:47:05',NULL),(6,'TENZIJMZJAJ','manager@localbutchershop.com','Bob Manager','manager@localbutchershop.com',NULL,NULL,'$2y$12$/Tc9PwD8wz6e7frM1ak/lOkyBjtHAblLBcObzrPV3r/EATJ5vFhg6','manager','{\"first_name\":\"Bob\",\"last_name\":\"Manager\",\"phone\":\"+1-555-0101\",\"address\":{\"street\":\"456 Oak Ave\",\"city\":\"Butchertown\",\"state\":\"CA\",\"zip_code\":\"90210\",\"country\":\"US\"}}','{\"can_manage_users\":false,\"can_manage_inventory\":true,\"can_process_sales\":true,\"can_view_reports\":true,\"can_manage_suppliers\":true,\"can_manage_customers\":true,\"can_export_data\":true,\"can_access_api\":false}','{\"language\":\"en\",\"timezone\":\"America\\/New_York\",\"theme\":\"light\",\"email_notifications\":true,\"sms_notifications\":false}',NULL,0,NULL,'active',NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06',NULL),(7,'TENZIJMZJAJ','cashier@localbutchershop.com','Lisa Cashier','cashier@localbutchershop.com',NULL,NULL,'$2y$12$wmEZeaHCq3iLt2GH2r9mKugh9BTVsxTts9WXONlXMxrMx1i.f9FcO','cashier','{\"first_name\":\"Lisa\",\"last_name\":\"Cashier\",\"phone\":\"+1-555-0102\",\"address\":{\"street\":\"456 Oak Ave\",\"city\":\"Butchertown\",\"state\":\"CA\",\"zip_code\":\"90210\",\"country\":\"US\"}}','{\"can_manage_users\":false,\"can_manage_inventory\":false,\"can_process_sales\":true,\"can_view_reports\":false,\"can_manage_suppliers\":false,\"can_manage_customers\":true,\"can_export_data\":false,\"can_access_api\":false}','{\"language\":\"en\",\"timezone\":\"America\\/New_York\",\"theme\":\"light\",\"email_notifications\":false,\"sms_notifications\":false}',NULL,0,NULL,'active',NULL,'2026-02-19 07:47:06','2026-02-19 07:47:06',NULL),(8,NULL,'signupfixcheck1773671403','Signup Fix Check','signupfixcheck1773671403@example.com',NULL,NULL,'$2y$12$Q862iek/CTpHGnIBxysXouvos0DqxO/gf8VwsS8cAU6OYMlyJnVa.','owner','{\"first_name\":\"Signup\",\"last_name\":\"Check\",\"full_name\":\"Signup Fix Check\"}','{\"can_manage_users\":true,\"can_manage_inventory\":true,\"can_process_sales\":true,\"can_view_reports\":true,\"can_manage_suppliers\":true,\"can_manage_customers\":true,\"can_export_data\":true,\"can_access_api\":true}',NULL,NULL,0,NULL,'active',NULL,'2026-03-16 06:30:04','2026-03-16 06:30:04',NULL),(9,'b97baebf-2350-41c4-9688-ee9caa77da85','hannnaares','Hanna Ares','areshanna088@gmail.com',NULL,NULL,'$2y$12$bUyJbeh6oLxw5Ehb1sxvPesf02pq3961WgZaT0otjT9aSH4jzY/Ii','owner','{\"first_name\":\"Hannna\",\"last_name\":\"Ares\",\"full_name\":\"Hannna Ares\"}','{\"can_manage_users\":true,\"can_manage_inventory\":true,\"can_process_sales\":true,\"can_view_reports\":true,\"can_manage_suppliers\":true,\"can_manage_customers\":true,\"can_export_data\":true,\"can_access_api\":true}',NULL,NULL,0,NULL,'active',NULL,'2026-03-16 06:30:27','2026-04-19 08:27:09',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `versions`
--

DROP TABLE IF EXISTS `versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `versions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(20) NOT NULL,
  `release_name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `type` enum('major','minor','patch','hotfix') NOT NULL,
  `status` enum('development','testing','stable','deprecated') NOT NULL,
  `release_date` datetime DEFAULT NULL,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `fixes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`fixes`)),
  `requirements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`requirements`)),
  `download_url` varchar(255) DEFAULT NULL,
  `checksum` varchar(255) DEFAULT NULL,
  `is_mandatory` tinyint(1) NOT NULL DEFAULT 0,
  `auto_update` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `versions_version_status_index` (`version`,`status`),
  KEY `versions_type_status_index` (`type`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `versions`
--

LOCK TABLES `versions` WRITE;
/*!40000 ALTER TABLE `versions` DISABLE KEYS */;
INSERT INTO `versions` VALUES (1,'1','new update','','major','development',NULL,'[]','[]','[]',NULL,NULL,0,0,'2026-04-20 07:19:22','2026-04-20 07:19:22'),(2,'1.0.6','v1.0.6','Instructor release','patch','stable','2026-04-20 00:00:00','[]','[]','[]',NULL,'82ef7f911fa33b24ab34e2fad365114a',0,0,'2026-04-20 09:28:10','2026-04-20 09:28:10');
/*!40000 ALTER TABLE `versions` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-24 15:58:17
