<?php

require_once __ROOT_DIR.'/core/sqlutil.php';
require_once __DIR__.'/gl.import.coa.php';


use \FGTA4\debug;

const USERNAME =  '5effbb0a0f7d1';

class GL_Import {
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



		// Import COA
		(new COA_Import($this->param))->setup();


		// Import Jurnal

		$param = $this->get_parameter();
		foreach ($param->month as $month) {
			$this->db->query('delete from xmp_jurnaldetil');
			$this->db->query('delete from xmp_jurnal');

			$a_date = mktime(0, 0, 0, $month, 1, $param->year);
			$max_date = (int)date('t', $a_date);
			for ($day=1; $day<=$max_date; $day++) {
				debug::log(color::yellow . 'Importing data tahun ' . $param->year . ' bulan ' . $month . ' tanggal ' . $day . color::reset);
				$this->importdata($param->year, $month, $day);
				
				// debug::log(color::red . 'Percobaan tanggal satu dulu, proses di '. color::bright . 'break' . color::reset . color::red . ' di iterasi pertama' . color::reset);
				// break;
			}

			$this->master_data_inject();
			$this->transaksi_data_inject();
		}



		$this->saldo_data_inject();

	}

	function get_parameter() {
		$obj = new \stdClass;
		try {
			if (!property_exists($this->args->params, '--year')) throw new \Exception('perlu parameter --year untuk eksekusi perintah ini');
			$obj->year =$this->args->params->{'--year'};
			$obj->month = [1,2,3,4,5,6,7,8,9,10,11,12];
			if  (property_exists($this->args->params, '--month')) {
				if (!checkdate((int)$this->args->params->{'--month'}, 1, $obj->year)) {
					throw new \Exception('pameter --month salah');
				} else {
					$obj->month = [(int)$this->args->params->{'--month'}];
				}
			}
			return $obj;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	function importdata($year, $month, $day) {
		
		$sql_detil = "
			select 
			A.*,
			(select region_name from master_region where region_id = A.region_id) as region_name,
			(select acc_name from master_acc where acc_id = A.acc_id) as acc_name
			from transaksi_jurnaldetil A
			where A.jurnal_id = :jurnal_id
		";
		$stmt_detil = $this->db_frm2->prepare($sql_detil);

		$date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
		$sql_head = "
			select 
			A.*,
			(select periode_name from master_periode where periode_id=A.periode_id) as periode_name,
			(select periode_datestart from master_periode where periode_id=A.periode_id) as periode_datestart,
			(select periode_dateend from master_periode where periode_id=A.periode_id) as periode_dateend
			from transaksi_jurnal A 
			where A.jurnal_isposted=1 and convert(varchar(10), A.jurnal_bookdate, 120) = :date
		";
		$stmt_head = $this->db_frm2->prepare($sql_head, [PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL]);
		$stmt_head->execute([':date'=>$date]);
		$rowshead = $stmt_head->fetchall(PDO::FETCH_ASSOC);
		foreach ($rowshead as $rowhead) {
			$jurnal_id = $rowhead['jurnal_id'];
			debug::log($jurnal_id . '... ', ['nonewline'=>true]);

			$header = (object)[
				'batch_id' => $this->import_batch_id,
				'jurnal_id' => $jurnal_id,
				'jurnal_ref' => '',
				'jurnal_ispost' => 1,
				'jurnal_date' => $rowhead['jurnal_bookdate'],
				'jurnal_descr' => str_replace(["'", '"'], ['', ''], substr($rowhead['jurnal_descr'], 0, 80)),
				'periodemo_id' => $year . str_pad($month, 2, 0, STR_PAD_LEFT),
				'periodemo_name' => substr($rowhead['periode_name'], 0, 30),
				'periodemo_dtstart' => $rowhead['periode_datestart'],
				'periodemo_dtend' => $rowhead['periode_dateend'],
				'_createby' => USERNAME,
				'_createdate' => date("Y-m-d H:i:s")
			];

			$detil = [];
			$stmt_detil->execute([':jurnal_id'=>$jurnal_id]);
			$rowsdetil = $stmt_detil->fetchall(PDO::FETCH_ASSOC);
			foreach ($rowsdetil as $rowdetil) {
				$detil[] = (object)[
					'batch_id' => $this->import_batch_id,
					'jurnaldetil_id' => uniqid() ,
					'jurnaldetil_descr' => str_replace(["'", '"'], ['', ''], substr($rowdetil['jurnaldetil_descr'], 0, 80)),
					'jurnaldetil_valfrg' => $rowdetil['jurnaldetil_idr'],
					'jurnaldetil_valfrgrate' => 1,
					'jurnaldetil_validr' => $rowdetil['jurnaldetil_idr'],
					'coa_id' => 'C' . $rowdetil['acc_id'],
					'coa_name' => substr($rowdetil['acc_name'], 0, 200),
					'dept_id' => $rowdetil['region_id'],
					'dept_name' => substr($rowdetil['region_name'], 0, 30),
					'curr_id' => 'IDR',
					'curr_name' => 'IDR',
					'jurnal_id' => $jurnal_id,
					'_createby' => USERNAME,
					'_createdate' => date("Y-m-d H:i:s")				
				];
			}

			$this->save_to_temp((object)['header'=>$header, 'detil'=>$detil]);
			debug::log('ok');
		}

		


	}

	function save_to_temp($jurnal) {
		try {

			$cmd = \FGTA4\utils\SqlUtility::CreateSQLInsert('xmp_jurnal', $jurnal->header);
			$stmt = $this->db->prepare($cmd->sql);	
			$stmt->execute($cmd->params);	

			foreach ($jurnal->detil as $detil) {
				$cmd = \FGTA4\utils\SqlUtility::CreateSQLInsert('xmp_jurnaldetil', $detil);
				$stmt = $this->db->prepare($cmd->sql);	
				$stmt->execute($cmd->params);	
			}	

		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	function master_data_inject() {
		try {
			$this->master_periode();
			$this->master_dept();
			(new COA_Import($this->param))->inject_master();
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	function master_periode() {
		debug::log('Setup Periode');
		$sql_update = "
			INSERT INTO mst_periodemo 
				(periodemo_id, periodemo_name, periodemo_year, periodemo_month, periodemo_dtstart, periodemo_dtend, _createby) 
			VALUES 
				(:periodemo_id, :periodemo_name, :periodemo_year, periodemo_month, :periodemo_dtstart, :periodemo_dtend, :_createby) 
			ON DUPLICATE KEY UPDATE 
				periodemo_name=:periodemo_name, 
				periodemo_dtstart=:periodemo_dtstart,
				periodemo_dtend=:periodemo_dtend
		";
		$stmt_update = $this->db->prepare($sql_update);	


		try {
			$sql = '
				select distinct periodemo_id, periodemo_name, 
				YEAR(periodemo_dtstart) as periodemo_year, 
				MONTH(periodemo_dtstart) as periodemo_month, 
				periodemo_dtstart, periodemo_dtend  from xmp_jurnal where batch_id = :batch_id';
			$stmt = $this->db->prepare($sql);	
			$stmt->execute([':batch_id'=>$this->import_batch_id]);
			$rows = $stmt->fetchall(PDO::FETCH_ASSOC);
			foreach ($rows as $row) {
				$stmt_update->execute([
					':periodemo_id' => $row['periodemo_id'],
					':periodemo_name' => $row['periodemo_name'],
					':periodemo_year' => $row['periodemo_year'],
					':periodemo_month' => $row['periodemo_month'],
					':periodemo_dtstart' => $row['periodemo_dtstart'],
					':periodemo_dtend' => $row['periodemo_dtend'],
					':_createby' => USERNAME
				]);
			}
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	function master_dept() {
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
			$sql = 'select distinct dept_id, dept_name  from xmp_jurnaldetil where batch_id = :batch_id';
			$stmt = $this->db->prepare($sql);	
			$stmt->execute([':batch_id'=>$this->import_batch_id]);
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




	

	function transaksi_data_inject() {
		try {
			$this->transaksi_jurnal();
			$this->transaksi_jurnaldetil();
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	function transaksi_jurnal() {
		try {
			debug::log('Inject Transaksi Jurnal');


			$stmt_clear_jurnaldetil = $this->db->prepare("DELETE FROM trn_jurnaldetil WHERE jurnal_id = :jurnal_id");
			$stmt_clear_jurnal = $this->db->prepare("DELETE FROM trn_jurnal WHERE jurnal_id = :jurnal_id");


			$sql_update = "
				INSERT INTO trn_jurnal
					(jurnal_id, jurnal_date, jurnal_descr, jurnal_ispost, periodemo_id, _createby) 
				VALUES 
				(:jurnal_id, :jurnal_date, :jurnal_descr, :jurnal_ispost, :periodemo_id, :_createby); 
			";

			$stmt_update = $this->db->prepare($sql_update);	
			

			$sql = 'select jurnal_id, jurnal_date, jurnal_descr, jurnal_ispost, periodemo_id, _createby from xmp_jurnal where batch_id = :batch_id';
			$stmt = $this->db->prepare($sql);	
			$stmt->execute([':batch_id'=>$this->import_batch_id]);
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach ($rows as $row) {
				$stmt_clear_jurnaldetil->execute([':jurnal_id'=>$row['jurnal_id']]);
				$stmt_clear_jurnal->execute([':jurnal_id'=>$row['jurnal_id']]);
				$stmt_update->execute([
					':jurnal_id'=>$row['jurnal_id'],
					':jurnal_date' => $row['jurnal_date'],
					':jurnal_descr' => $row['jurnal_descr'],
					':jurnal_ispost' => $row['jurnal_ispost'],
					':periodemo_id' => $row['periodemo_id'],
					':_createby' => USERNAME
				]);
			}
			
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	function transaksi_jurnaldetil() {
		$process = '';
		try {
			debug::log('Inject Transaksi Jurnaldetil');

			$sql_update = "
				INSERT INTO trn_jurnaldetil
					(jurnaldetil_id, jurnaldetil_descr, dept_id, coa_id, curr_id, jurnaldetil_valfrg, jurnaldetil_valfrgrate, jurnaldetil_validr, jurnal_id, _createby) 
				VALUES 
					(:jurnaldetil_id, :jurnaldetil_descr, :dept_id, :coa_id, :curr_id, :jurnaldetil_valfrg, :jurnaldetil_valfrgrate, :jurnaldetil_validr, :jurnal_id, :_createby); 
			";

			$stmt_update = $this->db->prepare($sql_update);	
			$sql = '
				select jurnaldetil_id, jurnaldetil_descr, dept_id, coa_id, curr_id, jurnaldetil_valfrg, jurnaldetil_valfrgrate, jurnaldetil_validr, jurnal_id, _createby 
				from xmp_jurnaldetil 
				where batch_id = :batch_id';
			$stmt = $this->db->prepare($sql);	
			$stmt->execute([':batch_id'=>$this->import_batch_id]);
			$rows = $stmt->fetchall(PDO::FETCH_ASSOC);
			foreach ($rows as $row) {
				$process = $row['jurnal_id'] . " coa: " . $row['coa_id'];
				$stmt_update->execute([
					':jurnaldetil_id'=>$row['jurnaldetil_id'],
					':jurnaldetil_descr' => $row['jurnaldetil_descr'],
					':dept_id' => $row['dept_id'],
					':coa_id' => $row['coa_id'],
					':curr_id' => $row['curr_id'],
					':jurnaldetil_valfrg' => $row['jurnaldetil_valfrg'],
					':jurnaldetil_valfrgrate' => $row['jurnaldetil_valfrgrate'],
					':jurnaldetil_validr' => $row['jurnaldetil_validr'],
					':jurnal_id' => $row['jurnal_id'],
					':_createby' => USERNAME
				]);
			}
			
		} catch (\Exception $ex) {
			debug::log($process);
			throw $ex;
		}
	}


}