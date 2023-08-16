use kalistadb;


DROP PROCEDURE IF EXISTS coa_listing;
DELIMITER //
CREATE PROCEDURE coa_listing()
BEGIN
	
	declare p_cacheid bigint unsigned;
	declare p_cacheexp datetime;
	

	set p_cacheid = UUID_SHORT();
	set p_cacheexp = NOW() + interval 10 minute ;


	DROP TABLE IF EXISTS TEMP_COA_RESULT;
	CREATE TEMPORARY TABLE -- IF NOT EXISTS 
	 	TEMP_COA_RESULT ( INDEX(coa_path) ) 
		ENGINE=MyISAM 
	as (
	
		select
		'C' as part,  -- for partition
		A.coagroup_id as coa_id, 
		A.coagroup_name as coa_name,
		A.coagroup_parent as coa_parent, 
		1 as coa_isparent,
		A.coagroup_path as coa_path,
		A.coagroup_level as coa_level 
		from mst_coagroup A
		
		union
		
		select
		'C' as part,  -- for partition
		A.coa_id,
		A.coa_name,
		A.coagroup_id as coa_parent,
		0 as coa_isparent,
		B.coagroup_path as coa_path,
		B.coagroup_level + 1 as coa_level 
		from 
		mst_coa A inner join mst_coagroup B on B.coagroup_id = A.coagroup_id
		
	);


	delete from xhc_coa where cacheexp < NOW();
	
	insert into xhc_coa
	(cacheexp, cacheid, cacherownum, coa_id, coa_name, coa_parent, coa_isparent, coa_path, coa_level)
	select
	p_cacheexp as cacheexp,
	p_cacheid as cacheid,
	ROW_NUMBER() over (partition by part order by coa_path, coa_level) as cacherownum,
	coa_id, coa_name, coa_parent, coa_isparent, coa_path, coa_level
	from
	TEMP_COA_RESULT order by coa_path, coa_level;

	select
	'xhc_coa' as tablename,
	@cacheid := cast(p_cacheid as char) as cacheid,
	(select count(cacheid) from xhc_coa where cacheid = @cacheid) as rowcount,
	p_cacheexp as cacheexp;


	DROP TABLE IF EXISTS TEMP_COA_RESULT;
	
END //
DELIMITER ;


