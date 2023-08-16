<?php

require_once __ROOT_DIR.'/core/sqlutil.php';
require_once __ROOT_DIR.'/core/debug.php';	

use \FGTA4\debug;


const USERNAME =  '5effbb0a0f7d1';


console::class(new class($args) extends cli {

	function __construct($args) {
		$this->args = $args;

		$logfilepath = __LOCALDB_DIR . "/output/otomasi.txt";
		// debug::disable();
		debug::start($logfilepath, "w");

		$this->debugoutput = true;
		$DB_CONFIG = DB_CONFIG[$GLOBALS['MAINDB']];
		$DB_CONFIG['param'] = DB_CONFIG_PARAM[$GLOBALS['MAINDBTYPE']];
		$this->db = new \PDO(
					$DB_CONFIG['DSN'], 
					$DB_CONFIG['user'], 
					$DB_CONFIG['pass'], 
					$DB_CONFIG['param']
		);
		$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


		debug::printtoscreen();
		debug::log('Import GL to TB');
		debug::log("connecting to FRM ...", ['nonewline'=>true]);
		$this->db_frm2 = new \PDO(
			'dblib:host=172.18.10.254;dbname=E_FRM2_BACKUP', 
			'sa', 
			'rahasia',
			null
		);
		$this->db_frm2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		debug::log(color::green . "Connected" . color::reset);
	}

	function execute() {
		try {
			$obj = $this->get_parameter();

			$classarg = new \stdClass;
			$classarg->db = $this->db; 
			$classarg->db_frm2 = $this->db_frm2;
			$classarg->args = (object)['year'=>$obj->year];
			(new GLSaldo_Import($classarg))->run();
		} catch (\Exeption $ex) {
			throw $ex;
		}		
	}

	function get_parameter() {
		$obj = new \stdClass;
		try {
			if (!property_exists($this->args->params, '--year')) throw new \Exception('perlu parameter --year untuk eksekusi perintah ini');
			$obj->year =$this->args->params->{'--year'};
			return $obj;
		} catch (\Exception $ex) {
		
			throw $ex;
		}
	}
});


class GLSaldo_Import {
	function __construct($param) {
		$this->db = $param->db;
		$this->db_frm2 = $param->db_frm2;
		$this->args = $param->args;

		$this->import_batch_id = '5fd2ebee0ab36';
		$this->param = $param;
		$this->param->import_batch_id = $this->import_batch_id;
		$this->param->username = USERNAME;
	}

