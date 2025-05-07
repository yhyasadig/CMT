<?php
// التأكد من أن المستخدم هو مشرف
session_start();
if ($_SESSION['role'] != 'supervis') {
    header("Location: index");  // إعادة التوجيه إلى صفحة تسجيل الدخول إذا لم يكن مشرف
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم المشرف</title>
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
            left: 0;
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
            margin-left: 260px;
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

        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }

        .delete-btn {
            color: white;
            background-color: red;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .delete-btn:hover {
            background-color: darkred;
        }
    </style>
</head>
<body>

    <!-- سايد بار -->
    <div class="sidebar">
        <h2>لوحة تحكم المشرف</h2>
        <a href="add_project.php">إضافة مشروع</a>
        <a href="projects_list.php">عرض المشاريع</a>
        <a href="projects_list.php">عرض التقارير</a>
        <a href="project_dashboard.php" class="button">المشروع الخاص بي</a> <!-- زر المشروع الخاص بي -->
    </div>

    <!-- محتوى الصفحة -->
    <div class="main-content">
        <h1>مرحبًا في لوحة تحكم المشرف!</h1>
        <p>من هنا يمكنك إضافة المشاريع، عرض المشاريع، إضافة مشرفين جدد، عرض المشرفين الحاليين، و إدارة قائمة الطلبة.</p>

        <!-- أزرار لصفحات إضافية -->
        <a href="add_project.php" class="button">إضافة مشروع</a>
        <a href="projects_list.php" class="button">عرض المشاريع</a>
        <a href="projects_list.php" class="button">عرض التقارير</a>

    </div>

</body>
</html>
