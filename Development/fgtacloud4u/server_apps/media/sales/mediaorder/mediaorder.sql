-- SET FOREIGN_KEY_CHECKS=0;

-- drop table if exists `trn_mediaorder`;
-- drop table if exists `trn_mediaorderitem`;
-- drop table if exists `trn_mediaorderappr`;


CREATE TABLE `trn_mediaorder` (
	`mediaorder_id` varchar(30) NOT NULL , 
	`project_id` varchar(30) NOT NULL , 
	`orderintype_id` varchar(10)  , 
	`mediaorder_date` date NOT NULL , 
	`mediaorder_descr` varchar(255) NOT NULL , 
	`mediaorder_ref` varchar(90) NOT NULL , 
	`dept_id` varchar(30) NOT NULL , 
	`ae_empl_id` varchar(14)  , 
	`agency_partner_id` varchar(30) NOT NULL , 
	`advertiser_partner_id` varchar(30) NOT NULL , 
	`mediaorder_traffic` varchar(90) NOT NULL , 
	`mediaorder_status` varchar(90) NOT NULL , 
	`mediaorder_direct` varchar(90) NOT NULL , 
	`mediaorder_bundling` varchar(90) NOT NULL , 
	`orderin_totalqty` int(5) NOT NULL DEFAULT 0, 
	`orderin_totalitem` int(5) NOT NULL DEFAULT 0, 
	`orderin_salesgross` decimal(16, 0) NOT NULL DEFAULT 0, 
	`orderin_discount` decimal(16, 0) NOT NULL DEFAULT 0, 
	`orderin_subtotal` decimal(16, 0) NOT NULL DEFAULT 0, 
	`orderin_pph` decimal(16, 0) NOT NULL DEFAULT 0, 
	`orderin_nett` decimal(16, 0) NOT NULL DEFAULT 0, 
	`orderin_ppn` decimal(16, 0) NOT NULL DEFAULT 0, 
	`orderin_total` decimal(16, 0) NOT NULL DEFAULT 0, 
	`orderin_totaladdcost` decimal(16, 0) NOT NULL DEFAULT 0, 
	`orderin_payment` decimal(16, 0) NOT NULL DEFAULT 0, 
	`arunbill_coa_id` varchar(17) NOT NULL , 
	`ar_coa_id` varchar(17) NOT NULL , 
	`dp_coa_id` varchar(17) NOT NULL , 
	`sales_coa_id` varchar(17) NOT NULL , 
	`salesdisc_coa_id` varchar(17)  , 
	`ppn_coa_id` varchar(17)  , 
	`ppnsubsidi_coa_id` varchar(17)  , 
	`pph_coa_id` varchar(17)  , 
	`trxmodel_id` varchar(10) NOT NULL , 
	`doc_id` varchar(30) NOT NULL , 
	`mediaorder_version` int(4) NOT NULL DEFAULT 0, 
	`mediaorder_iscommit` tinyint(1) NOT NULL DEFAULT 0, 
	`mediaorder_commitby` varchar(14)  , 
	`mediaorder_commitdate` datetime  , 
	`mediaorder_isapprovalprogress` tinyint(1) NOT NULL DEFAULT 0, 
	`mediaorder_isapproved` tinyint(1) NOT NULL DEFAULT 0, 
	`mediaorder_approveby` varchar(14)  , 
	`mediaorder_approvedate` datetime  , 
	`mediaorder_isdeclined` tinyint(1) NOT NULL DEFAULT 0, 
	`mediaorder_declineby` varchar(14)  , 
	`mediaorder_declinedate` datetime  , 
	`mediaorder_notes` varchar(255)  , 
	`mediaorder_isclose` tinyint(1) NOT NULL DEFAULT 0, 
	`mediaorder_closeby` varchar(14)  , 
	`mediaorder_closedate` datetime  , 
	`_createby` varchar(13) NOT NULL , 
	`_createdate` datetime NOT NULL DEFAULT current_timestamp(), 
	`_modifyby` varchar(13)  , 
	`_modifydate` datetime  , 
	PRIMARY KEY (`mediaorder_id`)
) 
ENGINE=InnoDB
COMMENT='Daftar Media Order';

ALTER TABLE `trn_mediaorder` ADD KEY `project_id` (`project_id`);
ALTER TABLE `trn_mediaorder` ADD KEY `orderintype_id` (`orderintype_id`);
ALTER TABLE `trn_mediaorder` ADD KEY `dept_id` (`dept_id`);
ALTER TABLE `trn_mediaorder` ADD KEY `ae_empl_id` (`ae_empl_id`);
ALTER TABLE `trn_mediaorder` ADD KEY `agency_partner_id` (`agency_partner_id`);
ALTER TABLE `trn_mediaorder` ADD KEY `advertiser_partner_id` (`advertiser_partner_id`);
ALTER TABLE `trn_mediaorder` ADD KEY `arunbill_coa_id` (`arunbill_coa_id`);
ALTER TABLE `trn_mediaorder` ADD KEY `ar_coa_id` (`ar_coa_id`);
ALTER TABLE `trn_mediaorder` ADD KEY `dp_coa_id` (`dp_coa_id`);
ALTER TABLE `trn_mediaorder` ADD KEY `sales_coa_id` (`sales_coa_id`);
ALTER TABLE `trn_mediaorder` ADD KEY `salesdisc_coa_id` (`salesdisc_coa_id`);
ALTER TABLE `trn_mediaorder` ADD KEY `ppn_coa_id` (`ppn_coa_id`);
ALTER TABLE `trn_mediaorder` ADD KEY `ppnsubsidi_coa_id` (`ppnsubsidi_coa_id`);
ALTER TABLE `trn_mediaorder` ADD KEY `pph_coa_id` (`pph_coa_id`);
ALTER TABLE `trn_mediaorder` ADD KEY `trxmodel_id` (`trxmodel_id`);
ALTER TABLE `trn_mediaorder` ADD KEY `doc_id` (`doc_id`);

