<?php
require_once dirname(__DIR__, 2) . '/config/database.php';
$d=body(); $pdo=db(); $pdo->prepare("INSERT INTO afveo_sections(title,path,type,enabled,sort_order) VALUES(?,?,?,?,?)")->execute([(string)($d['title']??''),(string)($d['path']??''),(string)($d['type']??'movie'),(int)($d['enabled']??1),(int)($d['sortOrder']??0)]); json_response(['ok'=>true],201);