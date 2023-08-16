<?php

//  php cli.php deploy/fromtb/import/jurnaltag


require_once __ROOT_DIR.'/core/webapi.php';	
require_once __ROOT_DIR.'/core/webauth.php';	

define('USERID', '5effbb0a0f7d1');

console::class(new class($args) extends clibase {

	function __construct($args) {
		$this->args = $args;
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

		$this->db_frm2 = new \PDO(
			'dblib:host=172.18.10.254;dbname=E_FRM2_BACKUP', 
			'sa', 
			'rahasia',
			null
		);
		$this->db_frm2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}





	function execute() {
		// $periodes = ['1803', '1804', '1805', '1806', '1807', '1808', '1809', '1810', '1811', '1812'];
		//$periodes = ['1801', '1802', '1803', '1804', '1805', '1806', '1807', '1808', '1809', '1810', '1811', '1812'];
		//$periodes = ['1801', '1802', '1803', '1804', '1805', '1806', '1807', '1808', '1809', '1810', '1811', '1812'];


		$stmt = $this->db_frm2->prepare("select * from transaksi_jurnal where periode_id = :periode_id");
		$stmtdet = $this->db_frm2->prepare("select * from transaksi_jurnaldetil where jurnal_id = :jurnal_id");
		$stmtupd = $this->db_frm2->prepare("update transaksi_jurnaldetil set tag=:uniqid where jurnal_id=:jurnal_id and jurnaldetil_line=:jurnaldetil_line");


		$stmtinshead = $this->db->prepare("
			insert into trn_jurnal
			(jurnal_id, jurnal_date, jurnal_datedue, jurnal_descr, periodemo_id, curr_id, jurnalsource_id, jurnaltype_id, jurnal_ispost, _createby)
			values
			(:jurnal_id, :jurnal_date, :jurnal_datedue, :jurnal_descr, :periodemo_id, :curr_id, :jurnalsource_id, :jurnaltype_id, 1, :_createby)
		");


		$stmtinsdet = $this->db->prepare("
			insert into trn_jurnaldetil
			(jurnaldetil_id, jurnaldetil_descr, coa_id, dept_id, partner_id, curr_id, jurnaldetil_valfrg, jurnaldetil_valfrgrate, jurnaldetil_validr, jurnal_id, tbref, tbrefline, tbperiode_id, jurnaldetil_id_ref, _createby)
			values
			(:jurnaldetil_id, :jurnaldetil_descr, :coa_id, :dept_id, :partner_id, :curr_id, :jurnaldetil_valfrg, :jurnaldetil_valfrgrate, :jurnaldetil_validr, :jurnal_id, :tbref, :tbrefline, :tbperiode_id, :jurnaldetil_id_ref, :_createby)
		");


		$stmtupdateref = $this->db->prepare("
			update trn_jurnaldetil
			set
			jurnaldetil_id_ref = :jurnaldetil_id_ref
			where
			jurnaldetil_id = :jurnaldetil_id
		");

		try {

			
			// $this->db->query("SET @@session.triggers = OFF;");


			foreach ($periodes as $periode_id) {

				$periodeinfo = $this->getPeriode($periode_id);
				
				$periodemo_id = $periodeinfo[0];
				$periodemo_next = $periodeinfo[1];
				
				$sqld = "delete from trn_jurnaldetil where tbperiode_id = '$periodemo_id'";
				$sqlh = "delete from trn_jurnal where periodemo_id = '$periodemo_id'";
				$sqls = "delete from trn_jurnalsaldo where periodemo_id = '$periodemo_next'";
				$sqlp = "update mst_periodemo set periodemo_isclosed=0, periodemo_closeby=null, periodemo_closedate=null where periodemo_id ='$periodemo_id'; ";
				$sqln = "update mst_periodemo set periodemo_isclosed=0, periodemo_closeby=null, periodemo_closedate=null where periodemo_id ='$periodemo_next'; ";


				$this->db->query("SET @jurnal_skip_trigger = 1;");
				$this->db->query("SET FOREIGN_KEY_CHECKS=0;");
				$this->db->query($sqld);
				$this->db->query($sqlh);
				$this->db->query($sqls);
				$this->db->query($sqlp);
				$this->db->query($sqln);
				$this->db->query("SET FOREIGN_KEY_CHECKS=1;");


				$stmt->execute([':periode_id' => $periode_id]);
				$rows = $stmt->fetchall(PDO::FETCH_ASSOC);
				foreach ($rows as $row) {
					$jurnal_id = $row['jurnal_id'];
					$row['periodemo_id'] = $periodemo_id;

					$this->InsertJurnalH($row, $stmtinshead);				
					$stmtdet->execute([':jurnal_id' => $jurnal_id]);
					$rowsdet = $stmtdet->fetchall(PDO::FETCH_ASSOC);
					foreach ($rowsdet as $rwdet) {
						$jurnaldetil_line = $rwdet['jurnaldetil_line'];
						echo "inserting {$jurnal_id} {$jurnaldetil_line}\r\n";
						$this->InsertJurnalD($rwdet, $periodemo_id, $stmtinsdet);
					}
				}


				// update ref
				foreach ($rows as $row) {
					$jurnal_id = $row['jurnal_id'];
					$stmtdet->execute([':jurnal_id' => $jurnal_id]);
					$rowsdet = $stmtdet->fetchall(PDO::FETCH_ASSOC);
					foreach ($rowsdet as $rwdet) {
						$jurnaldetil_line = $rwdet['jurnaldetil_line'];
						echo ".";
						$this->UpdateRefJurnalD($rwdet, $periodemo_id, $stmtupdateref);
					}
				}
				

				// close periode
				echo "\r\nClosing periode {$periodemo_id} ..... \r\n";
				$this->db->query("call periodemo_closing('$periodemo_id', '5effbb0a0f7d1');");
				echo "\r\n\r\n================================\r\nPeriode $periodemo_id berhasil ditutup.\r\n================================\r\n\r\n";

			}

			
			// print_r($rwdet);

			die("\r\n\r\n");
		} catch (\Exception $ex) {

			die("\r\n\r\nERROR" . $ex->getMessage() . "\r\n\r\n");	
		}

		
	}


	function InsertJurnalH($data, $stmtinshead) {
		try {
			$stmtinshead->execute([
				':jurnal_id' => $data['jurnal_id'],
				':jurnal_date' => $data['jurnal_bookdate'],
				':jurnal_datedue' => $data['jurnal_duedate'], 
				':jurnal_descr' => str_replace(array("'", "\""), "", $data['jurnal_descr'] ),
				':periodemo_id' => $data['periodemo_id'],
				':curr_id' => $data['currency_id'],
				':jurnalsource_id' => 'MANUAL', 
				':jurnaltype_id' => 'MAN-JV',
				':_createby' => USERID
			]);

		} catch (\Exception $ex) {
			print_r($data);
			throw $ex;
		}
	}




	function InsertJurnalD($data, $periodemo_id, $stmtinsdet) {
		try {

			$stmtgetref = $this->db->prepare("
				select jurnaldetil_id from trn_jurnaldetil where tbref = :tbref and tbrefline = :tbrefline
			");
			
			$stmtgetref->execute([
				':tbref' => $data['ref_id'],
				':tbrefline' => $data['ref_line'],
			]);
			$rows = $stmtgetref->fetchall(PDO::FETCH_ASSOC);
			
			$jurnaldetil_id_ref = null;
			if (count($rows)>0) {
				$jurnaldetil_id_ref = $rows[0]['jurnaldetil_id'];
				echo "$jurnaldetil_id_ref\r\n";
			}	

			$stmtinsdet->execute([
				':jurnaldetil_id' => uniqid(),
				':jurnaldetil_descr' => str_replace(array("'", "\""), "", $data['jurnaldetil_descr'] ),
				':coa_id' => $data['acc_id'],
				':dept_id' => $this->getDept($data['region_id'], $data['branch_id']),
				':partner_id' => $data['rekanan_id'],
				':curr_id' => $data['currency_id'],
				':jurnaldetil_valfrg' => $data['jurnaldetil_foreign'],
				':jurnaldetil_valfrgrate' => $data['jurnaldetil_foreignrate'],
				':jurnaldetil_validr' => $data['jurnaldetil_idr'],
				':jurnal_id' => $data['jurnal_id'],
				':tbref' => $data['jurnal_id'],
				':tbrefline' => $data['jurnaldetil_line'],
				':tbperiode_id' => $periodemo_id,
				':jurnaldetil_id_ref' => $jurnaldetil_id_ref,
				':_createby' => USERID
			]);
			
		} catch (\Exception $ex) {
			print_r($data);
			throw $ex;
		}
	}

	function UpdateRefJurnalD($data, $periodemo_id, $stmtupdateref) {
		try {
			// jurnaldetil_id yang akan diupdate
			$stmtget = $this->db->prepare("
				select jurnaldetil_id, jurnaldetil_id_ref from trn_jurnaldetil where tbref = :jurnal_id and tbrefline = :jurnaldetil_line and coa_id = :coa_id
			");

			$stmtget->execute([
				':jurnal_id' => $data['jurnal_id'],
				':jurnaldetil_line' => $data['jurnaldetil_line'],
				':coa_id' => $data['acc_id'],
			]);
			$row = $stmtget->fetch(PDO::FETCH_ASSOC);
			$jurnaldetil_id = $row['jurnaldetil_id']; // jurnaldetil_id yang akan diupdate
			$jurnaldetil_id_ref = $row['jurnaldetil_id_ref'];
			

			if ($jurnaldetil_id_ref==null) {
				// cari referensinya
				$stmtgetref = $this->db->prepare("
					select jurnaldetil_id from trn_jurnaldetil where tbref = :tbref and tbrefline = :tbrefline
				");
				$stmtgetref->execute([
					':tbref' => $data['ref_id'],
					':tbrefline' => $data['ref_line']
				]);
				$rows = $stmtgetref->fetchall(PDO::FETCH_ASSOC);
				$jurnaldetil_id_ref = null;
				if (count($rows)>0) {
					$jurnaldetil_id_ref = $rows[0]['jurnaldetil_id'];
					echo "\r\nupdating $jurnaldetil_id {$data['jurnal_id']} {$data['jurnaldetil_line']}\r\n";
					
					$stmtupdateref->execute([
						':jurnaldetil_id' => $jurnaldetil_id,
						':jurnaldetil_id_ref' => $jurnaldetil_id_ref
					]);
				}
			}

	
		} catch (\Exception $ex) {
			print_r($data);
			throw $ex;
		}
	}




	function getPeriode($periode_id) {
		$map = array();
		$map['1712'] = ['201712', '201801'];
		$map['1801'] = ['201801', '201802'];
		$map['1802'] = ['201802', '201803'];
		$map['1803'] = ['201803', '201804'];
		$map['1804'] = ['201804', '201805'];
		$map['1805'] = ['201805', '201806'];
		$map['1806'] = ['201806', '201807'];
		$map['1807'] = ['201807', '201808'];
		$map['1808'] = ['201808', '201809'];
		$map['1809'] = ['201809', '201810'];
		$map['1810'] = ['201810', '201811'];
		$map['1811'] = ['201811', '201812'];
		$map['1812'] = ['201812', '201901'];
		$map['1901'] = ['201901', '201902'];
		$map['1902'] = ['201902', '201903'];
		$map['1903'] = ['201903', '201904'];
		$map['1904'] = ['201904', '201905'];
		$map['1905'] = ['201905', '201906'];
		$map['1906'] = ['201906', '201907'];
		$map['1907'] = ['201907', '201908'];
		$map['1908'] = ['201908', '201909'];
		$map['1909'] = ['201909', '201910'];
		$map['1910'] = ['201910', '201911'];
		$map['1911'] = ['201911', '201912'];
		$map['1912'] = ['201912', '202001'];
		$map['2001'] = ['202001', '202002'];
		$map['2002'] = ['202002', '202003'];
		$map['2003'] = ['202003', '202004'];
		$map['2004'] = ['202004', '202005'];
		$map['2005'] = ['202005', '202006'];
		$map['2006'] = ['202006', '202007'];
		$map['2007'] = ['202007', '202008'];
		$map['2008'] = ['202008', '202009'];
		$map['2009'] = ['202009', '202010'];
		$map['2010'] = ['202010', '202011'];
		$map['2011'] = ['202011', '202012'];
		$map['2012'] = ['202012', '202101'];
		$map['2101'] = ['202101', '202102'];
		$map['2102'] = ['202102', '202103'];
		$map['2103'] = ['202103', '202104'];
		$map['2104'] = ['202104', '202105'];
		

		if (array_key_exists($periode_id, $map)) {
			return $map[$periode_id];
		} else {
			return ['000000', '000000'];
		}

	}


	function getDept($region_id, $branch_id) {
		$map = array();
		$map['00100:0000100'] = '1000300';
		$map['00100:0000100'] = '1000100';
		$map['02400:0000100'] = 'GIA@HO';
		$map['00100:0000100'] = '1000400';
		$map['00100:0000100'] = '1000700';
		$map['02300:0000600'] = 'EMA-PI';
		$map['01100:0000100'] = 'HBS@HO';
		$map['00200:0000600'] = 'BRI-PI';
		$map['03400:0000412'] = 'GEX@CENTRAL-GI';
		$map['01100:0000800'] = 'HBS-PP';
		$map['00500:0000800'] = 'MNG-PP';
		$map['00400:0001400'] = 'FBI-SC';
		$map['00500:0000400'] = 'MNG-GI';
		$map['00500:0001000'] = 'MNG-PVJ';
		$map['01500:0000800'] = 'VAL-PP';
		$map['01400:0000800'] = 'CAN-PP';
		$map['00700:0000800'] = 'TOD-PP';
		$map['01400:0000900'] = 'CAN-PS';
		$map['00700:0000100'] = 'TOD@HO';
		$map['00500:0000100'] = 'MNG@HO';
		$map['01200:0000100'] = 'JCO@HO';
		$map['01500:0000100'] = 'VAL@HO';
		$map['01100:0000900'] = 'HBS-PS';
		$map['00700:0000600'] = 'TOD-PI';
		$map['00200:0000100'] = 'BRI@HO';
		$map['01100:0000700'] = 'HBS-PIM';
		$map['00900:0000100'] = 'EAG@HO';
		$map['00400:0000100'] = 'FBI@HO';
		$map['01100:0000600'] = 'HBS-PI';
		$map['00900:0001100'] = 'EAG@ICONIC';
		$map['00500:0000300'] = 'MNG-BSM';
		$map['01100:0001100'] = 'HBS@ICONIC';
		$map['01100:0000300'] = 'HBS-BSM';
		$map['00100:0000100'] = '1000500';
		$map['01800:0000900'] = 'FRG-PS';
		$map['00910:0000100'] = 'EAG@HO';
		$map['02100:0000100'] = 'TMH@HO';
		$map['01800:0000100'] = 'FRG@HO';
		$map['03400:0000100'] = 'GEX@HO';
		$map['02400:0000600'] = 'GIA-PI';
		$map['01110:0000100'] = 'HBS@HO';
		$map['02100:0001700'] = 'TMH-GC';
		$map['02600:0000100'] = 'FLA@HO';
		$map['01400:0000100'] = 'CAN@HO';
		$map['02500:0000100'] = 'ARJ@HO';
		$map['03410:0000100'] = 'GEX@METROWSL';
		$map['01800:0001400'] = 'FRG-SC';
		$map['01800:0000600'] = 'FRG-PI';
		$map['03400:0000300'] = 'GEX-BSM';
		$map['02300:0000100'] = 'EMA@HO';
		$map['00100:0000100'] = '1001000';
		$map['01800:0000300'] = 'FRG-BSM';
		$map['00100:0000100'] = '1000800';
		$map['00100:0000100'] = '1000550';
		$map['00100:0000100'] = '1000600';
		$map['00100:0000100'] = '1000200';
		$map['02600:0002830'] = 'FLA@METRO-PURI';
		$map['00910:0006040'] = 'EAG@DEALER';
		$map['01200:0000600'] = 'JCO-PI';
		$map['01400:0000600'] = 'CAN-PI';
		$map['03400:0004900'] = 'GEX@LOTTE-CWK';
		$map['02500:0000412'] = 'ARJ@CENTRAL-GI';
		$map['02700:0000412'] = 'VER@CENTRAL-GI';
		$map['01500:0000600'] = 'VAL-PI';
		$map['00900:0002830'] = 'EAG@METRO-PURI';
		$map['00900:0000910'] = 'EAG@METRO-PS';
		$map['02600:0006200'] = 'FLA@METRO-GKIM';
		$map['03400:0000411'] = 'GEX@SEIBU-GI';
		$map['02600:0000412'] = 'FLA@CENTRAL-GI';
		$map['03400:0005400'] = 'GEX@CENTRAL-NSH';
		$map['03400:0006040'] = 'GEX@DEALER';
		$map['02600:0006040'] = 'FLA@DEALER';
		$map['01800:0000830'] = 'FRG@FNF';
		$map['00900:0001800'] = 'EAG-CP';
		$map['03400:0001700'] = 'GEX-GC';
		$map['02800:0000412'] = 'VER@CENTRAL-GI';
		$map['02710:0000412'] = 'VER@CENTRAL-GI';
		$map['03410:0005800'] = 'GEX@METROWSL';
		$map['02300:0000620'] = 'EMA-PI';
		$map['00900:0001300'] = 'EAG-TP4';
		$map['01100:0001800'] = 'HBS-CP';
		$map['00900:0000600'] = 'EAG-PI';
		$map['02600:0000910'] = 'FLA@METRO-PS';
		$map['02300:0000412'] = 'EMA@CENTRAL-GI';
		$map['01510:0000100'] = 'RVL@HO';
		$map['00300:0000100'] = 'PRD@HO';
		$map['03100:0000100'] = 'KID@HO';
		$map['01140:0000100'] = 'HBS@HO';
		$map['02710:0000100'] = 'VER@HO';
		$map['02600:0001700'] = 'FLA-GC';
		$map['00100:0002300'] = '1000200';
		$map['00900:0006200'] = 'EAG@METRO-GKIM';
		$map['02600:0002300'] = 'FLA-CWS';
		$map['02300:0000670'] = 'EMA-PI';
		$map['01800:0000720'] = 'FRG@TEMP-PIM';
		$map['03400:0006000'] = 'GEX@LAZADA';
		$map['03400:0000910'] = 'GEX@METRO-PS';
		$map['03500:0004100'] = 'HO';
		$map['02100:0001900'] = 'TMH-TSM';
		$map['03400:0000810'] = 'GEX@METRO-PP';
		$map['01130:0001800'] = 'HBO-CP';
		$map['01130:0001900'] = 'HBO-TSM';
		$map['01130:0000100'] = 'HBO@HO';
		$map['00700:0000300'] = 'TOD-BSM';
		$map['02500:0000300'] = 'ARJ-BSM';
		$map['01100:0001900'] = 'HBS-TSM';
		$map['01100:0001300'] = 'HBS-TP4';
		$map['01100:0002300'] = 'HBS-CWS';
		$map['03400:0003400'] = 'GEX-KOCAS';
		$map['03400:0001110'] = 'GEX-CPO';
		$map['00900:0000300'] = 'EAG-BSM';
		$map['02600:0000300'] = 'FLA-BSM';
		$map['00900:0001900'] = 'EAG-TSM';
		$map['03400:0001900'] = 'GEX-TSM';
		$map['02600:0001900'] = 'FLA-TSM';
		$map['00700:0001400'] = 'TOD-SC';
		$map['00900:0002300'] = 'EAG-CWS';
		$map['02600:0005000'] = 'FLA-TP3';
		$map['01800:0002300'] = 'FRG-CWS';
		$map['00700:0002300'] = 'TOD-CWS';
		$map['03400:0004800'] = 'GEX-TP5';
		$map['01130:0002300'] = 'HBO-CWS';
		$map['00200:0000830'] = 'HO';
		$map['01100:0001700'] = 'HBS-GC';
		$map['02600:0000600'] = 'FLA-PI';
		$map['02600:0001400'] = 'FLA-SC';
		$map['02600:0001800'] = 'FLA-CP';
		$map['03400:0000700'] = 'GEX-PIM';
		$map['00900:0001720'] = 'EAG@FNF';
		$map['00900:0000400'] = 'EAG-GI';
		$map['00900:0000700'] = 'EAG-PIM';
		$map['02900:0000300'] = 'KIDS-BSM';
		$map['02500:0003200'] = 'ARJ-BWB';
		$map['02500:0002300'] = 'ARJ-CWS';
		$map['00100:0000100'] = '1000150';
		$map['00900:0001700'] = 'EAG-GC';
		$map['00100:0000100'] = '1000900';
		$map['03400:0003800'] = 'GEX-KMB';
		$map['02600:0004800'] = 'FLA-TP3';
		$map['00700:0000720'] = 'TOD@TEMP-PIM';
		$map['00900:0000720'] = 'EAG@TEMP-PIM';
		$map['01100:0000720'] = 'HBS@TEMP-PIM';
		$map['01400:0000720'] = 'CAN@TEMP-PIM';
		$map['02100:0000720'] = 'TMH@TEMP-PIM';
		$map['02600:0000720'] = 'FLA@TEMP-PIM';
		$map['03400:0000720'] = 'GEX@TEMP-PIM';
		$map['03400:0001720'] = 'GEX@FNF';
		$map['02100:0001720'] = 'TMH@FNF';
		$map['02710:0001720'] = 'VER@FNF';
		$map['02700:0001720'] = 'VER@FNF';
		$map['02800:0001720'] = 'VER@FNF';
		$map['01400:0000830'] = 'HO';
		$map['01100:0000830'] = 'HBS@FNF';
		$map['02500:0000670'] = 'ARJ@EMA-PI';
		$map['02400:0000670'] = 'GIA@EMA-PI';
		$map['01130:0002310'] = 'HBO-CWS';
		$map['02500:0002310'] = 'ARJ@HBO-CWS';
		$map['03400:0002310'] = 'GEX@HBO-CWS';
		$map['01100:0002310'] = 'HBS@HBO-CWS';
		$map['01200:0002310'] = 'JCO@HBO-CWS';
		$map['01510:0002310'] = 'RVL@HBO-CWS';
		$map['01500:0002310'] = 'VAL@HBO-CWS';
		$map['00900:0006040'] = 'EAG@DEALER';
		$map['01100:0006040'] = 'HBS@DEALER';
		$map['02600:0000700'] = 'FLA-PIM';
		$map['01400:0004410'] = 'CAN@FNF';
		$map['02500:0005400'] = 'ARJ@CENTRAL-NSH';
		$map['02100:0005400'] = 'TMH@CENTRAL-NSH';
		$map['02600:0001720'] = 'FLA@FNF';
		$map['00100:0000100'] = '1001200';
		$map['00900:0004310'] = 'EAG@FNF';
		$map['01100:0004310'] = 'HBS@FNF';
		$map['01800:0004310'] = 'FRG@FNF';
		$map['02600:0004310'] = 'FLA@FNF';
		$map['03400:0004310'] = 'GEX@FNF';
		$map['03400:0005000'] = 'GEX-TP5';
		$map['00100:0000100'] = 'HO';
		$map['01800:0002320'] = 'FRG@FNF';
		$map['02600:0002320'] = 'FLA@FNF';
		$map['00900:0002320'] = 'EAG@FNF';
		$map['00900:0005310'] = 'EAG@TMBJM';
		$map['02600:0005310'] = 'FLA@TMBJM';
		$map['01100:0000100'] = 'HBS@HO';
		$map['03400:0000413'] = 'GEX@FNF';
		$map['01100:0000413'] = 'HBS@FNF';
		$map['00100:0000100'] = 'HO';
		$map['00100:0000100'] = '1001100';
		$map['01800:0000413'] = 'FRG@FNF';
		$map['02600:0000413'] = 'FLA@FNF';
		$map['01100:0001720'] = 'HBS@FNF';
		$map['00100:0000100'] = 'HO';
		$map['00900:0000100'] = 'EAG@HO';
		$map['01800:0003100'] = 'FRG@FNF';
		$map['03400:0002710'] = 'GEX@FNF';
		$map['00100:0000100'] = '1001300';
		$map['03400:0001902'] = 'GEX-TPC';
		$map['03400:0006400'] = 'GEX-PKW';
		$map['00100:0001110'] = '1000100';
		$map['00900:0000413'] = 'EAG@FNF';
		$map['01800:0001720'] = 'FRG@FNF';
		$map['00900:0001902'] = 'EAG-TPC';
		$map['02600:0001902'] = 'FLA-TPC';
		$map['02600:0003410'] = 'FLA@FNF';
		$map['01100:0000100'] = 'HBS@HO';
		$map['00900:0001400'] = 'EAG-SC';
		$map['01100:0001400'] = 'HBS-SC';
		$map['03400:0000400'] = 'GEX-GI';
		$map['01800:0001903'] = 'FRG@TFI-TPC';
		$map['01100:0001903'] = 'HBS@TFI-TPC';
		$map['00900:0002920'] = 'EAG@TEMP-PIK';
		$map['02600:0002920'] = 'FLA@TEMP-PIK';
		$map['01400:0006100'] = 'CAN@FNF';
		$map['00900:0006100'] = 'EAG@FNF';
		$map['01800:0006100'] = 'FRG@FNF';
		$map['02600:0006100'] = 'FLA@FNF';
		$map['03400:0006100'] = 'GEX@FNF';
		$map['01100:0006100'] = 'HBS@FNF';
		$map['00700:0004310'] = 'TOD@FNF';
		$map['03400:0002502'] = 'GEX@FNF';
		$map['00900:0002502'] = 'EAG@FNF';
		$map['00700:0002502'] = 'TOD@FNF';
		$map['03400:0002320'] = 'GEX@FNF';
		$map['01100:0002320'] = 'HBS@FNF';
		$map['02600:0001903'] = 'HO';
		$map['00900:0001405'] = 'EAG@FNF';
		$map['00900:0000750'] = 'EAG@FNF';
		$map['00900:0001110'] = 'HO';
		$map['00900:0002720'] = 'EAG@FNF';
		$map['02600:0002720'] = 'FLA@FNF';
		$map['03400:0002720'] = 'GEX@FNF';
		$map['01100:0002720'] = 'HBS@FNF';
		$map['01100:0006120'] = 'HBS@FNF';
		$map['00900:0004400'] = 'EAG@FNF';
		$map['01800:0004400'] = 'FRG@FNF';
		$map['02600:0004400'] = 'FLA@FNF';
		$map['03400:0004400'] = 'GEX@FNF';
		$map['01100:0004400'] = 'HBS@FNF';
		$map['00700:0000750'] = 'TOD@FNF';
		$map['00900:0000710'] = 'EAG@METRO-PIM';
		$map['02600:0000710'] = 'FLA@METRO-PIM';
		$map['00900:0003410'] = 'HO';
		$map['03400:0003410'] = 'GEX@FNF';
		$map['01800:0003000'] = 'FRG@FNF';
		$map['03400:0001904'] = 'GEX-TPB';
		$map['00900:0001904'] = 'EAG-TPB';
		$map['02600:0001904'] = 'FLA-TPB';
		$map['01400:0003000'] = 'CAN@FNF';
		$map['00900:0003000'] = 'EAG@FNF';
		$map['02600:0003000'] = 'FLA@FNF';
		$map['03400:0003000'] = 'GEX@FNF';
		$map['01100:0003000'] = 'HBS@FNF';
		$map['00700:0003000'] = 'TOD@FNF';
		$map['00700:0000413'] = 'TOD@FNF';
		$map['02300:0002300'] = 'EMA@ARJ-CWS';
		$map['01100:0002710'] = 'HBS@FNF';
		$map['00900:0000412'] = 'EAG@CENTRAL-GI';
		$map['01100:0004410'] = 'HBS@FNF';
		$map['00900:0000840'] = 'EAG@FNF';
		$map['01400:0000840'] = 'CAN@FNF';
		$map['01800:0000840'] = 'FRG@FNF';
		$map['03400:0000840'] = 'GEX@FNF';
		$map['00700:0000840'] = 'TOD@FNF';
		$map['03700:0001904'] = 'FKP-TPB';
		$map['01100:0001902'] = 'HO';
		$map['00900:0009020'] = 'EAG@FNF';
		$map['01100:0009020'] = 'HBS@FNF';
		$map['01800:0009020'] = 'FRG@FNF';
		$map['02600:0009020'] = 'FLA@FNF';
		$map['03700:0000100'] = 'FKP@HO';
		$map['00100:0000100'] = 'HO';
		$map['00900:0001310'] = 'EAG@FNF';
		$map['00100:0002300'] = '1000100';
		$map['03400:0004800'] = 'GEX-TP5';
		$map['00200:0002502'] = 'BRI@FNF';
		$map['02300:0002502'] = 'EMA@FNF';
		$map['02400:0002502'] = 'GIA@FNF';
		$map['02100:0002502'] = 'TMH@FNF';
		$map['03400:0002311'] = 'GEX@FNF';
		$map['01400:0000200'] = 'CAN@DC-JKT';
		$map['01100:0001900'] = 'HBS-TSM';
		$map['03300:0000100'] = 'KID@HO';
		$map['03700:0001903'] = 'FKP@TFI-TPC';
		$map['03600:0000100'] = 'HO';
		$map['01100:0000840'] = 'HO';
		$map['00100:0001900'] = '1000100';
		$map['03400:0000830'] = 'GEX@FNF';
		$map['03700:0000830'] = 'FKP@FNF';
		$map['00900:0000830'] = 'EAG@FNF';
		$map['03400:0002501'] = 'GEX@FNF';
		$map['00100:0001904'] = '1000100';
		$map['00700:0002300'] = 'TOD-CWS';
		$map['01100:0002311'] = 'HBS@FNF';
		$map['03700:0000412'] = 'FKP@CENTRAL-GI';
		$map['03700:0000330'] = 'FKP@FNF';
		$map['03700:0000710'] = 'FKP@METRO-PIM';
		$map['00900:0000200'] = 'EAG@DC-JKT';
		$map['03600:0001903'] = 'COR@TFI-TPC';
		$map['02500:0001900'] = 'ARJ-TSM';
		$map['01130:0000300'] = 'HBO-BSM';
		$map['01130:0003200'] = 'HBO@HBS-BWB';
		$map['01130:0001100'] = 'HBO@ICONIC';
		$map['02600:0003200'] = 'FLA-BWB';
		$map['03400:0000310'] = 'GEX@METRO-BSM';
		$map['03400:0002360'] = 'GEX@METRO-CWS';
		$map['03400:0001120'] = 'GEX@SEIBU-SPM';
		$map['01100:0003200'] = 'HBS-BWB';
		$map['01200:0002300'] = 'JCO-CWS';
		$map['01510:0000300'] = 'RVL-BSM';
		$map['01510:0002300'] = 'RVL-CWS';
		$map['02100:0000300'] = 'TMH-BSM';
		$map['02100:0003200'] = 'TMH-BWB';
		$map['02100:0002300'] = 'TMH-CWS';
		$map['02710:0000300'] = 'VER@VJE-BSM';
		$map['02710:0002300'] = 'VER@VCO-CWS';
		$map['02710:0001100'] = 'VER@ICONIC';
		$map['02710:0001900'] = 'VER@VCO-TSM';
		$map['02700:0000300'] = 'VER@VJE-BSM';
		$map['02700:0003200'] = 'VER@VJE-BWB';
		$map['02700:0002300'] = 'VER@VCO-CWS';
		$map['02700:0001100'] = 'VER@ICONIC';
		$map['02800:0002300'] = 'VER@VRS-CWS';
		$map['01500:0000830'] = 'HO';
		$map['01130:0001700'] = 'HBO-GC';
		$map['02100:0000200'] = 'TMH@DC-JKT';
		$map['00700:0000200'] = 'TOD@DC-JKT';
		$map['01800:0000200'] = 'FRG@DC-JKT';
		$map['01800:0000110'] = 'FRG@HO';
		$map['01110:0000600'] = 'HBS-PI';
		$map['01110:0001700'] = 'HBS-GC';
		$map['01110:0002310'] = 'HBS@HBO-CWS';
		$map['02600:0000200'] = 'FLA@DC-JKT';
		$map['02600:0000110'] = 'FLA@HO';
		$map['02600:0000230'] = 'FLA@DC-JKT';
		$map['00900:0000110'] = 'EAG@HO_DEF';
		$map['03400:0000200'] = 'GEX@DC-JKT';
		$map['03400:0000110'] = 'GEX@HO';
		$map['03400:0000230'] = 'GEX@DC-JKT';
		$map['01100:0000200'] = 'HBS@DC-JKT';
		$map['00700:0001720'] = 'TOD@FNF';
		$map['00200:0001700'] = 'BRI@HBS-GC';
		$map['01130:0000600'] = 'HBO@HBS-PI';
		$map['02500:0000410'] = 'ARJ@CENTRAL-GI';
		$map['02600:0000410'] = 'FLA@CENTRAL-GI';
		$map['01110:0000900'] = 'HBS-PS';
		$map['00100:0000200'] = '1000500';
		$map['03500:0000100'] = 'HO';
		$map['02300:0001720'] = 'EMA@FNF';
		$map['02400:0001720'] = 'GIA@FNF';
		$map['03410:0000200'] = 'GEX@DC-JKT';
		$map['00100:0001720'] = '1000100';
		$map['02400:0000200'] = 'GIA@DC-JKT';
		$map['01100:0000230'] = 'HBS@DC-JKT';
		$map['02300:0000200'] = 'EMA@DC-JKT';
		$map['01130:0000200'] = 'HBO@DC-JKT';
		$map['01130:0000230'] = 'HBO@DC-JKT';
		$map['01200:0000200'] = 'JCO@DC-JKT';
		$map['01200:0000720'] = 'JCO@FNF';
		$map['01510:0000200'] = 'RVL@DC-JKT';
		$map['01510:0000720'] = 'RVL@TEMP-PIM';
		$map['00200:0000200'] = 'BRI@DC-JKT';
		$map['01110:0000200'] = 'HBS@DC-JKT';
		$map['01110:0000230'] = 'HBS@DC-JKT';
		$map['01500:0000200'] = 'VAL@DC-JKT';
		$map['01500:0000720'] = 'VAL@FNF';
		$map['02800:0000200'] = 'VER@DC-JKT';
		$map['02800:0000720'] = 'VER@TEMP-PIM';
		$map['02500:0004310'] = 'ARJ@FNF';
		$map['02500:0000200'] = 'ARJ@DC-JKT';
		$map['02500:0001720'] = 'ARJ@FNF';
		$map['02710:0000200'] = 'VER@DC-JKT';
		$map['02710:0000720'] = 'VER@TEMP-PIM';
		$map['02700:0000200'] = 'VER@DC-JKT';
		$map['02700:0000720'] = 'VER@TEMP-PIM';
		$map['02300:0000720'] = 'EMA@TEMP-PIM';
		$map['02400:0000720'] = 'GIA@FNF';
		$map['01200:0002320'] = 'JCO@FNF';
		$map['01510:0002320'] = 'RVL@FNF';
		$map['02100:0000230'] = 'TMH@DC-JKT';
		$map['02500:0002320'] = 'ARJ@FNF';
		$map['02500:0000230'] = 'ARJ@DC-JKT';
		$map['01130:0002320'] = 'HBO@FNF';
		$map['02710:0000230'] = 'VER@DC-JKT';
		$map['02300:0000230'] = 'EMA@DC-JKT';
		$map['02700:0000100'] = 'VER@HO';
		$map['02700:0000230'] = 'VER@DC-JKT';
		$map['00700:0003100'] = 'TOD@FNF';
		$map['00700:0002710'] = 'TOD@FNF';
		$map['00700:0001410'] = 'TOD@FNF';
		$map['00700:0005200'] = 'TOD@FNF';
		$map['00700:0009000'] = 'TOD@FNF';
		$map['00700:0006100'] = 'TOD@FNF';
		$map['00700:0008000'] = 'TOD@FNF';
		$map['00700:0000910'] = 'TOD@METRO-PS';
		$map['00700:0000110'] = 'TOD@HO';
		$map['00700:0000230'] = 'TOD@DC-JKT';
		$map['01130:0000800'] = 'HBO@HBS-PP';
		$map['01800:0000930'] = 'FRG@METRO-PS';
		$map['01800:0001410'] = 'FRG@FNF';
		$map['01800:0001900'] = 'FRG-TSM';
		$map['01800:0005200'] = 'FRG@FNF';
		$map['01800:0008000'] = 'FRG@FNF';
		$map['01800:0009000'] = 'FRG@FNF';
		$map['02700:0000330'] = 'VER@FNF';
		$map['02700:0003500'] = 'VER@FNF';
		$map['02700:0005200'] = 'VER@FNF';
		$map['02710:0000330'] = 'VER@FNF';
		$map['02710:0001800'] = 'VER@VCO-CP';
		$map['02710:0001820'] = 'VER@FNF';
		$map['02710:0002700'] = 'VER@VJE-KC';
		$map['02710:0002710'] = 'VER@FNF';
		$map['02800:0001410'] = 'VER@FNF';
		$map['02800:0000100'] = 'VER@HO';
		$map['02600:0001410'] = 'FLA@FNF';
		$map['02600:0005200'] = 'FLA@FNF';
		$map['00900:0000900'] = 'EAG-PS';
		$map['00900:0001410'] = 'EAG@FNF';
		$map['00900:0005200'] = 'EAG@FNF';
		$map['00910:0000200'] = 'EAG@DC-JKT';
		$map['03400:0000410'] = 'GEX@CENTRAL-GI';
		$map['03400:0007001'] = 'GEX-KC';
		$map['03400:0007003'] = 'GEX-GC';
		$map['02800:0000410'] = 'VER@CENTRAL-GI';
		$map['02800:0000230'] = 'VER@DC-JKT';
		$map['02710:0000410'] = 'VER@CENTRAL-GI';
		$map['02710:0005400'] = 'VER@CENTRAL-NSH';
		$map['02700:0000410'] = 'VER@CENTRAL-GI';
		$map['02500:0000600'] = 'ARJ@EMA-PI';
		$map['02100:0000110'] = 'TMH@HO';
		$map['02100:0000410'] = 'TMH@CENTRAL-GI';
		$map['02100:0000412'] = 'TMH@CENTRAL-GI';
		$map['02100:0001410'] = 'TMH@FNF';
		$map['02100:0001800'] = 'TMH-CP';
		$map['02100:0003100'] = 'TMH@FNF';
		$map['01510:0000600'] = 'RVL-PI';
		$map['01510:0000650'] = 'RVL@VAL-PI';
		$map['01130:0000413'] = 'HBO@FNF';
		$map['02300:0000413'] = 'EMA@ARJ@FNF';
		$map['01110:0004310'] = 'HBS@FNF';
		$map['02800:0000413'] = 'VER@FNF';
		$map['02500:0000413'] = 'ARJ@FNF';
		$map['02710:0000413'] = 'VER@FNF';
		$map['02700:0000413'] = 'VER@FNF';
		$map['02400:0000413'] = 'GIA@FNF';
		$map['02400:0000230'] = 'GIA@DC-JKT';
		$map['02100:0000413'] = 'TMH@FNF';
		$map['01800:0000230'] = 'FRG@DC-JKT';
		$map['01400:0005100'] = 'CAN@FNF';
		$map['01400:0005600'] = 'CAN@FNF';
		$map['01400:0009000'] = 'CAN@FNF';
		$map['02800:0001700'] = 'VER@VRS-GC';
		$map['02710:0001700'] = 'VER@VCO-GC';
		$map['02700:0001700'] = 'VER@VCO-GC';
		$map['02400:0001700'] = 'GIA@FNF';
		$map['01130:0001720'] = 'HBO@FNF';
		$map['01110:0001720'] = 'HBS@FNF';
		$map['01200:0000230'] = 'JCO@DC-JKT';
		$map['01510:0000230'] = 'RVL@DC-JKT';
		$map['01500:0000230'] = 'VAL@DC-JKT';
		$map['02500:0001700'] = 'ARJ-GC';
		$map['00200:0001720'] = 'BRI@FNF';
		$map['00900:0003100'] = 'EAG@FNF';
		$map['02600:0003100'] = 'FLA@FNF';
		$map['03400:0003100'] = 'GEX@FNF';
		$map['01100:0003100'] = 'HBS@FNF';
		$map['02100:0004310'] = 'TMH@FNF';
		$map['01200:0004310'] = 'JCO@FNF';
		$map['00900:0000700'] = 'EAG-PIM';
		$map['00900:0000600'] = 'EAG-PI';
		$map['00900:0001800'] = 'EAG-CP';
		$map['00700:0000600'] = 'TOD-PI';
		$map['00700:0000800'] = 'TOD-PP';
		$map['02600:0000600'] = 'FLA-PI';
		$map['02600:0001400'] = 'FLA-SC';
		$map['02600:0001800'] = 'FLA-CP';
		$map['02600:0000700'] = 'FLA-PIM';
		$map['02300:0004310'] = 'EMA@FNF';
		$map['00200:0004310'] = 'BRI@FNF';
		$map['01500:0004310'] = 'VAL@FNF';
		$map['02710:0004310'] = 'VER@FNF';
		$map['02400:0004310'] = 'GIA@FNF';
		$map['02700:0004310'] = 'VER@FNF';
		$map['01510:0004310'] = 'RVL@FNF';
		$map['02800:0004310'] = 'VER@FNF';
		$map['00200:0000230'] = 'BRI@DC-JKT';
		$map['01400:0000230'] = 'CAN@DC-JKT';
		$map['03400:0001300'] = 'GEX-TP5';
		$map['01100:0002501'] = 'HBS@FNF';
		$map['01110:0002501'] = 'HBS@FNF';
		$map['00700:0002501'] = 'TOD@FNF';
		$map['02600:0002501'] = 'FLA@FNF';
		$map['01400:0002501'] = 'CAN@FNF';
		$map['00900:0002501'] = 'EAG@FNF';
		$map['01800:0002501'] = 'FRG@FNF';
		$map['01130:0002501'] = 'HBS@FNF';
		$map['00900:0006110'] = 'EAG@FNF';
		$map['00100:0000110'] = '1000200';
		$map['02500:0002502'] = 'ARJ@FNF';
		$map['01130:0002502'] = 'HBO@FNF';
		$map['02800:0002502'] = 'VER@FNF';
		$map['02700:0002502'] = 'VER@FNF';
		$map['01500:0002502'] = 'VAL@FNF';
		$map['01100:0002502'] = 'HBS@FNF';
		$map['01400:0002502'] = 'CAN@FNF';
		$map['02600:0002502'] = 'FLA@FNF';
		$map['01800:0002502'] = 'FRG@FNF';
		$map['02600:0006000'] = 'FLA@LAZADA';
		$map['02600:0006050'] = 'FLA@JDID';
		$map['00900:0000230'] = 'EAG@DC-JKT';
		$map['01100:0000110'] = 'HBS@HO';
		$map['02500:0001800'] = 'ARJ-CP';
		$map['02500:0001903'] = 'ARJ@TFI-TPC';
		$map['02400:0001903'] = 'GIA@TFI-TPC';
		$map['00700:0001903'] = 'TOD@TFI-TPC';
		$map['00200:0001903'] = 'BRI@TFI-TPC';
		$map['00900:0006060'] = 'EAG@FACTORY';
		$map['02300:0001903'] = 'EMA@TFI-TPC';
		$map['02500:0006100'] = 'ARJ@FNF';
		$map['02710:0002502'] = 'VER@FNF';
		$map['02710:0006100'] = 'VER@FNF';
		$map['01110:0002502'] = 'HBS@FNF';
		$map['01200:0002502'] = 'JCO@FNF';
		$map['01510:0002502'] = 'RVL@FNF';
		$map['02100:0006100'] = 'TMH@FNF';
		$map['01100:0006060'] = 'HBS@FACTORY';
		$map['00100:0006100'] = '1001200';
		$map['01800:0000710'] = 'HO';
		$map['01800:0000910'] = 'FRG@METRO-PS';
		$map['01800:0002830'] = 'HO';
		$map['01100:0005100'] = 'HBS@FNF';
		$map['01500:0000820'] = 'VAL-PP';
		$map['01800:0009010'] = 'FRG@FNF';
		$map['03400:0009010'] = 'GEX@FNF';
		$map['01100:0009010'] = 'HBS@FNF';
		$map['01400:0002310'] = 'CAN@FNF';
		$map['00900:0009010'] = 'EAG@FNF';
		$map['01100:0003500'] = 'HBS@FNF';
		$map['03400:0003500'] = 'GEX@FNF';
		$map['02600:0003500'] = 'FLA@FNF';
		$map['00900:0003500'] = 'EAG@FNF';
		$map['00100:0000800'] = 'HO';
		$map['01800:0000413'] = 'FRG@FNF';
		$map['00400:0003600'] = 'FBI-SMO';
		$map['02700:0003600'] = 'VER@VJE-SMO';
		$map['01130:0003600'] = 'HBO-SMO';
		$map['02600:0004420'] = 'FLA@FNF';
		$map['03700:0000200'] = 'FKP@DC-JKT';
		$map['03400:0004420'] = 'GEX@FNF';
		$map['01100:0004420'] = 'HBS@FNF';
		$map['00900:0004420'] = 'EAG@FNF';
		$map['01110:0002311'] = 'HBS@FNF';
		$map['01510:0002311'] = 'RVL@FNF';
		$map['01500:0002311'] = 'VAL@FNF';
		$map['01130:0002311'] = 'HBO@FNF';
		$map['02500:0002311'] = 'ARJ@FNF';
		$map['00900:0006041'] = 'EAG@DEALER-WHS';
		$map['00700:0002311'] = 'TOD@FNF';
		$map['01130:0002300'] = 'HBO-CWS';
		$map['02600:0001300'] = 'FLA-TP3';
		$map['01100:0002502'] = 'HBS@FNF';
		$map['03600:0000200'] = 'HO';
		$map['03600:0001902'] = 'HO';
		$map['00900:0002311'] = 'HO';
		$map['01800:0002311'] = 'HO';
		$map['03700:0002311'] = 'FKP@FNF';
		$map['01400:0002311'] = 'CAN@FNF';
		$map['01200:0002311'] = 'JCO@FNF';
		$map['02300:0002501'] = 'COR@CLOSE';
		$map['02400:0002501'] = 'COR@CLOSE';
		$map['02500:0002501'] = 'COR@CLOSE';
		$map['01510:0002501'] = 'COR@CLOSE';
		$map['01500:0002501'] = 'COR@CLOSE';
		$map['00200:0002501'] = 'COR@CLOSE';
		$map['02100:0002501'] = 'COR@CLOSE';
		$map['02700:0002501'] = 'COR@CLOSE';
		$map['02710:0002501'] = 'COR@CLOSE';
		$map['02800:0002501'] = 'COR@CLOSE';
		$map['03100:0002501'] = 'COR@CLOSE';
		$map['03200:0002501'] = 'COR@CLOSE';
		$map['03100:0000200'] = 'KID@DC-JKT';
		$map['03200:0000200'] = 'KID@DC-JKT';
		$map['03300:0000200'] = 'KID@DC-JKT';
		$map['03300:0002501'] = 'COR@CLOSE';
		$map['03500:0000200'] = 'HO';
		$map['03700:0000720'] = 'FKP@FNF';
		$map['02600:0005000'] = 'FLA-TP3';
		$map['00100:0000600'] = '1000200';
		$map['02600:0000910'] = 'FLA@METRO-PS';
		$map['00100:0000100'] = 'HO';
		$map['01130:0002700'] = 'HBO-KC';
		$map['02100:0002700'] = 'TMH-KC';
		$map['02500:0002700'] = 'ARJ-KC';
		$map['02700:0002700'] = 'VER@VJE-KC';
		$map['03400:0000920'] = 'GEX@SEIBU-PS';
		$map['03400:0004601'] = 'GEX@SEIBU-KG';
		$map['01500:0003000'] = 'VAL@FNF';
		$map['00100:0000300'] = '1000200';
		$map['00100:0001700'] = '1000200';
		$map['00100:0001400'] = '1000200';
		$map['00100:0003400'] = '1000200';
		$map['01100:0001903'] = '1000100';
		$map['00900:0001902'] = '1000100';
		$map['02600:0001902'] = '1000100';
		$map['03400:0001902'] = '1000100';
		$map['00900:0001400'] = 'EAG-SC';
		$map['02600:0001900'] = 'FLA-TSM';
		$map['03400:0001900'] = 'GEX-TSM';
		$map['01100:0000830'] = 'HBS@FNF';
		$map['01100:0000300'] = 'HBS-BSM';
		$map['02600:0000300'] = 'FLA-BSM';
		$map['03400:0000300'] = 'GEX-BSM';
		$map['01800:0000300'] = 'FRG-BSM';
		$map['01100:0002300'] = 'HBS-CWS';
		$map['01100:0001300'] = 'HBS-TP4';
		$map['02600:0002300'] = 'FLA-CWS';
		$map['02600:0005000'] = 'FLA-TP3';
		$map['03400:0004800'] = 'GEX-TP5';
		$map['03400:0006400'] = 'GEX-PKW';
		$map['01800:0002300'] = 'FRG-CWS';
		$map['00700:0002300'] = 'TOD-CWS';
		$map['03700:0001902'] = 'HO';
		$map['01100:0006030'] = 'HO';
		$map['02600:0000300'] = 'FLA-BSM';
		$map['01100:0001800'] = 'HBS-CP';
		$map['01100:0002300'] = 'HBS-CWS';
		$map['00100:0006050'] = '1000200';
		$map['00100:0000100'] = 'HO';
		$map['00900:0001902'] = 'EAG-TPC';
		$map['01800:0002300'] = 'FRG-CWS';
		$map['01100:0001110'] = 'HO';
		
		$id = $region_id . ":" . $branch_id;
		if (array_key_exists($id, $map)) {
			return $map[$id];
		} else {
			return 'HO';
		}

	}


});

