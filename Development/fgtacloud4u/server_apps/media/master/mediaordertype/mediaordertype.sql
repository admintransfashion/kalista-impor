CREATE TABLE `mst_mediaordertype` (
	`mediaordertype_id` varchar(10) NOT NULL , 
	`mediaordertype_name` varchar(30)  , 
	`mediaordertype_descr` varchar(90)  , 
	`_createby` varchar(13) NOT NULL , 
	`_createdate` datetime NOT NULL DEFAULT current_timestamp(), 
	`_modifyby` varchar(13)  , 
	`_modifydate` datetime  , 
	UNIQUE KEY `mediaordertype_name` (`mediaordertype_name`),
	PRIMARY KEY (`mediaordertype_id`)
) 
ENGINE=InnoDB
COMMENT='Daftar tipe-tipe Salmediaes Order';




INSERT INTO mst_mediaordertype (`mediaordertype_id`, `mediaordertype_name`, `_createby`, `_createdate`) VALUES ('BRT', 'BARTER', 'root', NOW());
INSERT INTO mst_mediaordertype (`mediaordertype_id`, `mediaordertype_name`, `_createby`, `_createdate`) VALUES ('BLO', 'BLOCKING', 'root', NOW());
INSERT INTO mst_mediaordertype (`mediaordertype_id`, `mediaordertype_name`, `_createby`, `_createdate`) VALUES ('CPR', 'CPRP', 'root', NOW());
INSERT INTO mst_mediaordertype (`mediaordertype_id`, `mediaordertype_name`, `_createby`, `_createdate`) VALUES ('RTL', 'RETAIL', 'root', NOW());
INSERT INTO mst_mediaordertype (`mediaordertype_id`, `mediaordertype_name`, `_createby`, `_createdate`) VALUES ('SPO', 'SPONSOR', 'root', NOW());



