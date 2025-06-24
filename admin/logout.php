<?php
session_start();

// Set cache control headers to prevent back button from showing authenticated pages
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Fri, 01 Jan 1990 00:00:00 GMT");

// Destroy all session data
session_destroy();

// Redirect to login page
header('Location: index.php');
exit;
?> 