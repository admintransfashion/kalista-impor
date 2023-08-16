CREATE TABLE `mst_mediaprog` (
	`mediaprog_id` varchar(14) NOT NULL , 
	`mediaprog_name` varchar(90) NOT NULL , 
	`mediaprog_descr` varchar(90)  , 
	`mediaprog_season` int(4) NOT NULL DEFAULT 0, 
	`mediaprog_episode` int(4) NOT NULL DEFAULT 0, 
	`mediaprog_isdisabled` tinyint(1) NOT NULL DEFAULT 0, 
	`projbudget_id` varchar(30) NOT NULL , 
	`projbudgettask_id` varchar(14)  , 
	`dept_id` varchar(30) NOT NULL , 
	`_createby` varchar(13) NOT NULL , 
	`_createdate` datetime NOT NULL DEFAULT current_timestamp(), 
	`_modifyby` varchar(13)  , 
	`_modifydate` datetime  , 
	UNIQUE KEY `mediaprog_name` (`mediaprog_name`),
	PRIMARY KEY (`mediaprog_id`)
) 
ENGINE=InnoDB
COMMENT='Daftar Media Programme';

ALTER TABLE `mst_mediaprog` ADD KEY `projbudget_id` (`projbudget_id`);
ALTER TABLE `mst_mediaprog` ADD KEY `projbudgettask_id` (`projbudgettask_id`);
ALTER TABLE `mst_mediaprog` ADD KEY `dept_id` (`dept_id`);

ALTER TABLE `mst_mediaprog` ADD CONSTRAINT `fk_mst_mediaprog_mst_projbudget` FOREIGN KEY (`projbudget_id`) REFERENCES `mst_projbudget` (`projbudget_id`);
ALTER TABLE `mst_mediaprog` ADD CONSTRAINT `fk_mst_mediaprog_mst_projbudgettask` FOREIGN KEY (`projbudgettask_id`) REFERENCES `mst_projbudgettask` (`projbudgettask_id`);
ALTER TABLE `mst_mediaprog` ADD CONSTRAINT `fk_mst_mediaprog_mst_dept` FOREIGN KEY (`dept_id`) REFERENCES `mst_dept` (`dept_id`);





