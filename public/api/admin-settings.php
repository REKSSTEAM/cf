<?php
require_once dirname(__DIR__, 2) . '/config/database.php';
$d=body(); $pdo=db(); $pdo->prepare("UPDATE afveo_settings SET version_json=?, maintenance_json=?, social_json=? WHERE id=1")->execute([json_encode($d['version']??[]),json_encode($d['maintenance']??[]),json_encode($d['social']??[])]);
json_response(['ok'=>true]);