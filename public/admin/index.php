<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>AFVeo • لوحة الإدارة</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <aside><div class="brand"><b>AF</b><span>AFVeo<br><small>لوحة الإدارة</small></span></div>
    <nav><button class="active" data-tab="overview">نظرة عامة</button><button data-tab="settings">الإعدادات</button><button data-tab="announcements">الإعلانات</button><button data-tab="sections">الأقسام</button></nav>
  </aside>
  <main><header><div><small>مركز التحكم</small><h1 id="title">نظرة عامة</h1></div><span class="status"><i></i> النظام يعمل</span></header>
    <section id="overview" class="tab"><div id="metrics" class="metrics"></div><div class="panel"><h2>حالة التطبيق</h2><div id="activity"></div></div></section>
    <section id="settings" class="tab hidden"><div class="panel"><h2>الإصدار والتحديث</h2><form id="settingsForm"><div class="grid"><label>الإصدار الحالي<input name="current"></label><label class="check"><input type="checkbox" name="forceUpdate"> إجبار التحديث</label><label>عنوان الرسالة<input name="title"></label><label>رابط التحديث<input name="updateUrl"></label><label class="wide">نص الرسالة<textarea name="description"></textarea></label></div><h2>الصيانة والروابط</h2><div class="grid"><label class="check"><input type="checkbox" name="maintenance"> تفعيل وضع الصيانة</label><label>رسالة الصيانة<input name="maintenanceDescription"></label><label class="wide">قناة تيليجرام<input name="telegram" placeholder="https://t.me/..."></label></div><button class="primary">حفظ التغييرات</button><span id="saveNote"></span></form></div></section>
    <section id="announcements" class="tab hidden"><div class="panel"><div class="panel-head"><h2>الإعلانات</h2><button class="primary" onclick="addAnnouncement()">إضافة إعلان</button></div><div id="announcementList"></div></div></section>
    <section id="sections" class="tab hidden"><div class="panel"><div class="panel-head"><h2>أقسام الصفحة الرئيسية</h2><button class="primary" onclick="addSection()">إضافة قسم</button></div><div id="sectionList"></div></div></section>
  </main><script src="app.js"></script>
</body></html>