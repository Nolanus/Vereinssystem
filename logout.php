<?php
// Session beenden und zerst�ren
session_start();
session_destroy();

// Zum Login-Formular weiterleiten
header ("Location: login.php?loggedout");
?>