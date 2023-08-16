-- SET FOREIGN_KEY_CHECKS=0;

-- drop table if exists `trn_medialogproofitem`;


CREATE TABLE `trn_medialogproofitem` (
	`medialogproofitem_id` varchar(14) NOT NULL , 
	`mediaadslot_timestart` varchar(8) NOT NULL , 
	`mediaadslot_timeend` varchar(8) NOT NULL , 
	`mediaadslot_descr` varchar(90)  , 
	`actual_timestart` varchar(8) NOT NULL , 
	`actual_timeend` varchar(8) NOT NULL , 
	`actual_duration` decimal(5, 2) NOT NULL , 
	`spot_id` varchar(30) NOT NULL , 
	`mediaorderitem_validr` decimal(14, 2) NOT NULL DEFAULT 0, 
	`mediaorderitem_ppnidr` decimal(14, 2) NOT NULL DEFAULT 0, 
	`pph_taxtype_id` varchar(10)  , 
	`mediaorder_id` varchar(14)  , 
	`mediaorderitem_id` varchar(14)  , 
	`projbudget_id` varchar(30)  , 
	`projbudgettask_id` varchar(14)  , 
	`billoutpreprocess_id` varchar(10)  , 
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
ALTER TABLE `trn_medialogproofitem` ADD KEY `projbudget_id` (`projbudget_id`);
ALTER TABLE `trn_medialogproofitem` ADD KEY `projbudgettask_id` (`projbudgettask_id`);
ALTER TABLE `trn_medialogproofitem` ADD KEY `billoutpreprocess_id` (`billoutpreprocess_id`);
ALTER TABLE `trn_medialogproofitem` ADD KEY `mediaordertype_id` (`mediaordertype_id`);
ALTER TABLE `trn_medialogproofitem` ADD KEY `agency_partner_id` (`agency_partner_id`);
ALTER TABLE `trn_medialogproofitem` ADD KEY `advertiser_partner_id` (`advertiser_partner_id`);
ALTER TABLE `trn_medialogproofitem` ADD KEY `brand_id` (`brand_id`);
ALTER TABLE `trn_medialogproofitem` ADD KEY `project_id` (`project_id`);
ALTER TABLE `trn_medialogproofitem` ADD KEY `projecttask_id` (`projecttask_id`);

ALTER TABLE `trn_medialogproofitem` ADD CONSTRAINT `fk_trn_medialogproofitem_mst_taxtype` FOREIGN KEY (`pph_taxtype_id`) REFERENCES `mst_taxtype` (`taxtype_id`);
ALTER TABLE `trn_medialogproofitem` ADD CONSTRAINT `fk_trn_medialogproofitem_trn_mediaorder` FOREIGN KEY (`mediaorder_id`) REFERENCES `trn_mediaorder` (`mediaorder_id`);
ALTER TABLE `trn_medialogproofitem` ADD CONSTRAINT `fk_trn_medialogproofitem_trn_mediaorderitem` FOREIGN KEY (`mediaorderitem_id`) REFERENCES `trn_mediaorderitem` (`mediaorderitem_id`);
ALTER TABLE `trn_medialogproofitem` ADD CONSTRAINT `fk_trn_medialogproofitem_mst_projbudget` FOREIGN KEY (`projbudget_id`) REFERENCES `mst_projbudget` (`projbudget_id`);
ALTER TABLE `trn_medialogproofitem` ADD CONSTRAINT `fk_trn_medialogproofitem_mst_projbudgettask` FOREIGN KEY (`projbudgettask_id`) REFERENCES `mst_projbudgettask` (`projbudgettask_id`);
ALTER TABLE `trn_medialogproofitem` ADD CONSTRAINT `fk_trn_medialogproofitem_mst_billoutpreprocess` FOREIGN KEY (`billoutpreprocess_id`) REFERENCES `mst_billoutpreprocess` (`billoutpreprocess_id`);
ALTER TABLE `trn_medialogproofitem` ADD CONSTRAINT `fk_trn_medialogproofitem_mst_mediaordertype` FOREIGN KEY (`mediaordertype_id`) REFERENCES `mst_mediaordertype` (`mediaordertype_id`);
ALTER TABLE `trn_medialogproofitem` ADD CONSTRAINT `fk_trn_medialogproofitem_mst_partner` FOREIGN KEY (`agency_partner_id`) REFERENCES `mst_partner` (`partner_id`);
ALTER TABLE `trn_medialogproofitem` ADD CONSTRAINT `fk_trn_medialogproofitem_mst_partner_2` FOREIGN KEY (`advertiser_partner_id`) REFERENCES `mst_partner` (`partner_id`);
ALTER TABLE `trn_medialogproofitem` ADD CONSTRAINT `fk_trn_medialogproofitem_mst_brand` FOREIGN KEY (`brand_id`) REFERENCES `mst_brand` (`brand_id`);
ALTER TABLE `trn_medialogproofitem` ADD CONSTRAINT `fk_trn_medialogproofitem_mst_project` FOREIGN KEY (`project_id`) REFERENCES `mst_project` (`project_id`);
ALTER TABLE `trn_medialogproofitem` ADD CONSTRAINT `fk_trn_medialogproofitem_mst_projecttask` FOREIGN KEY (`projecttask_id`) REFERENCES `mst_projecttask` (`projecttask_id`);





