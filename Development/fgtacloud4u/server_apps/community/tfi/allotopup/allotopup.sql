-- SET FOREIGN_KEY_CHECKS=0;

-- drop table if exists `trn_allotopup`;


CREATE TABLE IF NOT EXISTS `trn_allotopup` (
	`allotopup_id` varchar(36)  , 
	`site_id` varchar(30)  , 
	`allotopup_date` date NOT NULL , 
	`allotopup_name` varchar(64)  , 
	`allotopup_email` varchar(128)  , 
	`allotopup_phone` varchar(64)  , 
	`allotopup_validr` decimal(14, 2) NOT NULL DEFAULT 0, 
	`allotopup_clientref` varchar(64)  , 
	`allotopup_txid` varchar(64)  , 
	`allotopup_nonce` varchar(64)  , 
	`allotopup_timestamp` varchar(64)  , 
	`allotopup_barcode` varchar(64)  , 
	`allotopup_alloref` varchar(64)  , 
	`allotopup_status` varchar(64)  , 
	`allotopup_message` varchar(64)  , 
	`allotopup_isdone` tinyint(1) NOT NULL DEFAULT 0, 
	`allotopup_isgen` tinyint(1) NOT NULL DEFAULT 0, 
	`allotopup_genby` varchar(14)  , 
	`allotopup_gendate` datetime  , 
	`_createby` varchar(14) NOT NULL , 
	`_createdate` datetime NOT NULL DEFAULT current_timestamp(), 
	`_modifyby` varchar(14)  , 
	`_modifydate` datetime  , 
	PRIMARY KEY (`allotopup_id`)
) 
ENGINE=InnoDB
COMMENT='Daftar Topup Allo';


ALTER TABLE `trn_allotopup` ADD COLUMN IF NOT EXISTS  `site_id` varchar(30)   AFTER `allotopup_id`;
ALTER TABLE `trn_allotopup` ADD COLUMN IF NOT EXISTS  `allotopup_date` date NOT NULL  AFTER `site_id`;
ALTER TABLE `trn_allotopup` ADD COLUMN IF NOT EXISTS  `allotopup_name` varchar(64)   AFTER `allotopup_date`;
ALTER TABLE `trn_allotopup` ADD COLUMN IF NOT EXISTS  `allotopup_email` varchar(128)   AFTER `allotopup_name`;
ALTER TABLE `trn_allotopup` ADD COLUMN IF NOT EXISTS  `allotopup_phone` varchar(64)   AFTER `allotopup_email`;
ALTER TABLE `trn_allotopup` ADD COLUMN IF NOT EXISTS  `allotopup_validr` decimal(14, 2) NOT NULL DEFAULT 0 AFTER `allotopup_phone`;
ALTER TABLE `trn_allotopup` ADD COLUMN IF NOT EXISTS  `allotopup_clientref` varchar(64)   AFTER `allotopup_validr`;
ALTER TABLE `trn_allotopup` ADD COLUMN IF NOT EXISTS  `allotopup_txid` varchar(64)   AFTER `allotopup_clientref`;
ALTER TABLE `trn_allotopup` ADD COLUMN IF NOT EXISTS  `allotopup_nonce` varchar(64)   AFTER `allotopup_txid`;
ALTER TABLE `trn_allotopup` ADD COLUMN IF NOT EXISTS  `allotopup_timestamp` varchar(64)   AFTER `allotopup_nonce`;
ALTER TABLE `trn_allotopup` ADD COLUMN IF NOT EXISTS  `allotopup_barcode` varchar(64)   AFTER `allotopup_timestamp`;
ALTER TABLE `trn_allotopup` ADD COLUMN IF NOT EXISTS  `allotopup_alloref` varchar(64)   AFTER `allotopup_barcode`;
ALTER TABLE `trn_allotopup` ADD COLUMN IF NOT EXISTS  `allotopup_status` varchar(64)   AFTER `allotopup_alloref`;
ALTER TABLE `trn_allotopup` ADD COLUMN IF NOT EXISTS  `allotopup_message` varchar(64)   AFTER `allotopup_status`;
ALTER TABLE `trn_allotopup` ADD COLUMN IF NOT EXISTS  `allotopup_isdone` tinyint(1) NOT NULL DEFAULT 0 AFTER `allotopup_message`;
ALTER TABLE `trn_allotopup` ADD COLUMN IF NOT EXISTS  `allotopup_isgen` tinyint(1) NOT NULL DEFAULT 0 AFTER `allotopup_isdone`;
ALTER TABLE `trn_allotopup` ADD COLUMN IF NOT EXISTS  `allotopup_genby` varchar(14)   AFTER `allotopup_isgen`;
ALTER TABLE `trn_allotopup` ADD COLUMN IF NOT EXISTS  `allotopup_gendate` datetime   AFTER `allotopup_genby`;


