CREATE TABLE `trn_mediaorder` (
	`mediaorder_id` varchar(30) NOT NULL , 
	`mediaorder_descr` varchar(90) NOT NULL , 
	`mediaorder_date` date NOT NULL , 
	`mediaordertype_id` varchar(10) NULL , 
	`agency_partner_id` varchar(30) NOT NULL , 
	`advertiser_partner_id` varchar(30) NOT NULL , 
	`brand_id` varchar(10) NOT NULL , 
	`mediapackage_id` varchar(10)  , 
	`salesordertype_id` varchar(10) NOT NULL , 
	`doc_id` varchar(30) NOT NULL , 
	`_createby` varchar(13) NOT NULL , 
	`_createdate` datetime NOT NULL DEFAULT current_timestamp(), 
	`_modifyby` varchar(13)  , 
	`_modifydate` datetime  , 
	PRIMARY KEY (`mediaorder_id`)
) 
ENGINE=InnoDB
COMMENT='Daftar Media Order';

ALTER TABLE `trn_mediaorder` ADD KEY `mediaordertype_id` (`mediaordertype_id`);
ALTER TABLE `trn_mediaorder` ADD KEY `agency_partner_id` (`agency_partner_id`);
ALTER TABLE `trn_mediaorder` ADD KEY `advertiser_partner_id` (`advertiser_partner_id`);
ALTER TABLE `trn_mediaorder` ADD KEY `brand_id` (`brand_id`);
ALTER TABLE `trn_mediaorder` ADD KEY `mediapackage_id` (`mediapackage_id`);
ALTER TABLE `trn_mediaorder` ADD KEY `salesordertype_id` (`salesordertype_id`);
ALTER TABLE `trn_mediaorder` ADD KEY `doc_id` (`doc_id`);

ALTER TABLE `trn_mediaorder` ADD CONSTRAINT `fk_trn_mediaorder_mst_mediaordertype` FOREIGN KEY (`mediaordertype_id`) REFERENCES `mst_mediaordertype` (`mediaordertype_id`);
ALTER TABLE `trn_mediaorder` ADD CONSTRAINT `fk_trn_mediaorder_mst_partner` FOREIGN KEY (`agency_partner_id`) REFERENCES `mst_partner` (`partner_id`);
ALTER TABLE `trn_mediaorder` ADD CONSTRAINT `fk_trn_mediaorder_mst_partner_2` FOREIGN KEY (`advertiser_partner_id`) REFERENCES `mst_partner` (`partner_id`);
ALTER TABLE `trn_mediaorder` ADD CONSTRAINT `fk_trn_mediaorder_mst_brand` FOREIGN KEY (`brand_id`) REFERENCES `mst_brand` (`brand_id`);
ALTER TABLE `trn_mediaorder` ADD CONSTRAINT `fk_trn_mediaorder_mst_mediapackage` FOREIGN KEY (`mediapackage_id`) REFERENCES `mst_mediapackage` (`mediapackage_id`);
ALTER TABLE `trn_mediaorder` ADD CONSTRAINT `fk_trn_mediaorder_mst_salesordertype` FOREIGN KEY (`salesordertype_id`) REFERENCES `mst_salesordertype` (`salesordertype_id`);
ALTER TABLE `trn_mediaorder` ADD CONSTRAINT `fk_trn_mediaorder_mst_doc` FOREIGN KEY (`doc_id`) REFERENCES `mst_doc` (`doc_id`);





CREATE TABLE `trn_mediaorderitem` (
	`mediaorderitem_id` varchar(14) NOT NULL , 
	`mediaorderitem_descr` varchar(90) NOT NULL , 
	`mediaadslot_id` varchar(14)  , 
	`itemclass_id` varchar(14) NOT NULL , 
	`mediaorderitem_price` decimal(14, 2) NOT NULL DEFAULT 0, 
	`mediaordertype_id` varchar(10) NOT NULL , 
	`mediaorder_id` varchar(30) NOT NULL , 
	`_createby` varchar(13) NOT NULL , 
	`_createdate` datetime NOT NULL DEFAULT current_timestamp(), 
	`_modifyby` varchar(13)  , 
	`_modifydate` datetime  , 
	PRIMARY KEY (`mediaorderitem_id`)
) 
ENGINE=InnoDB
COMMENT='Meida Order Item';

ALTER TABLE `trn_mediaorderitem` ADD KEY `mediaadslot_id` (`mediaadslot_id`);
ALTER TABLE `trn_mediaorderitem` ADD KEY `itemclass_id` (`itemclass_id`);
ALTER TABLE `trn_mediaorderitem` ADD KEY `mediaorder_id` (`mediaorder_id`);
ALTER TABLE `trn_mediaorderitem` ADD KEY `mediaordertype_id` (`mediaordertype_id`);

ALTER TABLE `trn_mediaorderitem` ADD CONSTRAINT `fk_trn_mediaorderitem_mst_mediaadslot` FOREIGN KEY (`mediaadslot_id`) REFERENCES `mst_mediaadslot` (`mediaadslot_id`);
ALTER TABLE `trn_mediaorderitem` ADD CONSTRAINT `fk_trn_mediaorderitem_mst_itemclass` FOREIGN KEY (`itemclass_id`) REFERENCES `mst_itemclass` (`itemclass_id`);
ALTER TABLE `trn_mediaorderitem` ADD CONSTRAINT `fk_trn_mediaorderitem_trn_mediaorder` FOREIGN KEY (`mediaorder_id`) REFERENCES `trn_mediaorder` (`mediaorder_id`);
ALTER TABLE `trn_mediaorderitem` ADD CONSTRAINT `fk_trn_mediaorderitem_mst_mediaordertype` FOREIGN KEY (`mediaordertype_id`) REFERENCES `mst_mediaordertype` (`mediaordertype_id`);





CREATE TABLE `trn_mediaorderpayterm` (
	`mediaorderpayterm_id` varchar(14) NOT NULL , 
	`mediaorderpayterm_descr` varchar(90) NOT NULL , 
	`mediaorderpayterm_dt` date NOT NULL , 
	`mediaorderpayterm_value` decimal(14, 2) NOT NULL DEFAULT 0, 
	`mediaorder_id` varchar(30) NOT NULL , 
	`_createby` varchar(13) NOT NULL , 
	`_createdate` datetime NOT NULL DEFAULT current_timestamp(), 
	`_modifyby` varchar(13)  , 
	`_modifydate` datetime  , 
	PRIMARY KEY (`mediaorderpayterm_id`)
) 
ENGINE=InnoDB
COMMENT='Termin pembayaran Media Order';

ALTER TABLE `trn_mediaorderpayterm` ADD KEY `mediaorder_id` (`mediaorder_id`);

ALTER TABLE `trn_mediaorderpayterm` ADD CONSTRAINT `fk_trn_mediaorderpayterm_trn_mediaorder` FOREIGN KEY (`mediaorder_id`) REFERENCES `trn_mediaorder` (`mediaorder_id`);





