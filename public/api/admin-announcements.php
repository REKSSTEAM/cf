<?php
require_once dirname(__DIR__, 2) . '/config/database.php';
$d=body(); $pdo=db(); $pdo->prepare("INSERT INTO afveo_announcements(title,message,placement,enabled) VALUES(?,?,?,?)")->execute([(string)($d['title']??''),(string)($d['message']??''),(string)($d['placement']??'home_open'),(int)($d['enabled']??1)]); json_response(['ok'=>true],201);