<?php
$url = isset($_GET["url"]) ? $_GET["url"] : null;
if (!isset($url)) {
	die("EMPTY_URL");
} else if (!filter_var($url, FILTER_VALIDATE_URL)) {
	die("INVALID_URL");
} else {
	$imgInfo = getimagesize($url);
	if (stripos($imgInfo["mime"], "image/") === false) {
    	die("INVALID_IMAGE");
	} else {
		header("Content-type: " . $imgInfo["mime"]);
		header("Cache-Control: max-age=31536000, public");
		readfile($url);
	}
}
?>