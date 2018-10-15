<?php
header("Content-type: application/json; charset=utf-8");
require "functions.php";
setlocale(LC_ALL,'C.UTF-8');
$error = false;
$sortOptions = [
	"following",
	"everything"
];
$token = $_GET["token"];
$validateApiToken = validateApiToken($token);
$username = $validateApiToken;
if (isset($_GET["sort"])) {
	if (in_array($_GET["sort"], $sortOptions)) {
		$sort = strtolower($_GET["sort"]);
	} else {
		$error = "INVALID_SORT";
	}
} else {
	$sort = "following";
}
if ($error === false || $error === "INVALID_TOKEN") {
	$files = array();
	foreach (glob("data/statuses/*.json", GLOB_BRACE) as $filename) {
		$file = basename($filename, ".json");
		$files[$file] = filemtime($filename);
	}
	arsort($files);
	$newest = array_slice($files, 0, 500);
	foreach ($newest as $key => $value) {
		$statusJson = json_decode(file_get_contents("data/statuses/$key.json"), true);
		$statusUsername = $statusJson["username"];
		$statusId = $key;
		$pic = getPic($statusUsername);
		$header = getHeader($statusUsername);
		if (!userRegistered($username)) {
			$followed = null;
			$liked = null;
		} else {
			$followed = followExists($username, $statusUsername);
			$liked = likeExists($username, $statusId);
		}
		$likes = getLikeCount($statusId);
		if ($likes > 0) {
			$files2 = array();
			foreach (glob("data/likes/$statusId/*.json", GLOB_BRACE) as $filename2) {
				$file2 = basename($filename2, ".json");
				$files2[$file2] = filemtime($filename2);
			}
			arsort($files2);
			$likers = array_slice($files2, 0, 200);
		} else {
			$likers = null;
		}
		if (file_exists("data/domains/$statusUsername.json")) {
			$domainJson = json_decode(file_get_contents("data/domains/$statusUsername.json"), true);
			$domain = $domainJson["domain"];
			$verifiedUsers = file_get_contents("http://$domain/henlo-verified.txt");
			if ($verifiedUsers === false) {
				$verified = false;
			} else {
				$verifiedArray = explode(",", $verifiedUsers);
				if (in_array($statusUsername, $verifiedArray)) {
					$verified = true;
				} else {
					$verified = false;
				}
			}
		} else {
			$domain = false;
			$verified = false;
		}
		$time = $statusJson["time"];
		$status = htmlspecialchars($statusJson["status"], ENT_QUOTES, "UTF-8");
		// Announcement
		if ($sort !== "following" && $verified === true && $domain === "henlo.xyz" && strpos(strtolower($status), "@everyone") !== false && $liked === false) {
			$announcementsArray[] = array(
				"time" => $time,
				"id" => $statusId,
				"username" => $statusUsername,
				"pic" => $pic,
				"header" => $header,
				"status" => $status,
				"domain" => $domain,
				"verified" => $verified,
				"followed" => $followed,
				"likes" => $likes,
				"liked" => $liked,
				"likers" => $likers,
			);
		// Following
		} else if ($sort === "following" && followExists($username, $statusUsername)) {
			// Mentioned by a user that you're following.
			if ($username !== $statusUsername && strpos(strtolower($status), "@$username") !== false && followExists($username, $statusUsername) && $liked === false) {
				$mentionsArray[] = array(
					"time" => $time,
					"id" => $statusId,
					"username" => $statusUsername,
					"pic" => $pic,
					"header" => $header,
					"status" => $status,
					"domain" => $domain,
					"verified" => $verified,
					"followed" => $followed,
					"likes" => $likes,
					"liked" => $liked,
					"likers" => $likers,
				);
			} else {
				$statusesArray[] = array(
					"time" => $time,
					"id" => $statusId,
					"username" => $statusUsername,
					"pic" => $pic,
					"header" => $header,
					"status" => $status,
					"domain" => $domain,
					"verified" => $verified,
					"followed" => $followed,
					"likes" => $likes,
					"liked" => $liked,
					"likers" => $likers,
				);
			}
		// Everything Else...
		} else if ($sort !== "following" && strpos(strtolower($status), "#bot") === false) {
			// Mentioned by a user that you aren't following.
			if ($username !== $statusUsername && strpos(strtolower($status), "@$username") !== false && $liked === false) {
				$mentionsArray[] = array(
					"time" => $time,
					"id" => $statusId,
					"username" => $statusUsername,
					"pic" => $pic,
					"header" => $header,
					"status" => $status,
					"domain" => $domain,
					"verified" => $verified,
					"followed" => $followed,
					"likes" => $likes,
					"liked" => $liked,
					"likers" => $likers,
				);
			} else {
				$statusesArray[] = array(
					"time" => $time,
					"id" => $statusId,
					"username" => $statusUsername,
					"pic" => $pic,
					"header" => $header,
					"status" => $status,
					"domain" => $domain,
					"verified" => $verified,
					"followed" => $followed,
					"likes" => $likes,
					"liked" => $liked,
					"likers" => $likers,
				);
			}
		} else {
			//
		}
	}
}
if (isset($announcementsArray, $mentionsArray)) {
	$result = array_merge($announcementsArray, $mentionsArray, $statusesArray);
} else if (isset($announcementsArray)) {
	$result = array_merge($announcementsArray, $statusesArray);
} else if (isset($mentionsArray)) {
	$result = array_merge($mentionsArray, $statusesArray);
} else {
	$result = $statusesArray;
}
$outputArray = array(
	"error" => $error,
	"statuses" => $result
);
echo json_encode($outputArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
?>