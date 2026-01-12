<?php
session_start();
require_once 'functions.php';

logout();
header('Location: login.php');
exit();
?>