	function run() {
		try {


			$months = [1,2,3,4,5,6,7,8,9,10,11,12];
			$periode_id = date('y', mktime(0,0,0,1,1,$this->args->year)) . '01';
			$periodemo_id = date('Y', mktime(0,0,0,1,1,$this->args->year)) . '01';

			$lastyear_periodemo_id = date('Y', mktime(0,0,0,1,1,((int)$this->args->year)-1)) . '12';

			foreach ($months as $month) {
				if ($month==1) {
					// $this->db->query("delete from xmp_saldo where periodemo_id = '$periodemo_id'");
					// $this->import_saldo($periode_id, $periodemo_id);
					// $this->update_dept($periodemo_id);
					// $this->update_coa($periodemo_id);
					// $this->copy_nonexist_coa();
					// $this->periode_prepare($lastyear_periodemo_id, 1);	
					// $this->inject_saldo_awaltahun($periodemo_id, $lastyear_periodemo_id);
					// $this->periode_prepare($periodemo_id, 0);	
					$this->periode_close($periodemo_id);
				} else {
					$periodemo_id = date('Ym', mktime(0,0,0,$month,1,$this->args->year));
					$this->periode_prepare($periodemo_id, 0);	
					$this->periode_close($periodemo_id);

				}
			}
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	function import_saldo($periode_id, $periodemo_id) {
		debug::log('import saldo');

		try {
			$sql = "
				select 
				A.acc_id, A.region_id,
				(select acc_name from master_acc where acc_id = A.acc_id) as acc_name,
				(select region_name from master_region where region_id = A.region_id) as region_name,
				SUM(A.jurnalsaldo_idr) as idr
				from 
				transaksi_jurnalsaldo A
				where 
				A.periode_id = :periode_id and A.region_id<>'00000'
				group by A.acc_id, A.region_id 
			";
			$stmt = $this->db_frm2->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL]);
			$stmt->execute([':periode_id'=>$periode_id]);
			$rows = $stmt->fetchall(PDO::FETCH_ASSOC);
			foreach ($rows as $row) {
				$saldo = (object)[
					'periode_id' => $periode_id,
					'periodemo_id' => $periodemo_id,
					'coa_id' => 'C' . $row['acc_id'],
					'coa_name' => $row['acc_name'],
					'dept_id' => $row['region_id'],
					'dept_name' => $row['region_name'],
					'saldo_idr' => $row['idr']
				];

				$cmd = \FGTA4\utils\SqlUtility::CreateSQLInsert('xmp_saldo', $saldo);
				$stmt = $this->db->prepare($cmd->sql);	
				$stmt->execute($cmd->params);					
			}

		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	function update_dept($periodemo_id) {
		try {
			debug::log('Setup Departemen');
			$sql_update = "
				INSERT INTO mst_dept 
					(dept_id, dept_name, deptgroup_id, depttype_id, deptmodel_id, auth_id, _createby) 
				VALUES 
					(:dept_id, :dept_name, :deptgroup_id, :depttype_id, :deptmodel_id, :auth_id, :_createby) 
				ON DUPLICATE KEY UPDATE 
					dept_name=:dept_name
			";

			$stmt_update = $this->db->prepare($sql_update);	
			$sql = 'select distinct dept_id, dept_name  from xmp_saldo where periodemo_id = :periodemo_id';
			$stmt = $this->db->prepare($sql);	
			$stmt->execute([':periodemo_id'=>$periodemo_id]);
			$rows = $stmt->fetchall(PDO::FETCH_ASSOC);
			foreach ($rows as $row) {
				$stmt_update->execute([
					':dept_id'=>$row['dept_id'],
					':dept_name' => $row['dept_name'],
					':deptgroup_id' => 'COR',
					':depttype_id' => 'COR',
					':deptmodel_id' => 'COR',
					':auth_id' => 'DIRUT',
					':_createby' => USERNAME
				]);
			}
			

		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	function update_coa($periodemo_id) {
		try {
			$this->master_coatype($periodemo_id);
			$this->master_coagroup($periodemo_id);
			$this->master_coa($periodemo_id);
		} catch (\Exception $ex) {
			throw $ex;
		}
	}



	function master_coatype($periodemo_id) {
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
				distinct C.coatype_id, C.coatype_name, C.coatype_group, C.reportsection as coatype_order, C.report as coareport_id  
				from xmp_saldo A inner join xmp_coa B on B.coa_id = A.coa_id 
								inner join xmp_coatype C on C.coatype_id = B.coatype_id   
				WHERE 
				A.periodemo_id = :periodemo_id
			";
			$stmt = $this->db->prepare($sql);	
			$stmt->execute([':periodemo_id'=>$periodemo_id]);
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


	function master_coagroup($periodemo_id) {
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
				distinct C.coagroup_id, C.coagroup_name
				from xmp_saldo A inner join xmp_coa B on B.coa_id = A.coa_id 
								inner join xmp_coagroup C on C.coagroup_id = B.coagroup_id   
				WHERE 
				A.periodemo_id = :periodemo_id
			";
			$stmt = $this->db->prepare($sql);	
			$stmt->execute([':periodemo_id'=>$periodemo_id]);
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



	function master_coa($periodemo_id) {
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
				distinct B.coa_id, B.coa_name, B.coatype_id, B.coagroup_id, B.coa_isdisabled , C.reportsection
				from xmp_saldo A inner join xmp_coa B on B.coa_id = A.coa_id 
								inner join xmp_coatype C on C.coatype_id = B.coatype_id
				WHERE 
				A.periodemo_id = :periodemo_id
			
			";
			$stmt = $this->db->prepare($sql);	
			$stmt->execute([':periodemo_id'=>$periodemo_id]);
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

	function copy_nonexist_coa() {
		try {
			$copies = [
				(object)['source'=>'C1020210', 'target'=>'C1020280', 'targetname' => 'Cash On Hand-JPY']
			];
			
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


			$stmt_get = $this->db->prepare('select * from mst_coa where coa_id = :coa_id');
			foreach ($copies as $copy) {
				
				$stmt_get->execute([':coa_id'=>$copy->source]);
				$rows = $stmt_get->fetchall(PDO::FETCH_ASSOC);

				foreach ($rows as $row) {
					debug::log("Copy Coa from " . $copy->source . " to " . $copy->target);
					$stmt_update->execute([
						':coa_id' => $copy->target, 
						':coa_name' => $copy->targetname , 
						':coa_descr' => $row['coa_name'],
						':coa_dk' => $row['coa_dk'], 
						':coagroup_id' => $row['coagroup_id'], 
						':coatype_id' => $row['coatype_id'], 
						':coamodel_id' => $row['coamodel_id'], 
						':coa_isdisabled' => 0,
						':_createby' => USERNAME
					]);
				}
			}
		} catch (\Exception $ex) {
			throw $ex;
		}	
	}


	function inject_saldo_awaltahun($periodemo_id, $lastyear_periodemo_id) {
		try {


			debug::log("Inject Saldo awal tahun $periodemo_id");

			$stmt_clear_saldo = $this->db->prepare("DELETE FROM trn_jurnalsaldo WHERE periodemo_id = :periodemo_id");
			$stmt_clear_saldo->execute([':periodemo_id'=>$lastyear_periodemo_id]);

			$sql_update = "
				INSERT INTO trn_jurnalsaldo
					(jurnalsaldo_id, dept_id, coa_id, jurnalsaldo_awal, jurnalsaldo_mutasi, jurnalsaldo_akhir, periodemo_id, _createby) 
				VALUES 
					(:jurnalsaldo_id, :dept_id, :coa_id, :jurnalsaldo_awal, :jurnalsaldo_mutasi, :jurnalsaldo_akhir, :periodemo_id, :_createby); 
			";

			$stmt_update = $this->db->prepare($sql_update);	
			

			$sql = 'select periodemo_id, coa_id, dept_id, saldo_idr from xmp_saldo where periodemo_id = :periodemo_id';
			$stmt = $this->db->prepare($sql);	
			$stmt->execute([':periodemo_id'=>$periodemo_id]);
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach ($rows as $row) {
				debug::log($row['dept_id'] . ' ' . $row['coa_id'] . ' ' .  $row['saldo_idr']);
				$stmt_update->execute([
					':jurnalsaldo_id'=>uniqid(),
					':dept_id' => $row['dept_id'],
					':coa_id' => $row['coa_id'],
					':jurnalsaldo_awal' => 0,
					':jurnalsaldo_mutasi' => 0,
					':jurnalsaldo_akhir' => $row['saldo_idr'],
					':periodemo_id' => $lastyear_periodemo_id,
					':_createby' => USERNAME
				]);
			}

		} catch (\Exception $ex) {
			throw $ex;
		}
	}






	/**
     * CLOSING PERIODE
     */

	function periode_close($periodemo_id) {
		try {
			$closeby = USERNAME;


			debug::log(color::bright . 'closing periode ' . $periodemo_id . color::reset);
			$periodeinfo = $this->periode_getinfo($periodemo_id, 1);


			debug::log("check previous periode (". $periodeinfo->prev->periodemo_id .")... ");
			if (!$periodeinfo->prev->periodemo_isclosed) {
				debug::log('Periode sebelumnya ' . color::red . color::bright . $periodeinfo->prev->periodemo_id . color::reset . ' -> ' .  $periodeinfo->periodemo_id  .  ' belum di close!');
				throw new \Exception('Periode sebelumnya belum di close! Silakan close periode ' . $periodeinfo->prev->periodemo_id . ' terlebih dahulu');
			}

			debug::log("check next periode (". $periodeinfo->next->periodemo_id .")... ");
			if ($periodeinfo->next->periodemo_isclosed) {
				debug::log('Status Periode berikutnya (' . $periodeinfo->periodemo_id . ' -> ' . color::red . color::bright . $periodeinfo->next->periodemo_id .  color::reset  . ') masih dalam keadaan close.');
				throw new \Exception('Status Periode berikutnya masih dalam keadaan close. Silakan buka dulu periode ' . $periodeinfo->next->periodemo_id);
			}

			debug::log('cek apakah ada jurnal yang belum diposting');
			$stmt = $this->db->prepare('select jurnal_id from trn_jurnal where periodemo_id = :periodemo_id and jurnal_ispost=0');
			$stmt->execute([':periodemo_id'=>$periodemo_id]);
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if ($n=count($rows)>0) {
				debug::log("Ada $n jurnal yang belum diposting di periode " . $periodeinfo->periodemo_id . ". Periode tidak bisa di tututp.");
				throw new \Exception("Ada $n jurnal yang belum diposting di periode " . $periodeinfo->periodemo_id . ". Periode tidak bisa di tututp.");
			}

			$sql = $this->get_sqljurnalsummary();
			$stmt = $this->db->prepare($sql);
			$stmt->execute([
				':periodemo_id'=>$periodemo_id,
				':prev_periodemo_id' => $periodeinfo->prev->periodemo_id
			]);	
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$this->db->setAttribute(\PDO::ATTR_AUTOCOMMIT,0);
			$this->db->beginTransaction();



			$stmt_clear_saldo = $this->db->prepare("DELETE FROM trn_jurnalsaldo WHERE periodemo_id = :periodemo_id");
			$stmt_clear_saldo->execute([':periodemo_id'=>$periodemo_id]);

			$sql_update = "
				INSERT INTO trn_jurnalsaldo
					(jurnalsaldo_id, dept_id, coa_id, jurnalsaldo_awal, jurnalsaldo_mutasi, jurnalsaldo_akhir, periodemo_id, _createby) 
				VALUES 
					(:jurnalsaldo_id, :dept_id, :coa_id, :jurnalsaldo_awal, :jurnalsaldo_mutasi, :jurnalsaldo_akhir, :periodemo_id, :_createby); 
			";

			$stmt_update = $this->db->prepare($sql_update);	


			$sql_close = "
				update mst_periodemo 
				set 
				periodemo_isclosed = 1,
				periodemo_closeby = :periodemo_closeby,
				periodemo_closedate = :periodemo_closedate
				where
				periodemo_id = :periodemo_id
			";	
			$stmt_close = $this->db->prepare($sql_close);	



			try {
				foreach ($rows as $row) {
					$obj = [
						'jurnalsaldo_id' => uniqid(),
						'coa_id' => $row['coa_id'],
						'dept_id' => $row['dept_id'],
						'jurnalsaldo_awal' => $row['saldoawal'],
						'jurnalsaldo_mutasi' => $row['mutasi'],
						'jurnalsaldo_akhir' => $row['saldoakhir'],
						'periodemo_id' => $periodemo_id,
						'_createby' => $closeby,
					];

					$stmt_update->execute($obj);
				}

				$stmt_close->execute([
					':periodemo_id' => $periodemo_id,
					':periodemo_closeby' =>  $closeby,
					':periodemo_closedate' => date("Y-m-d H:i:s")
				]);

				if ($periodeinfo->periodemo_month == 12) {
					// closing akhir tahun
				}

				$this->db->commit();
				return true;
			} catch (\Exception $ex) {
				$this->db->rollBack();
				throw $ex;
			} finally {
				$this->db->setAttribute(\PDO::ATTR_AUTOCOMMIT,1);
			}





		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	function get_sqljurnalsummary() {
		return "

			select
			AX.coareport_id,
			AX.dept_id,
			AX.coa_id,
			SUM(AX.saldoawal) as saldoawal,
			SUM(AX.mutasi) as mutasi,
			SUM(AX.saldoawal) + SUM(AX.mutasi) as saldoakhir
			from (
				
				-- masukkan saldo awal
				select
				D.coareport_id, 
				B.dept_id,
				B.coa_id,
				SUM(jurnalsaldo_akhir) as saldoawal,
				0 as mutasi
				from 
				trn_jurnalsaldo B  left join mst_coa C on C.coa_id = B.coa_id
							left join mst_coatype D on D.coatype_id = C.coatype_id 
				where 
				B.periodemo_id = :prev_periodemo_id
				group by
				D.coareport_id, 
				B.dept_id,
				B.coa_id
				
				union all
				
				-- masukkan transaksi
				select 
				D.coareport_id, 
				B.dept_id,
				B.coa_id,
				0 as saldoawal,
				SUM(jurnaldetil_validr) as mutasi
				from 
				(trn_jurnal A inner join trn_jurnaldetil B on B.jurnal_id = A.jurnal_id) 
							left join mst_coa C on C.coa_id = B.coa_id
							left join mst_coatype D on D.coatype_id = C.coatype_id 
				where 
				A.periodemo_id = :periodemo_id
				group by
				D.coareport_id, 
				B.dept_id,
				B.coa_id
				
			) AX
			group by
			AX.coareport_id,
			AX.dept_id,
			AX.coa_id		
		
		";
	}


	function periode_getinfo($periodemo_id, $deep=0) {
		try {
			$stmt = $this->db->prepare("select * from mst_periodemo where periodemo_id = :periodemo_id");
			$stmt->execute([':periodemo_id'=>$periodemo_id]);
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if (count($rows)) {
				$row = $rows[0];
				$periode = (object)[
					'created' => true,
					'periodemo_id' => $row['periodemo_id'],
					'periodemo_name' => $row['periodemo_name'],
					'periodemo_isclosed' => $row['periodemo_isclosed'],
					'periodemo_year' => $row['periodemo_year'],
					'periodemo_month' => $row['periodemo_month'], 
					'periodemo_dtstart' => $row['periodemo_dtstart'], 
					'periodemo_dtend' => $row['periodemo_dtend']
				];

				if ($deep==0) {
					return $periode;
				} else {
					$prev_periodemo_dtend = date_create($periode->periodemo_dtstart);
					date_add($prev_periodemo_dtend, date_interval_create_from_date_string("-1 days"));
	
					$next_periodemo_dtstart = date_create($periode->periodemo_dtend);
					date_add($next_periodemo_dtstart, date_interval_create_from_date_string("1 days"));
	

					$prev_periodemo_id = $prev_periodemo_dtend->format('Ym');
					$next_periodemo_id = $next_periodemo_dtstart->format('Ym');

					// debug::log($prev_periodemo_id . ' ' . $periodemo_id . ' ' .  $next_periodemo_id);

					$periode->prev = $this->periode_getinfo($prev_periodemo_id, 0);
					$periode->next = $this->periode_getinfo($next_periodemo_id, 0);

					return $periode;
				}


			} else {
				return (object)[
					'created' => false,
					'periodemo_isclosed' => 0
				];
			}
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	function periode_prepare($periodemo_id, $periodemo_isclosed) {
		try {
			debug::log('-> get periode info ' . $periodemo_id);
			$sql_update = "
				INSERT INTO mst_periodemo 
					(periodemo_id, periodemo_name, periodemo_isclosed, periodemo_year, periodemo_month, periodemo_dtstart, periodemo_dtend, _createby) 
				VALUES 
					(:periodemo_id, :periodemo_name, :periodemo_isclosed, :periodemo_year, periodemo_month, :periodemo_dtstart, :periodemo_dtend, :_createby) 
				ON DUPLICATE KEY UPDATE 
					periodemo_dtstart=:periodemo_dtstart,
					periodemo_dtend=:periodemo_dtend,
					periodemo_isclosed = :periodemo_isclosed
			";
			$stmt_update = $this->db->prepare($sql_update);	
			
			$year = (int)substr($periodemo_id, 0, 4);
			$month = (int)substr($periodemo_id, 4, 2);
			$date_start = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
			$date_end = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));

			$periodemo_name = ['JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI', 'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'][$month-1] . ' ' .  $year;

			$obj = [
				':periodemo_id' =>  $periodemo_id,
				':periodemo_name' => $periodemo_name,
				':periodemo_isclosed' => (int)$periodemo_isclosed,
				':periodemo_year' => (int)$year,
				':periodemo_month' => (int)$month,
				':periodemo_dtstart' => $date_start,
				':periodemo_dtend' => $date_end,
				':_createby' => USERNAME
			];
			$stmt_update->execute($obj);
		} catch (\Exception $ex) {
			throw $ex;
		}	
	}


}

