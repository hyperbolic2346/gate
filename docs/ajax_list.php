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

$date = date('YmdHis', $_REQUEST['last_update']/1000);

session_start();

$mysqli = NULL;

if (!isset($_SESSION['user'])) {
	header('Location: http://'.$_SERVER['SERVER_NAME']);
        exit();
}

$mysqli = new mysqli($sql_host, $sql_user, $sql_pass, $sql_db);

if (isset($_SESSION['user']) && $_SESSION['user']['access_level'] === '0') {
        $smarty->assign('cur', $_SERVER['PHP_SELF']);
}

/*
// get all events since the last update
$query = 'SELECT TIME(event_time_stamp) as timefield, '.
                'event_time_stamp+0 as time_stamp, file_size, camera, filename, file_type '.
                'FROM security '.
                'WHERE event_time_stamp >= '.date('YmdHis', $_REQUEST['last_update']/1000).' '.
                'ORDER BY timefield DESC, camera';*/

$query = 'SELECT TIME(event_time_stamp) as timefield, '.
                'event_time_stamp+0 as time_stamp, file_size, camera, filename, file_type '.
                'FROM security '.
                'WHERE event_time_stamp >= '.date('Ymd', $_REQUEST['last_update']/1000).'000000 '.
                'ORDER BY timefield DESC, camera';

$result = $mysqli->query($query) or die("Unable to query database - $query");

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

$smarty->display('templates/index_ajax.tpl');

?>
