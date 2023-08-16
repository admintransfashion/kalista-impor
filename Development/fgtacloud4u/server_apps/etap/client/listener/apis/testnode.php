<?php

$descriptorspec = array(
   0 => array("pipe", "r"),  
   1 => array("pipe", "w"), 
//    2 => array("file", dirname(__FILE__).'/output.txt', "a") 
);


$i = 0;

$i++;
$data = (object)[
	"i" => $i,
	"data" => (object)[
		"nama" => "agung nugroho",
		"alamat" => "jakarta"
	]
];


$cwd = dirname(__FILE__);
$env = array('data' => json_encode($data));

$process = proc_open('node settopouchdb.js', $descriptorspec, $pipes, $cwd, $env);

if (is_resource($process)) {
	fwrite($pipes[0], '<?php print_r($_ENV); ?>');
	fclose($pipes[0]);
	$content = stream_get_contents($pipes[1]);
	fclose($pipes[1]);

	$return_value = proc_close($process);
	echo "command returned $return_value\n";
	echo $content;
}


