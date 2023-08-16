<?php
namespace transfashion\allo;
/**
 * allotopup.lib.php
 * 
 * library untuk melakukan topup saldo allobank
 * berdasarkan dokumen https://drive.google.com/file/d/12QyVJJtua5QR1kLqbaTw1jRLnEA8NtTu/view?usp=share_link
 * 
 * pemakaian:
 * 
 *  $at = new allotopuplib();
 * 
 * 	$parameter = (object)[
 *		'topup_id' => '12345678123456781234567812345678',
 *		'storeId' => '2222',
 *		'cashierId' => '3333',
 *		'value' => 100000,
 *	];
 *	
 *	$result = $at->generate_barcode_topup($parameter);
 * 
 * 
 * agung <agung@transfashionindonesia.com>
 * 12/01/2023
 * 
 */

require_once implode('/', [__DIR__ , 'Crypt', 'RSA.php']);


/**
 * require config untuk ovveride default config dibawah
 */
if (!defined('__APP_ID')) {
	define('__APP_ID', '50002TFS11');
}

if (!defined('__APP_SECRET')) {
	define('__APP_SECRET', '9b19266ca34077805dd6d7bb7f371eef');
}

if (!defined('__PARTNER_ID')) {
	define('__PARTNER_ID', 'mega_bank');
}

