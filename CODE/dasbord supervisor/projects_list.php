<?php
// بدء الجلسة
session_start();

try {
    // التحقق من أن المستخدم هو مشرف
    if ($_SESSION['role'] != 'supervis') {
        header("Location: index.php");  // إعادة التوجيه إلى صفحة تسجيل الدخول إذا لم يكن مشرف
        exit();
    }

    // الاتصال بقاعدة البيانات
    include 'Database.php';   // الاتصال بقاعدة البيانات
    include 'Project.php';    // كلاس المشروع

    $db = new DatabaseConnection();

    // حذف مشروع
    if (isset($_GET['delete_id'])) {
        $deleteId = $_GET['delete_id'];
        $project = new Project($db, $deleteId);
        if ($project->deleteProject()) {
            $message = "تم حذف المشروع بنجاح!";
        } else {
            $message = "حدث خطأ أثناء حذف المشروع.";
        }
    }

    // تعديل مشروع
    if (isset($_POST['update_project'])) {
        $project_id = $_POST['project_id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $leader_id = $_POST['leader_id'];

        $project = new Project($db, $project_id);
        $project->setProjectName($name);
        $project->setProjectDescription($description);
        $project->setStartDate($start_date);
        $project->setEndDate($end_date);
        $project->setLeaderId($leader_id);

        if ($project->updateProjectDetails()) {
            $message = "تم تحديث المشروع بنجاح!";
        } else {
            $message = "حدث خطأ أثناء تحديث المشروع.";
        }
    }

    // جلب جميع المشاريع باستخدام الكلاس
    $projects = Project::getAllProjects($db);

} catch (PDOException $e) {
    $message = "خطأ في قاعدة البيانات: " . $e->getMessage();
} catch (Exception $e) {
    $message = "حدث خطأ غير متوقع: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض المشاريع</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            color: #333;
        }

        .container {
            max-width: 1000px;
            margin: 50px auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #ccc;
        }

        th, td {
            padding: 10px;
            text-align: center;
        }

        .action-btn {
            color: white;
            background-color: #007bff;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .action-btn:hover {
            background-color: #0056b3;
        }

        .message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            max-width: 1000px;
            margin: 20px auto;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>قائمة المشاريع</h2>

    <?php if (isset($message)): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <!-- عرض قائمة المشاريع -->
    <table>
        <thead>
            <tr>
                <th>رقم المشروع</th>
                <th>اسم المشروع</th>
                <th>الوصف</th>
                <th>تاريخ البداية</th>
                <th>تاريخ النهاية</th>
                <th>قائد الفريق</th>
                <th>الإجراء</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($projects as $project): ?>
                <tr>
                    <td><?php echo htmlspecialchars($project['project_id']); ?></td>
                    <td><?php echo htmlspecialchars($project['project_name']); ?></td>
                    <td><?php echo htmlspecialchars($project['description']); ?></td>
                    <td><?php echo htmlspecialchars($project['start_date']); ?></td>
                    <td><?php echo htmlspecialchars($project['end_date']); ?></td>
                    <td><?php echo htmlspecialchars($project['leader_name']); ?></td>
                    <td>
                        <a href="edit_project.php?project_id=<?php echo $project['project_id']; ?>">
                            <button class="action-btn">تعديل</button>
                        </a>
                        <a href="?delete_id=<?php echo $project['project_id']; ?>" onclick="return confirm('هل أنت متأكد من أنك تريد حذف هذا المشروع؟')">
                            <button class="action-btn" style="background-color: red;">حذف</button>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
