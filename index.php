<?php
declare(strict_types=1);

/**
 * AFVeo Admin front controller.
 *
 * هذا الملف هو نقطة الدخول الرئيسية عند رفع محتويات لوحة الإدارة إلى الاستضافة.
 * يمرر الطلب إلى ملفات PHP الموجودة فعلياً، ويرفض أي مسار خارج مجلد اللوحة.
 */

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');

// إزالة مسار مجلد اللوحة من بداية الطلب عند الرفع داخل مجلد فرعي.
$path = $uri;
if ($scriptDir !== '' && $scriptDir !== '/' && str_starts_with($path, $scriptDir)) {
    $path = substr($path, strlen($scriptDir));
}
$path = '/' . ltrim($path, '/');
$path = rawurldecode($path);

// حماية أساسية من traversal أو استدعاء ملفات خارج لوحة الإدارة.
if (str_contains($path, "\0") || str_contains($path, '..')) {
    respondNotFound();
}

// المسارات العامة المختصرة.
$routes = [
    '/' => 'admin/index.php',
    '/dashboard' => 'admin/index.php',
    '/dashboard.php' => 'admin/index.php',
    '/login' => 'login.php',
    '/login.php' => 'login.php',
    '/logout' => 'logout.php',
    '/logout.php' => 'logout.php',
];

// ملفات API تمرر كما هي، مثل /api/config.php و/api/heartbeat.php.
$relative = ltrim($path, '/');
if (str_starts_with($path, '/api/')) {
        $target = __DIR__ . '/' . $relative;
        dispatchPhp($target, $method);
}

if (isset($routes[$path])) {
    dispatchPhp(__DIR__ . '/' . $routes[$path], $method);
}

// السماح بملف PHP محدد موجود فعلياً، مع منع المسارات المتداخلة غير الضرورية.
if (preg_match('#^/[A-Za-z0-9_-]+\\.php$#', $path) === 1) {
    dispatchPhp(__DIR__ . '/' . ltrim($path, '/'), $method);
}

respondNotFound();

function dispatchPhp(string $target, string $method): never
{
    $realBase = realpath(__DIR__);
    $realTarget = is_file($target) ? realpath($target) : false;

    if ($realBase === false || $realTarget === false || !str_starts_with($realTarget, $realBase . DIRECTORY_SEPARATOR)) {
        respondNotFound();
    }

    // لا نسمح بتشغيل ملفات إعدادات أو SQL عبر المتصفح.
    $blocked = [
        DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR,
        '.sql',
        'README.md',
    ];
    foreach ($blocked as $part) {
        if (str_contains($realTarget, $part)) {
            respondNotFound();
        }
    }

    require $realTarget;
    exit;
}

function respondNotFound(): never
{
    $isApi = str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/');
    http_response_code(404);
    if ($isApi) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => false,
            'error' => 'route_not_found',
        ], JSON_UNESCAPED_UNICODE);
    } else {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><html lang="ar" dir="rtl"><meta charset="utf-8"><title>AFVeo Admin</title><p>الصفحة غير موجودة.</p>';
    }
    exit;
}
