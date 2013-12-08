<?php
// Session beenden und zerstören
session_start();
session_destroy();

// Zum Login-Formular weiterleiten
header ("Location: login.php?loggedout");
?>