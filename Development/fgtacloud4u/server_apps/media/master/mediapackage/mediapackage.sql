CREATE TABLE `mst_mediapackage` (
	`mediapackage_id` varchar(14) NOT NULL , 
	`mediapackage_descr` varchar(90)  , 
	`_createby` varchar(13) NOT NULL , 
	`_createdate` datetime NOT NULL DEFAULT current_timestamp(), 
	`_modifyby` varchar(13)  , 
	`_modifydate` datetime  , 
	PRIMARY KEY (`mediapackage_id`)
) 
ENGINE=InnoDB
COMMENT='Daftar Package Media';







CREATE TABLE `mst_mediapackageslot` (
	`mediapackageslot_id` varchar(14) NOT NULL , 
	`mediaadslot_id` varchar(14) NOT NULL , 
	`mediapackage_id` varchar(14) NOT NULL , 
	`_createby` varchar(13) NOT NULL , 
	`_createdate` datetime NOT NULL DEFAULT current_timestamp(), 
	`_modifyby` varchar(13)  , 
	`_modifydate` datetime  , 
	PRIMARY KEY (`mediapackageslot_id`)
) 
ENGINE=InnoDB
COMMENT='Daftar Slot untuk Package Media';

ALTER TABLE `mst_mediapackageslot` ADD KEY `mediaadslot_id` (`mediaadslot_id`);
ALTER TABLE `mst_mediapackageslot` ADD KEY `mediapackage_id` (`mediapackage_id`);

ALTER TABLE `mst_mediapackageslot` ADD CONSTRAINT `fk_mst_mediapackageslot_mst_mediaadslot` FOREIGN KEY (`mediaadslot_id`) REFERENCES `mst_mediaadslot` (`mediaadslot_id`);
ALTER TABLE `mst_mediapackageslot` ADD CONSTRAINT `fk_mst_mediapackageslot_mst_mediapackage` FOREIGN KEY (`mediapackage_id`) REFERENCES `mst_mediapackage` (`mediapackage_id`);





