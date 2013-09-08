<?php

include('config.inc');

session_start();

if (!isset($_SESSION['user'])) {
        exit();
}

$wilson = $_SESSION['user']['operate_wilson_gate'];
$brigman = $_SESSION['user']['operate_brigman_gate'];

$mysqli = new mysqli($sql_host, $sql_user, $sql_pass, $sql_db);

if ($wilson && $brigman) {
	$request = 'status:*';
} else if ($wilson) {
	$request = 'status:0';
} else if ($brigman) {
	$request = 'status:1';
} else {
  exit();
}

// Create a new socket
$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>1, "usec"=>0));

$arduino_ip = '10.0.1.25';
$arduino_port = 8888;

if (!socket_sendto($sock, $request, strlen($request), 0, $arduino_ip, $arduino_port)) {
	$buf = "{Unable to communicate with gate!}";
} else {
	socket_recvfrom($sock, $buf, 4096, 0, $ardiuno_ip, $arduino_port);
}

// Close
socket_close($sock);

// parse the response
$gates = json_decode($buf, true);
if ($wilson && $gates[0]['hold_state'] == 'HELD BY US') {
	$query = "SELECT event_time, display_name FROM event_log LEFT JOIN users ON event_log.user_id = users.user_id WHERE event_type>0 AND event_type <= 3 ORDER BY event_time DESC LIMIT 1";
	$result = $mysqli->query($query) or die("Unable to query database - $query");
	if ($result && $row = $result->fetch_assoc()) {
		$wilson_info = date('l g:ia', strtotime($row['event_time'])).' - '.$row['display_name'];
	}
	$gates[0]['info'] = $wilson_info;
}
if ($brigman && $gates[1]['hold_state'] == 'HELD BY US') {
	$query = "SELECT event_time, display_name FROM event_log LEFT JOIN users ON event_log.user_id = users.user_id WHERE event_type > 3 AND event_type <= 6 ORDER BY event_time DESC LIMIT 1";
	if ($result && $row = $result->fetch_assoc()) {
		$brigman_info = date('l g:ia', strtotime($row['event_time'])).' - '.$row['display_name'];
	}
	$gates[1]['info'] = $brigman_info;
}
echo json_encode($gates);

?>
