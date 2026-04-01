<?php
require_once '../settings/core.php';

// Destroy session
session_destroy();

// Redirect to home
header("Location: index.php");
exit();
