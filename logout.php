<?php
require 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION = [];       // clear all session data
    session_destroy();    // end the session
}

header('Location: login.php');
exit;