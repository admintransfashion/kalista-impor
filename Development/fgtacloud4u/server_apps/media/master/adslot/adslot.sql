CREATE TABLE `mst_mediaadslot` (
	`mediaadslot_id` varchar(14) NOT NULL , 
	`mediaadslot_date` date NOT NULL , 
	`mediaadslot_timestart` varchar(90) NOT NULL , 
	`mediaadslot_timeend` varchar(90) NOT NULL , 
	`mediaadslot_descr` varchar(90)  , 
	`mediaschprog_timestart` varchar(90) NOT NULL , 
	`mediaschprog_timeend` varchar(90) NOT NULL , 
	`mediaschprog_notes` varchar(90)  , 
	`mediaschprog_id` varchar(14) NOT NULL , 
	`itemclass_id` varchar(14) NOT NULL , 
	`_createby` varchar(13) NOT NULL , 
	`_createdate` datetime NOT NULL DEFAULT current_timestamp(), 
	`_modifyby` varchar(13)  , 
	`_modifydate` datetime  , 
	PRIMARY KEY (`mediaadslot_id`)
) 
ENGINE=InnoDB
COMMENT='Daftar Schedule harian';

ALTER TABLE `mst_mediaadslot` ADD KEY `mediaschprog_id` (`mediaschprog_id`);
ALTER TABLE `mst_mediaadslot` ADD KEY `itemclass_id` (`itemclass_id`);

ALTER TABLE `mst_mediaadslot` ADD CONSTRAINT `fk_mst_mediaadslot_mst_mediaschprog` FOREIGN KEY (`mediaschprog_id`) REFERENCES `mst_mediaschprog` (`mediaschprog_id`);
ALTER TABLE `mst_mediaadslot` ADD CONSTRAINT `fk_mst_mediaadslot_mst_itemclass` FOREIGN KEY (`itemclass_id`) REFERENCES `mst_itemclass` (`itemclass_id`);





