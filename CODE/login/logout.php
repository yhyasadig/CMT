<?php
// بدء الجلسة
session_start();

// حذف جميع بيانات الجلسة
$_SESSION = [];         // حذف كل متغيرات الجلسة
session_unset();        // إزالة الجلسة من الذاكرة
session_destroy();      // تدمير الجلسة تمامًا

// إعادة توجيه المستخدم إلى صفحة تسجيل الدخول
header("Location: login.php");
exit;
?>
