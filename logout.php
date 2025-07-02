// Vernietigt sessie en redirect naar login_start.php
<?php
session_start();
session_unset();
session_destroy();
header("Location: login_start.php");
exit;
