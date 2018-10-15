<?php
header("Content-type: application/json; charset=utf-8");
require "functions.php";
$token = $_GET["token"];
$validateApiToken = validateApiToken($token);
$username = $validateApiToken;
if ($validateApiToken === false) {
	$error = "INVALID_TOKEN";
} else if (!userRegistered($username)) {
	$error = "NOT_REGISTERED";
} else {
	$error = false;
	$getPp = getPic($username);
	$getPh = getHeader($username);
	if ($getPp === false) {
		$pp = false;
	} else {
		$pp = $getPp;
	}
	if ($getPh === false) {
		$ph = false;
	} else {
		$ph = $getPh;
	}
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
	$likes = getLikeCount($username);
}
if ($error !== false) {
	$outputArray = array(
		"error" => $error
	);
} else {
	$outputArray = array(
		"error" => false,
		"username" => $username,
		"pic" => $pp,
		"header" => $ph,
		"domain" => $domain,
		"verified" => $verified,
		"followers" => $followers,
		"following" => $following,
		"likes" => $likes
	);
}
echo json_encode($outputArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
?>