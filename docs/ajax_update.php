<?php

include('config.inc');
include('lib/common.php');

session_start();

$mysqli = NULL;

if (!isset($_SESSION['user'])) {
        exit();
}

$mysqli = new mysqli($sql_host, $sql_user, $sql_pass, $sql_db);

// see if we have a new image for this entry yet
$query = 'SELECT filename '.
                'FROM security '.
                'WHERE event_time_stamp = '.$_REQUEST['id'].' AND file_type = 1';

$result = $mysqli->query($query) or die("Unable to query database - $query");

while ($result && $row = $result->fetch_assoc()) {
        $parts = split_filename($row['filename']);
        // jpeg
	echo str_replace($base_path, '/media', $parts[0]).".thumb.jpg";
}

?>
