<?php
require_once '../includes/functions.php';

startSession();

// Destroy session
session_destroy();

// Redirect to login
header('Location: login.php');
exit();
?>

