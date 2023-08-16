<?php namespace FGTA4;

ini_set("session.gc_maxlifetime", "65535");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
date_default_timezone_set('Asia/Jakarta');


define('__APPNAME', 'fgtapos');
define('__APPTITLE', 'TFI - POS');
define('__LOCALCLIENT_DIR', __DIR__);
define('__LOCALDB_DIR', __DIR__ . '/data');
define('__TEMP_DIR', '/mnt/ramdisk');


define('__COMPANY_NAME__', 'TFI');
define('__LOCAL_CURR',  'IDR');

// define('__MODULE_RELOAD_BUTTON', false);
// define('__DISABLE_PHPINFO', true);
// define('__DISABLE_APIINFO', true);

define('__TEMPLATE', 'fgta-pos');
define('__TEMPLATE_MAIN_HTML', 'postemplate.phtml');
define('__STARTMODULE', 'retail/pos/poscontainer');
define('__FGTA_LOGIN', 'retail/pos/poslogin');

// define('__DISABLE_PHPINFO', true);
// define('__DISABLE_APIINFO', true);


define('DB_CONFIG', [
	
	
	'KALISTADB' => [
		'DSN' => "mysql:host=172.18.10.34;dbname=kalistadb",
		'user' => "kalista",
		'pass' => "kalista23#!"
	],
	

        'KALISTAFS' => [
                'host' => '172.18.10.34',
                'port' => '5984',
                'protocol' => 'http',
                'username' => 'kalista',
                'password' => 'kalista23#!',
                'database' => 'kalistafs'             
        ],

	
	'TFIWEBBETA' => [
		'type'=>'mariadb',
		'DSN'=>'mysql:host=betadb.transfashion.id;dbname=infosol_tfi',
		'user'=>'john',
		'pass'=>'TFIxJakarta123!',
	],

 
	'DSR' => [
		'DSN' => "firebird:dbname=172.18.10.11:DSR.FDB",
		'user' => "SYSDBA",
		'pass' => "Modul@Oblongata"
	],	

	'FGTA2' => [
		'DSN' => "firebird:dbname=172.18.10.11:FGTA2.FDB",
		'user' => "SYSDBA",
		'pass' => "Modul@Oblongata"
	],	

	'FRM2_BACKUP' => [
		'DSN' => 'dblib:host=172.18.10.254;dbname=E_FRM2_BACKUP',
		'user' => 'sa',
		'pass' => 'rahasia'	
	],
	
	'FRM2_PROD' => [
		'DSN' => 'dblib:host=172.18.10.20;dbname=E_FRM2_MGP',
		'user' => 'transminer',
		'pass' => 'rahasiatfi2012!*'	
	],

]);





define('FGTA4_MAILER', [
	'NOREPLY' => [
		'host' => 'mail.transfashionindonesia.com',
		'port' => '587',
		'fromname' => 'Trans Fashion Indonesia',
		'email' => 'no-reply@transfashionindonesia.com',
		'username' => 'no-reply',
		'password' => 'tfi1901',
		'imap_inbox' => '{mail.transfashionindonesia.com:993/imap/ssl/novalidate-cert}INBOX',
		'setup' => function (&$mailer) {
			$mailer->SMTPKeepAlive = true;
			$mailer->Mailer = "smtp";
			$mailer->IsSMTP();
			$mailer->SMTPAuth = true;
			$mailer->SMTPSecure = "tls";
			$mailer->CharSet ='utf-8';
			$mailer->SMTPDebug = 0;
			$mailer->AuthType = "PLAIN";
			$mailer->SMTPOptions = array(
					'ssl' => array(
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true
				)
			);			
		}
	],
]);



$GLOBALS['MAINDB'] = 'KALISTADB';
$GLOBALS['MAINDBTYPE'] = 'mariadb';
$GLOBALS['MAIN_USERTABLE'] = 'kalistadb.fgt_user';
$GLOBALS['MAINFS'] = 'KALISTAFS';
$GLOBALS['MAINMAILER'] = 'KALISTAMAILER';
$GLOBALS['MAINTEMPLATE'] = 'fgtaerp';
$GLOBALS['TBDB'] = 'FRM2_PROD';
$GLOBALS['FGTADB'] = 'FGTA2';


