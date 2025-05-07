<?php
session_start();
include 'Database.php';

$db = new DatabaseConnection();
$connection = $db->getConnection();  // الحصول على الاتصال بقاعدة البيانات

// التحقق من أن المستخدم هو طالب
if ($_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

// استعلام لعرض معلومات الطالب
$query = "SELECT * FROM users WHERE user_id = :user_id";
$stmt = $connection->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$student = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل الطالب</title>
    <style>
        /* إضافة تنسيق للسايد بار */
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

        h2 {
            color: #333;
        }

        table {
            width: 100%;
            border: 1px solid #ccc;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            text-align: center;
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
    </div>

    <!-- المحتوى الرئيسي -->
    <div class="main-content">
        <h2>تفاصيل الطالب</h2>

        <table>
            <tr>
                <th>الاسم</th>
                <td><?php echo $student['name']; ?></td>
            </tr>
            <tr>
                <th>البريد الإلكتروني</th>
                <td><?php echo $student['email']; ?></td>
            </tr>
            <tr>
                <th>الدور</th>
                <td><?php echo $student['role']; ?></td>
            </tr>
        </table>
    </div>
</body>
</html>
