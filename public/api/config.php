<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/config/database.php';

$settings = db()->query("SELECT version_json, maintenance_json, social_json FROM afveo_settings WHERE id=1")->fetch();
$sections = db()->query("SELECT id, title, path, type, enabled, sort_order AS sortOrder, items FROM afveo_sections WHERE enabled=1 ORDER BY sort_order")->fetchAll();
$announcements = db()->query("SELECT id, title, message, placement, enabled, views FROM afveo_announcements WHERE enabled=1 ORDER BY created_at DESC")->fetchAll();

$version = json_decode($settings['version_json'] ?? '{}', true) ?: [];
$version = [
  'current' => (string)($version['current'] ?? '1.9.0'),
  'force_update' => (bool)($version['force_update'] ?? $version['forceUpdate'] ?? false),
  'title' => (string)($version['title'] ?? 'يتوفر تحديث جديد'),
  'description' => (string)($version['description'] ?? 'يرجى تنزيل أحدث نسخة للمتابعة.'),
  'button_url' => (string)($version['button_url'] ?? $version['updateUrl'] ?? ''),
];
$maintenance = json_decode($settings['maintenance_json'] ?? '{}', true) ?: [];
$maintenance = [
  'enabled' => (bool)($maintenance['enabled'] ?? false),
  'title' => (string)($maintenance['title'] ?? 'التطبيق تحت الصيانة'),
  'description' => (string)($maintenance['description'] ?? 'سنعود قريبًا.'),
];

json_response([
  'ok' => true,
  'version' => $version,
  'maintenance' => $maintenance,
  'social' => json_decode($settings['social_json'] ?? '{}', true),
  'home_sections' => $sections,
  'announcements' => $announcements,
]);