# AFVeo PHP + JavaScript Admin

هذه النسخة مخصصة للرفع على استضافة PHP/MySQL.

1. أنشئ قاعدة MySQL واستورد `database.sql`.
2. اضبط المتغيرات `AFVEO_DB_HOST`, `AFVEO_DB_NAME`, `AFVEO_DB_USER`, `AFVEO_DB_PASS`.
3. ارفع محتويات `public/` إلى النطاق، ثم عدّل `AdminRuntime.base` في تطبيق Flutter إلى:
   `https://your-domain.example/admin/api`
4. استخدم HTTPS دائمًا. لوحة الإدارة يجب أن تضيف تسجيل دخول قبل فتح ملفات التحكم.

واجهات التطبيق الجاهزة:
- `GET /api/config.php`
- `POST /api/heartbeat.php`

الحقول المتوافقة مع Flutter هي `version`, `maintenance`, `home_sections`,
`announcements`, و`social`.