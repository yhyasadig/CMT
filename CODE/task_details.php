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

    // الحصول على تفاصيل المهمة
    $taskDetails = $taskManager->getTaskById($taskId);  // استخدم getTaskById بدلاً من getTaskDetails

    // التحقق من وجود المهمة
    if (!$taskDetails) {
        $message = "المهمة غير موجودة";
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
    <title>تفاصيل المهمة</title>
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
            color: red;
            text-align: center;
            font-size: 16px;
        }

        .task-detail {
            font-size: 16px;
            color: #333;
        }

        .task-detail label {
            font-weight: bold;
        }

        .task-detail p {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>تفاصيل المهمة</h1>

    <!-- عرض رسالة في حالة عدم وجود المهمة -->
    <?php if (isset($message)): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php else: ?>
        <div class="task-detail">
            <p><label>عنوان المهمة:</label> <?php echo $taskDetails['title']; ?></p>
            <p><label>وصف المهمة:</label> <?php echo $taskDetails['description']; ?></p>
            <p><label>المكلف بالمهمة:</label> 
                <?php
                // جلب اسم المكلف بالمهمة
                $assignedTo = $taskDetails['assigned_to'];
                $query = "SELECT name FROM users WHERE user_id = :assigned_to";
                $stmt = $db->getConnection()->prepare($query);
                $stmt->bindParam(':assigned_to', $assignedTo);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                echo $user['name'];
                ?>
            </p>
            <p><label>تاريخ التسليم:</label> <?php echo $taskDetails['due_date']; ?></p>
            <p><label>الحالة:</label> <?php echo $taskDetails['status']; ?></p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
