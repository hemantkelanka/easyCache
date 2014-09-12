
--
-- Database: `cache_system`
--

CREATE DATABASE IF NOT EXISTS `cache_system` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `cache_system`;


--
-- Table structure for table `cache`
--

CREATE TABLE IF NOT EXISTS `cache` (
  `id` varchar(32) NOT NULL,
  `content` varchar(5000) NOT NULL,
  `tag` varchar(50) NOT NULL,
  `md5` varchar(32) NOT NULL,
  `expire_on` int(18) NOT NULL,
  UNIQUE KEY `key` (`id`),
  KEY `expire_on` (`expire_on`)
) ENGINE=MEMORY DEFAULT CHARSET=latin1;


