<?php

require_once __ROOT_DIR.'/core/sqlutil.php';



use \FGTA4\debug;

class COA_Import {
	function __construct($param) {
		$this->db = $param->db;
		$this->db_frm2 = $param->db_frm2;
		$this->args = $param->args;

		$this->import_batch_id = $param->import_batch_id;
		$this->username = $param->username;

	}


	function setup() {
		debug::log('setup coa dulu');

		$this->db->query('delete from xmp_coa');
		$this->db->query('delete from xmp_coagroup');
		$this->db->query('delete from xmp_coatype');

		try {
			$this->import_coatype();
			$this->import_coagroup();
			$this->import_coa();
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	function import_coatype() {
		try {

			$this->db_frm2->query("
				select 
				DISTINCT 
				(select top 1 acc_id from master_reportformatplain where report=A.report and h1=A.h1 and h2=A.h2 and reportsection = A.reportsection) as id,
				(A.report + '-' + A.reportsection + '-' + A.h1 + '-' + A.h2) as combo,
				A.h2 as coatype_name,
				A.h1 as coatype_group,
				A.reportsection, 
				A.report
				into #temp1
				from master_reportformatplain A
				order by id;			
			");			
			
			$sql = " select * from #temp1 ";

			$stmt = $this->db_frm2->prepare($sql);
			$stmt->execute();
			$rows = $stmt->fetchall(PDO::FETCH_ASSOC);
			foreach ($rows as $row) {
				$coatype = (object)[
					'coatype_id' => 'T'.$row['id'],
					'coatype_name' => $row['coatype_name'],
					'coatype_group' => $row['coatype_group'],
					'reportsection' => $row['reportsection']=='LEFT'?-1:1,
					'report' => $row['report']=='NERACA' ? 'NR' : 'LR',
					'combo' => $row['combo']
				];

				
				$cmd = \FGTA4\utils\SqlUtility::CreateSQLInsert('xmp_coatype', $coatype);
				$stmt = $this->db->prepare($cmd->sql);	
				$stmt->execute($cmd->params);				
			}

		} catch (\Exception $ex) {
			throw $ex;
		}	
	}


	function import_coagroup() {
		try {

			$this->db_frm2->query("
				select 
				DISTINCT 
				(select top 1 acc_id from master_reportformatplain where report=B.report and h1=B.h1 and h2=B.h2 and h3 = B.h3) as id,
				(B.report + '-' + B.reportsection + '-' + B.h1 + '-' + B.h2 + '-' + B.h3) as combo,
				B.h3 as coagroup_name,
				B.h2 as coatype_name,
				B.h1 as coatype_group,
				B.reportsection, 
				'' as report_id,
				B.report,
				(select top 1 #temp1.id from #temp1 where combo=(B.report + '-' + B.reportsection + '-' + B.h1 + '-' + B.h2)) as coatype_id
				into #temp2
				from master_reportformatplain B
				order by id 
			");

			$sql = " select * from #temp2 ";

			$stmt = $this->db_frm2->prepare($sql);
			$stmt->execute();
			$rows = $stmt->fetchall(PDO::FETCH_ASSOC);
			foreach ($rows as $row) {
				$coatype = (object)[
					'coagroup_id' => 'G'.$row['id'],
					'coagroup_name' => $row['coagroup_name'],
					'coatype_id' => $row['coatype_id'],
					'combo' => $row['combo']
				];

				$cmd = \FGTA4\utils\SqlUtility::CreateSQLInsert('xmp_coagroup', $coatype);
				$stmt = $this->db->prepare($cmd->sql);	
				$stmt->execute($cmd->params);				
			}


		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	function import_coa() {
		try {
			$this->db_frm2->query("
				select acc_name
				into #temp3
				from master_acc
				group by acc_name
				having count(*)>1
			");



			$sql = "
				select 
				C.acc_id as coa_id, 
				(case when C.acc_name in (select acc_name from #temp3) then C.acc_name + ' (' + C.acc_id + ')' else C.acc_name end) as coa_name,
				(select id from #temp2 where combo = (D.report + '-' + D.reportsection + '-' + D.h1 + '-' + D.h2 + '-' + D.h3)) as coagroup_id,
				(select id from #temp1 where combo = (D.report + '-' + D.reportsection + '-' + D.h1 + '-' + D.h2)) as coatype_id,
				C.acc_isdisabled as coa_isdisabled
				from 
				master_acc C inner join master_reportformatplain D on D.acc_id = C.acc_id
				order by C.acc_id				
			";
			

			$stmt = $this->db_frm2->prepare($sql);
			$stmt->execute();
			$rows = $stmt->fetchall(PDO::FETCH_ASSOC);
			foreach ($rows as $row) {
				$coatype = (object)[
					'coa_id' => 'C'.$row['coa_id'],
					'coa_name' => $row['coa_name'],
					'coa_isdisabled' => $row['coa_isdisabled'],
					'coatype_id' => 'T' . $row['coatype_id'],
					'coagroup_id' => 'G' . $row['coagroup_id']
				];

				$cmd = \FGTA4\utils\SqlUtility::CreateSQLInsert('xmp_coa', $coatype);
				$stmt = $this->db->prepare($cmd->sql);	
				$stmt->execute($cmd->params);				
			}


			$this->db_frm2->query("
				drop table #temp1;
				drop table #temp2;
				drop table #temp3;
			");

		} catch (\Exception $ex) {
			throw $ex;
		}
	}



	function inject_master() {
		try {
			$this->master_coatype();
			$this->master_coagroup();
			$this->master_coa();
		} catch (\Exception $ex) {
			throw $ex;
		}
	}



	function master_coatype() {
		try {
			debug::log('Setup COA Type');
			$sql_update = "
				INSERT INTO mst_coatype 
					(coatype_id, coatype_name, coatype_group, coatype_order, coareport_id, _createby) 
				VALUES 
					(:coatype_id, :coatype_name, :coatype_group,  :coatype_order, :coareport_id, :_createby) 
				ON DUPLICATE KEY UPDATE 
				coatype_name=:coatype_name
			";




			$stmt_update = $this->db->prepare($sql_update);	



			$sql = "
				select 
				distinct A.coatype_id, A.coatype_name, A.coatype_group, A.reportsection as coatype_order, A.report as coareport_id  
				from 
				xmp_coatype A
			";
			$stmt = $this->db->prepare($sql);	
			$stmt->execute();
			$rows = $stmt->fetchall(PDO::FETCH_ASSOC);
			foreach ($rows as $row) {
				debug::log($row['coatype_id'] . ' ' . $row['coatype_name'] . ' ... ');
				$stmt_update->execute([
					':coatype_id'=> $row['coatype_id'],
					':coatype_name' => $row['coatype_name'] . ' (TFI)',
					':coatype_group' => $row['coatype_group'],
					':coatype_order' => $row['coatype_order'],
					':coareport_id' => $row['coareport_id'],
					':_createby' => USERNAME
				]);
				// debug::log('ok');
			}
			

		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	function master_coagroup() {
		try {
			debug::log('Setup COA Group');
			$sql_update = "
				INSERT INTO mst_coagroup 
					(coagroup_id, coagroup_name, coagroup_descr,  _createby) 
				VALUES 
					(:coagroup_id, :coagroup_name, :coagroup_name,:_createby) 
				ON DUPLICATE KEY UPDATE 
					coagroup_name=:coagroup_name,
					coagroup_descr=:coagroup_name

			";

			$stmt_update = $this->db->prepare($sql_update);	
			$sql = "
				select 
				distinct A.coagroup_id, A.coagroup_name  
				from 
				(xmp_coagroup A left join xmp_coa B on B.coagroup_id=A.coagroup_id)
							inner join xmp_jurnaldetil C on C.coa_id = B.coa_id 
			";
			$stmt = $this->db->prepare($sql);	
			$stmt->execute();
			$rows = $stmt->fetchall(PDO::FETCH_ASSOC);
			foreach ($rows as $row) {
				$stmt_update->execute([
					':coagroup_id'=>$row['coagroup_id'],
					':coagroup_name' => $row['coagroup_name'],
					':_createby' => USERNAME
				]);
			}
			

		} catch (\Exception $ex) {
			throw $ex;
		}	
	}


	function master_coa() {
		try {
			debug::log('Setup COA');
			$sql_update = "
				INSERT INTO mst_coa
					(coa_id, coa_name, coa_descr, coa_dk, coagroup_id, coatype_id, coamodel_id, coa_isdisabled, _createby) 
				VALUES 
					(:coa_id, :coa_name, :coa_descr, :coa_dk, :coagroup_id, :coatype_id, :coamodel_id, :coa_isdisabled, :_createby) 
				ON DUPLICATE KEY UPDATE 
					coa_name = :coa_name, 
					coa_descr = :coa_descr, 
					coa_dk = :coa_dk,
					coagroup_id = :coagroup_id, 
					coatype_id = :coatype_id, 
					coamodel_id = :coamodel_id

			";
			$stmt_update = $this->db->prepare($sql_update);	



			$sql = "
				select 
				distinct A.coa_id, A.coa_name, A.coatype_id, A.coagroup_id, A.coa_isdisabled , C.reportsection
				from 
				xmp_coa A inner join xmp_jurnaldetil B on B.coa_id = A.coa_id 
				          inner join xmp_coatype C on C.coatype_id = A.coatype_id
			";
			$stmt = $this->db->prepare($sql);	
			$stmt->execute();
			$rows = $stmt->fetchall(PDO::FETCH_ASSOC);
			$this->db->query("SET FOREIGN_KEY_CHECKS=0;");
			foreach ($rows as $row) {
				// debug::log($row['coa_id'] . ' ' . $row['coatype_id']);
				$stmt_update->execute([
					':coa_id' => $row['coa_id'], 
					':coa_name' => $row['coa_name'] . ' tfi', 
					':coa_descr' => $row['coa_name'],
					':coa_dk' => $row['reportsection']=='RIGHT' ? -1 : 1, 
					':coagroup_id' => $row['coagroup_id'], 
					':coatype_id' => $row['coatype_id'], 
					':coamodel_id' => 'GN', 
					':coa_isdisabled' => $row['coa_isdisabled'],
					':_createby' => USERNAME
				]);
			}
			$this->db->query("SET FOREIGN_KEY_CHECKS=1;");
			

		} catch (\Exception $ex) {
			throw $ex;
		}	
	}	

}