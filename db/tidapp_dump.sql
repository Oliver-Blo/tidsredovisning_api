/*
SQLyog Community
MySQL - 5.7.36 : Database - tidsrapport
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `kategori` */

CREATE TABLE `kategori` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Kategori` varchar(30) COLLATE utf8_swedish_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Kategori` (`Kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

/*Data for the table `kategori` */

insert  into `kategori`(`ID`,`Kategori`) values 
(3,'CSS'),
(1,'HTML'),
(2,'Javascript');

/*Table structure for table `uppgifter` */

CREATE TABLE `uppgifter` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Tid` time NOT NULL COMMENT 'Min 00:05 Max 8:00',
  `Datum` date NOT NULL,
  `KategoriID` int(11) NOT NULL,
  `Beskrivning` varchar(255) COLLATE utf8_swedish_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `KategoriID` (`KategoriID`),
  CONSTRAINT `uppgifter_ibfk_1` FOREIGN KEY (`KategoriID`) REFERENCES `kategori` (`ID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

/*Data for the table `uppgifter` */

insert  into `uppgifter`(`ID`,`Tid`,`Datum`,`KategoriID`,`Beskrivning`) values 
(1,'13:40:00','2022-12-12',1,'hej gjorde select'),
(2,'12:20:00','2022-12-23',2,'function o grejor'),
(3,'08:30:00','2022-12-24',3,'fina grejer'),
(4,'14:30:00','2022-12-31',1,'select o p tagg'),
(5,'16:20:00','2023-01-03',3,'lite mer fina grejer');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
