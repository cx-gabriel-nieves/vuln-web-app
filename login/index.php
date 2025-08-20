<?php
require __DIR__ . '/../vendor/autoload.php';

use lfkeitel\phptotp\{Base32,Totp};

$user_has_session = isset($_COOKIE["PHPSESSID"]) && !empty($_COOKIE["PHPSESSID"]);
$split_url = explode("/", strtok($_SERVER["REQUEST_URI"], "?"));
$auth_type = isset($split_url[2]) ? strtolower($split_url[2]) : NULL;

if (isset($_GET["logout"])) {
	session_start();
	$_SESSION["logged_in"] = false;
	session_destroy();
	setcookie("PHPSESSID", '', time() - 42000, "/");
	header("location: /"); 
}

$creds = [
	"carlos" => "hunter2",
];

if (isset($_POST) && !empty($_POST)) {
$auth_failure = NULL;
while ($auth_failure == NULL) {
	if (!isset($creds[$_POST["username"]])) {
		$auth_failure = "INVALID_USERNAME";
		break;
	}

	if ($creds[$_POST["username"]] != $_POST["password"]) {
		$auth_failure = "INVALID_PASSWORD";
		break;
	}

        switch ($auth_type) {
	        case "mfa":
        	        $secret = "DAST-TEST";
                	$key = (new Totp('sha1'))->GenerateToken($secret);
			if ($_POST["mfa"] != $key) {
				$auth_failure = "INVALID_MFA";
                        }
                        break;
	}

	session_start();
	session_regenerate_id();
	$_SESSION["logged_in"] = true;
	header("location: /");
	die;
}

if ($auth_failure) {
	header("location: /login?bad=$auth_failure");
	setcookie("PHPSESSID", '', time() - 42000, "/");
	die;
}
}

if ($user_has_session) {
	session_destroy();
	setcookie("PHPSESSID", '', time() - 42000, "/");
}

?><!DOCTYPE html>
<html>
	<head>
		<title>Test Form</title>
		<style>
		html, body { width: 100%; height: 100%; margin: 0; padding: 0; }
		#container { display: flex; height: 100%; justify-content: center; align-items: center; align-content: center; }
		</style>
	</head>
	<body>
		<?php if ($auth_type == "mfa") { ?>
		<div style="position: fixed; top: 5px; left: 5px; border: 1px solid black; padding: 5px">
			<details>
				<summary>MFA Details</summary>
				<div style="text-align: center">
					<div><img src="/login/mfa-qr-lab3.png" alt="otpauth://totp/DAST%20Onboarding%20Test?secret=<?= Base32::encode("DAST-TEST") ?>&issuer=Checkmarx"></div>
					<textarea style="width: 300px; height: 50px; resize: none" readonly>otpauth://totp/DAST%20Onboarding%20Test?secret=<?= Base32::encode("DAST-TEST") ?>&issuer=Checkmarx</textarea>
				</div>
			</details>
		</div>
		<?php } ?>
		<div id="container">
			<?php if (!$_SESSION["logged_in"]) { ?>
			<form action="" method="post">
				<div>Username <input type="text" id="username" name="username"></div>
				<div>Password <input type="password" id="password" name="password"></div>
				<?php if ($auth_type == "mfa") { ?><div>MFA <input type="text" id="mfa" name="mfa" pattern="^[0-9]{6}$" style="width: 80px"></div><?php } ?>
				<input type="submit" value="Log In">
				<input type="button" value="Click to get credentials" onclick="alert(atob('VXNlcm5hbWU6IGNhcmxvcwpQYXNzd29yZDogaHVudGVyMg=='))">

				<?php if ($auth_type != "mfa") { ?><br><br><a href="/login/mfa">Use MFA</a><?php } ?>
			</form>

			<?php } else { ?>
			<div>
				<div>You are logged in</div>
				<div><a href="?logout">Log Out</a></div>
			</div>
			<?php } ?>
		</div>
	</body>
</html>
