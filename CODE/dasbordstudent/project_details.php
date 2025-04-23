<?php
session_start();
include 'Database.php';

$db = new DatabaseConnection();
$connection = $db->getConnection();  // الحصول على الاتصال بقاعدة البيانات

// التحقق من أن المستخدم هو أحد الأدوار (طالب، قائد فريق، أو مشرف)
if ($_SESSION['role'] != 'student' && $_SESSION['role'] != 'automember' && $_SESSION['role'] != 'supervis') {
    header("Location: index.php");
    exit();
}

// استعلام لعرض تفاصيل المشروع للمستخدم
$query = "SELECT p.project_id, p.name, p.description, p.start_date, p.end_date, u.name AS leader_name 
          FROM projects p 
          JOIN users u ON p.leader_id = u.user_id 
          WHERE p.project_id IN (SELECT project_id FROM users WHERE user_id = :user_id)";
$stmt = $connection->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$project = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل المشروع</title>
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

        .message {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- السايد بار -->
    <div class="sidebar">
        <h2>لوحة تحكم <?php echo ucfirst($_SESSION['role']); ?></h2>
        <a href="student_dashboard.php">الصفحة الرئيسية</a>
        <a href="student_details.php">تفاصيل الطالب</a>
        <a href="project_details.php">تفاصيل المشروع</a>
    </div>

    <!-- المحتوى الرئيسي -->
    <div class="main-content">
        <h2>تفاصيل المشروع</h2>

        <?php if ($project): ?>
            <table>
                <tr>
                    <th>اسم المشروع</th>
                    <td><?php echo $project['name']; ?></td>
                </tr>
                <tr>
                    <th>الوصف</th>
                    <td><?php echo $project['description']; ?></td>
                </tr>
                <tr>
                    <th>تاريخ البداية</th>
                    <td><?php echo $project['start_date']; ?></td>
                </tr>
                <tr>
                    <th>تاريخ النهاية</th>
                    <td><?php echo $project['end_date']; ?></td>
                </tr>
                <tr>
                    <th>قائد الفريق</th>
                    <td><?php echo $project['leader_name']; ?></td>
                </tr>
            </table>
        <?php else: ?>
            <p class="message">لم يتم إضافتك إلى أي مشروع بعد.</p>
        <?php endif; ?>
    </div>
</body>
</html>