ALTER TABLE `trn_mediaorder` ADD CONSTRAINT `fk_trn_mediaorder_mst_project` FOREIGN KEY (`project_id`) REFERENCES `mst_project` (`project_id`);
ALTER TABLE `trn_mediaorder` ADD CONSTRAINT `fk_trn_mediaorder_mst_orderintype` FOREIGN KEY (`orderintype_id`) REFERENCES `mst_orderintype` (`orderintype_id`);
ALTER TABLE `trn_mediaorder` ADD CONSTRAINT `fk_trn_mediaorder_mst_dept` FOREIGN KEY (`dept_id`) REFERENCES `mst_dept` (`dept_id`);
ALTER TABLE `trn_mediaorder` ADD CONSTRAINT `fk_trn_mediaorder_mst_empl` FOREIGN KEY (`ae_empl_id`) REFERENCES `mst_empl` (`empl_id`);
ALTER TABLE `trn_mediaorder` ADD CONSTRAINT `fk_trn_mediaorder_mst_partner` FOREIGN KEY (`agency_partner_id`) REFERENCES `mst_partner` (`partner_id`);
ALTER TABLE `trn_mediaorder` ADD CONSTRAINT `fk_trn_mediaorder_mst_partner_2` FOREIGN KEY (`advertiser_partner_id`) REFERENCES `mst_partner` (`partner_id`);
ALTER TABLE `trn_mediaorder` ADD CONSTRAINT `fk_trn_mediaorder_mst_coa` FOREIGN KEY (`arunbill_coa_id`) REFERENCES `mst_coa` (`coa_id`);
ALTER TABLE `trn_mediaorder` ADD CONSTRAINT `fk_trn_mediaorder_mst_coa_2` FOREIGN KEY (`ar_coa_id`) REFERENCES `mst_coa` (`coa_id`);
ALTER TABLE `trn_mediaorder` ADD CONSTRAINT `fk_trn_mediaorder_mst_coa_3` FOREIGN KEY (`dp_coa_id`) REFERENCES `mst_coa` (`coa_id`);
ALTER TABLE `trn_mediaorder` ADD CONSTRAINT `fk_trn_mediaorder_mst_coa_4` FOREIGN KEY (`sales_coa_id`) REFERENCES `mst_coa` (`coa_id`);
ALTER TABLE `trn_mediaorder` ADD CONSTRAINT `fk_trn_mediaorder_mst_coa_5` FOREIGN KEY (`salesdisc_coa_id`) REFERENCES `mst_coa` (`coa_id`);
ALTER TABLE `trn_mediaorder` ADD CONSTRAINT `fk_trn_mediaorder_mst_coa_6` FOREIGN KEY (`ppn_coa_id`) REFERENCES `mst_coa` (`coa_id`);
ALTER TABLE `trn_mediaorder` ADD CONSTRAINT `fk_trn_mediaorder_mst_coa_7` FOREIGN KEY (`ppnsubsidi_coa_id`) REFERENCES `mst_coa` (`coa_id`);
ALTER TABLE `trn_mediaorder` ADD CONSTRAINT `fk_trn_mediaorder_mst_coa_8` FOREIGN KEY (`pph_coa_id`) REFERENCES `mst_coa` (`coa_id`);
ALTER TABLE `trn_mediaorder` ADD CONSTRAINT `fk_trn_mediaorder_mst_trxmodel` FOREIGN KEY (`trxmodel_id`) REFERENCES `mst_trxmodel` (`trxmodel_id`);
ALTER TABLE `trn_mediaorder` ADD CONSTRAINT `fk_trn_mediaorder_mst_doc` FOREIGN KEY (`doc_id`) REFERENCES `mst_doc` (`doc_id`);





