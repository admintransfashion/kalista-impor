CREATE TABLE `mst_mediasch` (
	`mediasch_id` varchar(14) NOT NULL , 
	`mediasch_date` date NOT NULL , 
	`mediasch_iscommit` tinyint(1) NOT NULL DEFAULT 0, 
	`_createby` varchar(13) NOT NULL , 
	`_createdate` datetime NOT NULL DEFAULT current_timestamp(), 
	`_modifyby` varchar(13)  , 
	`_modifydate` datetime  , 
	UNIQUE KEY `mediasch_date` (`mediasch_date`),
	PRIMARY KEY (`mediasch_id`)
) 
ENGINE=InnoDB
COMMENT='Daftar Schedule harian';







CREATE TABLE `mst_mediaschprog` (
	`mediaschprog_id` varchar(14) NOT NULL , 
	`mediaschprog_timestart` varchar(90) NOT NULL , 
	`mediaschprog_timeend` varchar(90) NOT NULL , 
	`mediaschprog_notes` varchar(90)  , 
	`mediaprog_id` varchar(14) NOT NULL , 
	`mediasch_id` varchar(14) NOT NULL , 
	`_createby` varchar(13) NOT NULL , 
	`_createdate` datetime NOT NULL DEFAULT current_timestamp(), 
	`_modifyby` varchar(13)  , 
	`_modifydate` datetime  , 
	PRIMARY KEY (`mediaschprog_id`)
) 
ENGINE=InnoDB
COMMENT='Daftar Schedule harian';

ALTER TABLE `mst_mediaschprog` ADD KEY `mediaprog_id` (`mediaprog_id`);
ALTER TABLE `mst_mediaschprog` ADD KEY `mediasch_id` (`mediasch_id`);

ALTER TABLE `mst_mediaschprog` ADD CONSTRAINT `fk_mst_mediaschprog_mst_mediaprog` FOREIGN KEY (`mediaprog_id`) REFERENCES `mst_mediaprog` (`mediaprog_id`);
ALTER TABLE `mst_mediaschprog` ADD CONSTRAINT `fk_mst_mediaschprog_mst_mediasch` FOREIGN KEY (`mediasch_id`) REFERENCES `mst_mediasch` (`mediasch_id`);





