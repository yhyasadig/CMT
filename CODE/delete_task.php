<?php
// بدء الجلسة
session_start();

// تضمين الاتصال بقاعدة البيانات وكلاسات TaskManager
include 'Database.php';
include 'TaskManager.php';

// إنشاء كائن من الاتصال بقاعدة البيانات
$db = new DatabaseConnection();
$taskManager = new TaskManager($db->getConnection());  // استخدم الاتصال من الكائن $db

// التحقق من وجود معرف المهمة في الرابط
if (isset($_GET['task_id'])) {
    $taskId = $_GET['task_id'];

    // حذف المهمة
    if ($taskManager->deleteTask($taskId)) {
        $message = "تم حذف المهمة بنجاح.";
    } else {
        $message = "حدث خطأ أثناء حذف المهمة.";
    }
} else {
    $message = "لم يتم تحديد المهمة.";
}

?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حذف المهمة</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h1 {
            color: #2c3e50;
            text-align: center;
        }

        .message {
            color: green;
            text-align: center;
            font-size: 18px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>حذف المهمة</h1>

    <!-- عرض رسالة في حالة وجود أي خطأ أو نجاح -->
    <?php if (isset($message)): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>
</div>

</body>
</html>
