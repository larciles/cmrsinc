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


-- Dumping structure for table amasdb.empresa
CREATE TABLE IF NOT EXISTS `empresa` (
  `Rif` varchar(30) DEFAULT NULL,
  `Nit` varchar(30) DEFAULT NULL,
  `Nombre` varchar(100) DEFAULT NULL,
  `Direccion` varchar(255) DEFAULT NULL,
  `Telefonos` varchar(50) DEFAULT NULL,
  `Fax` varchar(50) DEFAULT NULL,
  `Surcursal` varchar(100) DEFAULT NULL,
  `Desde` date DEFAULT NULL,
  `UltimaFactura` varchar(10) DEFAULT NULL,
  `UltimaFacturaVirtual` varchar(7) DEFAULT NULL,
  `UltimoCliente` varchar(10) DEFAULT NULL,
  `UltimoPedido` varchar(10) DEFAULT NULL,
  `UltimaCotizacion` varchar(10) DEFAULT NULL,
  `UltimoAjuste` varchar(10) DEFAULT NULL,
  `UltimoMedico` varchar(3) DEFAULT NULL,
  `ultimoEntrega` varchar(10) DEFAULT '0',
  `UltimoCredito` varchar(10) DEFAULT '0',
  `UltimoPresupuesto` varchar(6) DEFAULT NULL,
  `Ultimoservicio` varchar(10) DEFAULT NULL,
  `Id_centro` varchar(1) DEFAULT '0',
  `FechaCierreInventario` date DEFAULT NULL,
  `PWTRANFEREN` varchar(10) DEFAULT NULL,
  `PRESUPUESTO` decimal(13,2) DEFAULT NULL,
  `PRESU_PIEPAGINA` text,
  `ALICUOTA` decimal(13,2) DEFAULT NULL,
  `PUNTO_COMA` varchar(1) DEFAULT NULL,
  `factor` decimal(13,2) DEFAULT NULL,
  `sucursal` varchar(100) DEFAULT NULL,
  `Ultimodescuento` varchar(3) DEFAULT NULL,
  `Ultimotecnico` varchar(3) DEFAULT NULL,
  `codsuc` varchar(5) DEFAULT NULL,
  `ultimahistoria` varchar(10) DEFAULT NULL,
  `tasa_iva` decimal(13,2) DEFAULT NULL,
  `descuento` int(11) DEFAULT '1',
  `impuesto` int(11) DEFAULT '1',
  `ultimatransf` varchar(10) DEFAULT '0',
  `formato` varchar(50) DEFAULT NULL,
  `ts` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- Dumping data for table amasdb.empresa: ~0 rows (approximately)
/*!40000 ALTER TABLE `empresa` DISABLE KEYS */;
INSERT INTO `empresa` (`Rif`, `Nit`, `Nombre`, `Direccion`, `Telefonos`, `Fax`, `Surcursal`, `Desde`, `UltimaFactura`, `UltimaFacturaVirtual`, `UltimoCliente`, `UltimoPedido`, `UltimaCotizacion`, `UltimoAjuste`, `UltimoMedico`, `ultimoEntrega`, `UltimoCredito`, `UltimoPresupuesto`, `Ultimoservicio`, `Id_centro`, `FechaCierreInventario`, `PWTRANFEREN`, `PRESUPUESTO`, `PRESU_PIEPAGINA`, `ALICUOTA`, `PUNTO_COMA`, `factor`, `sucursal`, `Ultimodescuento`, `Ultimotecnico`, `codsuc`, `ultimahistoria`, `tasa_iva`, `descuento`, `impuesto`, `ultimatransf`, `formato`, `ts`, `id`) VALUES
	(NULL, NULL, 'AMAS CORP.', 'PO Box 7065, Ponce PR 00732-7065', '841-1949', '848-0318', NULL, NULL, '0000002', NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, '0', NULL, '2019-06-04 08:24:19', 1);
/*!40000 ALTER TABLE `empresa` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
