<?php
header("Access-Control-Allow-Origin: *");


function removeDirectory($path) {
 	$files = glob($path . '/*');
	foreach ($files as $file) {
		is_dir($file) ? removeDirectory($file) : unlink($file);
	}
	rmdir($path);
 	return;
}

function userRegistered($username) {
	if (file_exists("data/accounts/$username.json")) {
		return true;
	} else {
		return false;
	}
}

function generateApiToken($username) {
	$time = time();
	$token = bin2hex(random_bytes(16));
	while(file_exists("data/api-tokens/$token.json")) {
		$token = bin2hex(random_bytes(16));
	} 
	$apiTokenArray = array(
		"time" => $time,
		"username" => $username
	);
	if (!file_exists("data/api-tokens")) {
		mkdir("data/api-tokens", 0777, true);
  	}
	file_put_contents("data/api-tokens/$token.json", json_encode($apiTokenArray, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK));
	return $token;
}

function validateApiToken($token) {
	$time = time();
	if (!file_exists("data/api-tokens/$token.json")) {
		return false;
	} else {
		$tokenFile = file_get_contents("data/api-tokens/$token.json");
		$json = json_decode($tokenFile, true);
		if ($time > $json["time"] + 60 * 60 * 24 * 7) {
			// expires after 1 week - delete api token
			return false;
			unlink("data/api-tokens/$token.json");
		} else {
			$username = $json["username"];
			$apiTokenArray = array(
				"time" => $time,
				"username" => $username
			);
			file_put_contents("data/api-tokens/$token.json", json_encode($apiTokenArray, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK));
			return $username;
		}
	}
}

function addDomain($token, $domain) {
	$time = time();
	$validateApiToken = validateApiToken($token);
	$tokenFile = file_get_contents("data/api-tokens/$token.json");
	$json = json_decode($tokenFile, true);
	$username = $json["username"];
  	if ($validateApiToken === false) {
  		return "INVALID_TOKEN";
	} else if (empty($domain)) {
  		return "EMPTY_DOMAIN";
	} else if (!filter_var(gethostbyname($domain), FILTER_VALIDATE_IP)) {
    	return "INVALID_DOMAIN";
	} else {
		$domainArray = array(
			"time" => $time,
			"domain" => $domain
		);
		if (!file_exists("data/domains")) {
			mkdir("data/domains", 0777, true);
  		}
		file_put_contents("data/domains/$username.json", json_encode($domainArray, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK));
		return true;
	}
}

function followExists($username, $usernameToFollow) {
	if (file_exists("data/follows/$username/$usernameToFollow.json")) {
		return true;
	} else {
		return false;
	}
}

function followerExists($username, $follower) {
	if (file_exists("data/followers/$username/$follower.json")) {
		return true;
	} else {
		return false;
	}
}

function likeExists($username, $statusId) {
	if (file_exists("data/likes/$statusId/$username.json")) {
		return true;
	} else {
		return false;
	}
}

function getFollowerCount($username) {
	if (!userRegistered($username)) {
		return "NOT_REGISTERED";
	} else {
		foreach (glob("data/followers/$username/*.json", GLOB_BRACE) as $filename) {
			$file = basename($filename, ".json");
			$files[$file] = filemtime($filename);
		}
		return count($files);
	}
}

function getFollowingCount($username) {
	if (!userRegistered($username)) {
		return "NOT_REGISTERED";
	} else {
		foreach (glob("data/follows/$username/*.json", GLOB_BRACE) as $filename) {
			$file = basename($filename, ".json");
			$files[$file] = filemtime($filename);
		}
		return count($files);
	}
}

function getLikeCount($statusId) {
	$json = json_decode(file_get_contents("data/statuses/$statusId.json"), true);
	$username = $json["username"];
	if (!file_get_contents("data/statuses/$statusId.json")) {
		return "INVALID_ID";
	} else if (!userRegistered($username)) {
		return "NOT_REGISTERED";
	} else {
		foreach (glob("data/likes/$statusId/*.json", GLOB_BRACE) as $filename) {
			$file = basename($filename, ".json");
			$files[$file] = filemtime($filename);
		}
		return count($files);
	}
}


