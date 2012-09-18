<?php
session_start();
$_SESSION["test"] = 'hello there';
header('Location: testsession2.php');
?>
