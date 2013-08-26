<?php
define("EVENT_TYPE_LOGIN", 0);
define("EVENT_TYPE_OPEN_WILSON_GATE", 1);
define("EVENT_TYPE_HOLD_WILSON_GATE", 2);
define("EVENT_TYPE_RELEASE_WILSON_GATE", 3);
define("EVENT_TYPE_OPEN_BRIGMAN_GATE", 4);
define("EVENT_TYPE_HOLD_BRIGMAN_GATE", 5);
define("EVENT_TYPE_RELEASE_BRIGMAN_GATE", 6);

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

?>
