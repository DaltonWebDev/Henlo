<?php
header("Content-type: application/json; charset=utf-8");
require "functions.php";
$apiActions = array(
	"login",
	"register"
);
$action = strtolower($_POST["action"]);
$username = strtolower($_POST["username"]);
$password = $_POST["password"];
if (!isset($action, $username, $password)) {
	$error = "MISSING_PARAMETER";
} else if (!in_array($action, $apiActions)) {
	$error = "INVALID_ACTION";
} else {
	if ($action === "login") {
		$result = login($username, $password);
		if (strlen($result) !== 32) {
			$error = $result;
		} else {
			$error = false;
			$token = $result;
		}
	} else if ($action === "register") {
		$result = register($username, $password);
		if (strlen($result) !== 32) {
			$error = $result;
		} else {
			$error = false;
			$token = $result;
		}
	} else {
		// IDK
	}
}
$array = array(
	"error" => $error,
	"token" => $token
);
echo json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
?>