<?php
session_start();

//  التأكد من أن المستخدم مشرف
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'supervis') {
    header("Location: index.php");
    exit();
}

//  استدعاء الملفات
require_once 'Database.php';
require_once 'Notifications.php';

<<<<<<< HEAD
try {
    // الاتصال بقاعدة البيانات وإنشاء كائن الإشعارات
    $db = new DatabaseConnection();
    $notificationsObj = new Notifications($db);

    //  جلب الإشعارات للمستخدم الحالي (المشرف)
    $userId = $_SESSION['user_id'];
    $notifications = $notificationsObj->getNotifications($userId);
} catch (Exception $e) {
    error_log("خطأ في جلب الإشعارات: " . $e->getMessage());
    $notifications = [];
}
=======
// الاتصال بقاعدة البيانات وإنشاء كائن الإشعارات
$db = new DatabaseConnection();
$notificationsObj = new Notifications($db);

//  جلب الإشعارات للمستخدم الحالي (المشرف)
$userId = $_SESSION['user_id'];
$notifications = $notificationsObj->getNotifications($userId);
>>>>>>> 2c437069192c41dc67c3eef3ba98c09f930e22d9
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>إشعارات المشرف</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
        }

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

<!--  الشريط الجانبي -->
<div class="sidebar">
    <h2>لوحة تحكم المشرف</h2>
    <a href="add_project.php">إضافة مشروع</a>
    <a href="projects_list.php">عرض المشاريع</a>
    <a href="dashboard_taskmanager.php">إدارة المهام</a>
    <a href="supervisor_reports.php">عرض التقارير</a>
    <a href="chat_supervisor.php">دردشة</a>
<<<<<<< HEAD
    <a href="logout.php" >تسجيل الخروج</a>
=======
>>>>>>> 2c437069192c41dc67c3eef3ba98c09f930e22d9
</div>

<!--  المحتوى الرئيسي -->
<div class="main-content">
    <h1>مرحبًا بك - إشعاراتك</h1>

    <div class="notification-box">
        <h3>الإشعارات:</h3>
        <?php
        if (count($notifications) > 0) {
            foreach ($notifications as $notification) {
                echo '<div class="notification-item ' . ($notification['is_read'] == 0 ? 'unread' : '') . '">';
                echo '<strong>' . htmlspecialchars($notification['message']) . '</strong><br>';
                echo '<small>' . $notification['created_at'] . '</small>';
                if (!empty($notification['project_name'])) {
                    echo '<br><small>مشروع: ' . htmlspecialchars($notification['project_name']) . '</small>';
                }
                echo '<br><small>المرسل: ' . htmlspecialchars($notification['sender_name']) . '</small>';
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
