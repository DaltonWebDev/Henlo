<?php
$time = time();
$files = array();
foreach (glob("data/api-keys/*.json", GLOB_BRACE) as $filename) {
	$file = basename($filename, ".json");
	$files[$file] = filemtime($filename);
}
arsort($files);
$tokens = array_slice($files, 0, 1000);
foreach ($tokens as $key => $value) {
	$json = json_decode(file_get_contents("data/api-keys/$key.json"), true);
	$delete = array();
	if ($time > $json["time"] + 60 * 60 * 24 * 7) {
		$delete[] = $file;
	}
}
echo count($delete);
?>