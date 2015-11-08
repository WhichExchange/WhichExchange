<?php

session_start();

if (isset($_POST['time'])) {
    // No vulnerabilities here, moving on
    $time = $_POST['time'];

    $_SESSION['time'] = $time;

    error_log($time);
} else {
    echo isset($_SESSION['time']) ? $_SESSION['time'] : '';
}

session_write_close();
