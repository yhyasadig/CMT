<?php
// بدء الجلسة
session_start();

// التأكد من أن المستخدم هو مشرف
if ($_SESSION['role'] != 'supervis') {
    header("Location: index.php");  // إعادة التوجيه إلى صفحة تسجيل الدخول إذا لم يكن مشرف
    exit();
}

// الاتصال بقاعدة البيانات
include 'Database.php';  // تأكد من أنك تستخدم الملف المناسب للاتصال بقاعدة البيانات
$db = new DatabaseConnection();
$connection = $db->getConnection();  // الحصول على الاتصال بقاعدة البيانات

// استعلام لجلب جميع الإشعارات مع الرسالة واسم المشروع
$query = "
    SELECT n.notification_id, n.message, n.created_at, n.is_read, p.name AS project_name, u.name AS sender_name
    FROM notifications n
    LEFT JOIN projects p ON n.project_id = p.project_id
    LEFT JOIN users u ON n.sender_id = u.user_id
    ORDER BY n.created_at DESC
";
$stmt = $connection->prepare($query);
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إشعارات المشرف</title>
    <style>
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
            color: white;
            padding-top: 20px;
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

        .notification-box {
            margin-top: 30px;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
        }

        .notification-item {
            padding: 10px;
            border-bottom: 1px solid #ccc;
        }

        .unread {
            background-color: #fffbcc;
        }

        .notification-item a {
            color: #007bff;
            text-decoration: none;
        }

        .notification-item a:hover {
            text-decoration: underline;
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
        <h1>مرحبًا في لوحة تحكم المشرف - الإشعارات</h1>

        <!-- عرض الإشعارات -->
        <div class="notification-box">
            <h3>الإشعارات:</h3>
            <?php
            // التحقق إذا كانت هناك إشعارات للمشرف
            if (count($notifications) > 0) {
                foreach ($notifications as $notification) {
                    echo '<div class="notification-item ' . ($notification['is_read'] == 0 ? 'unread' : '') . '">';
                    echo '<strong>' . htmlspecialchars($notification['message']) . '</strong><br>';
                    echo '<small>' . $notification['created_at'] . '</small>';
                    // عرض المشروع إذا كان موجوداً
                    if ($notification['project_name']) {
                        echo '<br><small>مشروع: ' . $notification['project_name'] . '</small>';
                    }
                    // عرض اسم المرسل
                    echo '<br><small>المرسل: ' . $notification['sender_name'] . '</small>';
                    echo '</div>';
                }
            } else {
                echo "<p>لا توجد إشعارات حالياً.</p>";
            }
            ?>
        </div>
    </div>

</body>
</html>
