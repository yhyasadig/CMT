<?php
session_start();
include 'Database.php';
include 'Project.php';  // تضمين ملف الكلاس

try {
    $db = new DatabaseConnection();
    $connection = $db->getConnection();

    // التحقق من أن المستخدم هو أحد الأدوار (طالب، قائد فريق، أو مشرف)
    if ($_SESSION['role'] != 'student' && $_SESSION['role'] != 'automember' && $_SESSION['role'] != 'supervis') {
        header("Location: index.php");
        exit();
    }

    // جلب project_id الخاص بالمستخدم أولاً
    $query = "SELECT project_id FROM users WHERE user_id = :user_id";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $userProject = $stmt->fetch(PDO::FETCH_ASSOC);

    $projectDetails = null;
    $leaderName = null;

    if ($userProject && $userProject['project_id']) {
        // إنشاء كائن Project
        $project = new Project($db, $userProject['project_id']);
        $projectDetails = $project->getProjectDetails();

        if ($projectDetails) {
            // جلب اسم قائد الفريق
            $leaderName = $project->getLeader();
        }
    }
} catch (PDOException $e) {
    // في حال وجود خطأ في قاعدة البيانات نعرض رسالة مناسبة
    $errorMessage = "حدث خطأ أثناء جلب بيانات المشروع: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
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

        .message, .error-message {
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

        <?php if (isset($errorMessage)): ?>
            <p class="error-message"><?php echo htmlspecialchars($errorMessage); ?></p>
        <?php else: ?>
            <?php if ($projectDetails): ?>
                <table>
                    <tr>
                        <th>اسم المشروع</th>
                        <td><?php echo htmlspecialchars($projectDetails['name']); ?></td>
                    </tr>
                    <tr>
                        <th>الوصف</th>
                        <td><?php echo htmlspecialchars($projectDetails['description']); ?></td>
                    </tr>
                    <tr>
                        <th>تاريخ البداية</th>
                        <td><?php echo htmlspecialchars($projectDetails['start_date']); ?></td>
                    </tr>
                    <tr>
                        <th>تاريخ النهاية</th>
                        <td><?php echo htmlspecialchars($projectDetails['end_date']); ?></td>
                    </tr>
                    <tr>
                        <th>قائد الفريق</th>
                        <td><?php echo htmlspecialchars($leaderName); ?></td>
                    </tr>
                </table>
            <?php else: ?>
                <p class="message">لم يتم إضافتك إلى أي مشروع بعد.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
