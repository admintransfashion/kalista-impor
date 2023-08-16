-- SET FOREIGN_KEY_CHECKS=0;

-- drop table if exists `trn_etapsalesdata`;


CREATE TABLE IF NOT EXISTS `trn_etapsalesdata` (
	`etapsalesdata_id` varchar(30) NOT NULL , 
	`tx_id` varchar(90) NOT NULL , 
	`tx_date` date NOT NULL , 
	`site_id` varchar(30) NOT NULL , 
	`etapsalesdata_qty` decimal(18, 2) NOT NULL DEFAULT 0, 
	`etapsalesdata_value` decimal(18, 2) NOT NULL DEFAULT 0, 
	`etapsalesdata_tax` decimal(18, 2) NOT NULL DEFAULT 0, 
	`_createby` varchar(14) NOT NULL , 
	`_createdate` datetime NOT NULL DEFAULT current_timestamp(), 
	`_modifyby` varchar(14)  , 
	`_modifydate` datetime  , 
	PRIMARY KEY (`etapsalesdata_id`)
) 
ENGINE=InnoDB
COMMENT='Sales Data Etap';


ALTER TABLE `trn_etapsalesdata` ADD COLUMN IF NOT EXISTS  `tx_id` varchar(90) NOT NULL  AFTER `etapsalesdata_id`;
ALTER TABLE `trn_etapsalesdata` ADD COLUMN IF NOT EXISTS  `tx_date` date NOT NULL  AFTER `tx_id`;
ALTER TABLE `trn_etapsalesdata` ADD COLUMN IF NOT EXISTS  `site_id` varchar(30) NOT NULL  AFTER `tx_date`;
ALTER TABLE `trn_etapsalesdata` ADD COLUMN IF NOT EXISTS  `etapsalesdata_qty` decimal(18, 2) NOT NULL DEFAULT 0 AFTER `site_id`;
ALTER TABLE `trn_etapsalesdata` ADD COLUMN IF NOT EXISTS  `etapsalesdata_value` decimal(18, 2) NOT NULL DEFAULT 0 AFTER `etapsalesdata_qty`;
ALTER TABLE `trn_etapsalesdata` ADD COLUMN IF NOT EXISTS  `etapsalesdata_tax` decimal(18, 2) NOT NULL DEFAULT 0 AFTER `etapsalesdata_value`;


ALTER TABLE `trn_etapsalesdata` MODIFY COLUMN IF EXISTS  `tx_id` varchar(90) NOT NULL  AFTER `etapsalesdata_id`;
ALTER TABLE `trn_etapsalesdata` MODIFY COLUMN IF EXISTS  `tx_date` date NOT NULL  AFTER `tx_id`;
ALTER TABLE `trn_etapsalesdata` MODIFY COLUMN IF EXISTS  `site_id` varchar(30) NOT NULL  AFTER `tx_date`;
ALTER TABLE `trn_etapsalesdata` MODIFY COLUMN IF EXISTS  `etapsalesdata_qty` decimal(18, 2) NOT NULL DEFAULT 0 AFTER `site_id`;
ALTER TABLE `trn_etapsalesdata` MODIFY COLUMN IF EXISTS  `etapsalesdata_value` decimal(18, 2) NOT NULL DEFAULT 0 AFTER `etapsalesdata_qty`;
ALTER TABLE `trn_etapsalesdata` MODIFY COLUMN IF EXISTS  `etapsalesdata_tax` decimal(18, 2) NOT NULL DEFAULT 0 AFTER `etapsalesdata_value`;



ALTER TABLE `trn_etapsalesdata` ADD KEY IF NOT EXISTS `site_id` (`site_id`);

ALTER TABLE `trn_etapsalesdata` ADD CONSTRAINT `fk_trn_etapsalesdata_mst_site` FOREIGN KEY IF NOT EXISTS  (`site_id`) REFERENCES `mst_site` (`site_id`);





