-- =========================================================
--  Taskora — complete database (schema + demo content)
--  Import into MySQL/MariaDB (XAMPP phpMyAdmin → Import).
--  Creates the database 	askora automatically.
--
--  Admin login : admin@taskora.digital    / Admin@123
--  Customer    : customer@taskora.digital / Demo@123
--  Provider    : provider@taskora.digital / Demo@123
--  Change these passwords before going live.
-- =========================================================

CREATE DATABASE IF NOT EXISTS `taskora` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `taskora`;

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
DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` (`id`, `name`, `email`, `password`, `role`, `last_login`, `created_at`) VALUES (1,'Administrator','admin@taskora.digital','$2y$10$tmo3lw7wNhJbcky1kqfDIup54UEegh/eJrRu.hHXPQH9Q2DJEJcuC','superadmin',NULL,'2026-10-11 10:35:25');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `booking_feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booking_feedback` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int(10) unsigned NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `professionalism` tinyint(4) DEFAULT NULL,
  `communication` tinyint(4) DEFAULT NULL,
  `punctuality` tinyint(4) DEFAULT NULL,
  `cleanliness` tinyint(4) DEFAULT NULL,
  `behaviour` tinyint(4) DEFAULT NULL,
  `recommend` tinyint(1) NOT NULL DEFAULT '1',
  `comments` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_id` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `booking_feedback` WRITE;
