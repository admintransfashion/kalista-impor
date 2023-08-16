-- SET FOREIGN_KEY_CHECKS=0;

-- drop table if exists `trn_medialogproofbilling`;
-- drop table if exists `trn_medialogproofbillingdet`;


CREATE TABLE IF NOT EXISTS `trn_medialogproofbilling` (
	`medialogproofbilling_id` varchar(14) NOT NULL , 
	`medialogproofbilling_date` date NOT NULL , 
	`periodemo_id` varchar(6) NOT NULL , 
	`dept_id` varchar(30)  , 
	`medialogproofbilling_version` int(4) NOT NULL DEFAULT 0, 
	`medialogproofbilling_iscommit` tinyint(1) NOT NULL DEFAULT 0, 
	`medialogproofbilling_commitby` varchar(14)  , 
	`medialogproofbilling_commitdate` datetime  , 
	`medialogproofbilling_isgenerate` tinyint(1) NOT NULL DEFAULT 0, 
	`_createby` varchar(13) NOT NULL , 
	`_createdate` datetime NOT NULL DEFAULT current_timestamp(), 
	`_modifyby` varchar(13)  , 
	`_modifydate` datetime  , 
	UNIQUE KEY `periodemo_id` (`periodemo_id`),
	PRIMARY KEY (`medialogproofbilling_id`)
) 
ENGINE=InnoDB
COMMENT='Daftar Schedule harian';


ALTER TABLE `trn_medialogproofbilling` ADD COLUMN IF NOT EXISTS  `medialogproofbilling_date` date NOT NULL  AFTER `medialogproofbilling_id`;
ALTER TABLE `trn_medialogproofbilling` ADD COLUMN IF NOT EXISTS  `periodemo_id` varchar(6) NOT NULL  AFTER `medialogproofbilling_date`;
ALTER TABLE `trn_medialogproofbilling` ADD COLUMN IF NOT EXISTS  `dept_id` varchar(30)   AFTER `periodemo_id`;
ALTER TABLE `trn_medialogproofbilling` ADD COLUMN IF NOT EXISTS  `medialogproofbilling_version` int(4) NOT NULL DEFAULT 0 AFTER `dept_id`;
ALTER TABLE `trn_medialogproofbilling` ADD COLUMN IF NOT EXISTS  `medialogproofbilling_iscommit` tinyint(1) NOT NULL DEFAULT 0 AFTER `medialogproofbilling_version`;
ALTER TABLE `trn_medialogproofbilling` ADD COLUMN IF NOT EXISTS  `medialogproofbilling_commitby` varchar(14)   AFTER `medialogproofbilling_iscommit`;
ALTER TABLE `trn_medialogproofbilling` ADD COLUMN IF NOT EXISTS  `medialogproofbilling_commitdate` datetime   AFTER `medialogproofbilling_commitby`;
ALTER TABLE `trn_medialogproofbilling` ADD COLUMN IF NOT EXISTS  `medialogproofbilling_isgenerate` tinyint(1) NOT NULL DEFAULT 0 AFTER `medialogproofbilling_commitdate`;


ALTER TABLE `trn_medialogproofbilling` MODIFY COLUMN IF EXISTS  `medialogproofbilling_date` date NOT NULL  AFTER `medialogproofbilling_id`;
ALTER TABLE `trn_medialogproofbilling` MODIFY COLUMN IF EXISTS  `periodemo_id` varchar(6) NOT NULL  AFTER `medialogproofbilling_date`;
ALTER TABLE `trn_medialogproofbilling` MODIFY COLUMN IF EXISTS  `dept_id` varchar(30)   AFTER `periodemo_id`;
ALTER TABLE `trn_medialogproofbilling` MODIFY COLUMN IF EXISTS  `medialogproofbilling_version` int(4) NOT NULL DEFAULT 0 AFTER `dept_id`;
ALTER TABLE `trn_medialogproofbilling` MODIFY COLUMN IF EXISTS  `medialogproofbilling_iscommit` tinyint(1) NOT NULL DEFAULT 0 AFTER `medialogproofbilling_version`;
ALTER TABLE `trn_medialogproofbilling` MODIFY COLUMN IF EXISTS  `medialogproofbilling_commitby` varchar(14)   AFTER `medialogproofbilling_iscommit`;
ALTER TABLE `trn_medialogproofbilling` MODIFY COLUMN IF EXISTS  `medialogproofbilling_commitdate` datetime   AFTER `medialogproofbilling_commitby`;
ALTER TABLE `trn_medialogproofbilling` MODIFY COLUMN IF EXISTS  `medialogproofbilling_isgenerate` tinyint(1) NOT NULL DEFAULT 0 AFTER `medialogproofbilling_commitdate`;


