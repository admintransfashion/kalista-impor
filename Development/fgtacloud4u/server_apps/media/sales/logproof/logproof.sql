-- SET FOREIGN_KEY_CHECKS=0;

-- drop table if exists `trn_medialogproof`;
-- drop table if exists `trn_medialogproofitem`;
-- drop table if exists `trn_medialogproofupload`;


CREATE TABLE `trn_medialogproof` (
	`medialogproof_id` varchar(14) NOT NULL , 
	`medialogproof_date` date NOT NULL , 
	`medialogproof_version` int(4) NOT NULL DEFAULT 0, 
	`medialogproof_iscommit` tinyint(1) NOT NULL DEFAULT 0, 
	`medialogproof_commitby` varchar(14)  , 
	`medialogproof_commitdate` datetime  , 
	`medialogproof_isgenerate` tinyint(1) NOT NULL DEFAULT 0, 
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
	`mediaadslot_timestart` time NOT NULL , 
	`mediaadslot_timeend` time NOT NULL , 
	`mediaadslot_descr` varchar(90)  , 
	`actual_timestart` time NOT NULL , 
	`actual_timeend`  time NOT NULL , 
	`actual_duration` decimal(5, 2) NOT NULL , 
	`spot_id` varchar(30) NOT NULL , 
	`mediaorderitem_validr` decimal(14, 2) NOT NULL DEFAULT 0, 
	`mediaorderitem_ppnidr` decimal(14, 2) NOT NULL DEFAULT 0, 
	`pph_taxtype_id` varchar(10)  , 
	`mediaorder_id` varchar(14)  , 
	`mediaorderitem_id` varchar(14)  , 
	`mediaordertype_id` varchar(10)  , 
	`logproof_partnerinfo` varchar(255)  , 
	`agency_partner_id` varchar(14)  , 
	`advertiser_partner_id` varchar(14)  , 
	`brand_id` varchar(14)  , 
	`project_id` varchar(14)  , 
	`projecttask_id` varchar(14)  , 
	`medialogproof_id` varchar(14) NOT NULL , 
	`_createby` varchar(13) NOT NULL , 
	`_createdate` datetime NOT NULL DEFAULT current_timestamp(), 
	`_modifyby` varchar(13)  , 
	`_modifydate` datetime  , 
	PRIMARY KEY (`medialogproofitem_id`)
) 
ENGINE=InnoDB
COMMENT='Daftar Schedule harian';

ALTER TABLE `trn_medialogproofitem` ADD KEY `pph_taxtype_id` (`pph_taxtype_id`);
ALTER TABLE `trn_medialogproofitem` ADD KEY `mediaorder_id` (`mediaorder_id`);
ALTER TABLE `trn_medialogproofitem` ADD KEY `mediaorderitem_id` (`mediaorderitem_id`);
ALTER TABLE `trn_medialogproofitem` ADD KEY `mediaordertype_id` (`mediaordertype_id`);
ALTER TABLE `trn_medialogproofitem` ADD KEY `agency_partner_id` (`agency_partner_id`);
ALTER TABLE `trn_medialogproofitem` ADD KEY `advertiser_partner_id` (`advertiser_partner_id`);
ALTER TABLE `trn_medialogproofitem` ADD KEY `brand_id` (`brand_id`);
ALTER TABLE `trn_medialogproofitem` ADD KEY `project_id` (`project_id`);
ALTER TABLE `trn_medialogproofitem` ADD KEY `projecttask_id` (`projecttask_id`);
ALTER TABLE `trn_medialogproofitem` ADD KEY `medialogproof_id` (`medialogproof_id`);

ALTER TABLE `trn_medialogproofitem` ADD CONSTRAINT `fk_trn_medialogproofitem_mst_taxtype` FOREIGN KEY (`pph_taxtype_id`) REFERENCES `mst_taxtype` (`taxtype_id`);
ALTER TABLE `trn_medialogproofitem` ADD CONSTRAINT `fk_trn_medialogproofitem_trn_mediaorder` FOREIGN KEY (`mediaorder_id`) REFERENCES `trn_mediaorder` (`mediaorder_id`);
ALTER TABLE `trn_medialogproofitem` ADD CONSTRAINT `fk_trn_medialogproofitem_trn_mediaorderitem` FOREIGN KEY (`mediaorderitem_id`) REFERENCES `trn_mediaorderitem` (`mediaorderitem_id`);
ALTER TABLE `trn_medialogproofitem` ADD CONSTRAINT `fk_trn_medialogproofitem_mst_mediaordertype` FOREIGN KEY (`mediaordertype_id`) REFERENCES `mst_mediaordertype` (`mediaordertype_id`);
ALTER TABLE `trn_medialogproofitem` ADD CONSTRAINT `fk_trn_medialogproofitem_mst_partner` FOREIGN KEY (`agency_partner_id`) REFERENCES `mst_partner` (`partner_id`);
ALTER TABLE `trn_medialogproofitem` ADD CONSTRAINT `fk_trn_medialogproofitem_mst_partner_2` FOREIGN KEY (`advertiser_partner_id`) REFERENCES `mst_partner` (`partner_id`);
ALTER TABLE `trn_medialogproofitem` ADD CONSTRAINT `fk_trn_medialogproofitem_mst_brand` FOREIGN KEY (`brand_id`) REFERENCES `mst_brand` (`brand_id`);
ALTER TABLE `trn_medialogproofitem` ADD CONSTRAINT `fk_trn_medialogproofitem_mst_project` FOREIGN KEY (`project_id`) REFERENCES `mst_project` (`project_id`);
ALTER TABLE `trn_medialogproofitem` ADD CONSTRAINT `fk_trn_medialogproofitem_mst_projecttask` FOREIGN KEY (`projecttask_id`) REFERENCES `mst_projecttask` (`projecttask_id`);
ALTER TABLE `trn_medialogproofitem` ADD CONSTRAINT `fk_trn_medialogproofitem_trn_medialogproof` FOREIGN KEY (`medialogproof_id`) REFERENCES `trn_medialogproof` (`medialogproof_id`);





