<?php
header("Content-type: application/json; charset=utf-8");
require "functions.php";
$token = $_POST["token"];
$status = $_POST["status"];
if (!isset($token, $status)) {
	$error = "MISSING_PARAMETER";
} else {
	$result = status($token, $status);
	if ($result !== true) {
		$error = $result;
	} else {
		$error = false;
	}
}
$array = array(
	"error" => $error
);
echo json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
?>