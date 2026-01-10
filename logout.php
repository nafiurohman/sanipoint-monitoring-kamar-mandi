<?php
session_start();

// Load configuration and core files
require_once 'config/config.php';
require_once 'core/Auth.php';

// Destroy session
session_destroy();

// Redirect to login page
header('Location: login.php');
exit;
?>