function getFollowers($username, $amount = 100) {
	if (!userRegistered($username)) {
		return "NOT_REGISTERED";
	} else {
    	$files = array();
    	foreach (glob("data/followers/$username/*.json", GLOB_BRACE) as $filename) {
    		$file = basename($filename, ".json");
        	$files[$file] = filemtime($filename);
    	}
    	arsort($files);
    	$newest = array_slice($files, 0, $amount);
    	foreach ($newest as $key => $value) {
    		$json = json_decode(file_get_contents("data/followers/$username/$key.json"), true);
    		$array[] = array(
				"username" => $key,
				"time" => $json["time"]
			);
		}
		if (!isset($array)) {
			return "NO_FOLLOWERS";
		} else {
			return json_encode($array, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
		}
	}
}

function follow($token, $usernameToFollow) {
	$time = time();
	$validateApiToken = validateApiToken($token);
	$tokenFile = file_get_contents("data/api-tokens/$token.json");
	$json = json_decode($tokenFile, true);
	$username = $json["username"];
  	if ($validateApiToken === false) {
  		return "INVALID_TOKEN";
  	} else if ($username === $usernameToFollow) {
  		return "SELF_FOLLOW";
  	} else if (getFollowingCount($username) >= 100) {
  		return "FOLLOW_LIMIT";
	} else {
		$followArray = array(
			"time" => $time
		);
		if (!file_exists("data/follows/$username")) {
  			mkdir("data/follows/$username", 0777, true);
  		}
		if (!file_exists("data/followers/$usernameToFollow")) {
  			mkdir("data/followers/$usernameToFollow", 0777, true);
  		}
		file_put_contents("data/follows/$username/$usernameToFollow.json", json_encode($followArray));
		file_put_contents("data/followers/$usernameToFollow/$username.json", json_encode($followArray));
		return true;
	}
}

function unfollow($token, $usernameToUnfollow) {
	$validateApiToken = validateApiToken($token);
	$tokenFile = file_get_contents("data/api-tokens/$token.json");
	$json = json_decode($tokenFile, true);
	$username = $json["username"];
	if ($validateApiToken === false) {
  	return "INVALID_TOKEN";
	} else if (!followExists($username, $usernameToUnfollow)) {
		return "NOT_FOLLOWED";
	} else {
		unlink("data/follows/$username/$usernameToUnfollow.json");
		unlink("data/followers/$usernameToUnfollow/$username.json");
		return true;
	}
}

function like($token, $statusId) {
	$time = time();
	$validateApiToken = validateApiToken($token);
	$tokenFile = file_get_contents("data/api-tokens/$token.json");
	$json = json_decode($tokenFile, true);
	$username = $json["username"];
	if ($validateApiToken === false) {
  	return "INVALID_TOKEN";
	} else {
		$likeArray = array(
			"time" => $time
		);
		if (!file_exists("data/likes/$statusId")) {
  			mkdir("data/likes/$statusId", 0777, true);
  		}
		file_put_contents("data/likes/$statusId/$username.json", json_encode($likeArray));
		return true;
	}
}

function unlike($token, $statusId) {
	$validateApiToken = validateApiToken($token);
	$tokenFile = file_get_contents("data/api-tokens/$token.json");
	$json = json_decode($tokenFile, true);
	$username = $json["username"];
	if ($validateApiToken === false) {
  	return "INVALID_TOKEN";
	} else if (!likeExists($username, $statusId)) {
		return "NOT_LIKED";
	} else {
		unlink("data/likes/$statusId/$username.json");
		return true;
	}
}

function failedLogin() {
	$time = time();
	$ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
	if (file_exists("data/failed-logins/$ip.json")) {
		$file = file_get_contents("data/failed-logins/$ip.json");
		$json = json_decode($file, true);
		if (count($json) < 5) {
			$json[] = array(
				"time" => $time
			);
		} else {
			$removeFirst = array_shift($json);
			$json[] = array(
				"time" => $time
			);
		}
	} else {
		$json[] = array(
			"time" => $time
		);
	}
	if (!file_exists("data/failed-logins")) {
		mkdir("data/failed-logins", 0777, true);
	}
	file_put_contents("data/failed-logins/$ip.json", json_encode($json, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK));
}

function lockoutCheck() {
	$time = time();
	$ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
	if (file_exists("data/failed-logins/$ip.json")) {
		$file = file_get_contents("data/failed-logins/$ip.json");
        $json = json_decode($file, true);
        if (count($json) < 5) {
        	return false;
        } else {
        	$failed = array();
        	foreach ($json as $value) {
        		// 5 minutes.. I think :)
        		if ($time < $value["time"] + 300) {
        			$failed[] = $time;
        		}
        	}
        	if (count($failed) === 5) {
        		return true;
        	} else {
        		return false;
        	}
        }
	} else {
		return false;
	}
}

function register($username, $password) {
	$reservedUsernames = array(
		"tos",
		"terms",
		"termsofservice",
		"privacy",
		"privacypolicy",
		"verification",
		"everyone",
		"all",
		"whispers",
		"whisper",
		"feed",
		"followers",
		"following",
		"likes"
	);
	if (empty($username)) {
		return "EMPTY_USERNAME";
	} else if (!ctype_alnum($username)) { 
		return "INVALID_USERNAME";
	} else if (in_array($username, $reservedUsernames)) {
		return "RESERVED_USERNAME";
	} else if (strlen($username) < 4) {
		return "SHORT_USERNAME";
	} else if (strlen($username) > 20) {
		return "LONG_USERNAME";
	} else if (userRegistered($username)) {
		return "USERNAME_EXISTS";
	} else if (empty($password)) {
		return "EMPTY_PASSWORD";
	} else if (strlen($password) < 8) {
		return "SHORT_PASSWORD";
	} else if (strlen($password) > 1000) {
		return "LONG_PASSWORD";
	} else {
		$passwordHash  = password_hash(hash("sha256", $password), PASSWORD_DEFAULT);
		$accountArray = array(
			"password" => $passwordHash
		);
		if (!file_exists("data/accounts")) {
  			mkdir("data/accounts", 0777, true);
  		}
		file_put_contents("data/accounts/$username.json", json_encode($accountArray, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK));
		$generateApiToken = generateApiToken($username);
		return $generateApiToken;
	}
}

function login($username, $password) {
	if (lockoutCheck()) {
		return "LOGIN_LOCKOUT";
	} else if (empty($username)) {
		return "EMPTY_USERNAME";
	} else if (!userRegistered($username)) {
		return "NOT_REGISTERED";
	} else if (empty($password)) {
		return "EMPTY_PASSWORD";
	} else {
		$accountFile = file_get_contents("data/accounts/$username.json");
        $json = json_decode($accountFile, true);
        $passwordHash = $json["password"];
        // legacy password support
        if (password_verify($password, $passwordHash)) {
        	$loggedIn = true;
        } else if (password_verify(hash("sha256", $password), $passwordHash)) {
        	$loggedIn = true;
        } else {
        	failedLogin();
        	return "INCORRECT_PASSWORD";
        }
        if ($loggedIn === true) {
        	if (password_needs_rehash($passwordHash, PASSWORD_DEFAULT)) {
          		$passwordRehash = password_hash(hash("sha256", $password), PASSWORD_DEFAULT);
          		$accountArray = array(
          			"password" => $passwordRehash
          		);
          		file_put_contents("data/accounts/username.json", json_encode($accountArray, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK));	
         	}
         	$generateApiToken = generateApiToken($username);
			return $generateApiToken;
		}
	}
}

function updatePic($token, $url) {
	$time = time();
	$validateApiToken = validateApiToken($token);
	$username = $validateApiToken;
	if ($validateApiToken === false) {
  		return "INVALID_TOKEN";
	} else if (empty($url)) {
		return "EMPTY_URL";
	} else if (!userRegistered($username)) {
		return "NOT_REGISTERED";
	} else if (!filter_var($url, FILTER_VALIDATE_URL)) {
		return "INVALID_URL";
	} else {
		$imgInfo = getimagesize($url);
		if (stripos($imgInfo["mime"], "image/") === false) {
    		return "INVALID_IMAGE";
		} else {
			$picArray = array(
				"time" => $time,
				"url" => $url
			);
			if (!file_exists("data/profile-pics")) {
				mkdir("data/profile-pics", 0777, true);
			}
			file_put_contents("data/profile-pics/$username.json", json_encode($picArray, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK));
			return true;
		}
	}
}

function getPic($username) {
	if (empty($username)) {
		return false;
	} else if (!userRegistered($username)) {
		return false;
	} else if (!file_exists("data/profile-pics/$username.json")) {
		return false;
	} else {
		$picFile = file_get_contents("data/profile-pics/$username.json");
        $json = json_decode($picFile, true);
        return $json["url"];
	}
}

function updateHeader($token, $url) {
	$time = time();
	$validateApiToken = validateApiToken($token);
	$username = $validateApiToken;
	if ($validateApiToken === false) {
  		return "INVALID_TOKEN";
	} else if (empty($url)) {
		return "EMPTY_URL";
	} else if (!userRegistered($username)) {
		// log them out...
		return "NOT_REGISTERED";
	} else if (!filter_var($url, FILTER_VALIDATE_URL)) {
		return "INVALID_URL";
	} else {
		$imgInfo = getimagesize($url);
		if (stripos($imgInfo["mime"], "image/") === false) {
    		return "INVALID_IMAGE";
		} else {
			$headerArray = array(
				"time" => $time,
				"url" => $url
			);
			if (!file_exists("data/profile-headers")) {
				mkdir("data/profile-headers", 0777, true);
			} 
			file_put_contents("data/profile-headers/$username.json", json_encode($headerArray, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK));
			return true;
		}
	}
}

function getHeader($username) {
	if (empty($username)) {
		return false;
	} else if (!userRegistered($username)) {
		return false;
	} else if (!file_exists("data/profile-headers/$username.json")) {
		return false;
	} else {
		$headerFile = file_get_contents("data/profile-headers/$username.json");
        $json = json_decode($headerFile, true);
        return $json["url"];
	}
}

$mods = array(
	"dalton",
	"safe",
	"henrik",
	"daniel"
);

function status($token, $status) {
	global $mods;
  	$time = time();
  	$statusId = bin2hex(random_bytes(16));
	while(file_exists("data/statuses/$statusId.json")) {
		$statusId = bin2hex(random_bytes(16));
	} 
  	if (!file_exists("data/statuses")) {
  		mkdir("data/statuses", 0777, true);
	  }
  $validateApiToken = validateApiToken($token);
  $tokenFile = file_get_contents("data/api-tokens/$token.json");
  $json = json_decode($tokenFile, true);
  $username = $json["username"];
  if ($validateApiToken === false) {
  	return "INVALID_TOKEN";
  } else if ($username === "avengetech") {
  	return "POST_BAN";
  } else {
  	// They executed a command.
  	if ($status[0] === "/") {
  		$command = explode(" ", $status);
  		switch ($command[0]) {
  			// Update Pic
    		case "/up":
        		$url = $command[1];
  				$result = updatePic($token, $url);
  				return $result;
        		break;
        	// Update Header
    		case "/uh":
    			$url = $command[1];
				$result = updateHeader($token, $url);
				return $result;
        		break;
        	// Delete Pic
    		case "/dp":
    			if ($command[1] === null) {
    				$picToDelete = $username;
    			} else if (in_array($username, $mods) && $command[1] !== "dalton") {
    				$picToDelete = $command[1];
    			} else {
    				//
    			}
				unlink("data/profile-pics/$picToDelete.json");
				removeDirectory("data/profile-pics/$picToDelete");
				return true;
        		break;
        	// Delete Header
    		case "/dh":
    			if ($command[1] === null) {
    				$headerToDelete = $username;
    			} else if (in_array($username, $mods) && $command[1] !== "dalton") {
    				$headerToDelete = $command[1];
    			} else {
    				//
    			}
				unlink("data/profile-headers/$headerToDelete.json");
				removeDirectory("data/profile-headers/$headerToDelete");
				return true;
        		break;
        	// Add Domain
        	case "/ad":
        		$domain = strtolower($command[1]);
				$result = addDomain($token, $domain);
				return $result;
			// Invalid Command
    		default:
       			return "INVALID_COMMAND";
		}
	// This is a status update 😊
	} else {
		if (empty($status) || ctype_space($status)) {
  			return "EMPTY_STATUS";
  		} else if (strlen($status) > 1000) {
  			return "LONG_STATUS";
  		} else {
  		 	$statusArray = array(
  		 		"username" => $username,
  		 		"status" => $status,
  		 		"time" => $time
  			);
  			file_put_contents("data/statuses/$statusId.json", json_encode($statusArray, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK));
  			$userStatusesArray = array(
				"time" => $time
			);
			if (!file_exists("data/user-statuses/$username")) {
  				mkdir("data/user-statuses/$username", 0777, true);
  			}
			file_put_contents("data/user-statuses/$username/$statusId.json", json_encode($userStatusesArray));
    		return true;
       	}
      }
  }
}
?>
