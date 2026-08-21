<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/config/database.php';
$data = body();
$device = trim((string)($data['device_id'] ?? ''));
if ($device === '') json_response(['ok' => false, 'error' => 'device_id is required'], 422);
$stmt = db()->prepare(
  "INSERT INTO afveo_devices (device_id, platform, app_version)
   VALUES (:device, :platform, :version)
   ON DUPLICATE KEY UPDATE platform=VALUES(platform), app_version=VALUES(app_version), last_seen_at=CURRENT_TIMESTAMP"
);
$stmt->execute([
  'device' => $device,
  'platform' => (string)($data['platform'] ?? 'unknown'),
  'version' => (string)($data['app_version'] ?? 'unknown'),
]);
json_response(['ok' => true]);