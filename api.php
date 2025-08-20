<?php
$logged_in = false;
$auth_header = isset($_SERVER["HTTP_AUTHORIZATION"]) ? $_SERVER["HTTP_AUTHORIZATION"] : NULL;
if ($auth_header) {
	$bearer = preg_match("/^bearer /i", $_SERVER["HTTP_AUTHORIZATION"]) ? $_SERVER["HTTP_AUTHORIZATION"] : NULL;
}
if ($bearer) {
	$bearer_token = preg_replace("/^bearer /i", "", $_SERVER["HTTP_AUTHORIZATION"]);
	$decoded_token = base64_decode($bearer_token);
}

if ($decoded_token == "test")
	$logged_in = true;

switch (strtok($_SERVER["REQUEST_URI"], "?")) {
	case "/api/whoami":
		header("Content-type: application/json");
		echo json_encode(["code" => 0, "authenticated" => $logged_in ]);
		break;

	case "/api/elevated":
		header("Content-type: application/json");
		if ($logged_in) {
			echo json_encode(["code" => 0, "message" => "You are elevated"]);
		} else {
			echo json_encode(["code" => 1, "message" => "You are not elevated"]);
		}
		break;

	case "/api/repeat":
		header("Content-type: application/json");
		if ($logged_in) {
			if ($_GET["item"]) {
	                        echo json_encode(["code" => 0, "message" => $_GET["item"]]);
			} else {
				echo json_encode(["code" => 2, "message" => "Specify an item with ?item param"]);
			}
                } else {
                        echo json_encode(["code" => 1, "message" => "You are not logged in"]);
                }
		break;

	case "/api/redirect":
		if (isset($_GET["url"])) {
			header("location: " . $_GET["url"]);
		} else {
			header("Content-type: application/json");
		        echo json_encode(["code" => 1, "message" => "Specify a url with ?url param"]);
			http_response_code(400);
		}
		break;

	default:
		http_response_code(400);
		echo json_encode(["code" => 1, "errorMsg" => "No endpoint set"]);
		break;
}
