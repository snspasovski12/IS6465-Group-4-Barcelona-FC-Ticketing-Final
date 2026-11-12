<?php
/* Logout: end the session and return to the Home page (WF04A, WF07). */
require_once __DIR__ . "/includes/init.php";
$_SESSION = [];
session_destroy();
redirect("index.php?msg=logged_out");
