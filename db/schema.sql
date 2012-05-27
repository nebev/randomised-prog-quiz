CREATE DATABASE  IF NOT EXISTS `quiz_db` /*!40100 DEFAULT CHARACTER SET latin1 COLLATE latin1_general_ci */;
USE `quiz_db`;
-- MySQL dump 10.13  Distrib 5.5.16, for osx10.5 (i386)
--
-- Host: localhost    Database: quiz_db
-- ------------------------------------------------------
-- Server version	5.5.22-0ubuntu1

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
-- Table structure for table `question_base`
--

DROP TABLE IF EXISTS `question_base`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question_base` (
  `question_id` int(10) NOT NULL AUTO_INCREMENT,
  `xml` varchar(30) COLLATE latin1_general_ci NOT NULL,
  `question_type` varchar(50) COLLATE latin1_general_ci NOT NULL COMMENT 'eg. Multiple',
  `difficulty` int(2) NOT NULL,
  `estimated_time` int(4) NOT NULL,
  `added_on` date NOT NULL,
  PRIMARY KEY (`question_id`),
  UNIQUE KEY `xml` (`xml`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `generated_questions`
--

DROP TABLE IF EXISTS `generated_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `generated_questions` (
  `generated_id` int(10) NOT NULL AUTO_INCREMENT,
  `instructions` varchar(1500) COLLATE latin1_general_ci NOT NULL,
  `question_data` mediumtext COLLATE latin1_general_ci NOT NULL,
  `correct_answer` varchar(255) COLLATE latin1_general_ci NOT NULL,
  `alt_ans_1` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `alt_desc_1` varchar(400) COLLATE latin1_general_ci DEFAULT NULL,
  `alt_ans_2` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `alt_desc_2` varchar(400) COLLATE latin1_general_ci DEFAULT NULL,
  `alt_ans_3` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `alt_desc_3` varchar(400) COLLATE latin1_general_ci DEFAULT NULL,
  `question_basequestion_id` int(10) NOT NULL,
  PRIMARY KEY (`generated_id`),
  KEY `question_basequestion_id` (`question_basequestion_id`),
  CONSTRAINT `generated_questions_ibfk_1` FOREIGN KEY (`question_basequestion_id`) REFERENCES `question_base` (`question_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=317 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `question_attempt`
--

DROP TABLE IF EXISTS `question_attempt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question_attempt` (
  `attempt_id` int(10) NOT NULL AUTO_INCREMENT,
  `initial_result` int(1) DEFAULT NULL,
  `secondary_result` int(1) DEFAULT NULL,
  `question_basequestion_id` int(10) NOT NULL,
  `attempted_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `time_started` timestamp NULL DEFAULT NULL,
  `time_finished` timestamp NULL DEFAULT NULL,
  `quiz_attemptquiz_attempt_id` int(10) NOT NULL,
  `generated_questionsgenerated_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`attempt_id`),
  KEY `question_basequestion_id` (`question_basequestion_id`,`quiz_attemptquiz_attempt_id`,`generated_questionsgenerated_id`),
  KEY `quiz_attemptquiz_attempt_id` (`quiz_attemptquiz_attempt_id`),
  CONSTRAINT `question_attempt_ibfk_1` FOREIGN KEY (`question_basequestion_id`) REFERENCES `question_base` (`question_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `question_attempt_ibfk_2` FOREIGN KEY (`quiz_attemptquiz_attempt_id`) REFERENCES `quiz_attempt` (`quiz_attempt_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=426 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quiz`
--

DROP TABLE IF EXISTS `quiz`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quiz` (
  `quiz_id` int(10) NOT NULL AUTO_INCREMENT,
  `quiz_name` varchar(20) COLLATE latin1_general_ci NOT NULL,
  `permissions_group` varchar(25) COLLATE latin1_general_ci NOT NULL,
  `open_date` datetime NOT NULL,
  `close_date` datetime NOT NULL,
  `max_attempts` int(2) NOT NULL,
  `percentage_pass` int(3) NOT NULL,
  PRIMARY KEY (`quiz_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quiz_attempt`
--

DROP TABLE IF EXISTS `quiz_attempt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quiz_attempt` (
  `quiz_attempt_id` int(10) NOT NULL AUTO_INCREMENT,
  `date_started` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_finished` timestamp NULL DEFAULT NULL,
  `total_score` int(3) DEFAULT NULL,
  `quizquiz_id` int(10) NOT NULL,
  `ad_user_cachesamaccountname` varchar(30) COLLATE latin1_general_ci NOT NULL,
  `last_question` int(5) NOT NULL,
  PRIMARY KEY (`quiz_attempt_id`),
  KEY `quizquiz_id` (`quizquiz_id`,`ad_user_cachesamaccountname`),
  KEY `ad_user_cachesamaccountname` (`ad_user_cachesamaccountname`),
  CONSTRAINT `quiz_attempt_ibfk_1` FOREIGN KEY (`quizquiz_id`) REFERENCES `quiz` (`quiz_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ad_user_cache`
--

DROP TABLE IF EXISTS `ad_user_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ad_user_cache` (
  `samaccountname` varchar(30) COLLATE latin1_general_ci NOT NULL,
  `first_name` varchar(50) COLLATE latin1_general_ci NOT NULL,
  `last_name` varchar(50) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`samaccountname`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `concepts`
--

DROP TABLE IF EXISTS `concepts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `concepts` (
  `concept_name` varchar(40) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`concept_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `concepts_tested`
--

DROP TABLE IF EXISTS `concepts_tested`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `concepts_tested` (
  `ctest_id` int(10) NOT NULL AUTO_INCREMENT,
  `lower_difficulty` int(2) NOT NULL,
  `higher_difficulty` int(2) NOT NULL,
  `number_tested` int(2) NOT NULL,
  `conceptsconcept_name` varchar(40) COLLATE latin1_general_ci NOT NULL,
  `quizquiz_id` int(10) NOT NULL,
  PRIMARY KEY (`ctest_id`),
  KEY `conceptsconcept_name` (`conceptsconcept_name`,`quizquiz_id`),
  KEY `quizquiz_id` (`quizquiz_id`),
  CONSTRAINT `concepts_tested_ibfk_1` FOREIGN KEY (`conceptsconcept_name`) REFERENCES `concepts` (`concept_name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `concepts_tested_ibfk_2` FOREIGN KEY (`quizquiz_id`) REFERENCES `quiz` (`quiz_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `question_concepts`
--

DROP TABLE IF EXISTS `question_concepts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question_concepts` (
  `qc_id` int(10) NOT NULL AUTO_INCREMENT,
  `question_basequestion_id` int(10) NOT NULL,
  `conceptsconcept_name` varchar(40) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`qc_id`),
  KEY `question_basequestion_id` (`question_basequestion_id`),
  KEY `conceptsconcept_name` (`conceptsconcept_name`),
  CONSTRAINT `question_concepts_ibfk_1` FOREIGN KEY (`question_basequestion_id`) REFERENCES `question_base` (`question_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `question_concepts_ibfk_2` FOREIGN KEY (`conceptsconcept_name`) REFERENCES `concepts` (`concept_name`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-05-27  9:58:36
