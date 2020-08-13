-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.37-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             9.3.0.4984
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping database structure for amasdb
CREATE DATABASE IF NOT EXISTS `amasdb` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `amasdb`;


-- Dumping structure for table amasdb.impuestos
CREATE TABLE IF NOT EXISTS `impuestos` (
  `Impuesto` varchar(50) DEFAULT NULL,
  `Porcentaje` float DEFAULT '0',
  `Activo` int(11) DEFAULT '1',
  `AplicaYN` int(11) DEFAULT '1',
  `Fecha` date DEFAULT NULL,
  `Usuario` char(10) DEFAULT NULL,
  `Codigo` decimal(13,2) NOT NULL,
  `Borrado` int(11) DEFAULT '0',
  `ts` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- Dumping data for table amasdb.impuestos: ~0 rows (approximately)
/*!40000 ALTER TABLE `impuestos` DISABLE KEYS */;
INSERT INTO `impuestos` (`Impuesto`, `Porcentaje`, `Activo`, `AplicaYN`, `Fecha`, `Usuario`, `Codigo`, `Borrado`, `ts`, `id`) VALUES
	('IVU-Hacienda', 4, 1, 1, NULL, NULL, 0.00, 0, '2019-05-15 13:49:42', 1);
/*!40000 ALTER TABLE `impuestos` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
