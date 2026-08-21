CREATE TABLE IF NOT EXISTS afveo_settings (
  id TINYINT UNSIGNED PRIMARY KEY,
  version_json JSON NOT NULL,
  maintenance_json JSON NOT NULL,
  social_json JSON NOT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS afveo_announcements (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(180) NOT NULL,
  message TEXT NOT NULL,
  placement VARCHAR(40) NOT NULL DEFAULT 'home_open',
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  views INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS afveo_sections (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(180) NOT NULL,
  path VARCHAR(255) NOT NULL,
  type VARCHAR(30) NOT NULL DEFAULT 'movie',
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  items INT UNSIGNED NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS afveo_devices (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  device_id VARCHAR(180) NOT NULL UNIQUE,
  platform VARCHAR(30) NOT NULL,
  app_version VARCHAR(30) NOT NULL,
  last_seen_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_last_seen (last_seen_at)
);

INSERT IGNORE INTO afveo_settings (id, version_json, maintenance_json, social_json)
VALUES (
  1,
  '{"current":"1.9.0","forceUpdate":false,"title":"يتوفر تحديث جديد","description":"يرجى تنزيل أحدث نسخة للمتابعة.","updateUrl":""}',
  '{"enabled":false,"title":"التطبيق تحت الصيانة","description":"سنعود قريبًا. شكرًا لصبركم."}',
  '{"telegram":"","instagram":"","facebook":"","x":""}'
);