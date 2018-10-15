<?php
header("Content-type: application/json; charset=utf-8");
require "functions.php";
$username = $_GET["username"];
$token = $_GET["token"];
$validateApiToken = validateApiToken($token);
$key = $validateApiToken;
if (!isset($username)) {
	$error = "EMPTY_USERNAME";
} else if (!userRegistered($username)) {
	$error = "NOT_REGISTERED";
} else {
	$error = false;
	$pic = getPic($username);
	$header = getHeader($username);
	if (file_exists("data/domains/$username.json")) {
		$domainJson = json_decode(file_get_contents("data/domains/$username.json"), true);
		$domain = $domainJson["domain"];
		$verifiedUsers = file_get_contents("http://$domain/henlo-verified.txt");
		if ($verifiedUsers === false) {
			$verified = false;
		} else {
			$verifiedArray = explode(",", $verifiedUsers);
			if (in_array($username, $verifiedArray)) {
				$verified = true;
			} else {
				$verified = false;
			}
		}
	} else {
		$domain = false;
		$verified = false;
	}
	$followers = getFollowerCount($username);
	$following = getFollowingCount($username);
	if ($key === false) {
		$followed = null;
	} else {
		if (followExists($key, $username)) {
			$followed = true;
		} else {
			$followed = false;
		}
	}
	$files2 = array();
	foreach (glob("data/likes/$username/*.json", GLOB_BRACE) as $filename2) {
		$file2 = basename($filename2, ".json");
		$files2[$file2] = filemtime($filename2);
	}
	arsort($files2);
}
$outputArray = array(
	"error" => $error,
	"username" => $username,
	"pic" => $pic,
	"header" => $header,
	"domain" => $domain,
	"verified" => $verified,
	"followers" => $followers,
	"following" => $following,
	"followed" => $followed,
);
echo json_encode($outputArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
?>