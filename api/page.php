<?php
header("Content-type: application/json; charset=utf-8");
$name = isset($_GET["name"]) ? $_GET["name"] : null;
$html = false;
if (!isset($name)) {
	$error = "MISSING_NAME";
} else if (!file_exists("pages/$name.html")) {
	$error = "HTTP_404";
} else {
	$error = false;
	$html = file_get_contents("pages/$name.html");
}
$apiResponse = array(
	"error" => $error,
	"html" => $html
);
echo json_encode($apiResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK); 
?>