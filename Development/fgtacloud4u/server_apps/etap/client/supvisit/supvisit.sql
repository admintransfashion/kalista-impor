CREATE TABLE `trn_supvisit` (
	`supvisit_id` varchar(14) NOT NULL , 
	`supvisit_descr` varchar(90) NOT NULL , 
	`supvisit_datestart` date NOT NULL , 
	`supvisit_dateend` date NOT NULL , 
	`supvisit_iscommit` tinyint(1) NOT NULL DEFAULT 0, 
	`land_id` varchar(30) NOT NULL , 
	`_createby` varchar(13) NOT NULL , 
	`_createdate` datetime NOT NULL DEFAULT current_timestamp(), 
	`_modifyby` varchar(13)  , 
	`_modifydate` datetime  , 
	PRIMARY KEY (`supvisit_id`)
) 
ENGINE=InnoDB
COMMENT='Master Site Visit';

ALTER TABLE `trn_supvisit` ADD KEY `land_id` (`land_id`);

ALTER TABLE `trn_supvisit` ADD CONSTRAINT `fk_trn_supvisit_mst_land` FOREIGN KEY (`land_id`) REFERENCES `mst_land` (`land_id`);





CREATE TABLE `trn_supvisitsite` (
	`supvisitsite_id` varchar(14) NOT NULL , 
	`site_id` varchar(30) NOT NULL , 
	`supvisit_id` varchar(14) NOT NULL , 
	`_createby` varchar(13) NOT NULL , 
	`_createdate` datetime NOT NULL DEFAULT current_timestamp(), 
	`_modifyby` varchar(13)  , 
	`_modifydate` datetime  , 
	UNIQUE KEY `user_id` (`supvisit_id`, `site_id`),
	PRIMARY KEY (`supvisitsite_id`)
) 
ENGINE=InnoDB
COMMENT='Site yang dikunjungi user';

ALTER TABLE `trn_supvisitsite` ADD KEY `site_id` (`site_id`);
ALTER TABLE `trn_supvisitsite` ADD KEY `supvisit_id` (`supvisit_id`);

ALTER TABLE `trn_supvisitsite` ADD CONSTRAINT `fk_trn_supvisitsite_mst_site` FOREIGN KEY (`site_id`) REFERENCES `mst_site` (`site_id`);
ALTER TABLE `trn_supvisitsite` ADD CONSTRAINT `fk_trn_supvisitsite_trn_supvisit` FOREIGN KEY (`supvisit_id`) REFERENCES `trn_supvisit` (`supvisit_id`);





CREATE TABLE `trn_supvisituser` (
	`supvisituser_id` varchar(14) NOT NULL , 
	`user_id` varchar(14) NOT NULL , 
	`supvisit_id` varchar(14) NOT NULL , 
	`_createby` varchar(13) NOT NULL , 
	`_createdate` datetime NOT NULL DEFAULT current_timestamp(), 
	`_modifyby` varchar(13)  , 
	`_modifydate` datetime  , 
	UNIQUE KEY `user_id` (`supvisit_id`, `user_id`),
	PRIMARY KEY (`supvisituser_id`)
) 
ENGINE=InnoDB
COMMENT='User yang mengunjungi site';

ALTER TABLE `trn_supvisituser` ADD KEY `user_id` (`user_id`);
ALTER TABLE `trn_supvisituser` ADD KEY `supvisit_id` (`supvisit_id`);

ALTER TABLE `trn_supvisituser` ADD CONSTRAINT `fk_trn_supvisituser_fgt_user` FOREIGN KEY (`user_id`) REFERENCES `fgt_user` (`user_id`);
ALTER TABLE `trn_supvisituser` ADD CONSTRAINT `fk_trn_supvisituser_trn_supvisit` FOREIGN KEY (`supvisit_id`) REFERENCES `trn_supvisit` (`supvisit_id`);





