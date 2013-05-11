<?php

include('config.inc');
include('lib/common.php');

session_start();

$mysqli = NULL;

if (!isset($_SESSION['user']) || $_SESSION['user']['access_level'] !== '0') {
	echo "invalid user information!";
	exit();
}

if (!isset($_REQUEST['delete'])) {
	echo "invalid request!";
	exit();
}

$mysqli = new mysqli($sql_host, $sql_user, $sql_pass, $sql_db);

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
$result = $mysqli->query($query) or die("Unable to query database2 - $query");
echo "Deleted.";
exit();

?>
