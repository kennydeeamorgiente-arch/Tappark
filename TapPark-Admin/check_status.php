<?php
require 'app/Config/Paths.php';
$paths = new \Config\Paths();
require $paths->systemDirectory . '/bootstrap.php';
$db = \Config\Database::connect();
$res = $db->query('SELECT DISTINCT status FROM feedback')->getResultArray();
echo json_encode($res);
