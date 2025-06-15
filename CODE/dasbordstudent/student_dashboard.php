<?php
session_start();
include 'Database.php';

require_once 'UserManager.php'; // تضمين كلاس UserManager
require_once 'NotificationManager.php'; // تضمين كلاس NotificationManager

try {
    // إنشاء كائنات من الكلاسات المعنية
    $db = new DatabaseConnection();
    $userManager = new UserManager($db);
    $notificationManager = new NotificationManager($db);

    // التحقق من أن المستخدم هو طالب
    if ($_SESSION['role'] != 'student') {
        header("Location: index.php");
        exit();
    }

    // جلب بيانات الطالب
    $student = $userManager->getUserById($_SESSION['user_id']);

    // جلب الإشعارات
    $notifications = $notificationManager->getNotifications($_SESSION['user_id']);

} catch (PDOException $e) {
    die("حدث خطأ في قاعدة البيانات: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم الطالب</title>
    <style>
        .sidebar {
            width: 250px;
            height: 100%;
            background-color: #333;
            padding-top: 20px;
            position: fixed;
            top: 0;
            left: 0;
            color: white;
            text-align: center;
        }

        .sidebar a {
            display: block;
            padding: 10px;
            color: white;
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

        .notification-box {
            background-color: #f9f9f9;
            border: 1px solid #ccc;
            padding: 10px;
            margin-top: 20px;
        }

        .notification-item {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .notification-item.unread {
            background-color: #fffbcc;
        }
    </style>
</head>
<body>
    <!-- السايد بار -->
    <div class="sidebar">
        <h2>لوحة تحكم الطالب</h2>
        <a href="student_dashboard.php">الصفحة الرئيسية</a>
        <a href="student_details.php">تفاصيل الطالب</a>
        <a href="project_details.php">تفاصيل المشروع</a>
        <a href="dashboard_file.php">المهام</a>
    </div>

    <!-- المحتوى الرئيسي -->
    <div class="main-content">
        <h2>مرحبًا، <?php echo htmlspecialchars($student['name']); ?>!</h2>
        <p>مرحبًا بك في لوحة تحكم الطالب. يمكنك هنا إدارة التفاصيل الخاصة بك والمشاريع المسجلة لك.</p>

        <!-- إشعارات الطالب -->
        <div class="notification-box">
            <h3>الإشعارات:</h3>
            <?php if (count($notifications) > 0): ?>
                <?php foreach ($notifications as $noti): ?>
                    <div class="notification-item <?php echo $noti['is_read'] == 0 ? 'unread' : ''; ?>">
                        <strong><?php echo htmlspecialchars($noti['message']); ?></strong><br>
                        <small><?php echo $noti['created_at']; ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>لا توجد إشعارات حالياً.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
