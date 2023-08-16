CREATE TABLE `mst_channel` (
	`channel_id` varchar(10) NOT NULL , 
	`channel_name` varchar(30)  , 
	`channel_descr` varchar(90)  , 
	`_createby` varchar(13) NOT NULL , 
	`_createdate` datetime NOT NULL DEFAULT current_timestamp(), 
	`_modifyby` varchar(13)  , 
	`_modifydate` datetime  , 
	UNIQUE KEY `channel_name` (`channel_name`),
	PRIMARY KEY (`channel_id`)
) 
ENGINE=InnoDB
COMMENT='Daftar Channel Media';




INSERT INTO mst_channel (`channel_id`, `channel_name`, `_createby`, `_createdate`) VALUES ('MAIN', 'MAIN', 'root', NOW());



