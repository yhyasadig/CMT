<?php
session_start();

// التأكد من أن المستخدم هو مشرف
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'supervis') {
    header("Location: index.php");
    exit();
}

// استدعاء الملفات
require_once 'Database.php';
require_once 'NotificationManager.php'; // تضمين كلاس NotificationManager

try {
    // الاتصال بقاعدة البيانات وإنشاء كائن الإشعارات
    $db = new DatabaseConnection();
    $notificationManager = new NotificationManager($db);

    // جلب الإشعارات للمستخدم (المشرف)
    $userId = $_SESSION['user_id'];
    $notifications = $notificationManager->getNotifications($userId);

} catch (Exception $e) {
    error_log("خطأ في جلب الإشعارات: " . $e->getMessage());
    $notifications = [];
}
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

<!-- الشريط الجانبي -->
<div class="sidebar">
    <h2>لوحة تحكم المشرف</h2>
    <a href="add_project.php">إضافة مشروع</a>
    <a href="projects_list.php">عرض المشاريع</a>
    <a href="dashboard_taskmanager.php">إدارة المهام</a>
    <a href="supervisor_reports.php">عرض التقارير</a>
    <a href="chat_supervisor.php">دردشة</a>
    <a href="logout.php">تسجيل الخروج</a>
</div>

<!-- المحتوى الرئيسي -->
<div class="main-content">
    <h1>مرحبًا بك - إشعاراتك</h1>

    <div class="notification-box">
        <h3>الإشعارات:</h3>
        <?php
        // التحقق إذا كانت هناك إشعارات أم لا
        if (count($notifications) > 0) {
            // عرض الإشعارات
            foreach ($notifications as $notification) {
                // تحديد إذا كانت الإشعار غير مقروء
                echo '<div class="notification-item ' . ($notification['is_read'] == 0 ? 'unread' : '') . '">';
                echo '<strong>' . htmlspecialchars($notification['message']) . '</strong><br>';
                echo '<small>' . $notification['created_at'] . '</small>';

                // التحقق إذا كان هناك اسم المشروع
                if (!empty($notification['project_name'])) {
                    echo '<br><small>مشروع: ' . htmlspecialchars($notification['project_name']) . '</small>';
                }

                // التحقق من وجود مرسل الإشعار باستخدام user_id
                if (isset($notification['user_id'])) {
                    $userQuery = "SELECT name FROM users WHERE user_id = :user_id";
                    $stmt = $db->getConnection()->prepare($userQuery);
                    $stmt->bindParam(':user_id', $notification['user_id']);
                    $stmt->execute();
                    $sender = $stmt->fetch(PDO::FETCH_ASSOC);
                    $senderName = $sender ? htmlspecialchars($sender['name']) : 'المرسل غير معروف';

                    echo '<br><small>المرسل: ' . $senderName . '</small>';
                } else {
                    echo '<br><small>المرسل غير معروف</small>';
                }

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
