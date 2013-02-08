<?php

// put full path to Smarty.class.php
require('/srv/gate/lib/Smarty/Smarty.class.php');
include('config.inc');
$smarty = new Smarty();

$smarty->setTemplateDir('/srv/gate//smarty/templates');
$smarty->setCompileDir('/srv/gate/smarty/templates_c');
$smarty->setCacheDir('/srv/gate/smarty/cache');
$smarty->setConfigDir('/srv/gate/smarty/configs');

session_start();

$mysqli = NULL;

if (isset($_REQUEST['login_name'])) {
	// connect to database
	$mysqli = new mysqli($sql_host, $sql_user, $sql_pass, $sql_db);
//	echo md5($_REQUEST['login_pw'])."<br />";
	$result = $mysqli->query("SELECT * from users WHERE username = '".$_REQUEST['login_name']."' AND password = '". md5($_REQUEST['login_pw']). "'");
	if ($result && $row = $result->fetch_assoc()) {
		$_SESSION['user'] = $row;
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

function split_filename($filename) 
{ 
    $pos = strrpos($filename, '.'); 
    if ($pos === false) {
        // dot is not found in the filename 
        return array($filename, ''); // no extension 
    } else {
        $basename = substr($filename, 0, $pos); 
        $extension = substr($filename, $pos+1); 
        return array($basename, $extension); 
    }
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

if (isset($_SESSION['user']) && $_SESSION['user']['access_level'] === '0') {
	$smarty->assign('cur', $_SERVER['PHP_SELF'].'?view_date='.$date);
}
$smarty->assign('next', $_SERVER['PHP_SELF'].'?view_date='.date('Ymd', $view_date+86400));
$smarty->assign('prev', $_SERVER['PHP_SELF'].'?view_date='.date('Ymd', $view_date-86400));

$query = 'SELECT TIME(event_time_stamp) as timefield, '. //HOUR(event_time_stamp) as hourfield, '.
                'event_time_stamp+0 as time_stamp, file_size, camera, filename, file_type '.
                'FROM security '.
                'WHERE event_time_stamp >= '.$date.'000000 '.
                'AND event_time_stamp <= '.$date.'235959 '.
                'ORDER BY timefield DESC, camera';// LIMIT '.$_REQUEST['start'].', 20';

$result = $mysqli->query($query) or die("Unable to query database - $query");

$smarty->assign('day', date('l, F jS Y', $view_date));

while ($result && $row = $result->fetch_assoc()) {
	$file_parts = split_filename($row['filename']);
	$parts = split_filename($row['filename']);
	if ($row['file_type'] == 8) {
		// found a movie
		$camera_data[$row['time_stamp']]['movie'] = str_replace($base_path, '/media', $parts[0]);
//		$camera_data[$row['time_stamp']]['file_size'] = $row['file_size'];
		$camera_data[$row['time_stamp']]['camera'] = $row['camera'];
		$camera_data[$row['time_stamp']]['pretty_time'] = date('g:i:s a', strtotime($row['timefield']));
		if (!isset($camera_data[$row['time_stamp']])) {
			$camera_data[$row['time_stamp']]['thumbnail'] = "/media/static.jpg";
		}
	} else if ($row['file_type'] == 1) {
		// jpeg
		$camera_data[$row['time_stamp']]['thumbnail'] = str_replace($base_path, '/media', $parts[0]); //$row['filename']);
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
