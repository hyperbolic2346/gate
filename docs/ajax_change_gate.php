<?php

include('config.inc');
include('lib/common.php');

session_start();

function valid_id() {
	if (!isset($_REQUEST['id'])) {
		return false;
	}

	$id = $_REQUEST['id'];

	$wilson = $_SESSION['user']['operate_wilson_gate'];
	$brigman = $_SESSION['user']['operate_brigman_gate'];

	if ($id == 0 && $wilson) {
		return true;
	} else if ($id == 1 && $brigman) {
		return true;
	} else {
		return false;
	}
}

if (!isset($_SESSION['user']) || !valid_id() || (!isset($_REQUEST['release']) && !isset($_REQUEST['hold']) && !isset($_REQUEST['open']) && !isset($_REQUEST['close']))) {
        exit();
}

$mysqli = new mysqli($sql_host, $sql_user, $sql_pass, $sql_db);

if (isset($_REQUEST['id']) && $_REQUEST['id'] == 0 && isset($_REQUEST['release'])) {
	$query = "SELECT display_name, access_level FROM event_log LEFT JOIN users ON event_log.user_id = users.user_id WHERE event_type=2 ORDER BY event_time DESC LIMIT 1";
	$result = $mysqli->query($query) or die('unable to execute query - '.$query);
	if ($result) {
		$row = $result->fetch_assoc();
		if ($row['access_level'] < $_SESSION['user']['access_level']) {
			echo 'No permission to release gate when '.$row['display_name'].' holds it!';
			exit();
		}
	}
}

define('SOCKET_COMMUNICATION', false);

if (SOCKET_COMMUNICATION) {
	// Create a new socket
	$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

	$arduino_ip = '10.0.1.25';
	$arduino_port = 8888;

	if (isset($_REQUEST['release'])) {
		if ($_REQUEST['id'] == 0) {
			$event_type = EVENT_TYPE_RELEASE_WILSON_GATE;
		} else {
			$event_type = EVENT_TYPE_RELEASE_BRIGMAN_GATE;
		}
		$request = 'unhold:'.$_REQUEST['id'];
	} else if (isset($_REQUEST['hold'])) {
		if ($_REQUEST['id'] == 0) {
			$event_type = EVENT_TYPE_HOLD_WILSON_GATE;
		} else {
			$event_type = EVENT_TYPE_HOLD_BRIGMAN_GATE;
		}
		$request = 'hold:'.$_REQUEST['id'];
	} else if (isset($_REQUEST['open'])) {
		if ($_REQUEST['id'] == 0) {
			$event_type = EVENT_TYPE_OPEN_WILSON_GATE;
		} else {
			$event_type = EVENT_TYPE_OPEN_BRIGMAN_GATE;
		}
		$request = 'open:'.$_REQUEST['id'];
	}

	socket_sendto($sock, $request, strlen($request), 0, $arduino_ip, $arduino_port);
	socket_recvfrom($sock, $buf, 4096, 0, $ardiuno_ip, $arduino_port);

	// Close
	socket_close($sock);
} else {
	if (isset($_REQUEST['release'])) {
		if ($_REQUEST['id'] == 0) {
			$event_type = EVENT_TYPE_RELEASE_WILSON_GATE;
		} else {
			$event_type = EVENT_TYPE_RELEASE_BRIGMAN_GATE;
		}
		$request = 'http://10.0.1.25/gate/'.$_REQUEST['id'].'/release';
	} else if (isset($_REQUEST['hold'])) {
		if ($_REQUEST['id'] == 0) {
			$event_type = EVENT_TYPE_HOLD_WILSON_GATE;
		} else {
			$event_type = EVENT_TYPE_HOLD_BRIGMAN_GATE;
		}
		$request = 'http://10.0.1.25/gate/'.$_REQUEST['id'].'/hold';
	} else if (isset($_REQUEST['open'])) {
		if ($_REQUEST['id'] == 0) {
			$event_type = EVENT_TYPE_OPEN_WILSON_GATE;
		} else {
			$event_type = EVENT_TYPE_OPEN_BRIGMAN_GATE;
		}
		$request = 'http://10.0.1.25/gate/'.$_REQUEST['id'].'/open';
	}

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $request);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $buf = curl_exec($curl);
}

$result = $mysqli->query("INSERT into event_log set user_id = '".$_SESSION['user']['user_id']."', event_type = '".$event_type."'");

// parse the response
$gates = json_decode($buf, true);
echo $gates['status'];

?>