CREATE TABLE `trn_mediaorderitem` (
	`mediaorderitem_id` varchar(14) NOT NULL , 
	`itemclass_id` varchar(14) NOT NULL , 
	`brand_id` varchar(14) NOT NULL , 
	`mediaorderitem_spot` varchar(90) NOT NULL , 
	`mediaorderitem_descr` varchar(90) NOT NULL , 
	`mediaorderitem_validr` decimal(14, 2) NOT NULL DEFAULT 0, 
	`projbudget_id` varchar(30)  , 
	`projbudgettask_id` varchar(14)  , 
	`project_id` varchar(30)  , 
	`projecttask_id` varchar(14)  , 
	`mediaorder_id` varchar(30) NOT NULL , 
	`_createby` varchar(13) NOT NULL , 
	`_createdate` datetime NOT NULL DEFAULT current_timestamp(), 
	`_modifyby` varchar(13)  , 
	`_modifydate` datetime  , 
	PRIMARY KEY (`mediaorderitem_id`)
) 
ENGINE=InnoDB
COMMENT='Meida Order Item';

ALTER TABLE `trn_mediaorderitem` ADD KEY `itemclass_id` (`itemclass_id`);
ALTER TABLE `trn_mediaorderitem` ADD KEY `brand_id` (`brand_id`);
ALTER TABLE `trn_mediaorderitem` ADD KEY `projbudget_id` (`projbudget_id`);
ALTER TABLE `trn_mediaorderitem` ADD KEY `projbudgettask_id` (`projbudgettask_id`);
ALTER TABLE `trn_mediaorderitem` ADD KEY `project_id` (`project_id`);
ALTER TABLE `trn_mediaorderitem` ADD KEY `projecttask_id` (`projecttask_id`);
ALTER TABLE `trn_mediaorderitem` ADD KEY `mediaorder_id` (`mediaorder_id`);

ALTER TABLE `trn_mediaorderitem` ADD CONSTRAINT `fk_trn_mediaorderitem_mst_itemclass` FOREIGN KEY (`itemclass_id`) REFERENCES `mst_itemclass` (`itemclass_id`);
ALTER TABLE `trn_mediaorderitem` ADD CONSTRAINT `fk_trn_mediaorderitem_mst_brand` FOREIGN KEY (`brand_id`) REFERENCES `mst_brand` (`brand_id`);
ALTER TABLE `trn_mediaorderitem` ADD CONSTRAINT `fk_trn_mediaorderitem_mst_projbudget` FOREIGN KEY (`projbudget_id`) REFERENCES `mst_projbudget` (`projbudget_id`);
ALTER TABLE `trn_mediaorderitem` ADD CONSTRAINT `fk_trn_mediaorderitem_mst_projbudgettask` FOREIGN KEY (`projbudgettask_id`) REFERENCES `mst_projbudgettask` (`projbudgettask_id`);
ALTER TABLE `trn_mediaorderitem` ADD CONSTRAINT `fk_trn_mediaorderitem_mst_project` FOREIGN KEY (`project_id`) REFERENCES `mst_project` (`project_id`);
ALTER TABLE `trn_mediaorderitem` ADD CONSTRAINT `fk_trn_mediaorderitem_mst_projecttask` FOREIGN KEY (`projecttask_id`) REFERENCES `mst_projecttask` (`projecttask_id`);
ALTER TABLE `trn_mediaorderitem` ADD CONSTRAINT `fk_trn_mediaorderitem_trn_mediaorder` FOREIGN KEY (`mediaorder_id`) REFERENCES `trn_mediaorder` (`mediaorder_id`);





CREATE TABLE `trn_mediaorderappr` (
	`mediaorderappr_id` varchar(14) NOT NULL , 
	`mediaorderappr_isapproved` tinyint(1) NOT NULL DEFAULT 0, 
	`mediaorderappr_by` varchar(14)  , 
	`mediaorderappr_date` datetime  , 
	`mediaorder_version` int(4) NOT NULL DEFAULT 0, 
	`mediaorderappr_isdeclined` tinyint(1) NOT NULL DEFAULT 0, 
	`mediaorderappr_declinedby` varchar(14)  , 
	`mediaorderappr_declineddate` datetime  , 
	`mediaorderappr_notes` varchar(255)  , 
	`mediaorder_id` varchar(30) NOT NULL , 
	`docauth_descr` varchar(90)  , 
	`docauth_order` int(4) NOT NULL DEFAULT 0, 
	`docauth_value` int(4) NOT NULL DEFAULT 100, 
	`docauth_min` int(4) NOT NULL DEFAULT 0, 
	`authlevel_id` varchar(10) NOT NULL , 
	`authlevel_name` varchar(60) NOT NULL , 
	`auth_id` varchar(10)  , 
	`auth_name` varchar(60) NOT NULL , 
	`_createby` varchar(13) NOT NULL , 
	`_createdate` datetime NOT NULL DEFAULT current_timestamp(), 
	`_modifyby` varchar(13)  , 
	`_modifydate` datetime  , 
	UNIQUE KEY `mediaorder_auth_id` (`mediaorder_id`, `auth_id`),
	PRIMARY KEY (`mediaorderappr_id`)
) 
ENGINE=InnoDB
COMMENT='Approval undefined';

ALTER TABLE `trn_mediaorderappr` ADD KEY `mediaorder_id` (`mediaorder_id`);

ALTER TABLE `trn_mediaorderappr` ADD CONSTRAINT `fk_trn_mediaorderappr_trn_mediaorder` FOREIGN KEY (`mediaorder_id`) REFERENCES `trn_mediaorder` (`mediaorder_id`);





