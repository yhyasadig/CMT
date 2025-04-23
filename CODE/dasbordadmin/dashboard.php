<?php
// التأكد من أن المستخدم هو مشرف
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: login.php");  // إعادة التوجيه إلى صفحة تسجيل الدخول إذا لم يكن مشرف
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم الادمن</title>
    <style>
        /* تنسيق الصفحة */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
        }

        /* تصميم السايد بار */
        .sidebar {
            position: fixed;
            right: 0;
            top: 0;
            width: 250px;
            height: 100%;
            background-color: #333;
            padding-top: 20px;
            color: white;
            text-align: center;
        }

        .sidebar a {
            color: white;
            display: block;
            padding: 15px;
            text-decoration: none;
            font-size: 18px;
        }

        .sidebar a:hover {
            background-color: #575757;
        }

        /* محتوى الصفحة */
        .main-content {
            margin-right: 260px;
            padding: 20px;
        }

        h1 {
            color: #333;
        }

        /* تنسيق الأزرار في الداش بورد */
        .button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 20px;
        }

        .button:hover {
            background-color: #45a049;
        }

    </style>
</head>
<body>

    <!-- سايد بار -->
    <div class="sidebar">
        <h2>لوحة تحكم الادمن </h2>
        <a href="add_student.php">إضافة طالب</a>
        <a href="students_list.php">صفحة الطلبة</a>
        <a href="add_supervisor.php">إضافة مشرف</a>
        <a href="supervisors_list.php">صفحة المشرفين</a>
    </div>

    <!-- محتوى الصفحة -->
    <div class="main-content">
        <h1>مرحبًا في لوحة تحكم الادمن!</h1>
        <p>من هنا يمكنك إضافة الطلبة، عرض قائمة الطلبة، إضافة مشرفين جدد، أو عرض المشرفين الموجودين.</p>

        <!-- أزرار لصفحات إضافية -->
        <a href="add_student.php" class="button">إضافة طالب</a>
        <a href="students_list.php" class="button">عرض الطلبة</a>
        <a href="add_supervisor.php" class="button">إضافة مشرف</a>
        <a href="supervisors_list.php" class="button">عرض المشرفين</a>
    </div>

</body>
</html>
