<?php

require_once __ROOT_DIR.'/core/webapi.php';	
require_once __ROOT_DIR.'/core/webauth.php';	

class SCENARIO {
	public static $id;
	public static $username;
	public static $param;
	public static $data;

	public static function Run() {

		$testdata = '[
			 {"Date":"2021-10-01 00:00:00","SlotTimeStart":"09:10:05","SlotTimeEnd":"09:10:20","SlotDuration":"15","SlotDescr":"Susu Ultra","SlotCode":"A005","ActualTimeStart":"09:10:25","ActualTimeEnd":"09:10:40","ActualDuration":"15","SpotId":"B2100544","MediaOrderId":"1111","MediaOrderRef":"001\/Mindshare\/PO\/X\/2021 Lokal PBB ^","TypeMediaOrder":"Blocking","MediaOrderDescr":"Bloking Kabar Malam","AgencyCode":"210966","AgencyName":"Wira Pamungkas Pariwara","AdvertiserCode":"210919","AdvertiserName":"Ultra Sakti","BrandCode":"212819","BrandName":"Susu Coklat","ProgrammeCode":"PJ21060009","ProgrammeName":"KABAR MALAM","EpisodeCode":"6166c013f0e42","EpisodeName":"Kabar Malam Eps 2","Value":10000000}
			,{"Date":"2021-10-01 00:00:00","SlotTimeStart":"10:15:25","SlotTimeEnd":"10:15:40","SlotDuration":"15","SlotDescr":"Susu Ultra","SlotCode":"A005","ActualTimeStart":"10:15:25","ActualTimeEnd":"10:15:40","ActualDuration":"15","SpotId":"B2100545","MediaOrderId":"1111","MediaOrderRef":"001\/Mindshare\/PO\/X\/2021 Lokal PBB ^","TypeMediaOrder":"Blocking","MediaOrderDescr":"Bloking Kabar Malam","AgencyCode":"210966","AgencyName":"Wira Pamungkas Pariwara","AdvertiserCode":"210919","AdvertiserName":"Ultra Sakti","BrandCode":"212819","BrandName":"Susu Coklat","ProgrammeCode":"PJ21060009","ProgrammeName":"KABAR MALAM","EpisodeCode":"6166c013f0e42","EpisodeName":"Kabar Malam Eps 2","Value":10000000}
			,{"Date":"2021-10-01 00:00:00","SlotTimeStart":"02:11:20","SlotTimeEnd":"02:11:50","SlotDuration":"30","SlotDescr":"Susu Ultra","SlotCode":"A005","ActualTimeStart":"02:11:25","ActualTimeEnd":"02:11:55","ActualDuration":"30","SpotId":"B2100546","MediaOrderId":"1111","MediaOrderRef":"001\/Mindshare\/PO\/X\/2021 Lokal PBB ^","TypeMediaOrder":"Blocking","MediaOrderDescr":"Bloking Kabar Malam","AgencyCode":"210966","AgencyName":"Wira Pamungkas Pariwara","AdvertiserCode":"210919","AdvertiserName":"Ultra Sakti","BrandCode":"212819","BrandName":"Susu Coklat","ProgrammeCode":"PJ21060009","ProgrammeName":"KABAR MALAM","EpisodeCode":"6166c013f0e42","EpisodeName":"Kabar Malam Eps 2","Value":20000000}
			,{"Date":"2021-10-01 00:00:00","SlotTimeStart":"08:20:05","SlotTimeEnd":"08:20:20","SlotDuration":"15","SlotDescr":"Susu Ultra","SlotCode":"A005","ActualTimeStart":"08:20:25","ActualTimeEnd":"08:20:40","ActualDuration":"15","SpotId":"B2100547","MediaOrderId":"1111","MediaOrderRef":"001\/Mindshare\/PO\/X\/2021 Lokal PBB ^","TypeMediaOrder":"Blocking","MediaOrderDescr":"Bloking Kabar Malam","AgencyCode":"210966","AgencyName":"Wira Pamungkas Pariwara","AdvertiserCode":"210919","AdvertiserName":"Ultra Sakti","BrandCode":"212819","BrandName":"Susu Coklat","ProgrammeCode":"PJ21060009","ProgrammeName":"KABAR MALAM","EpisodeCode":"6166c013f0e42","EpisodeName":"Kabar Malam Eps 2","Value":10000000}
		]';

		require __DIR__ . '/../apis/xtion-upload.php';
		SCENARIO::$id = '61a5ad8d8f18d';
		SCENARIO::$data = json_decode($testdata);

		SCENARIO::$username = '5effbb0a0f7d1';  // MANAGER
		// SCENARIO::$username = '5facb8a36127f';  // GM
		// SCENARIO::$username = '5facb8bebf826';  // DIREKTUR
		
		
		$API->auth = new class {
			public function session_get_user() {
				return (object) [
					'username' => SCENARIO::$username
				];
			}			
		};

		$API->useotp = false;
		$API->reqinfo = (object)['modulefullname'=>'media/sales/logproof'];
		$result = $API->execute(SCENARIO::$id, SCENARIO::$data);

		print_r($result);

	}
}


console::class(new class($args) extends clibase {
	function execute() {
		SCENARIO::Run();
	}
});

