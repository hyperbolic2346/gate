<?php

// put full path to Smarty.class.php
require(realpath(dirname(__FILE__).'/../lib/Smarty/Smarty.class.php'));
include('config.inc');
include('lib/common.php');
$smarty = new Smarty();

$smarty->setTemplateDir(realpath(dirname(__FILE__).'/../smarty/templates'));
$smarty->setCompileDir(realpath(dirname(__FILE__).'/../smarty/templates_c'));
$smarty->setCacheDir(realpath(dirname(__FILE__).'/../smarty/cache'));
$smarty->setConfigDir(realpath(dirname(__FILE__).'/../smarty/configs'));

session_start();
//unset($_SESSION['user']);
$mysqli = NULL;

if (isset($_REQUEST['login_name'])) {
	// connect to database
	$mysqli = new mysqli($sql_host, $sql_user, $sql_pass, $sql_db);
	$result = $mysqli->query("SELECT * from users WHERE username = '".$_REQUEST['login_name']."' AND password = '". md5($_REQUEST['login_pw']). "'");
	if ($result && $row = $result->fetch_assoc()) {
		$_SESSION['user'] = $row;
		$result = $mysqli->query("INSERT into event_log set user_id = '".$row['user_id']."', event_type = '".EVENT_TYPE_LOGIN."'");
		header('Location: '.$_SERVER['PHP_SELF']);
		exit();
	} else {
		$smarty->assign('info', 'Unable to authorize.');
	}
}

if (!isset($_SESSION['user'])) {
	$smarty->display('templates/login.tpl');
	exit();
}

if (isset($_REQUEST['new_username']) && isset($_SESSION['user']) && $_SESSION['user']['username'] == 'knobby') {
	if (!isset($mysqli)) {
		$mysqli = new mysqli($sql_host, $sql_user, $sql_pass, $sql_db);
	}

	$query = 'INSERT into users SET username="'.$_REQUEST['new_username'].'", password = "'.md5($_REQUEST['new_pw']).'", access_level="1"';
	$result = $mysqli->query($query) or die("Unable to query database - $query");
	$smarty->assign('info', 'Added.');
}

if (isset($_REQUEST['delete']) && isset($_SESSION['user']) && $_SESSION['user']['access_level'] === '0') {
	if (!isset($mysqli)) {
		$mysqli = new mysqli($sql_host, $sql_user, $sql_pass, $sql_db);
	}

	$query = 'SELECT filename, file_type FROM security WHERE event_time_stamp = "'.$_REQUEST['delete'].'"';
	$result = $mysqli->query($query) or die("Unable to query database - $query");
	while ($row = $result->fetch_assoc()) {
		$ar = split_filename($row['filename']);
		unlink($row['filename']);
		if ($row['file_type'] == 8) {
			// movie
			unlink($ar[0].".webm");
			unlink($ar[0].".ipad.mp4");
		} else {
			// must be a jpeg
			unlink($ar[0].".thumb.jpg");
		}
	}
	$query = 'DELETE FROM security WHERE event_time_stamp = "'.$_REQUEST['delete'].'"';
	$result = $mysqli->query($query) or die("Unable to query database - $query");
	$smarty->assign('info', 'Deleted.');
}

// if no day has been selected in the calendar, use current time
if (isset($_GET['view_date'])) {
	$view_date = strtotime($_GET['view_date']);
} else {
	$view_date = time();
}

// get all events for the selected day in a order that allow us to
// show the result in a nice way!
$date = date('Ymd', $view_date);

if (!isset($mysqli)) {
	$mysqli = new mysqli($sql_host, $sql_user, $sql_pass, $sql_db);
}

#access = array('read' => 1);
if (isset($_SESSION['user'])) {
	if ($_SESSION['user']['access_level'] === '0') {
		$access['delete'] = 1;
		$smarty->assign('cur', $_SERVER['PHP_SELF'].'?view_date='.$date);
	}
	if ($_SESSION['user']['operate_wilson_gate'] == true || $_SESSION['user']['operate_brigman_gate'] == true) {
		$access['control'] = 1;
	}
}

$smarty->assign('access', $access);

if ($date == date('Ymd') && isset($live_camera_url)) {
	$smarty->assign('live_cam', $live_camera_url);
}

$query = 'SELECT TIME(event_time_stamp) as timefield, '. 
                'event_time_stamp+0 as time_stamp, file_size, camera, filename, file_type '.
                'FROM security '.
                'WHERE event_time_stamp >= '.$date.'000000 '.
                'AND event_time_stamp <= '.$date.'235959 '.
                'ORDER BY timefield DESC, camera';

$result = $mysqli->query($query) or die("Unable to query database - $query");

$smarty->assign('day', date('l, F jS Y', $view_date));

while ($result && $row = $result->fetch_assoc()) {
	$parts = split_filename($row['filename']);
	if ($row['file_type'] == 8) {
		// found a movie
		$camera_data[$row['time_stamp']]['movie'] = str_replace($base_path, '/media', $parts[0]);
		$camera_data[$row['time_stamp']]['camera'] = $row['camera'];
		$camera_data[$row['time_stamp']]['pretty_time'] = date('g:i:s a', strtotime($row['timefield']));
		$camera_data[$row['time_stamp']]['refresh_id'] = $row['time_stamp'];
		if (!isset($camera_data[$row['time_stamp']]['thumbnail'])) {
			$camera_data[$row['time_stamp']]['thumbnail'] = "/media/static";
			$camera_data[$row['time_stamp']]['refresh'] = 1;
		}
	} else if ($row['file_type'] == 1) {
		// jpeg
		$camera_data[$row['time_stamp']]['thumbnail'] = str_replace($base_path, '/media', $parts[0]);
		unset($camera_data[$row['time_stamp']]['refresh']);
	}
}

if (isset($camera_data)) {
	$smarty->assign('camera_data', $camera_data);
}

include('calendar.inc');

$smarty->assign('calendar', calendar($view_date));

/*if ($_SESSION['user']['username'] == 'knobby') {
	$smarty->assign('add_user', 'true');
}*/

$smarty->display('templates/index.tpl');

?>