if (!defined('__PUBLIC_KEY')) {
	define('__PUBLIC_KEY', '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC/6U5A4cB4ybHFZX8TyAlfA7+U
PL34OcWz4FBBLyFcEdirCto/9qTMkQem9qiRcJyKoDalIoaNd6vnpDtJ527hxutY
eeq3D7joyhYvdinsZj4AujsN/5HQku1UtBtEV+wuFvunAP06tSYDY7pHB68dyAVM
3/WU3MKJWYFE8vwiPwIDAQAB
-----END PUBLIC KEY-----');
}

if (!defined('__PRIVATE_KEY')) {
	define('__PRIVATE_KEY', '-----BEGIN PRIVATE KEY-----
MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAL/pTkDhwHjJscVl
fxPICV8Dv5Q8vfg5xbPgUEEvIVwR2KsK2j/2pMyRB6b2qJFwnIqgNqUiho13q+ek
O0nnbuHG61h56rcPuOjKFi92KexmPgC6Ow3/kdCS7VS0G0RX7C4W+6cA/Tq1JgNj
ukcHrx3IBUzf9ZTcwolZgUTy/CI/AgMBAAECgYEAh6otSI6OWpTyl8HFUL2Talij
AO7juGtWyG7lLP9dTNHM4jUcziTUoUxRJYWu6om6EAFsP9alWbJkkq81vmvdpIGT
VD7pzrk4d+igVItIQ1baGnyfbzfoZHNCufDwtD8of99gFuwcMFhO7hOFSVpAt4CB
1Z7MqsRaEnbx2qPUQzkCQQDuyL80Hhjrjvkjhz/jBsj32yln11VtRozcLrQRCSwR
5kSbSayF/8mLoDxSlFb/vnVahycYCQvCyPeGHbbAYAJbAkEAzb9srGUEucVfjncJ
SE3G2mC742UyfREBTtU7ZDkuvHqWQuhpEhQZtEmx0ArcxslEPt7fy2R7937UDoBD
hQgc7QJBANgEE7V6ZOD0/r39ZPZk9PoE8nhWfZnV8Shft9aRjaTDw90X0VOICzSd
SPUL7Yh/LHsxOVStGk6ksAhMaT+vm3ECQAPG1sTXWd3bHKTt1HLKUrMhgXFsIyAe
lsqOqXdugUgea/GweHVHfy1LCQOLn3/ao/EPgEYogwoI/vWBLzwa6QUCQBW3dK3k
7FlciQzu7nTJC1wTiLlygpSZP6iH5F1EH0WFs9ypJJf0Fex31TNVGZLt+OP/szGa
ExXWPYlLqXU7smA=
-----END PRIVATE KEY-----');
}

if (!defined('__BASE_URL')) {
	define('__BASE_URL', 'https://uatopenapi.ctcorpmpc.com');
}



class allotopuplib {

	const ConnectTimeout = 10;
	const ExecuteTimemout = 30;

	const api_barcodetopup = ['T0000000000001', '/api/v2.0/wallet/offline-topup/barcode/generate'];
	const api_cekstatus = ['T0000000000002', '/api/v2.0/wallet/offline-topup/barcode/query'];


	private bool $showrequestdata = false;

	private function CreateCodeVerifier() : string {
		$verifier_bytes = random_bytes(32);
        $code_verifier = rtrim(strtr(base64_encode($verifier_bytes), "+/", "-_"), "=");
		return $code_verifier;
	}

    private function CreateChalangeCode(string $code_verifier) : string {
        $challenge_bytes = hash("sha256", $code_verifier, true);
        $code_challenge = rtrim(strtr(base64_encode($challenge_bytes), "+/", "-_"), "=");
        return $code_challenge;
    }

	private function CreateTransactionNo(string $apiCode) : string {
		return date('ymd') . $apiCode . substr(uniqid(), -12);
	}

	private function CreateTimestamp() : string {
		$timestamp = round(microtime(true) * 1000);
		return (string)$timestamp;
	}

	private function CreateNonce(string $timestamp) : string {
		$nonce = (string) \floor(100000000 * ((float)rand()/(float)getrandmax()));
		return $nonce;
	}

	private function CreateSign(string $body, string $nonce, string $timestamp) : string {
		$arr = [__APP_ID, $nonce, $timestamp, __APP_SECRET, $body];
		asort($arr,2);
        $data = join('', $arr);
		$obj = hash('sha256', $data);
        $objBin = hex2bin($obj);
		$strSign = '';
		$encrypted = openssl_private_encrypt($objBin, $crypttext, __PRIVATE_KEY);
		if ($encrypted) {
            $strSign = bin2hex($crypttext);
		}
		return $strSign;
	}

	private function get_api_info(array $apidata) : object {
		return (object)[
			'id' => $apidata[0],
			'url' => $apidata[1]
		];
	}

	private function SendRequest(string $url, string $json_body, array $arr_header) : string {
		$timestart = microtime(true);

		try {
			$header = [];
			foreach ($arr_header as $key=>$value) {
				$header[] = "$key: $value";
			}

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::ConnectTimeout);
			curl_setopt($ch, CURLOPT_TIMEOUT, self::ExecuteTimemout);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_body);

			$output = curl_exec($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$curl_errno = curl_errno($ch);
    		$curl_error = curl_error($ch);			
			curl_close($ch); 
			$timeend = microtime(true);

			if ($this->showrequestdata) {
				echo "\r\n=== HEADER ==========================================\r\n";
				print_r((object)$arr_header);
				echo "\r\n\r\n== BODY =============================================\r\n";
				print_r($json_body);
				echo "\r\n\r\n=== RESULT ==========================================\r\n";
				print_r($output);
				echo "\r\n\r\n";
			}

			if ($curl_errno > 0) {
				throw new Exception("cURL Error ($curl_errno): $curl_error");
			} 

			return $output;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	private function processResponse(string $json_data) : object {
		try {
			$result = json_decode($json_data);
			if (json_last_error() !== JSON_ERROR_NONE) {
				throw new \Exception ("json data is not correct!\r\n\r\n$json_data\r\n\r\n");
			}

			if ($result->code!=0) {
				throw new \Exception($result->message, $result->code);
			}

			if (!property_exists($result, 'responseData')) {
				throw new \Exception('responseData is not exist in json response', 9);
			}

			return $result->responseData;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function createRequestHeader(string $json_body) : array {
		$code_verifier = $this->CreateCodeVerifier();
		$code_challenge = $this->CreateChalangeCode($code_verifier);
		$timestamp = $this->CreateTimestamp();
		$nonce = $this->CreateNonce($timestamp);

		$sign = $this->CreateSign($json_body, $nonce, $timestamp);
		$arr_header = [
			'Content-Type' => 'application/json',
			'appId' => __APP_ID,
			'nonce' => $nonce,
			'sign' => $sign,
			'timestamp' => $timestamp
		];

		return $arr_header;
	}

	
	/**
	 * untuk menampilkan request data yang dikirimkan (mode terminal)
	 */
	public function showDebugRequest(bool $value) : void {
		$this->showrequestdata = $value;
	}


	/**
	 * Generate barcode topup, untuk discan oleh customer di allo apps
	 * 
	 * parameter:
	 *		string topup_id
	 *		string storeId
	 *		string cashierId
	 *		int value
	 */
	public function generate_barcode_topup(object $parameter) : object {
		try {
			$api = $this->get_api_info(self::api_barcodetopup);
			$url = __BASE_URL . $api->url;

			// data yang akan dikirim
			$payload = new \stdClass;
			$payload->transactionNo = $this->CreateTransactionNo($api->id);
			$payload->requestData = (object) [
				'partnerReferenceNo' => $parameter->topup_id,
				'merchantId' => __APP_ID,
				'storeId' => $parameter->storeId,
				'cashierId' => $parameter->cashierId,
				'topUpAmount' => (object)[
					'value' => (string)number_format($parameter->value, 2, '.', ''),
					'currency' => 'IDR'
				]
			];

			// compose header request
			$json_body = json_encode($payload);
			$arr_header = $this->createRequestHeader($json_body);


			try {
				$response = $this->SendRequest($url, $json_body, $arr_header);
				$result = $this->processResponse($response);
				
				return $result;
			} catch (\Exception $ex) {
				throw $ex;
			}

		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	/**
	 * Cek status topup, 
	 * 
	 * parameter:
	 * 		string barcode (dihasilkan dari result saat generate barcode)
	 *		string reference (dihasilkan dari result saat generate barcode)
	 *		string storeId
	 *		string cashierId
	 *		int value
	 */	
	public function cek_status(object $parameter) : object {
		try {
			$api = $this->get_api_info(self::api_cekstatus);
			$url = __BASE_URL . $api->url;

			// data yang akan dikirim
			$payload = new \stdClass;
			$payload->transactionNo = $this->CreateTransactionNo($api->id);
			$payload->requestData = (object) [
				'barcode' => $parameter->barcode,
				'originalReferenceNo' => $parameter->referenceNo,
				'merchantId' => __APP_ID,
				'storeId' => $parameter->storeId,
				'cashierId' => $parameter->cashierId
			];

			// compose header request
			$json_body = json_encode($payload);
			$arr_header = $this->createRequestHeader($json_body);


			try {
				$response = $this->SendRequest($url, $json_body, $arr_header);
				$result = $this->processResponse($response);
				
				return $result;
			} catch (\Exception $ex) {
				throw $ex;
			}

		} catch (\Exception $ex) {
			throw $ex;
		}
	}	



}


