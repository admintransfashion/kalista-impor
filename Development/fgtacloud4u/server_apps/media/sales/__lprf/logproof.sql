-- SET FOREIGN_KEY_CHECKS=0;

-- drop table if exists `trn_medialogproof`;
-- drop table if exists `trn_medialogproofitem`;


CREATE TABLE `trn_medialogproof` (
	`medialogproof_id` varchar(14) NOT NULL , 
	`medialogproof_date` date NOT NULL , 
	`medialogproof_iscommit` tinyint(1) NOT NULL DEFAULT 0, 
	`_createby` varchar(13) NOT NULL , 
	`_createdate` datetime NOT NULL DEFAULT current_timestamp(), 
	`_modifyby` varchar(13)  , 
	`_modifydate` datetime  , 
	UNIQUE KEY `medialogproof_date` (`medialogproof_date`),
	PRIMARY KEY (`medialogproof_id`)
) 
ENGINE=InnoDB
COMMENT='Daftar Schedule harian';







CREATE TABLE `trn_medialogproofitem` (
	`medialogproofitem_id` varchar(14) NOT NULL , 
	`mediaadslot_timestart` varchar(90) NOT NULL , 
	`mediaadslot_timeend` varchar(90) NOT NULL , 
	`mediaadslot_descr` varchar(90)  , 
	`actual_timestart` varchar(90) NOT NULL , 
	`actual_timeend` varchar(90) NOT NULL , 
	`actual_duration` decimal(5, 2) NOT NULL , 
	`medialogproofitem_spot` varchar(10)  , 
	`mediaorderitem_validr` decimal(14, 2) NOT NULL DEFAULT 0, 
	`mediaorder_id` varchar(14)  , 
	`mediaorderitem_id` varchar(14)  , 
	`medialogproof_id` varchar(14) NOT NULL , 
	`_createby` varchar(13) NOT NULL , 
	`_createdate` datetime NOT NULL DEFAULT current_timestamp(), 
	`_modifyby` varchar(13)  , 
	`_modifydate` datetime  , 
	PRIMARY KEY (`medialogproofitem_id`)
) 
ENGINE=InnoDB
COMMENT='Daftar Schedule harian';

ALTER TABLE `trn_medialogproofitem` ADD KEY `mediaorder_id` (`mediaorder_id`);
ALTER TABLE `trn_medialogproofitem` ADD KEY `mediaorderitem_id` (`mediaorderitem_id`);
ALTER TABLE `trn_medialogproofitem` ADD KEY `medialogproof_id` (`medialogproof_id`);

ALTER TABLE `trn_medialogproofitem` ADD CONSTRAINT `fk_trn_medialogproofitem_trn_mediaorder` FOREIGN KEY (`mediaorder_id`) REFERENCES `trn_mediaorder` (`mediaorder_id`);
ALTER TABLE `trn_medialogproofitem` ADD CONSTRAINT `fk_trn_medialogproofitem_trn_mediaorderitem` FOREIGN KEY (`mediaorderitem_id`) REFERENCES `trn_mediaorderitem` (`mediaorderitem_id`);
ALTER TABLE `trn_medialogproofitem` ADD CONSTRAINT `fk_trn_medialogproofitem_trn_medialogproof` FOREIGN KEY (`medialogproof_id`) REFERENCES `trn_medialogproof` (`medialogproof_id`);





