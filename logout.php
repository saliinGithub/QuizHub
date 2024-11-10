<?php
// Start the session
session_start();

// Destroy the session and redirect to homepage
session_destroy();
header("Location: ../index/index.html");
exit();
?>
