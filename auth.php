<?php
// Starts the session and gives two helpers for protecting pages.
session_start();

// For AUTHENTICATED pages: kick guests to the login page (requirement 5).
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// For GUEST pages (login, register): logged-in users go to the feed (requirement 4).
function require_guest() {
    if (isset($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }
}