ALTER TABLE `trn_medialogproofbilling` ADD CONSTRAINT `periodemo_id` UNIQUE IF NOT EXISTS  (`periodemo_id`);

ALTER TABLE `trn_medialogproofbilling` ADD KEY IF NOT EXISTS `periodemo_id` (`periodemo_id`);
ALTER TABLE `trn_medialogproofbilling` ADD KEY IF NOT EXISTS `dept_id` (`dept_id`);

ALTER TABLE `trn_medialogproofbilling` ADD CONSTRAINT `fk_trn_medialogproofbilling_mst_periodemo` FOREIGN KEY IF NOT EXISTS  (`periodemo_id`) REFERENCES `mst_periodemo` (`periodemo_id`);
ALTER TABLE `trn_medialogproofbilling` ADD CONSTRAINT `fk_trn_medialogproofbilling_mst_dept` FOREIGN KEY IF NOT EXISTS  (`dept_id`) REFERENCES `mst_dept` (`dept_id`);





CREATE TABLE IF NOT EXISTS `trn_medialogproofbillingdet` (
	`medialogproofbillingdet_id` varchar(14) NOT NULL , 
	`mediaorder_id` varchar(14)  , 
	`billout_id` varchar(14)  , 
	`medialogproofbilling_gendate` date NOT NULL , 
	`medialogproofbilling_id` varchar(14) NOT NULL , 
	`_createby` varchar(13) NOT NULL , 
	`_createdate` datetime NOT NULL DEFAULT current_timestamp(), 
	`_modifyby` varchar(13)  , 
	`_modifydate` datetime  , 
	PRIMARY KEY (`medialogproofbillingdet_id`)
) 
ENGINE=InnoDB
COMMENT='Daftar Schedule harian';


ALTER TABLE `trn_medialogproofbillingdet` ADD COLUMN IF NOT EXISTS  `mediaorder_id` varchar(14)   AFTER `medialogproofbillingdet_id`;
ALTER TABLE `trn_medialogproofbillingdet` ADD COLUMN IF NOT EXISTS  `billout_id` varchar(14)   AFTER `mediaorder_id`;
ALTER TABLE `trn_medialogproofbillingdet` ADD COLUMN IF NOT EXISTS  `medialogproofbilling_gendate` date NOT NULL  AFTER `billout_id`;
ALTER TABLE `trn_medialogproofbillingdet` ADD COLUMN IF NOT EXISTS  `medialogproofbilling_id` varchar(14) NOT NULL  AFTER `medialogproofbilling_gendate`;


ALTER TABLE `trn_medialogproofbillingdet` MODIFY COLUMN IF EXISTS  `mediaorder_id` varchar(14)   AFTER `medialogproofbillingdet_id`;
ALTER TABLE `trn_medialogproofbillingdet` MODIFY COLUMN IF EXISTS  `billout_id` varchar(14)   AFTER `mediaorder_id`;
ALTER TABLE `trn_medialogproofbillingdet` MODIFY COLUMN IF EXISTS  `medialogproofbilling_gendate` date NOT NULL  AFTER `billout_id`;
ALTER TABLE `trn_medialogproofbillingdet` MODIFY COLUMN IF EXISTS  `medialogproofbilling_id` varchar(14) NOT NULL  AFTER `medialogproofbilling_gendate`;



ALTER TABLE `trn_medialogproofbillingdet` ADD KEY IF NOT EXISTS  `mediaorder_id` (`mediaorder_id`);
ALTER TABLE `trn_medialogproofbillingdet` ADD KEY IF NOT EXISTS  `billout_id` (`billout_id`);
ALTER TABLE `trn_medialogproofbillingdet` ADD KEY IF NOT EXISTS `medialogproofbilling_id` (`medialogproofbilling_id`);

ALTER TABLE `trn_medialogproofbillingdet` ADD CONSTRAINT `fk_trn_medialogproofbillingdet_trn_mediaorder` FOREIGN KEY IF NOT EXISTS (`mediaorder_id`) REFERENCES `trn_mediaorder` (`mediaorder_id`);
ALTER TABLE `trn_medialogproofbillingdet` ADD CONSTRAINT `fk_trn_medialogproofbillingdet_trn_billout` FOREIGN KEY IF NOT EXISTS (`billout_id`) REFERENCES `trn_billout` (`billout_id`);
ALTER TABLE `trn_medialogproofbillingdet` ADD CONSTRAINT `fk_trn_medialogproofbillingdet_trn_medialogproofbilling` FOREIGN KEY IF NOT EXISTS (`medialogproofbilling_id`) REFERENCES `trn_medialogproofbilling` (`medialogproofbilling_id`);





