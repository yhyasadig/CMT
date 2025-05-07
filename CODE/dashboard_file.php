<?php
// تضمين الاتصال بقاعدة البيانات وكلاسات FileManager و TaskManager
include 'Database.php';
include 'FileManager.php';
include 'TaskManager.php';

// إنشاء كائن من الاتصال بقاعدة البيانات
$db = new DatabaseConnection();
$fileManager = new FileManager($db->getConnection());
$taskManager = new TaskManager($db->getConnection());

// التحقق إذا تم إرسال الملف عبر POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['task_file'])) {
    $taskId = $_POST['task_id'];  // معرف المهمة التي يتم رفع الملف لها
    $file = $_FILES['task_file'];  // ملف المهمة
    $uploadedBy = $_SESSION['user_id'];  // معرف المستخدم الذي رفع الملف (يجب أن يكون موجود في الجلسة)

    // رفع الملف عبر كلاس FileManager
    $result = $fileManager->uploadTaskFile($taskId, $file, $uploadedBy);

    if ($result === true) {
        echo "<div class='message'>تم رفع الملف بنجاح!</div>";
    } else {
        echo "<div class='error-message'>حدث خطأ في رفع الملف: " . $result . "</div>";
    }
}

// جلب جميع المهام الخاصة بالمشروع
$projectId = 1;  // معرف المشروع (يمكن تغييره ديناميكيًا حسب المشروع المطلوب)
$tasks = $taskManager->getTasksByProject($projectId);  // جلب المهام المرتبطة بالمشروع

?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رفع ملفات المهام - لوحة التحكم</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 50px auto;
            background-color: #ffffff;
            padding: 20px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h1 {
            color: #2c3e50;
            text-align: center;
        }

        label {
            font-size: 14px;
            color: #34495e;
        }

        input[type="file"], input[type="hidden"], button {
            width: 100%;
            padding: 12px;
            font-size: 14px;
            margin-top: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        button {
            background-color: #27ae60;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #2ecc71;
        }

        table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: center;
            font-size: 14px;
        }

        th {
            background-color: #3498db;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        a {
            color: #3498db;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .message {
            color: #27ae60;
            font-size: 16px;
            margin-top: 20px;
            text-align: center;
        }

        .error-message {
            color: #e74c3c;
            font-size: 16px;
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>رفع ملف المهمة</h1>

    <!-- عرض جميع المهام الخاصة بالمشروع -->
    <h2>قائمة المهام الخاصة بالمشروع</h2>
    <table>
        <tr>
            <th>العنوان</th>
            <th>التاريخ النهائي</th>
            <th>الحالة</th>
            <th>رفع الملفات</th>
        </tr>
        <?php foreach ($tasks as $task): ?>
        <tr>
            <td><?php echo $task['title']; ?></td>
            <td><?php echo $task['due_date']; ?></td>
            <td><?php echo $task['status']; ?></td>
            <td>
                <!-- نموذج رفع الملف لكل مهمة -->
                <form action="dashboard_file.php" method="POST" enctype="multipart/form-data">
                    <input type="file" name="task_file" required>
                    <input type="hidden" name="task_id" value="<?php echo $task['task_id']; ?>">  <!-- ربط المهمة بالملف -->
                    <button type="submit" name="upload_file">رفع الملف</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