CREATE TABLE `trn_medialogproofupload` (
	`medialogproofupload_id` varchar(14) NOT NULL , 
	`mediaadslot_timestart` time NOT NULL , 
	`mediaadslot_timeend`time NOT NULL , 
	`mediaadslot_duration` decimal(6, 2) NOT NULL , 
	`mediaadslot_descr` varchar(255)  , 
	`mediaadslot_code` varchar(255)  , 
	`actual_timestart` time NOT NULL , 
	`actual_timeend` time NOT NULL , 
	`actual_duration` decimal(6, 2) NOT NULL , 
	`spot_id` varchar(30) NOT NULL , 
	`mediaorder_ref` varchar(30)  , 
	`mediaorder_reftype` varchar(30)  , 
	`mediaorder_descr` varchar(255)  , 
	`mediaorder_id` varchar(30)  , 
	`mediaordertype_id` varchar(10)  , 
	`agency_code` varchar(30)  , 
	`agency_name` varchar(90)  , 
	`agency_partner_id` varchar(14)  , 
	`advertiser_code` varchar(30)  , 
	`advertiser_name` varchar(90)  , 
	`advertiser_partner_id` varchar(14)  , 
	`brand_code` varchar(30)  , 
	`brand_name` varchar(90)  , 
	`brand_id` varchar(14)  , 
	`programme_code` varchar(30)  , 
	`programme_name` varchar(90)  , 
	`project_id` varchar(14)  , 
	`episode_code` varchar(30)  , 
	`episode_name` varchar(90)  , 
	`projecttask_id` varchar(14)  , 
	`medialogproof_validr` decimal(14, 2) NOT NULL DEFAULT 0, 
	`medialogproof_ppnidr` decimal(14, 2) NOT NULL DEFAULT 0, 
	`pph_taxtype_id` varchar(10)  , 
	`medialogproof_id` varchar(14) NOT NULL , 
	`_createby` varchar(13) NOT NULL , 
	`_createdate` datetime NOT NULL DEFAULT current_timestamp(), 
	`_modifyby` varchar(13)  , 
	`_modifydate` datetime  , 
	PRIMARY KEY (`medialogproofupload_id`)
) 
ENGINE=InnoDB
COMMENT='Daftar Schedule harian';

ALTER TABLE `trn_medialogproofupload` ADD KEY `mediaordertype_id` (`mediaordertype_id`);
ALTER TABLE `trn_medialogproofupload` ADD KEY `agency_partner_id` (`agency_partner_id`);
ALTER TABLE `trn_medialogproofupload` ADD KEY `advertiser_partner_id` (`advertiser_partner_id`);
ALTER TABLE `trn_medialogproofupload` ADD KEY `brand_id` (`brand_id`);
ALTER TABLE `trn_medialogproofupload` ADD KEY `project_id` (`project_id`);
ALTER TABLE `trn_medialogproofupload` ADD KEY `projecttask_id` (`projecttask_id`);
ALTER TABLE `trn_medialogproofupload` ADD KEY `pph_taxtype_id` (`pph_taxtype_id`);
ALTER TABLE `trn_medialogproofupload` ADD KEY `medialogproof_id` (`medialogproof_id`);

ALTER TABLE `trn_medialogproofupload` ADD CONSTRAINT `fk_trn_medialogproofupload_mst_mediaordertype` FOREIGN KEY (`mediaordertype_id`) REFERENCES `mst_mediaordertype` (`mediaordertype_id`);
ALTER TABLE `trn_medialogproofupload` ADD CONSTRAINT `fk_trn_medialogproofupload_mst_partner` FOREIGN KEY (`agency_partner_id`) REFERENCES `mst_partner` (`partner_id`);
ALTER TABLE `trn_medialogproofupload` ADD CONSTRAINT `fk_trn_medialogproofupload_mst_partner_2` FOREIGN KEY (`advertiser_partner_id`) REFERENCES `mst_partner` (`partner_id`);
ALTER TABLE `trn_medialogproofupload` ADD CONSTRAINT `fk_trn_medialogproofupload_mst_brand` FOREIGN KEY (`brand_id`) REFERENCES `mst_brand` (`brand_id`);
ALTER TABLE `trn_medialogproofupload` ADD CONSTRAINT `fk_trn_medialogproofupload_mst_project` FOREIGN KEY (`project_id`) REFERENCES `mst_project` (`project_id`);
ALTER TABLE `trn_medialogproofupload` ADD CONSTRAINT `fk_trn_medialogproofupload_mst_projecttask` FOREIGN KEY (`projecttask_id`) REFERENCES `mst_projecttask` (`projecttask_id`);
ALTER TABLE `trn_medialogproofupload` ADD CONSTRAINT `fk_trn_medialogproofupload_mst_taxtype` FOREIGN KEY (`pph_taxtype_id`) REFERENCES `mst_taxtype` (`taxtype_id`);
ALTER TABLE `trn_medialogproofupload` ADD CONSTRAINT `fk_trn_medialogproofupload_trn_medialogproof` FOREIGN KEY (`medialogproof_id`) REFERENCES `trn_medialogproof` (`medialogproof_id`);





