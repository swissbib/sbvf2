-- MySQL dump 10.13  Distrib 5.1.66, for redhat-linux-gnu (x86_64)
--
-- Host: localhost    Database: vufind
-- ------------------------------------------------------
-- Server version	5.1.66

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `vufind`
--

/*!40000 DROP DATABASE IF EXISTS `vufind`*/;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `vufind` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `vufind`;

--
-- Table structure for table `change_tracker`
--

DROP TABLE IF EXISTS `change_tracker`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `change_tracker` (
  `core` varchar(30) NOT NULL,
  `id` varchar(120) NOT NULL,
  `first_indexed` datetime DEFAULT NULL,
  `last_indexed` datetime DEFAULT NULL,
  `last_record_change` datetime DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`core`,`id`),
  KEY `deleted_index` (`deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `change_tracker`
--

LOCK TABLES `change_tracker` WRITE;
/*!40000 ALTER TABLE `change_tracker` DISABLE KEYS */;
/*!40000 ALTER TABLE `change_tracker` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `resource_id` int(11) NOT NULL DEFAULT '0',
  `comment` text NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `resource_id` (`resource_id`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`resource_id`) REFERENCES `resource` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oai_resumption`
--

DROP TABLE IF EXISTS `oai_resumption`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oai_resumption` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `params` text,
  `expires` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oai_resumption`
--

LOCK TABLES `oai_resumption` WRITE;
/*!40000 ALTER TABLE `oai_resumption` DISABLE KEYS */;
/*!40000 ALTER TABLE `oai_resumption` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resource`
--

DROP TABLE IF EXISTS `resource`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resource` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `record_id` varchar(120) NOT NULL DEFAULT '',
  `title` varchar(200) NOT NULL DEFAULT '',
  `author` varchar(200) DEFAULT NULL,
  `year` mediumint(6) DEFAULT NULL,
  `source` varchar(50) NOT NULL DEFAULT 'VuFind',
  PRIMARY KEY (`id`),
  KEY `record_id` (`record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resource`
--

LOCK TABLES `resource` WRITE;
/*!40000 ALTER TABLE `resource` DISABLE KEYS */;
/*!40000 ALTER TABLE `resource` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resource_tags`
--

DROP TABLE IF EXISTS `resource_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resource_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resource_id` int(11) NOT NULL DEFAULT '0',
  `tag_id` int(11) NOT NULL DEFAULT '0',
  `list_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `posted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `resource_id` (`resource_id`),
  KEY `tag_id` (`tag_id`),
  KEY `list_id` (`list_id`),
  CONSTRAINT `resource_tags_ibfk_14` FOREIGN KEY (`resource_id`) REFERENCES `resource` (`id`) ON DELETE CASCADE,
  CONSTRAINT `resource_tags_ibfk_15` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE,
  CONSTRAINT `resource_tags_ibfk_16` FOREIGN KEY (`list_id`) REFERENCES `user_list` (`id`) ON DELETE SET NULL,
  CONSTRAINT `resource_tags_ibfk_17` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resource_tags`
--

LOCK TABLES `resource_tags` WRITE;
/*!40000 ALTER TABLE `resource_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `resource_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `search`
--

DROP TABLE IF EXISTS `search`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `search` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `session_id` varchar(128) DEFAULT NULL,
  `folder_id` int(11) DEFAULT NULL,
  `created` date NOT NULL DEFAULT '0000-00-00',
  `title` varchar(20) DEFAULT NULL,
  `saved` int(1) NOT NULL DEFAULT '0',
  `search_object` blob,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `folder_id` (`folder_id`),
  KEY `session_id` (`session_id`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `search`
--

LOCK TABLES `search` WRITE;
/*!40000 ALTER TABLE `search` DISABLE KEYS */;
INSERT INTO `search` VALUES (3,0,'95l7t06sieieov96o71qvb3n05',NULL,'2012-12-09',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:2:\"c*\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:3;s:1:\"i\";d:1355077781.9354898929595947265625;s:1:\"s\";d:0.031729221343994140625;s:1:\"r\";i:0;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(4,0,'95l7t06sieieov96o71qvb3n05',NULL,'2012-12-09',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:5:\"Title\";s:1:\"l\";s:2:\"a*\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:4;s:1:\"i\";d:1355077794.196957111358642578125;s:1:\"s\";d:0.0303289890289306640625;s:1:\"r\";i:0;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(5,0,'95l7t06sieieov96o71qvb3n05',NULL,'2012-12-09',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:5:\"Title\";s:1:\"l\";s:2:\"f*\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:5;s:1:\"i\";d:1355077808.3484649658203125;s:1:\"s\";d:0.0284039974212646484375;s:1:\"r\";i:0;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(6,0,'95l7t06sieieov96o71qvb3n05',NULL,'2012-12-09',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:2:\"a*\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:6;s:1:\"i\";d:1355078376.9267389774322509765625;s:1:\"s\";d:0.0448319911956787109375;s:1:\"r\";i:0;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(8,0,'95l7t06sieieov96o71qvb3n05',NULL,'2012-12-09',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:3:\"xm*\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:8;s:1:\"i\";d:1355078535.8893959522247314453125;s:1:\"s\";d:0.10754108428955078125;s:1:\"r\";i:0;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(10,0,'95l7t06sieieov96o71qvb3n05',NULL,'2012-12-09',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:2:\"h*\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:10;s:1:\"i\";d:1355080554.78983306884765625;s:1:\"s\";d:0.0691540241241455078125;s:1:\"r\";i:0;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(11,0,'95l7t06sieieov96o71qvb3n05',NULL,'2012-12-09',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:4:\"te?t\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:11;s:1:\"i\";d:1355080653.66535091400146484375;s:1:\"s\";d:0.0475370883941650390625;s:1:\"r\";i:0;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(12,0,'95l7t06sieieov96o71qvb3n05',NULL,'2012-12-09',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:4:\"test\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:12;s:1:\"i\";d:1355080658.461040973663330078125;s:1:\"s\";d:0.034821987152099609375;s:1:\"r\";i:0;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(13,0,'95l7t06sieieov96o71qvb3n05',NULL,'2012-12-09',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:2:\"b*\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:13;s:1:\"i\";d:1355084017.533586025238037109375;s:1:\"s\";d:0.0419938564300537109375;s:1:\"r\";i:0;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(14,0,'jl6l3dd8k0tg6s8sdidsn8o4e0',NULL,'2012-12-11',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:2:\"a*\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:14;s:1:\"i\";d:1355254751.5097000598907470703125;s:1:\"s\";d:0.289546966552734375;s:1:\"r\";i:0;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(15,0,'jl6l3dd8k0tg6s8sdidsn8o4e0',NULL,'2012-12-11',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:3:\"der\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:15;s:1:\"i\";d:1355254760.69141101837158203125;s:1:\"s\";d:0.326344966888427734375;s:1:\"r\";i:18;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(16,0,'jl6l3dd8k0tg6s8sdidsn8o4e0',NULL,'2012-12-11',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:2:\"ha\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:16;s:1:\"i\";d:1355254771.41845607757568359375;s:1:\"s\";d:0.2303688526153564453125;s:1:\"r\";i:0;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(17,0,'jl6l3dd8k0tg6s8sdidsn8o4e0',NULL,'2012-12-11',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:4:\"aber\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:17;s:1:\"i\";d:1355254775.3270690441131591796875;s:1:\"s\";d:0.2213099002838134765625;s:1:\"r\";i:0;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(18,0,'jl6l3dd8k0tg6s8sdidsn8o4e0',NULL,'2012-12-11',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:5:\"samen\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:18;s:1:\"i\";d:1355254781.2324049472808837890625;s:1:\"s\";d:0.2200281620025634765625;s:1:\"r\";i:0;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(19,0,'jl6l3dd8k0tg6s8sdidsn8o4e0',NULL,'2012-12-11',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:3:\"der\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:19;s:1:\"i\";d:1355254785.0348060131072998046875;s:1:\"s\";d:0.2925798892974853515625;s:1:\"r\";i:18;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(20,0,'jl6l3dd8k0tg6s8sdidsn8o4e0',NULL,'2012-12-11',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:8:\"christen\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:20;s:1:\"i\";d:1355254795.6610000133514404296875;s:1:\"s\";d:0.2397019863128662109375;s:1:\"r\";i:1;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(21,0,'jl6l3dd8k0tg6s8sdidsn8o4e0',NULL,'2012-12-11',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:6:\"luzern\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:21;s:1:\"i\";d:1355254804.5751760005950927734375;s:1:\"s\";d:0.245212078094482421875;s:1:\"r\";i:1;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(22,0,'jl6l3dd8k0tg6s8sdidsn8o4e0',NULL,'2012-12-11',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:1:\"a\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:22;s:1:\"i\";d:1355254808.6839330196380615234375;s:1:\"s\";d:0.2549369335174560546875;s:1:\"r\";i:9;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(23,0,'jl6l3dd8k0tg6s8sdidsn8o4e0',NULL,'2012-12-11',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:1:\"a\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:23;s:1:\"i\";d:1355254819.304254055023193359375;s:1:\"s\";d:0.2600200176239013671875;s:1:\"r\";i:9;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(24,0,'jl6l3dd8k0tg6s8sdidsn8o4e0',NULL,'2012-12-11',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:1:\"a\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:24;s:1:\"i\";d:1355254824.8142330646514892578125;s:1:\"s\";d:0.252421855926513671875;s:1:\"r\";i:9;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(25,0,'jl6l3dd8k0tg6s8sdidsn8o4e0',NULL,'2012-12-11',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:3:\"die\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:25;s:1:\"i\";d:1355254842.0517139434814453125;s:1:\"s\";d:0.3045599460601806640625;s:1:\"r\";i:18;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(26,0,'jl6l3dd8k0tg6s8sdidsn8o4e0',NULL,'2012-12-11',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:3:\"ein\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:26;s:1:\"i\";d:1355254853.677360057830810546875;s:1:\"s\";d:0.243422031402587890625;s:1:\"r\";i:3;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(27,0,'jl6l3dd8k0tg6s8sdidsn8o4e0',NULL,'2012-12-11',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:2:\"ei\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:27;s:1:\"i\";d:1355254859.991178989410400390625;s:1:\"s\";d:0.215116977691650390625;s:1:\"r\";i:0;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(28,0,'jl6l3dd8k0tg6s8sdidsn8o4e0',NULL,'2012-12-11',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:2:\"e*\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:28;s:1:\"i\";d:1355254865.19178104400634765625;s:1:\"s\";d:0.2319488525390625;s:1:\"r\";i:0;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(29,0,'s62f2e65vmpf9o8jbkcsqr49e5',NULL,'2012-12-12',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:3:\"der\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:29;s:1:\"i\";d:1355325878.34401988983154296875;s:1:\"s\";d:0.222031116485595703125;s:1:\"r\";i:18;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(30,0,'s62f2e65vmpf9o8jbkcsqr49e5',NULL,'2012-12-12',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:3:\"der\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:30;s:1:\"i\";d:1355325920.37907695770263671875;s:1:\"s\";d:0.2830410003662109375;s:1:\"r\";i:18;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(31,0,'1t6berhqcj3p5gv4en81nf6pq0',NULL,'2013-01-01',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:1:\"h\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:31;s:1:\"i\";d:1357035591.2520420551300048828125;s:1:\"s\";d:0.155994892120361328125;s:1:\"r\";i:4;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(32,0,'1t6berhqcj3p5gv4en81nf6pq0',NULL,'2013-01-01',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:1:\"h\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:32;s:1:\"i\";d:1357035601.7812459468841552734375;s:1:\"s\";d:0.1148021221160888671875;s:1:\"r\";i:4;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(48,0,'5fa0efu4c6jogvpubm0k70v2m4',NULL,'2013-01-02',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:1:\"h\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:48;s:1:\"i\";d:1357132962.6121981143951416015625;s:1:\"s\";d:0.11866092681884765625;s:1:\"r\";i:4;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(49,0,'5fa0efu4c6jogvpubm0k70v2m4',NULL,'2013-01-02',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:1:\"a\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:49;s:1:\"i\";d:1357132967.02233600616455078125;s:1:\"s\";d:0.122951984405517578125;s:1:\"r\";i:9;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(50,0,'5fa0efu4c6jogvpubm0k70v2m4',NULL,'2013-01-02',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:1:\"a\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:50;s:1:\"i\";d:1357133143.0828609466552734375;s:1:\"s\";d:0.116014957427978515625;s:1:\"r\";i:9;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(51,0,'5fa0efu4c6jogvpubm0k70v2m4',NULL,'2013-01-02',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:1:\"a\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:51;s:1:\"i\";d:1357134451.6111850738525390625;s:1:\"s\";d:0.2647998332977294921875;s:1:\"r\";i:9;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(52,0,'5fa0efu4c6jogvpubm0k70v2m4',NULL,'2013-01-02',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:1:\"a\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:52;s:1:\"i\";d:1357134465.6325359344482421875;s:1:\"s\";d:0.255465030670166015625;s:1:\"r\";i:9;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(53,0,'5fa0efu4c6jogvpubm0k70v2m4',NULL,'2013-01-02',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:1:\"b\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:53;s:1:\"i\";d:1357134581.5926029682159423828125;s:1:\"s\";d:32.128180980682373046875;s:1:\"r\";i:2;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(54,0,'5fa0efu4c6jogvpubm0k70v2m4',NULL,'2013-01-02',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:1:\"c\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:54;s:1:\"i\";d:1357134652.667736053466796875;s:1:\"s\";d:2925.953629970550537109375;s:1:\"r\";i:4;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(55,0,'5fa0efu4c6jogvpubm0k70v2m4',NULL,'2013-01-02',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:1:\"c\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:55;s:1:\"i\";d:1357137694.16267299652099609375;s:1:\"s\";d:0.259378910064697265625;s:1:\"r\";i:4;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(56,0,'5fa0efu4c6jogvpubm0k70v2m4',NULL,'2013-01-02',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:1:\"c\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:56;s:1:\"i\";d:1357138180.88218593597412109375;s:1:\"s\";d:0.11415004730224609375;s:1:\"r\";i:4;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(57,0,'5fa0efu4c6jogvpubm0k70v2m4',NULL,'2013-01-02',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:1:\"c\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:57;s:1:\"i\";d:1357138238.726151943206787109375;s:1:\"s\";d:0.1227591037750244140625;s:1:\"r\";i:4;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(58,0,'5fa0efu4c6jogvpubm0k70v2m4',NULL,'2013-01-02',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:1:\"c\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:58;s:1:\"i\";d:1357138259.9624679088592529296875;s:1:\"s\";d:0.1098880767822265625;s:1:\"r\";i:4;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(59,0,'0ot0kg79bkq6fje0ji1tq3ahl4',NULL,'2013-01-02',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:1:\"a\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:59;s:1:\"i\";d:1357138278.0222070217132568359375;s:1:\"s\";d:43.6723930835723876953125;s:1:\"r\";i:9;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(60,0,'0ot0kg79bkq6fje0ji1tq3ahl4',NULL,'2013-01-02',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:1:\"a\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:60;s:1:\"i\";d:1357139782.39966106414794921875;s:1:\"s\";d:29.72764682769775390625;s:1:\"r\";i:9;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(61,0,'pg1ljsvvrejar124k6m99b2mg5',NULL,'2013-01-02',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:1:\"a\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:61;s:1:\"i\";d:1357155464.148747920989990234375;s:1:\"s\";d:0.2929160594940185546875;s:1:\"r\";i:9;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(63,0,'c6qljaqbaao5und4gpbhn551q5',NULL,'2013-01-03',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:1:\"a\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:63;s:1:\"i\";d:1357226875.09407806396484375;s:1:\"s\";d:0.1360738277435302734375;s:1:\"r\";i:9;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(64,0,'0cj3d46h79rdfepbk8at466ph0',NULL,'2013-01-04',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:1:\"a\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:64;s:1:\"i\";d:1357296009.2259719371795654296875;s:1:\"s\";d:0.1715240478515625;s:1:\"r\";i:456;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(65,0,'0cj3d46h79rdfepbk8at466ph0',NULL,'2013-01-04',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:2:\"pr\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:65;s:1:\"i\";d:1357296014.4109179973602294921875;s:1:\"s\";d:0.11824798583984375;s:1:\"r\";i:0;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(66,0,'0cj3d46h79rdfepbk8at466ph0',NULL,'2013-01-04',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:3:\"der\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:66;s:1:\"i\";d:1357296017.1443269252777099609375;s:1:\"s\";d:0.1754131317138671875;s:1:\"r\";i:869;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}'),(67,0,'0cj3d46h79rdfepbk8at466ph0',NULL,'2013-01-04',NULL,0,'O:5:\"minSO\":8:{s:1:\"t\";a:1:{i:0;a:2:{s:1:\"i\";s:9:\"AllFields\";s:1:\"l\";s:1:\"a\";}}s:1:\"f\";a:0:{}s:2:\"id\";i:67;s:1:\"i\";d:1357297654.7378680706024169921875;s:1:\"s\";d:0.1722648143768310546875;s:1:\"r\";i:456;s:2:\"ty\";s:5:\"basic\";s:2:\"cl\";s:4:\"Solr\";}');
/*!40000 ALTER TABLE `search` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `session`
--

DROP TABLE IF EXISTS `session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(128) DEFAULT NULL,
  `data` text,
  `last_used` int(12) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`),
  KEY `last_used` (`last_used`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `session`
--

LOCK TABLES `session` WRITE;
/*!40000 ALTER TABLE `session` DISABLE KEYS */;
/*!40000 ALTER TABLE `session` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tags`
--

LOCK TABLES `tags` WRITE;
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL DEFAULT '',
  `password` varchar(32) NOT NULL DEFAULT '',
  `firstname` varchar(50) NOT NULL DEFAULT '',
  `lastname` varchar(50) NOT NULL DEFAULT '',
  `email` varchar(250) NOT NULL DEFAULT '',
  `cat_username` varchar(50) DEFAULT NULL,
  `cat_password` varchar(50) DEFAULT NULL,
  `college` varchar(100) NOT NULL DEFAULT '',
  `major` varchar(100) NOT NULL DEFAULT '',
  `home_library` varchar(100) NOT NULL DEFAULT '',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_list`
--

DROP TABLE IF EXISTS `user_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `public` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_list_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_list`
--

LOCK TABLES `user_list` WRITE;
/*!40000 ALTER TABLE `user_list` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_resource`
--

DROP TABLE IF EXISTS `user_resource`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_resource` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `list_id` int(11) DEFAULT NULL,
  `notes` text,
  `saved` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `resource_id` (`resource_id`),
  KEY `user_id` (`user_id`),
  KEY `list_id` (`list_id`),
  CONSTRAINT `user_resource_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_resource_ibfk_2` FOREIGN KEY (`resource_id`) REFERENCES `resource` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_resource_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_resource_ibfk_4` FOREIGN KEY (`resource_id`) REFERENCES `resource` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_resource_ibfk_5` FOREIGN KEY (`list_id`) REFERENCES `user_list` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_resource`
--

LOCK TABLES `user_resource` WRITE;
/*!40000 ALTER TABLE `user_resource` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_resource` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_stats`
--

DROP TABLE IF EXISTS `user_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_stats` (
  `id` varchar(24) NOT NULL,
  `datestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `browser` varchar(32) NOT NULL,
  `browserVersion` varchar(8) NOT NULL,
  `ipaddress` varchar(15) NOT NULL,
  `referrer` varchar(512) NOT NULL,
  `url` varchar(512) NOT NULL,
  `session` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_stats`
--

LOCK TABLES `user_stats` WRITE;
/*!40000 ALTER TABLE `user_stats` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_stats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_stats_fields`
--

DROP TABLE IF EXISTS `user_stats_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_stats_fields` (
  `id` varchar(24) NOT NULL,
  `field` varchar(32) NOT NULL,
  `value` varchar(1024) NOT NULL,
  PRIMARY KEY (`id`,`field`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_stats_fields`
--

LOCK TABLES `user_stats_fields` WRITE;
/*!40000 ALTER TABLE `user_stats_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_stats_fields` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-01-04 12:36:24