/*!40000 ALTER TABLE `booking_feedback` DISABLE KEYS */;
/*!40000 ALTER TABLE `booking_feedback` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `booking_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booking_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `role` enum('customer','provider') COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_booking` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `booking_messages` WRITE;
/*!40000 ALTER TABLE `booking_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `booking_messages` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `booking_offers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booking_offers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int(10) unsigned NOT NULL,
  `by_role` enum('customer','provider') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `message` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_booking` (`booking_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `booking_offers` WRITE;
/*!40000 ALTER TABLE `booking_offers` DISABLE KEYS */;
INSERT INTO `booking_offers` (`id`, `booking_id`, `by_role`, `amount`, `message`, `created_at`) VALUES (1,1,'customer',5000.00,'Budget for both units','2026-10-11 10:35:25');
INSERT INTO `booking_offers` (`id`, `booking_id`, `by_role`, `amount`, `message`, `created_at`) VALUES (2,1,'provider',4400.00,'Includes gas top-up check and drain-pipe cleaning for both units.','2026-10-11 10:35:25');
/*!40000 ALTER TABLE `booking_offers` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bookings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ref` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `provider_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cnic` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `map_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `urgency` enum('normal','urgent','emergency') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `preferred_date` date DEFAULT NULL,
  `budget` decimal(12,2) DEFAULT NULL,
  `quotation_required` tinyint(1) NOT NULL DEFAULT '0',
  `payment_method` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('requested','provider_joined','quoted','deal_locked','in_progress','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'requested',
  `deal_amount` decimal(12,2) DEFAULT NULL,
  `source` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'portal',
  `admin_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ref` (`ref`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_provider` (`provider_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
INSERT INTO `bookings` (`id`, `ref`, `customer_id`, `provider_id`, `name`, `phone`, `email`, `cnic`, `city`, `address`, `map_link`, `category`, `service`, `description`, `urgency`, `preferred_date`, `budget`, `quotation_required`, `payment_method`, `status`, `deal_amount`, `source`, `admin_note`, `created_at`, `updated_at`) VALUES (1,'TSK-DEMO-0001',1,2,'Ayesha Khan','03001234567','customer@taskora.digital',NULL,'Islamabad','House 12, Street 4, F-7/2',NULL,'Home Services','AC Repair & Installation','Two split ACs need a full service before summer; one is leaking water.','normal','2026-10-13',5000.00,0,'Cash','quoted',4400.00,'portal',NULL,'2026-10-11 10:35:25',NULL);
INSERT INTO `bookings` (`id`, `ref`, `customer_id`, `provider_id`, `name`, `phone`, `email`, `cnic`, `city`, `address`, `map_link`, `category`, `service`, `description`, `urgency`, `preferred_date`, `budget`, `quotation_required`, `payment_method`, `status`, `deal_amount`, `source`, `admin_note`, `created_at`, `updated_at`) VALUES (2,'TSK-DEMO-0002',1,NULL,'Ayesha Khan','03001234567','customer@taskora.digital',NULL,'Islamabad','House 12, Street 4, F-7/2',NULL,'Home Services','House & Deep Cleaning','Deep cleaning for a 3-bed portion including kitchen and washrooms.','urgent','2026-10-15',9000.00,0,'Easypaisa','requested',NULL,'portal',NULL,'2026-10-11 10:35:25',NULL);
/*!40000 ALTER TABLE `bookings` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `catalogue_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalogue_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `count_label` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'green',
  `note` text COLLATE utf8mb4_unicode_ci,
  `body` text COLLATE utf8mb4_unicode_ci,
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `catalogue_groups` WRITE;
/*!40000 ALTER TABLE `catalogue_groups` DISABLE KEYS */;
INSERT INTO `catalogue_groups` (`id`, `name`, `count_label`, `tone`, `note`, `body`, `sort_order`, `is_active`) VALUES (1,'Home Services','10 groups · 74 specialisations','green','','Electrical Services: Electricians, Wiring Specialists, Distribution Board Installation, Generator Installation, UPS Installation, Smart Home Electrical Systems, Lighting Installation, Emergency Electricians\nPlumbing Services: Plumbers, Water Supply Specialists, Drainage Experts, Leak Detection, Sewerage Repair, Water Tank Installation, Bathroom Plumbing, Kitchen Plumbing\nHVAC & AC Services: AC Installation, AC Repair, AC Maintenance, HVAC Engineers, Ventilation Systems, Chiller Specialists, Duct Cleaning, Refrigeration Technicians\nHome Cleaning: Deep Cleaning, Sofa Cleaning, Carpet Cleaning, Kitchen Cleaning, Bathroom Cleaning, Glass Cleaning, Water Tank Cleaning, Post-Construction Cleaning\nPest Control: Termite Control, General Pest Control, Rodent Control, Mosquito Control, Fumigation Services\nAppliance Repair: Refrigerator, Washing Machine, Microwave, Oven, Dishwasher, Water Dispenser, Small Appliances\nCarpentry: Furniture Repair, Custom Furniture, Wardrobes, Kitchen Cabinets, Wooden Flooring, Door Installation\nPainting Services: Interior Painting, Exterior Painting, Texture Paint, Decorative Paint, Waterproof Coating\nGardening & Landscaping: Garden Design, Lawn Maintenance, Tree Trimming, Irrigation, Plant Supply\nMoving & Shifting: House Moving, Packing, Loading, Unloading, Storage Solutions',10,1);
INSERT INTO `catalogue_groups` (`id`, `name`, `count_label`, `tone`, `note`, `body`, `sort_order`, `is_active`) VALUES (2,'Construction & Property','20 specialisations','green-dark','','Civil Engineers, Architects, Interior Designers, Structural Engineers, Quantity Surveyors, Project Managers, Building Contractors, Renovation Specialists, Masonry Experts, Tile Installation, Marble & Granite, Aluminum & Glass, Steel Fabrication, Roofing Contractors, Waterproofing, False Ceiling, Flooring Specialists, Property Inspectors, Building Maintenance, Smart Building Automation',20,1);
INSERT INTO `catalogue_groups` (`id`, `name`, `count_label`, `tone`, `note`, `body`, `sort_order`, `is_active`) VALUES (3,'Property & Real Estate','10 specialisations','black','','Property Consultants, Real Estate Agents, Property Valuation Experts, Property Documentation, Property Verification, Transfer Specialists, Investment Advisors, Facility Managers, Rental Management, Property Marketing',30,1);
INSERT INTO `catalogue_groups` (`id`, `name`, `count_label`, `tone`, `note`, `body`, `sort_order`, `is_active`) VALUES (4,'Business Services','10 specialisations','gold','','Chartered Accountants, Tax Consultants, Auditors, Bookkeepers, Financial Consultants, Payroll Specialists, Company Registration, Corporate Secretarial Services, Business Licensing, Compliance Consultants',40,1);
INSERT INTO `catalogue_groups` (`id`, `name`, `count_label`, `tone`, `note`, `body`, `sort_order`, `is_active`) VALUES (5,'Legal Services','11 specialisations','green','','Lawyers, Corporate Lawyers, Property Lawyers, Family Lawyers, Civil Lawyers, Criminal Lawyers, Labour Lawyers, Tax Lawyers, Contract Drafting, Legal Documentation, Arbitration & Mediation',50,1);
INSERT INTO `catalogue_groups` (`id`, `name`, `count_label`, `tone`, `note`, `body`, `sort_order`, `is_active`) VALUES (6,'HR & Recruitment','8 specialisations','green-dark','','HR Consultants, Recruiters, Payroll Management, Background Verification, Executive Search, Corporate Training, Performance Management, HR Outsourcing',60,1);
INSERT INTO `catalogue_groups` (`id`, `name`, `count_label`, `tone`, `note`, `body`, `sort_order`, `is_active`) VALUES (7,'IT & Technology','15 specialisations','black','','Software Developers, Web Developers, Mobile App Developers, AI Engineers, Data Scientists, Cybersecurity Specialists, Cloud Engineers, DevOps Engineers, ERP Consultants, CRM Specialists, UI/UX Designers, QA Engineers, Network Engineers, IT Support, Database Administrators',70,1);
INSERT INTO `catalogue_groups` (`id`, `name`, `count_label`, `tone`, `note`, `body`, `sort_order`, `is_active`) VALUES (8,'Digital Marketing','14 specialisations','gold','','SEO Experts, Social Media Managers, PPC Specialists, Google Ads, Meta Ads, Email Marketing, Content Writers, Copywriters, Graphic Designers, Brand Consultants, Video Editors, Motion Graphics, Photography, Videography',80,1);
INSERT INTO `catalogue_groups` (`id`, `name`, `count_label`, `tone`, `note`, `body`, `sort_order`, `is_active`) VALUES (9,'Education','9 specialisations','green','','Tutors, Language Teachers, Science Teachers, Mathematics Teachers, Computer Instructors, Quran Tutors, Test Preparation, Online Coaching, Professional Trainers',90,1);
INSERT INTO `catalogue_groups` (`id`, `name`, `count_label`, `tone`, `note`, `body`, `sort_order`, `is_active`) VALUES (10,'Health & Wellness','8 specialisations','green-dark','','Doctors (Partner Network), Physiotherapists, Nutritionists, Fitness Trainers, Yoga Instructors, Mental Wellness Coaches, Home Nursing, Elder Care',100,1);
INSERT INTO `catalogue_groups` (`id`, `name`, `count_label`, `tone`, `note`, `body`, `sort_order`, `is_active`) VALUES (11,'Security Services','7 specialisations','black','','Security Guards, CCTV Installation, Access Control, Alarm Systems, Fire Safety, Security Audits, Event Security',110,1);
INSERT INTO `catalogue_groups` (`id`, `name`, `count_label`, `tone`, `note`, `body`, `sort_order`, `is_active`) VALUES (12,'Logistics & Transport','7 specialisations','gold','','Courier Services, Freight Forwarding, Packers & Movers, Warehousing, Fleet Management, Dispatch Services, Last-Mile Delivery',120,1);
INSERT INTO `catalogue_groups` (`id`, `name`, `count_label`, `tone`, `note`, `body`, `sort_order`, `is_active`) VALUES (13,'Event Services','8 specialisations','green','','Event Planners, Wedding Planners, Decorators, Caterers, Sound & Lighting, Photography, Videography, Stage Management',130,1);
INSERT INTO `catalogue_groups` (`id`, `name`, `count_label`, `tone`, `note`, `body`, `sort_order`, `is_active`) VALUES (14,'Corporate Services','7 specialisations','green-dark','','Office Administration, Reception Services, Document Management, Office Maintenance, Office Relocation, Business Process Outsourcing, Virtual Assistants',140,1);
INSERT INTO `catalogue_groups` (`id`, `name`, `count_label`, `tone`, `note`, `body`, `sort_order`, `is_active`) VALUES (15,'Insurance & Financial Services','Partner-based · 16 specialisations','black','Taskora only introduces customers to licensed banks, insurers and brokerages — it never holds funds or underwrites policies itself.','Insurance Advisors, Health Insurance, Life Insurance, Motor Insurance, Property Insurance, SME Insurance, Takaful Services, Savings Plans, Budget Planning, Gold Savings, Retirement Planning, Mutual Fund Referrals, Investment Referrals, SME Financing, Invoice Financing, Freelancer Banking Solutions',150,1);
/*!40000 ALTER TABLE `catalogue_groups` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `short_desc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `items` text COLLATE utf8mb4_unicode_ci,
  `meta_title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` (`id`, `code`, `title`, `slug`, `icon`, `short_desc`, `description`, `items`, `meta_title`, `meta_description`, `sort_order`, `is_active`) VALUES (1,'T01','Home Services','home-services','home','Everyday upkeep handled by vetted technicians — cleaning, repairs, cooling and more, scheduled around you.','<p>Everyday upkeep handled by vetted technicians — cleaning, repairs, cooling and more, scheduled around you.</p><p>Every home services professional on Taskora is verified before activation, quotes transparently before work begins and is rated by customers after every job. Book online, track progress live and pay securely — in Islamabad, Rawalpindi, Lahore, Karachi and beyond.</p>','House & Deep Cleaning\nPlumbing & Electrical\nAC Repair & Installation\nCarpentry & Painting\nPest Control & Disinfection','Home Services in Pakistan — Book Verified Professionals | Taskora','Book verified home services professionals on Taskora. Everyday upkeep handled by vetted technicians — cleaning, repairs, cooling and more, scheduled around you.',10,1);
INSERT INTO `categories` (`id`, `code`, `title`, `slug`, `icon`, `short_desc`, `description`, `items`, `meta_title`, `meta_description`, `sort_order`, `is_active`) VALUES (2,'T02','Construction & Renovation','construction-and-renovation','build','From grey structure to final finish, with qualified contractors and transparent BOQs at every stage.','<p>From grey structure to final finish, with qualified contractors and transparent BOQs at every stage.</p><p>Every construction & renovation professional on Taskora is verified before activation, quotes transparently before work begins and is rated by customers after every job. Book online, track progress live and pay securely — in Islamabad, Rawalpindi, Lahore, Karachi and beyond.</p>','Grey Structure & Finishing\nRenovation & Remodeling\nWaterproofing & Flooring\nProject Management\nQuantity Surveying & BOQ','Construction & Renovation in Pakistan — Book Verified Professionals | Taskora','Book verified construction & renovation professionals on Taskora. From grey structure to final finish, with qualified contractors and transparent BOQs at every stage.',20,1);
INSERT INTO `categories` (`id`, `code`, `title`, `slug`, `icon`, `short_desc`, `description`, `items`, `meta_title`, `meta_description`, `sort_order`, `is_active`) VALUES (3,'T03','Architects & Interior Design','architects-and-interior-design','compass','Registered architects and designers who turn ideas into buildable, beautiful spaces.','<p>Registered architects and designers who turn ideas into buildable, beautiful spaces.</p><p>Every architects & interior design professional on Taskora is verified before activation, quotes transparently before work begins and is rated by customers after every job. Book online, track progress live and pay securely — in Islamabad, Rawalpindi, Lahore, Karachi and beyond.</p>','Architectural Design\nInterior & Landscape Design\n3D Rendering & BIM\nStructural & MEP Design\nRenovation Consultancy','Architects & Interior Design in Pakistan — Book Verified Professionals | Taskora','Book verified architects & interior design professionals on Taskora. Registered architects and designers who turn ideas into buildable, beautiful spaces.',30,1);
INSERT INTO `categories` (`id`, `code`, `title`, `slug`, `icon`, `short_desc`, `description`, `items`, `meta_title`, `meta_description`, `sort_order`, `is_active`) VALUES (4,'T04','Legal Services','legal-services','scale','Qualified lawyers for contracts, property, family matters and disputes — clear advice, fair fees.','<p>Qualified lawyers for contracts, property, family matters and disputes — clear advice, fair fees.</p><p>Every legal services professional on Taskora is verified before activation, quotes transparently before work begins and is rated by customers after every job. Book online, track progress live and pay securely — in Islamabad, Rawalpindi, Lahore, Karachi and beyond.</p>','Corporate & Contract Law\nProperty & Family Law\nLegal Documentation\nDispute Resolution\nCompany Registration','Legal Services in Pakistan — Book Verified Professionals | Taskora','Book verified legal services professionals on Taskora. Qualified lawyers for contracts, property, family matters and disputes — clear advice, fair fees.',40,1);
INSERT INTO `categories` (`id`, `code`, `title`, `slug`, `icon`, `short_desc`, `description`, `items`, `meta_title`, `meta_description`, `sort_order`, `is_active`) VALUES (5,'T05','Chartered Accountants & Taxation','chartered-accountants-and-taxation','calc','Chartered professionals for tax, audit, registrations and books that stay in order.','<p>Chartered professionals for tax, audit, registrations and books that stay in order.</p><p>Every chartered accountants & taxation professional on Taskora is verified before activation, quotes transparently before work begins and is rated by customers after every job. Book online, track progress live and pay securely — in Islamabad, Rawalpindi, Lahore, Karachi and beyond.</p>','Tax Filing & Advisory\nAudit & Assurance\nSECP Company Registration\nBookkeeping & Payroll\nFinancial Consulting','Chartered Accountants & Taxation in Pakistan — Book Verified Professionals | Taskora','Book verified chartered accountants & taxation professionals on Taskora. Chartered professionals for tax, audit, registrations and books that stay in order.',50,1);
INSERT INTO `categories` (`id`, `code`, `title`, `slug`, `icon`, `short_desc`, `description`, `items`, `meta_title`, `meta_description`, `sort_order`, `is_active`) VALUES (6,'T06','IT Services & Software Solutions','it-services-and-software-solutions','code','Engineers and specialists who build, secure and support the technology your business runs on.','<p>Engineers and specialists who build, secure and support the technology your business runs on.</p><p>Every it services & software solutions professional on Taskora is verified before activation, quotes transparently before work begins and is rated by customers after every job. Book online, track progress live and pay securely — in Islamabad, Rawalpindi, Lahore, Karachi and beyond.</p>','Web & App Development\nSoftware & AI Solutions\nCloud & Cybersecurity\nIT Support & Networking\nData Entry & Analytics','IT Services & Software Solutions in Pakistan — Book Verified Professionals | Taskora','Book verified it services & software solutions professionals on Taskora. Engineers and specialists who build, secure and support the technology your business runs on.',60,1);
INSERT INTO `categories` (`id`, `code`, `title`, `slug`, `icon`, `short_desc`, `description`, `items`, `meta_title`, `meta_description`, `sort_order`, `is_active`) VALUES (7,'T07','Tutors & Education','tutors-and-education','book','Verified tutors and trainers, at home or online, for every grade, test and skill.','<p>Verified tutors and trainers, at home or online, for every grade, test and skill.</p><p>Every tutors & education professional on Taskora is verified before activation, quotes transparently before work begins and is rated by customers after every job. Book online, track progress live and pay securely — in Islamabad, Rawalpindi, Lahore, Karachi and beyond.</p>','Home & Online Tutors\nTest Preparation\nLanguage Coaching\nSkill Development\nComputer Training','Tutors & Education in Pakistan — Book Verified Professionals | Taskora','Book verified tutors & education professionals on Taskora. Verified tutors and trainers, at home or online, for every grade, test and skill.',70,1);
INSERT INTO `categories` (`id`, `code`, `title`, `slug`, `icon`, `short_desc`, `description`, `items`, `meta_title`, `meta_description`, `sort_order`, `is_active`) VALUES (8,'T08','Corporate & Business Services','corporate-and-business-services','briefcase','Support for founders and companies — from setup and research to strategy and day-to-day admin.','<p>Support for founders and companies — from setup and research to strategy and day-to-day admin.</p><p>Every corporate & business services professional on Taskora is verified before activation, quotes transparently before work begins and is rated by customers after every job. Book online, track progress live and pay securely — in Islamabad, Rawalpindi, Lahore, Karachi and beyond.</p>','Business Setup & Consulting\nVirtual Assistance\nCorporate Contracts\nMarket Research\nStrategy Advisory','Corporate & Business Services in Pakistan — Book Verified Professionals | Taskora','Book verified corporate & business services professionals on Taskora. Support for founders and companies — from setup and research to strategy and day-to-day admin.',80,1);
INSERT INTO `categories` (`id`, `code`, `title`, `slug`, `icon`, `short_desc`, `description`, `items`, `meta_title`, `meta_description`, `sort_order`, `is_active`) VALUES (9,'T09','Facility Management','facility-management','building','Complete upkeep for offices, plazas and campuses under managed, reportable contracts.','<p>Complete upkeep for offices, plazas and campuses under managed, reportable contracts.</p><p>Every facility management professional on Taskora is verified before activation, quotes transparently before work begins and is rated by customers after every job. Book online, track progress live and pay securely — in Islamabad, Rawalpindi, Lahore, Karachi and beyond.</p>','Building Maintenance\nJanitorial Services\nWaste Management\nHVAC & AMC Contracts\nVendor Management','Facility Management in Pakistan — Book Verified Professionals | Taskora','Book verified facility management professionals on Taskora. Complete upkeep for offices, plazas and campuses under managed, reportable contracts.',90,1);
INSERT INTO `categories` (`id`, `code`, `title`, `slug`, `icon`, `short_desc`, `description`, `items`, `meta_title`, `meta_description`, `sort_order`, `is_active`) VALUES (10,'T10','HR & Recruitment Services','hr-and-recruitment-services','users','Hire, pay, verify and train your people with experienced HR partners.','<p>Hire, pay, verify and train your people with experienced HR partners.</p><p>Every hr & recruitment services professional on Taskora is verified before activation, quotes transparently before work begins and is rated by customers after every job. Book online, track progress live and pay securely — in Islamabad, Rawalpindi, Lahore, Karachi and beyond.</p>','Recruitment & Staffing\nPayroll Management\nHR Consulting\nBackground Checks\nTraining & Certification','HR & Recruitment Services in Pakistan — Book Verified Professionals | Taskora','Book verified hr & recruitment services professionals on Taskora. Hire, pay, verify and train your people with experienced HR partners.',100,1);
INSERT INTO `categories` (`id`, `code`, `title`, `slug`, `icon`, `short_desc`, `description`, `items`, `meta_title`, `meta_description`, `sort_order`, `is_active`) VALUES (11,'T11','Digital Marketing & Media','digital-marketing-and-media','megaphone','Creative and performance teams that grow your brand online and offline.','<p>Creative and performance teams that grow your brand online and offline.</p><p>Every digital marketing & media professional on Taskora is verified before activation, quotes transparently before work begins and is rated by customers after every job. Book online, track progress live and pay securely — in Islamabad, Rawalpindi, Lahore, Karachi and beyond.</p>','SEO & Social Media\nGraphic Design\nPhotography & Videography\nBranding & Content\nPaid Advertising','Digital Marketing & Media in Pakistan — Book Verified Professionals | Taskora','Book verified digital marketing & media professionals on Taskora. Creative and performance teams that grow your brand online and offline.',110,1);
INSERT INTO `categories` (`id`, `code`, `title`, `slug`, `icon`, `short_desc`, `description`, `items`, `meta_title`, `meta_description`, `sort_order`, `is_active`) VALUES (12,'T12','Health & Wellness','health-and-wellness','heart','Caring, certified professionals for nursing, fitness, beauty and elder care at home.','<p>Caring, certified professionals for nursing, fitness, beauty and elder care at home.</p><p>Every health & wellness professional on Taskora is verified before activation, quotes transparently before work begins and is rated by customers after every job. Book online, track progress live and pay securely — in Islamabad, Rawalpindi, Lahore, Karachi and beyond.</p>','Home Nursing & Caregiving\nFitness Trainers\nBeauty & Salon\nWellness Consulting\nElderly Care','Health & Wellness in Pakistan — Book Verified Professionals | Taskora','Book verified health & wellness professionals on Taskora. Caring, certified professionals for nursing, fitness, beauty and elder care at home.',120,1);
INSERT INTO `categories` (`id`, `code`, `title`, `slug`, `icon`, `short_desc`, `description`, `items`, `meta_title`, `meta_description`, `sort_order`, `is_active`) VALUES (13,'T13','Event Management','event-management','calendar','Planners, caterers and creatives who deliver memorable weddings and corporate events.','<p>Planners, caterers and creatives who deliver memorable weddings and corporate events.</p><p>Every event management professional on Taskora is verified before activation, quotes transparently before work begins and is rated by customers after every job. Book online, track progress live and pay securely — in Islamabad, Rawalpindi, Lahore, Karachi and beyond.</p>','Event Planning & Catering\nPhotography & Décor\nVenue & Vendor Coordination\nCorporate Events\nTicketing & Sponsorship','Event Management in Pakistan — Book Verified Professionals | Taskora','Book verified event management professionals on Taskora. Planners, caterers and creatives who deliver memorable weddings and corporate events.',130,1);
INSERT INTO `categories` (`id`, `code`, `title`, `slug`, `icon`, `short_desc`, `description`, `items`, `meta_title`, `meta_description`, `sort_order`, `is_active`) VALUES (14,'T14','Logistics & Transport','logistics-and-transport','truck','Reliable moving, delivery, storage and transport — tracked from pickup to drop-off.','<p>Reliable moving, delivery, storage and transport — tracked from pickup to drop-off.</p><p>Every logistics & transport professional on Taskora is verified before activation, quotes transparently before work begins and is rated by customers after every job. Book online, track progress live and pay securely — in Islamabad, Rawalpindi, Lahore, Karachi and beyond.</p>','Packers & Movers\nCourier & Delivery\nVehicle Rentals\nWarehousing\nIntercity Transport','Logistics & Transport in Pakistan — Book Verified Professionals | Taskora','Book verified logistics & transport professionals on Taskora. Reliable moving, delivery, storage and transport — tracked from pickup to drop-off.',140,1);
INSERT INTO `categories` (`id`, `code`, `title`, `slug`, `icon`, `short_desc`, `description`, `items`, `meta_title`, `meta_description`, `sort_order`, `is_active`) VALUES (15,'T15','Security Services','security-services','shield','Trained guards and certified installers to protect homes, offices and events.','<p>Trained guards and certified installers to protect homes, offices and events.</p><p>Every security services professional on Taskora is verified before activation, quotes transparently before work begins and is rated by customers after every job. Book online, track progress live and pay securely — in Islamabad, Rawalpindi, Lahore, Karachi and beyond.</p>','CCTV & Alarm Systems\nSecurity Guards\nAccess Control\nFire Safety\nBackground Verification','Security Services in Pakistan — Book Verified Professionals | Taskora','Book verified security services professionals on Taskora. Trained guards and certified installers to protect homes, offices and events.',150,1);
INSERT INTO `categories` (`id`, `code`, `title`, `slug`, `icon`, `short_desc`, `description`, `items`, `meta_title`, `meta_description`, `sort_order`, `is_active`) VALUES (16,'T16','Insurance, Financial & Investment Services','insurance-financial-and-investment-services','coins','Introductions to licensed banks, insurers and brokerages — Taskora never holds customer funds.','<p>Introductions to licensed banks, insurers and brokerages — Taskora never holds customer funds.</p><p>Every insurance, financial & investment services professional on Taskora is verified before activation, quotes transparently before work begins and is rated by customers after every job. Book online, track progress live and pay securely — in Islamabad, Rawalpindi, Lahore, Karachi and beyond.</p>','Insurance Marketplace\nSME Financing\nInvestment Referral (Partner-Only)\nSavings & Gold Plans\nInvoice Financing','Insurance, Financial & Investment Services in Pakistan — Book Verified Professionals | Taskora','Book verified insurance, financial & investment services professionals on Taskora. Introductions to licensed banks, insurers and brokerages — Taskora never holds customer funds.',160,1);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `form_submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `form_submissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ref` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `form_type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data` longtext COLLATE utf8mb4_unicode_ci,
  `status` enum('new','in_review','resolved') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `admin_note` text COLLATE utf8mb4_unicode_ci,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`form_type`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `form_submissions` WRITE;
/*!40000 ALTER TABLE `form_submissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `form_submissions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` (`id`, `user_id`, `title`, `body`, `link`, `is_read`, `created_at`) VALUES (1,1,'Quotation received','Imran Mahmood shared a quotation of PKR 4,400 for TSK-DEMO-0001.','account/booking.php?id=1',0,'2026-10-11 10:35:25');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_description` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` (`id`, `slug`, `title`, `meta_description`, `content`, `is_active`, `updated_at`) VALUES (1,'about','About Taskora','Learn about Taskora, Pakistan\'s connected ecosystem for verified home, business and property services.','<h2>Our story</h2><p>Taskora began with a simple frustration: finding a dependable professional in Pakistan usually meant a chain of phone calls, vague quotes and no way to follow up. We set out to replace that with one calm, transparent experience.</p><h2>What we do</h2><p>Taskora connects households, housing societies and businesses with verified professionals across sixteen service categories — from deep cleaning and AC repair to architecture, legal and accounting support. Every job can be estimated, booked, tracked, paid for and reviewed in one place.</p><h2>How we work</h2><ul><li><strong>Verification first</strong> — professionals are screened before activation.</li><li><strong>Clear pricing</strong> — quotations are shared and agreed before work starts.</li><li><strong>Accountability</strong> — every completed job is rated, and ratings shape who gets matched next.</li></ul><h2>Who we are</h2><p>Taskora (Pvt.) Ltd. is registered with the SECP and is a Trovec Technologies company.</p>',1,'2026-10-11 10:35:25');
INSERT INTO `pages` (`id`, `slug`, `title`, `meta_description`, `content`, `is_active`, `updated_at`) VALUES (2,'privacy','Privacy Policy','How Taskora collects, uses and protects your personal information.','<p>This policy explains what information Taskora collects, why we collect it and the choices you have.</p><h2>Information we collect</h2><ul><li>Account details such as your name, phone number, email and city.</li><li>Booking details including address, service requirements and preferred dates.</li><li>For professionals: identity, qualification and experience information used for verification.</li><li>Basic technical data such as browser type and IP address for security.</li></ul><h2>How we use it</h2><p>We use your information to match bookings with professionals, communicate about your jobs, process payments, verify providers, resolve complaints and improve our service. We do not sell personal data.</p><h2>Sharing</h2><p>Relevant booking details are shared with the professional assigned to your job. Financial products are provided by licensed partners who apply their own privacy terms.</p><h2>Security</h2><p>Passwords are stored using one-way hashing and access to personal data is restricted to authorised staff.</p><h2>Your rights</h2><p>You may request a copy, correction or deletion of your personal data by contacting us using the details on our Contact page.</p>',1,'2026-10-11 10:35:25');
INSERT INTO `pages` (`id`, `slug`, `title`, `meta_description`, `content`, `is_active`, `updated_at`) VALUES (3,'terms','Terms & Conditions','The terms that apply when you use Taskora as a customer or a professional.','<p>By using Taskora you agree to these terms. Please read them carefully.</p><h2>The platform</h2><p>Taskora is a marketplace that connects customers with independent, verified professionals. The professional is responsible for performing the service; Taskora facilitates booking, communication, payment and dispute resolution.</p><h2>Bookings & pricing</h2><p>Estimates shown on the website are indicative. The final price is agreed through a quotation before work starts. Once a customer accepts a quotation, the deal is locked.</p><h2>Cancellations & refunds</h2><p>Bookings can be cancelled before work starts. Refund requests are reviewed within three business days using the Refund Request form.</p><h2>Professional conduct</h2><p>Professionals must keep their profile accurate, honour agreed quotations and maintain respectful conduct. Accounts may be suspended for repeated low ratings or policy breaches.</p><h2>Financial services</h2><p>Taskora does not hold customer investments. Insurance and financial products are delivered exclusively by licensed partners.</p><h2>Liability</h2><p>To the extent permitted by law, Taskora\'s liability is limited to the platform fees paid for the affected booking.</p>',1,'2026-10-11 10:35:25');
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sections` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `skey` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `page` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'home',
  `label` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tag` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` text COLLATE utf8mb4_unicode_ci,
  `emphasis` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `data` longtext COLLATE utf8mb4_unicode_ci,
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `skey` (`skey`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `sections` WRITE;
/*!40000 ALTER TABLE `sections` DISABLE KEYS */;
INSERT INTO `sections` (`id`, `skey`, `page`, `label`, `tag`, `title`, `emphasis`, `description`, `data`, `sort_order`, `is_active`, `updated_at`) VALUES (1,'promo','home','Promo Strip','','Now launching across Pakistan — your first booking is up to 20% off','','','{\"link_label\":\"Claim offer\",\"link_href\":\"#download\"}',10,1,'2026-10-11 10:35:24');
INSERT INTO `sections` (`id`, `skey`, `page`, `label`, `tag`, `title`, `emphasis`, `description`, `data`, `sort_order`, `is_active`, `updated_at`) VALUES (2,'hero','home','Hero','Pakistan\'s verified services ecosystem','One app.\nEvery service,\nevery need.','every need.','From a midnight electrician to an enterprise facility contract — discover, compare and book 10,000+ vetted professionals across 100+ services, then follow every job live until it is done.','{\"ctas\":[{\"label\":\"Download the App\",\"href\":\"#download\",\"variant\":\"gold\"},{\"label\":\"Explore Services\",\"href\":\"catalogue.php\",\"variant\":\"ghost\"}],\"stats\":[{\"value\":\"16+\",\"label\":\"Categories\"},{\"value\":\"100+\",\"label\":\"Core Services\"},{\"value\":\"10,000+\",\"label\":\"Verified Pros\"},{\"value\":\"24/7\",\"label\":\"Support\"}],\"phone_categories\":[\"Home\",\"Business\",\"Facility\",\"Construction\"],\"popular_services\":[{\"name\":\"Deep Cleaning\",\"rating\":\"4.9\",\"price\":\"from Rs 900\"},{\"name\":\"Electrician Visit\",\"rating\":\"4.8\",\"price\":\"from Rs 1,200\"},{\"name\":\"AC Service\",\"rating\":\"4.7\",\"price\":\"from Rs 2,200\"}],\"ticker\":[\"Home Services\",\"Construction\",\"Architecture\",\"Legal\",\"Chartered Accountants\",\"IT & Software\",\"Tutors & Education\",\"Corporate Services\",\"Facility Management\",\"HR & Recruitment\",\"Digital Marketing\",\"Health & Wellness\",\"Event Management\",\"Logistics & Transport\",\"Security Services\",\"Insurance & Finance\"]}',20,1,'2026-10-11 10:35:24');
INSERT INTO `sections` (`id`, `skey`, `page`, `label`, `tag`, `title`, `emphasis`, `description`, `data`, `sort_order`, `is_active`, `updated_at`) VALUES (3,'stats_bar','home','Stats Bar','','','','','{\"items\":[{\"value\":\"100+\",\"label\":\"Service Categories\"},{\"value\":\"10,000+\",\"label\":\"Verified Professionals\"},{\"value\":\"50,000+\",\"label\":\"Happy Customers\"},{\"value\":\"100%\",\"label\":\"Satisfaction Guarantee\"}]}',30,1,'2026-10-11 10:35:24');
INSERT INTO `sections` (`id`, `skey`, `page`, `label`, `tag`, `title`, `emphasis`, `description`, `data`, `sort_order`, `is_active`, `updated_at`) VALUES (4,'company','home','Who We Are','Who we are','Crafted for the way\nPakistan gets things done.','gets things done.','Taskora brings home care, business support, facility operations, architecture and construction under one roof — a single, trusted place to find the right expert and see the work through.','{\"why_items\":[\"Every professional is screened before they ever reach your door.\",\"Clear, upfront pricing — what you see is what you pay.\",\"Live status from the moment you book until the job is signed off.\",\"Protected digital payments on every single transaction.\",\"Scales from one apartment to multi-site enterprise contracts.\"],\"badge_value\":\"16\",\"badge_label\":\"service categories under one roof\"}',40,1,'2026-10-11 10:35:24');
INSERT INTO `sections` (`id`, `skey`, `page`, `label`, `tag`, `title`, `emphasis`, `description`, `data`, `sort_order`, `is_active`, `updated_at`) VALUES (5,'flow','home','Customer Journey','What sets Taskora apart','Beyond the booking.\nThe entire journey.','The entire journey.','Most apps hand you a contact and walk away. Taskora stays with you — from your very first question to the finished job, and long after it.','{\"steps\":[{\"title\":\"Learn\",\"description\":\"Guides and checklists\"},{\"title\":\"Compare\",\"description\":\"Profiles side by side\"},{\"title\":\"Estimate\",\"description\":\"Instant cost ranges\"},{\"title\":\"Plan\",\"description\":\"Dates, scope, budget\"},{\"title\":\"Book\",\"description\":\"Confirmed in minutes\"},{\"title\":\"Track\",\"description\":\"Live job status\"},{\"title\":\"Pay\",\"description\":\"Secure & digital\"},{\"title\":\"Review\",\"description\":\"Rate every job\"},{\"title\":\"Maintain\",\"description\":\"Plans & reminders\"}]}',50,1,'2026-10-11 10:35:24');
INSERT INTO `sections` (`id`, `skey`, `page`, `label`, `tag`, `title`, `emphasis`, `description`, `data`, `sort_order`, `is_active`, `updated_at`) VALUES (6,'services','home','Services Directory (heading)','Service directory — 16 categories, 100+ core services','Everything you need,\nbeautifully organised.','beautifully organised.','A curated directory spanning home, business, professional and property services — every listing held to one verification standard.','{\"disclaimer\":\"Taskora never holds customer investments. Financial products are offered only through licensed banks, insurers and brokerage partners.\"}',60,1,'2026-10-11 10:35:24');
INSERT INTO `sections` (`id`, `skey`, `page`, `label`, `tag`, `title`, `emphasis`, `description`, `data`, `sort_order`, `is_active`, `updated_at`) VALUES (7,'market_store','home','Market Store','New — materials marketplace','Taskora\nMarket Store.','Market Store.','Verified materials at wholesale rates for technicians, and itemised, honest invoices for customers — one store across every trade.','{\"pricing_tag\":\"Wholesale price engine\",\"pricing_title\":\"Honest numbers, every order.\",\"pricing_note\":\"Sample figures for illustration — live prices refresh continuously from verified distributors.\",\"headers\":[\"Product\",\"Market Price\",\"Wholesale\",\"Taskora Partner\",\"You Save\"],\"rows\":[{\"product\":\"PVC Pipe\",\"market\":\"Rs 480\",\"wholesale\":\"Rs 430\",\"partner\":\"Rs 415\",\"save\":\"Rs 65\"},{\"product\":\"Paint (20L)\",\"market\":\"Rs 11,000\",\"wholesale\":\"Rs 10,300\",\"partner\":\"Rs 9,950\",\"save\":\"Rs 1,050\"},{\"product\":\"LED Bulb\",\"market\":\"Rs 900\",\"wholesale\":\"Rs 760\",\"partner\":\"Rs 720\",\"save\":\"Rs 180\"},{\"product\":\"Cement Bag\",\"market\":\"Rs 1,600\",\"wholesale\":\"Rs 1,500\",\"partner\":\"Rs 1,450\",\"save\":\"Rs 150\"}],\"audiences\":[{\"tag\":\"For Technicians\",\"title\":\"Source like a pro\",\"description\":\"Compare live prices and have materials sent straight to site.\",\"items\":\"Verified product catalogue, One-tap quotation builder, Purchase history & invoices, Healthier cash flow\"},{\"tag\":\"For Customers\",\"title\":\"Zero surprise mark-ups\",\"description\":\"Know exactly what each material costs before work begins.\",\"items\":\"Transparent material pricing, Genuine branded products, Digital warranties, Itemised professional invoices\"},{\"tag\":\"For Suppliers\",\"title\":\"Sell to more buyers\",\"description\":\"Reach verified technicians and businesses across the country.\",\"items\":\"Bulk order access, Digital inventory tools, Nationwide reach, Demand & trend analytics\"}],\"categories\":[\"Plumbing\",\"Electrical\",\"Construction\",\"Hardware & Tools\",\"Paints & Coatings\",\"AC & Cooling\",\"Cleaning Supplies\",\"IT & Electronics\",\"Furniture & Decor\",\"Automotive\",\"Laundry Supplies\",\"Packaging & Moving\",\"Safety & Security\",\"Solar & Energy\",\"Agriculture\",\"Office Supplies\",\"HR & Uniforms\",\"Medical & First Aid\",\"Legal & Stationery\",\"Marketing & Branding\"],\"how_tag\":\"How it works\",\"how_title\":\"From search to same-day delivery.\",\"how_steps\":[{\"title\":\"Search\",\"description\":\"Find exactly the item you need.\"},{\"title\":\"Compare\",\"description\":\"Market vs. wholesale, side by side.\"},{\"title\":\"Order\",\"description\":\"Check out securely in seconds.\"},{\"title\":\"Delivered\",\"description\":\"Dispatched quickly to the site.\"},{\"title\":\"Use & Grow\",\"description\":\"Better margins, repeat clients.\"}]}',70,1,'2026-10-11 10:35:24');
INSERT INTO `sections` (`id`, `skey`, `page`, `label`, `tag`, `title`, `emphasis`, `description`, `data`, `sort_order`, `is_active`, `updated_at`) VALUES (8,'knowledge','home','Knowledge Base','Beyond the marketplace','Pakistan\'s richest\nhome & property\nknowledge library.','knowledge library.','Practical guides, checklists and estimators that help you decide with confidence — long before you book anyone.','{\"guides\":[\"Home maintenance guides\",\"Construction planning resources\",\"Architecture & interior inspiration\",\"Renovation checklists\",\"Cost estimation articles\",\"Facility management best practices\",\"Energy-saving & smart home tips\",\"Seasonal maintenance calendars\"]}',80,1,'2026-10-11 10:35:24');
INSERT INTO `sections` (`id`, `skey`, `page`, `label`, `tag`, `title`, `emphasis`, `description`, `data`, `sort_order`, `is_active`, `updated_at`) VALUES (9,'calculator','home','Cost Estimator','Smart digital tools','Try the cost\nestimator.','estimator.','See an instant, indicative price range before you book. Your professional always confirms the final figure first.','{\"note\":\"Indicative estimate based on typical bookings. The assigned professional confirms your final price before any work starts — no hidden charges.\",\"size_min\":3,\"size_max\":20,\"size_default\":5,\"size_unit\":\"Marla\",\"service_types\":[{\"id\":\"house_cleaning\",\"label\":\"House Cleaning\",\"base_price\":450},{\"id\":\"deep_cleaning\",\"label\":\"Deep Cleaning\",\"base_price\":900},{\"id\":\"electrician\",\"label\":\"Electrician Visit & Repair\",\"base_price\":1200},{\"id\":\"plumbing\",\"label\":\"Plumbing Repair\",\"base_price\":1500},{\"id\":\"ac_service\",\"label\":\"AC Service & Repair\",\"base_price\":2200},{\"id\":\"painting\",\"label\":\"Painting (per room)\",\"base_price\":3500},{\"id\":\"movers\",\"label\":\"Packers & Movers\",\"base_price\":6000}],\"frequencies\":[{\"id\":\"one_time\",\"label\":\"One-Time\",\"factor\":1},{\"id\":\"weekly\",\"label\":\"Weekly · 10% off\",\"factor\":0.9},{\"id\":\"monthly\",\"label\":\"Monthly · 15% off\",\"factor\":0.85}]}',90,1,'2026-10-11 10:35:24');
INSERT INTO `sections` (`id`, `skey`, `page`, `label`, `tag`, `title`, `emphasis`, `description`, `data`, `sort_order`, `is_active`, `updated_at`) VALUES (10,'tools','home','Planning Tools','More planning tools','Plan first. Book with confidence.','Book with confidence.','','{\"items\":[\"Construction budget calculator\",\"Paint & tile quantity calculator\",\"Solar savings calculator\",\"AMC savings calculator\",\"Home maintenance planner\",\"Property inspection checklist\",\"Project timeline planner\",\"Moving cost estimator\"]}',100,1,'2026-10-11 10:35:24');
INSERT INTO `sections` (`id`, `skey`, `page`, `label`, `tag`, `title`, `emphasis`, `description`, `data`, `sort_order`, `is_active`, `updated_at`) VALUES (11,'dashboard','home','Customer Dashboard Preview','Your account','A private dashboard\nfor every customer.','for every customer.','Follow bookings, settle payments, keep your favourite professionals close and rate every job — all in one elegant account.','{\"nav\":[\"Bookings\",\"Payments & Invoices\",\"Saved Professionals\",\"My Reviews\",\"Account Settings\"],\"user_name\":\"Ayesha Khan\",\"user_email\":\"ayesha@example.com\",\"cards\":[{\"label\":\"Total Bookings\",\"value\":\"12\"},{\"label\":\"Total Spent\",\"value\":\"PKR 38,400\"},{\"label\":\"Avg. Rating Given\",\"value\":\"4.9★\"}],\"bookings\":[{\"who\":\"Bilal Ahmed — Electrician\",\"what\":\"Circuit repair · Today, 4:30 PM · DHA Phase 5\",\"status\":\"En Route\"},{\"who\":\"CleanPro Team — Deep Cleaning\",\"what\":\"3-bed apartment · Tomorrow, 10:00 AM\",\"status\":\"Confirmed\"}],\"rate_title\":\"How was your last service?\",\"rate_subtitle\":\"AC Service with Imran M. — completed Tuesday\"}',110,1,'2026-10-11 10:35:25');
INSERT INTO `sections` (`id`, `skey`, `page`, `label`, `tag`, `title`, `emphasis`, `description`, `data`, `sort_order`, `is_active`, `updated_at`) VALUES (12,'reviews','home','Ratings & Reviews','Ratings & reviews','Rated highly by\nreal bookings.','real bookings.','','{\"score\":\"4.8\",\"count\":\"Across 28,600+ reviews\",\"note\":\"Sample reviews shown for demonstration.\",\"distribution\":[{\"stars\":5,\"percent\":78},{\"stars\":4,\"percent\":15},{\"stars\":3,\"percent\":4},{\"stars\":2,\"percent\":2},{\"stars\":1,\"percent\":1}]}',120,1,'2026-10-11 10:35:25');
INSERT INTO `sections` (`id`, `skey`, `page`, `label`, `tag`, `title`, `emphasis`, `description`, `data`, `sort_order`, `is_active`, `updated_at`) VALUES (13,'career','home','Professional Career Path','Professional network','From first sign-up\nto regional leader.','to regional leader.','Every professional passes through one verified pipeline — then rises through a career ladder that rewards great work, openly and fairly.','{\"steps\":[{\"title\":\"Registration\",\"description\":\"Sign up digitally in minutes.\"},{\"title\":\"Verification\",\"description\":\"Identity and documents reviewed.\"},{\"title\":\"Training\",\"description\":\"Skills assessed, onboarding done.\"},{\"title\":\"Activation\",\"description\":\"Your profile goes live.\"},{\"title\":\"Job Assignment\",\"description\":\"Matched with nearby bookings.\"},{\"title\":\"Performance\",\"description\":\"Rated after every job.\"},{\"title\":\"Growth\",\"description\":\"Unlock the next level.\"}],\"levels\":[\"Bronze\",\"Silver\",\"Gold\",\"Platinum\",\"Master Pro\",\"Team Leader\",\"Regional Manager\"]}',130,1,'2026-10-11 10:35:25');
INSERT INTO `sections` (`id`, `skey`, `page`, `label`, `tag`, `title`, `emphasis`, `description`, `data`, `sort_order`, `is_active`, `updated_at`) VALUES (14,'solutions','home','Business Solutions','Beyond the household','Solutions sized\nfor every scale.','for every scale.','One partner, one dashboard and complete operational support — whether you run a home, a housing society or a portfolio of sites.','{\"items\":[{\"tag\":\"For Business\",\"title\":\"Business Solutions\",\"description\":\"Run facility operations, maintenance requests, vendors and recurring services from one central platform.\",\"features\":\"Corporate office facility management, Recurring maintenance contracts, Vendor consolidation, Digital reporting dashboard\"},{\"tag\":\"For Communities\",\"title\":\"Housing Society Solutions\",\"description\":\"Purpose-built digital tools for modern residential communities and the committees that run them.\",\"features\":\"Resident service portal, Complaint & visitor management, Community announcements, Society-wide dashboard\"},{\"tag\":\"For Enterprise\",\"title\":\"Enterprise Solutions\",\"description\":\"Multi-site coverage for hospitality, healthcare, education and industrial facilities.\",\"features\":\"Multi-location coordination, Compliance-ready reporting, Dedicated account management, Custom SLAs\"}]}',140,1,'2026-10-11 10:35:25');
INSERT INTO `sections` (`id`, `skey`, `page`, `label`, `tag`, `title`, `emphasis`, `description`, `data`, `sort_order`, `is_active`, `updated_at`) VALUES (15,'marketplace','home','Community Marketplace','Community marketplace','Where customers and\nprofessionals meet.','professionals meet.','A living showcase of real work — reviews, portfolios and before-and-after galleries that let quality speak for itself.','{\"tags\":[\"Verified Reviews\",\"Professional Portfolios\",\"Before & After Galleries\",\"Q&A Forums\",\"Expert Recommendations\",\"Success Stories\",\"Seasonal Campaigns\",\"Local Recommendations\"]}',150,1,'2026-10-11 10:35:25');
INSERT INTO `sections` (`id`, `skey`, `page`, `label`, `tag`, `title`, `emphasis`, `description`, `data`, `sort_order`, `is_active`, `updated_at`) VALUES (16,'trust','home','Trust & Safety','Trust & safety','Verified before every dispatch.','every dispatch.','Onboarding can include identity checks, document review, skills assessment and ongoing quality monitoring. The exact steps depend on the service category and applicable law.','{\"checks\":[\"Identity Verification\",\"Document Review\",\"Skills Assessment\",\"Customer Quality Monitoring\",\"Secure In-App Payments\"],\"badges\":[\"SECP Registered\",\"Data Privacy Compliant\",\"Secure Cloud Hosting\",\"Audit Logged\"]}',160,1,'2026-10-11 10:35:25');
INSERT INTO `sections` (`id`, `skey`, `page`, `label`, `tag`, `title`, `emphasis`, `description`, `data`, `sort_order`, `is_active`, `updated_at`) VALUES (17,'customers','home','Who We Serve','Who we serve','Made for every kind\nof customer.','of customer.','From a single flat to a network of industrial sites — the platform grows right alongside you.','{\"tags\":[\"Homeowners\",\"Apartment Residents\",\"Overseas Pakistanis\",\"Property Investors\",\"Restaurants & Hotels\",\"Hospitals & Schools\",\"Banks\",\"Developers\",\"Housing Societies\",\"Government Organizations\",\"Industrial Facilities\"]}',170,1,'2026-10-11 10:35:25');
INSERT INTO `sections` (`id`, `skey`, `page`, `label`, `tag`, `title`, `emphasis`, `description`, `data`, `sort_order`, `is_active`, `updated_at`) VALUES (18,'business_model','home','Business Model','Business model','How Taskora keeps\nthe platform strong.','the platform strong.','Diversified, transparent revenue — designed to keep the service dependable for customers and fair for professionals.','{\"notes\":[\"Indicative planning split based on growth assumptions — not a financial guarantee.\",\"The Market Store adds further upside beyond this split: material margin, wholesale subscriptions and delivery fees.\"],\"streams\":[{\"label\":\"Marketplace Commissions\",\"percent\":40,\"color\":\"#0E8F57\"},{\"label\":\"Corporate Contracts\",\"percent\":20,\"color\":\"#065F46\"},{\"label\":\"Housing Society Contracts\",\"percent\":10,\"color\":\"#D4AF37\"},{\"label\":\"Subscription Plans\",\"percent\":10,\"color\":\"#7FB8A0\"},{\"label\":\"Facility Management\",\"percent\":8,\"color\":\"#0B0D0C\"},{\"label\":\"Featured Listings\",\"percent\":4,\"color\":\"#C9A96A\"},{\"label\":\"Advertising\",\"percent\":3,\"color\":\"#9AA79E\"},{\"label\":\"Training & Certification\",\"percent\":2,\"color\":\"#4E6B5F\"},{\"label\":\"Franchise Fees\",\"percent\":2,\"color\":\"#D9D2BE\"},{\"label\":\"SaaS & Analytics (Future)\",\"percent\":1,\"color\":\"#BFC7C2\"}]}',180,1,'2026-10-11 10:35:25');
INSERT INTO `sections` (`id`, `skey`, `page`, `label`, `tag`, `title`, `emphasis`, `description`, `data`, `sort_order`, `is_active`, `updated_at`) VALUES (19,'professional','home','Become a Professional','Join the network','Grow your career with Taskora.','with Taskora.','Become part of one of Pakistan\'s fastest-growing professional networks — steady work, weekly payouts and a digital reputation that travels with you.','{\"benefits\":[\"More customers\",\"Weekly payouts\",\"Digital profile\",\"Performance rewards\",\"Training & certification\",\"Flexible hours\"],\"cta_label\":\"Apply to Join\",\"cta_href\":\"account/register.php?role=provider\"}',190,1,'2026-10-11 10:35:25');
INSERT INTO `sections` (`id`, `skey`, `page`, `label`, `tag`, `title`, `emphasis`, `description`, `data`, `sort_order`, `is_active`, `updated_at`) VALUES (20,'vision','home','Long-Term Vision','Long-term vision','We are building the digital backbone of Pakistan\'s service economy — not another booking app, but one trusted ecosystem where people discover services, learn, hire professionals and run their businesses.','','Taskora is powered by Trovec Technologies.','{\"legal\":\"Taskora (Pvt.) Ltd. · Registered under SECP · A Trovec Technologies Company\"}',200,1,'2026-10-11 10:35:25');
INSERT INTO `sections` (`id`, `skey`, `page`, `label`, `tag`, `title`, `emphasis`, `description`, `data`, `sort_order`, `is_active`, `updated_at`) VALUES (21,'cta','home','Download CTA','Scan · Book · Relax','One app.\nEvery service. Every need.','Every need.','Get the Taskora app and keep a trusted professional just a tap away.','{\"buttons\":[{\"label\":\"Download for iOS\",\"href\":\"#\",\"variant\":\"ios\"},{\"label\":\"Download for Android\",\"href\":\"#\",\"variant\":\"android\"},{\"label\":\"Talk to Sales\",\"href\":\"contact.php\",\"variant\":\"ghost\"}]}',210,1,'2026-10-11 10:35:25');
INSERT INTO `sections` (`id`, `skey`, `page`, `label`, `tag`, `title`, `emphasis`, `description`, `data`, `sort_order`, `is_active`, `updated_at`) VALUES (22,'cat_hero','catalogue','Catalogue — Hero','Master professional catalogue','Every specialist, verified the same way.','verified the same way.','Fifteen major service groups and 300+ professional specialisations — and every single listing clears the same trusted verification framework before a customer sees it.','{\"stats\":[{\"value\":\"15\",\"label\":\"Major Service Groups\"},{\"value\":\"300+\",\"label\":\"Specialisations\"},{\"value\":\"16\",\"label\":\"Verification Checks\"},{\"value\":\"100%\",\"label\":\"Profiles Verified\"}]}',220,1,'2026-10-11 10:35:25');
INSERT INTO `sections` (`id`, `skey`, `page`, `label`, `tag`, `title`, `emphasis`, `description`, `data`, `sort_order`, `is_active`, `updated_at`) VALUES (23,'cat_verification','catalogue','Catalogue — Verification','Our verification standard','Sixteen checks. One standard.','One standard.','Whether it is an electrician or a chartered accountant, every professional on Taskora clears the same framework before activation.','{\"checks\":[\"CNIC Verification\",\"Mobile Verification\",\"Email Verification\",\"Address Verification\",\"Police Clearance (where applicable)\",\"Qualification Verification\",\"Experience Verification\",\"Trade License Verification\",\"SECP / NTN Verification\",\"Tax Compliance Verification\",\"Background Screening\",\"Customer Rating System\",\"Quality Inspection\",\"Insurance Eligibility\",\"Digital Service Record\",\"Continuous Performance Monitoring\"]}',230,1,'2026-10-11 10:35:25');
INSERT INTO `sections` (`id`, `skey`, `page`, `label`, `tag`, `title`, `emphasis`, `description`, `data`, `sort_order`, `is_active`, `updated_at`) VALUES (24,'cat_browse','catalogue','Catalogue — Browse heading','Browse by category','The complete catalogue.','catalogue.','Open any category to reveal every specialisation inside it.','[]',240,1,'2026-10-11 10:35:25');
INSERT INTO `sections` (`id`, `skey`, `page`, `label`, `tag`, `title`, `emphasis`, `description`, `data`, `sort_order`, `is_active`, `updated_at`) VALUES (25,'cat_profile','catalogue','Catalogue — Sample Profile','Verified professional profile','What every listing tells you.','tells you.','Each field below is captured, checked and kept up to date before anyone can book.','{\"name\":\"Imran Mahmood\",\"initials\":\"IM\",\"role\":\"HVAC & AC Services — Islamabad\",\"fields\":[{\"label\":\"Professional ID\",\"value\":\"TSK-AC-08421\",\"tone\":\"\"},{\"label\":\"Category\",\"value\":\"HVAC & AC Services\",\"tone\":\"\"},{\"label\":\"Experience\",\"value\":\"9 Years\",\"tone\":\"\"},{\"label\":\"Qualifications\",\"value\":\"HVAC Diploma, TEVTA\",\"tone\":\"\"},{\"label\":\"Languages\",\"value\":\"Urdu, English\",\"tone\":\"\"},{\"label\":\"Service Areas\",\"value\":\"Islamabad, Rawalpindi\",\"tone\":\"\"},{\"label\":\"Customer Rating\",\"value\":\"4.9 ★ (612 reviews)\",\"tone\":\"gold\"},{\"label\":\"Completed Jobs\",\"value\":\"1,284\",\"tone\":\"\"},{\"label\":\"Response Time\",\"value\":\"< 12 min\",\"tone\":\"green\"},{\"label\":\"Success Rate\",\"value\":\"98.4%\",\"tone\":\"green\"},{\"label\":\"Warranty Offered\",\"value\":\"30 Days on Repairs\",\"tone\":\"\"},{\"label\":\"Emergency Availability\",\"value\":\"Available\",\"tone\":\"green\"},{\"label\":\"Insurance Status\",\"value\":\"Covered\",\"tone\":\"\"},{\"label\":\"Pricing\",\"value\":\"PKR 800 – 2,200 / visit\",\"tone\":\"\"},{\"label\":\"Digital Service History\",\"value\":\"Available on request\",\"tone\":\"\"}]}',250,1,'2026-10-11 10:35:25');
INSERT INTO `sections` (`id`, `skey`, `page`, `label`, `tag`, `title`, `emphasis`, `description`, `data`, `sort_order`, `is_active`, `updated_at`) VALUES (26,'cat_quality','catalogue','Catalogue — Quality Promise','Quality promise','What every customer can count on.','count on.','','{\"items\":[\"Verified Professional\",\"Transparent Pricing\",\"Digital Booking\",\"Live Job Tracking\",\"Secure Digital Payments\",\"Electronic Invoices\",\"Warranty (Where Applicable)\",\"24/7 Customer Support\",\"Complaint Resolution\",\"Digital Service History\",\"Verified Reviews\",\"Quality Assurance\"]}',260,1,'2026-10-11 10:35:25');
INSERT INTO `sections` (`id`, `skey`, `page`, `label`, `tag`, `title`, `emphasis`, `description`, `data`, `sort_order`, `is_active`, `updated_at`) VALUES (27,'cat_cta','catalogue','Catalogue — CTA','','Find the right professional — verified from day one.','verified from day one.','','{\"primary_label\":\"Book a Service\",\"primary_href\":\"forms.php?type=booking\",\"secondary_label\":\"List Your Services\",\"secondary_href\":\"account/register.php?role=provider\"}',270,1,'2026-10-11 10:35:25');
/*!40000 ALTER TABLE `sections` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `skey` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `svalue` text COLLATE utf8mb4_unicode_ci,
  `label` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grp` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `input` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`skey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` (`skey`, `svalue`, `label`, `grp`, `input`, `sort_order`) VALUES ('address','Islamabad, Pakistan','Office address','contact','textarea',8);
INSERT INTO `settings` (`skey`, `svalue`, `label`, `grp`, `input`, `sort_order`) VALUES ('app_android','#','Google Play link','social','text',18);
INSERT INTO `settings` (`skey`, `svalue`, `label`, `grp`, `input`, `sort_order`) VALUES ('app_ios','#','App Store link','social','text',17);
INSERT INTO `settings` (`skey`, `svalue`, `label`, `grp`, `input`, `sort_order`) VALUES ('cities','Islamabad\nRawalpindi\nLahore\nKarachi\nFaisalabad\nPeshawar\nMultan','Service cities (one per line)','contact','textarea',11);
INSERT INTO `settings` (`skey`, `svalue`, `label`, `grp`, `input`, `sort_order`) VALUES ('copyright','© {year} Taskora (Pvt.) Ltd. · A Trovec Technologies Company','Copyright line ({year} is replaced)','footer','text',25);
INSERT INTO `settings` (`skey`, `svalue`, `label`, `grp`, `input`, `sort_order`) VALUES ('email','info@taskora.digital','Email address','contact','text',7);
INSERT INTO `settings` (`skey`, `svalue`, `label`, `grp`, `input`, `sort_order`) VALUES ('facebook','#','Facebook URL','social','text',12);
INSERT INTO `settings` (`skey`, `svalue`, `label`, `grp`, `input`, `sort_order`) VALUES ('favicon','','Favicon image','general','image',4);
INSERT INTO `settings` (`skey`, `svalue`, `label`, `grp`, `input`, `sort_order`) VALUES ('footer_about','Pakistan\'s digital home for trusted, verified home, business and property services — all in a single app.','Footer about text','footer','textarea',24);
INSERT INTO `settings` (`skey`, `svalue`, `label`, `grp`, `input`, `sort_order`) VALUES ('google_analytics','','Google Analytics ID (G-XXXX)','seo','text',23);
INSERT INTO `settings` (`skey`, `svalue`, `label`, `grp`, `input`, `sort_order`) VALUES ('hours','Support available 24/7 · Office: Mon–Sat, 9:00 AM – 6:00 PM','Working hours','contact','text',9);
INSERT INTO `settings` (`skey`, `svalue`, `label`, `grp`, `input`, `sort_order`) VALUES ('instagram','#','Instagram URL','social','text',13);
INSERT INTO `settings` (`skey`, `svalue`, `label`, `grp`, `input`, `sort_order`) VALUES ('linkedin','#','LinkedIn URL','social','text',14);
INSERT INTO `settings` (`skey`, `svalue`, `label`, `grp`, `input`, `sort_order`) VALUES ('logo','','Logo image (leave empty to use built-in logo)','general','image',3);
INSERT INTO `settings` (`skey`, `svalue`, `label`, `grp`, `input`, `sort_order`) VALUES ('map_embed','','Google Maps embed URL (optional)','contact','text',10);
INSERT INTO `settings` (`skey`, `svalue`, `label`, `grp`, `input`, `sort_order`) VALUES ('meta_description','Taskora is Pakistan\'s all-in-one services app. Book verified electricians, cleaners, AC technicians, lawyers, accountants, architects and 100+ more services with live tracking and secure payments.','Default SEO description','seo','textarea',20);
INSERT INTO `settings` (`skey`, `svalue`, `label`, `grp`, `input`, `sort_order`) VALUES ('meta_keywords','home services Pakistan, verified professionals, book electrician Islamabad, AC service, deep cleaning, facility management','SEO keywords','seo','text',21);
INSERT INTO `settings` (`skey`, `svalue`, `label`, `grp`, `input`, `sort_order`) VALUES ('meta_title','Taskora — Book Verified Home, Business & Property Services in Pakistan','Default SEO title','seo','text',19);
INSERT INTO `settings` (`skey`, `svalue`, `label`, `grp`, `input`, `sort_order`) VALUES ('phone','+92 300 0000000','Phone number','contact','text',5);
INSERT INTO `settings` (`skey`, `svalue`, `label`, `grp`, `input`, `sort_order`) VALUES ('powered_by','Powered by Trovec Technologies','Powered-by line','general','text',2);
INSERT INTO `settings` (`skey`, `svalue`, `label`, `grp`, `input`, `sort_order`) VALUES ('seo_keyword','home services in Pakistan','Main SEO keyword','seo','text',22);
INSERT INTO `settings` (`skey`, `svalue`, `label`, `grp`, `input`, `sort_order`) VALUES ('site_name','Taskora','Site name','general','text',0);
INSERT INTO `settings` (`skey`, `svalue`, `label`, `grp`, `input`, `sort_order`) VALUES ('site_tagline','One App. Every Service. Every Need.','Tagline','general','text',1);
INSERT INTO `settings` (`skey`, `svalue`, `label`, `grp`, `input`, `sort_order`) VALUES ('twitter','#','X / Twitter URL','social','text',15);
INSERT INTO `settings` (`skey`, `svalue`, `label`, `grp`, `input`, `sort_order`) VALUES ('whatsapp','923000000000','WhatsApp number (digits only, with country code)','contact','text',6);
INSERT INTO `settings` (`skey`, `svalue`, `label`, `grp`, `input`, `sort_order`) VALUES ('youtube','','YouTube URL','social','text',16);
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `subscribers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscribers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `subscribers` WRITE;
/*!40000 ALTER TABLE `subscribers` DISABLE KEYS */;
/*!40000 ALTER TABLE `subscribers` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `testimonials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testimonials` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `author` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stars` tinyint(4) NOT NULL DEFAULT '5',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `testimonials` WRITE;
/*!40000 ALTER TABLE `testimonials` DISABLE KEYS */;
INSERT INTO `testimonials` (`id`, `text`, `author`, `location`, `stars`, `sort_order`, `is_active`) VALUES (1,'Our main breaker tripped late at night. The app found an electrician nearby and the fault was fixed within the hour — calm, polite and tidy.','Sample review','DHA, Lahore',5,10,1);
INSERT INTO `testimonials` (`id`, `text`, `author`, `location`, `stars`, `sort_order`, `is_active`) VALUES (2,'We moved facility management for two office floors onto one dashboard. Fewer vendors to chase and every request is logged.','Sample review — Office Manager','Karachi',5,20,1);
INSERT INTO `testimonials` (`id`, `text`, `author`, `location`, `stars`, `sort_order`, `is_active`) VALUES (3,'Residents now raise maintenance requests themselves and can see the status. Our committee meetings got a lot shorter.','Sample review — Society Committee','Islamabad',4,30,1);
INSERT INTO `testimonials` (`id`, `text`, `author`, `location`, `stars`, `sort_order`, `is_active`) VALUES (4,'The cost estimator was close to the final quote, and the painter shared an itemised invoice for every material used.','Sample review — Homeowner','Rawalpindi',5,40,1);
INSERT INTO `testimonials` (`id`, `text`, `author`, `location`, `stars`, `sort_order`, `is_active`) VALUES (5,'Booked a deep clean before Eid on a Thursday evening. The team arrived on time Saturday morning with their own equipment.','Sample review — Apartment Resident','Faisalabad',5,50,1);
/*!40000 ALTER TABLE `testimonials` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `provider_applications`;
CREATE TABLE `provider_applications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ref` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `provider_type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tier` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `waitlist` tinyint(1) NOT NULL DEFAULT 0,
  `data` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `documents` longtext COLLATE utf8mb4_unicode_ci,
  `fees` longtext COLLATE utf8mb4_unicode_ci,
  `status` enum('submitted','under_review','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'submitted',
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ref` (`ref`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role` enum('customer','provider') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cnic` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subcategory` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `experience` int(11) DEFAULT NULL,
  `service_areas` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qualifications` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','active','suspended') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `role`, `name`, `email`, `phone`, `cnic`, `city`, `password`, `category`, `subcategory`, `experience`, `service_areas`, `qualifications`, `status`, `created_at`) VALUES (1,'customer','Ayesha Khan','customer@taskora.digital','03001234567',NULL,'Islamabad','$2y$10$HXI1xbyWJUBPKxUENlfj2OWB4K.3AnLwfTMaNHKMVmlVTX/5A0z1C',NULL,NULL,NULL,NULL,NULL,'active','2026-10-11 10:35:25');
INSERT INTO `users` (`id`, `role`, `name`, `email`, `phone`, `cnic`, `city`, `password`, `category`, `subcategory`, `experience`, `service_areas`, `qualifications`, `status`, `created_at`) VALUES (2,'provider','Imran Mahmood','provider@taskora.digital','03111234567',NULL,'Islamabad','$2y$10$jWmL7jt9O0JoLVY6PRyHCeZkfV2UTZn3QkuTioPhCXdFPQ1JrQIjy','Home Services','AC Repair & Installation',9,'Islamabad, Rawalpindi','HVAC Diploma, TEVTA','active','2026-10-11 10:35:25');
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