ALTER TABLE `trn_allotopup` MODIFY COLUMN IF EXISTS  `site_id` varchar(30)   AFTER `allotopup_id`;
ALTER TABLE `trn_allotopup` MODIFY COLUMN IF EXISTS  `allotopup_date` date NOT NULL  AFTER `site_id`;
ALTER TABLE `trn_allotopup` MODIFY COLUMN IF EXISTS  `allotopup_name` varchar(64)   AFTER `allotopup_date`;
ALTER TABLE `trn_allotopup` MODIFY COLUMN IF EXISTS  `allotopup_email` varchar(128)   AFTER `allotopup_name`;
ALTER TABLE `trn_allotopup` MODIFY COLUMN IF EXISTS  `allotopup_phone` varchar(64)   AFTER `allotopup_email`;
ALTER TABLE `trn_allotopup` MODIFY COLUMN IF EXISTS  `allotopup_validr` decimal(14, 2) NOT NULL DEFAULT 0 AFTER `allotopup_phone`;
ALTER TABLE `trn_allotopup` MODIFY COLUMN IF EXISTS  `allotopup_clientref` varchar(64)   AFTER `allotopup_validr`;
ALTER TABLE `trn_allotopup` MODIFY COLUMN IF EXISTS  `allotopup_txid` varchar(64)   AFTER `allotopup_clientref`;
ALTER TABLE `trn_allotopup` MODIFY COLUMN IF EXISTS  `allotopup_nonce` varchar(64)   AFTER `allotopup_txid`;
ALTER TABLE `trn_allotopup` MODIFY COLUMN IF EXISTS  `allotopup_timestamp` varchar(64)   AFTER `allotopup_nonce`;
ALTER TABLE `trn_allotopup` MODIFY COLUMN IF EXISTS  `allotopup_barcode` varchar(64)   AFTER `allotopup_timestamp`;
ALTER TABLE `trn_allotopup` MODIFY COLUMN IF EXISTS  `allotopup_alloref` varchar(64)   AFTER `allotopup_barcode`;
ALTER TABLE `trn_allotopup` MODIFY COLUMN IF EXISTS  `allotopup_status` varchar(64)   AFTER `allotopup_alloref`;
ALTER TABLE `trn_allotopup` MODIFY COLUMN IF EXISTS  `allotopup_message` varchar(64)   AFTER `allotopup_status`;
ALTER TABLE `trn_allotopup` MODIFY COLUMN IF EXISTS  `allotopup_isdone` tinyint(1) NOT NULL DEFAULT 0 AFTER `allotopup_message`;
ALTER TABLE `trn_allotopup` MODIFY COLUMN IF EXISTS  `allotopup_isgen` tinyint(1) NOT NULL DEFAULT 0 AFTER `allotopup_isdone`;
ALTER TABLE `trn_allotopup` MODIFY COLUMN IF EXISTS  `allotopup_genby` varchar(14)   AFTER `allotopup_isgen`;
ALTER TABLE `trn_allotopup` MODIFY COLUMN IF EXISTS  `allotopup_gendate` datetime   AFTER `allotopup_genby`;



ALTER TABLE `trn_allotopup` ADD KEY IF NOT EXISTS `site_id` (`site_id`);

ALTER TABLE `trn_allotopup` ADD CONSTRAINT `fk_trn_allotopup_mst_site` FOREIGN KEY IF NOT EXISTS  (`site_id`) REFERENCES `mst_site` (`site_id`);





