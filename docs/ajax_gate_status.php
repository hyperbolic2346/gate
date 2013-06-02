<?php

include('config.inc');

session_start();

if (!isset($_SESSION['user'])) {
        exit();
}

$wilson = $_SESSION['user']['operate_wilson_gate'];
$brigman = $_SESSION['user']['operate_brigman_gate'];

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
//	$gates = json_decode($buf, true);
echo $buf;		

?>
