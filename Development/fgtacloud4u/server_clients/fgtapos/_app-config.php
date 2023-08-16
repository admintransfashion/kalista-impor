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


define('DB_CONFIG', [
	
	// Database utama mariadb
	'KALISTADBLOCAL' => [
		'DSN' => "mysql:host=fgtadb;dbname=kalistadblocal",
		'user' => "root",
		'pass' => ""
	],

	
	// Object storage couchdb
	'KALISTAFSLOCAL' => [
		'host' => 'fgtafs',
		'port' => '5984',
		'protocol' => 'http',
		'username' => 'admin',
		'password' => 'rahasia',
		'database' => 'kalistafslocal'		
	],


]);



$GLOBALS['MAINDB'] = 'KALISTADBLOCAL';
$GLOBALS['MAINDBTYPE'] = 'mariadb';
$GLOBALS['MAIN_USERTABLE'] = 'kalistadblocal.fgt_user';



