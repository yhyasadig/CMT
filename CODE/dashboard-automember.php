
<?php
// بدء الجلسة
session_start();

// التحقق من أن المستخدم هو قائد الفريق أو مشرف
if ($_SESSION['role'] != 'supervis' && $_SESSION['role'] != 'automember') {
    header("Location: index.php");  // إعادة التوجيه إلى صفحة تسجيل الدخول إذا لم يكن قائد الفريق أو مشرف
    exit();
}

// الاتصال بقاعدة البيانات
include 'Database.php';  // تأكد من أنك تستخدم الملف المناسب للاتصال بقاعدة البيانات
$db = new DatabaseConnection();
$connection = $db->getConnection();  // الحصول على الاتصال بقاعدة البيانات

// التحقق من وجود معرف المشروع في الجلسة
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];  // الحصول على ID المستخدم من الجلسة

    try {
        // استعلام لجلب تفاصيل المشروع باستخدام leader_id
        $query = "SELECT * FROM projects WHERE leader_id = :user_id";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);  // التأكد من أن قائد الفريق هو المستخدم الحالي
        $stmt->execute();
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        // التحقق من وجود المشروع
        if (!$project) {
            $message = "لا يوجد مشروع مرتبط بحسابك."; // رسالة في حالة عدم وجود مشروع
        } else {
            // إذا تم العثور على المشروع، نقوم بتخزين project_id و user_id في الجلسة
            $_SESSION['project_id'] = $project['project_id'];  // تخزين project_id في الجلسة
            $_SESSION['user_id'] = $user_id;  // تخزين user_id في الجلسة
        }
    } catch (PDOException $e) {
        // في حالة حدوث خطأ في الاتصال أو الاستعلام
        die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
    }
} else {
    $message = "لا يوجد مشروع مرتبط بحسابك."; // رسالة في حالة عدم وجود مشروع
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم قائد الفريق</title>
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
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar h2 {
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 15px;
            text-decoration: none;
            font-size: 18px;
            text-align: center;
            margin: 10px 0;
            border-radius: 5px;
        }

        .sidebar a:hover {
            background-color: #575757;
        }

        .main-content {
            margin-left: 260px;  /* Leave space for sidebar */
            padding: 20px;
            background-color: white;
            height: 100vh;
            overflow-y: auto;
        }

        h2, h3 {
            color: #333;
        }

        h2 {
            font-size: 28px;
        }

        p {
            font-size: 16px;
            color: #555;
        }

        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #45a049;
        }

        select {
            width: 100%;
            padding: 12px;
            font-size: 14px;
            margin-top: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        label {
            font-size: 14px;
            color: #333;
        }

        .message {
            color: green;
            text-align: center;
            font-size: 18px;
            margin-top: 20px;
        }
.error-message {
            color: red;
            text-align: center;
            font-size: 18px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <!-- شريط التنقل أو السايد بار -->
    <div class="sidebar">
        <h2>لوحة تحكم قائد الفريق</h2>
        <ul>
            <li><a href="student_dashboard.php">الصفحة الرئيسية</a></li>
            <li><a href="project_details.php">عرض تفاصيل المشروع</a></li>
            <li><a href="add_team_members.php">إضافة أعضاء الفريق</a></li>
            <!-- زر المشروع الخاص بي -->
            <li><a href="project_dashboard.php">المشروع الخاص بي</a></li> <!-- يوجه إلى الصفحة الخاصة بالمشروع -->
            <!-- زر إضافة مهام -->
            <li><a href="dashboard_taskmanager.php">إضافة مهام</a></li> <!-- رابط صفحة إدارة المهام -->
        </ul>
    </div>

    <!-- محتوى الصفحة الرئيسية -->
    <div class="main-content">
        <h2>مرحبًا بك في لوحة تحكم قائد الفريق</h2>