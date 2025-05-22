<?php
// بدء الجلسة
session_start();

// تضمين الاتصال بقاعدة البيانات وكلاسات TaskManager
include 'Database.php';
include 'TaskManager.php';

// إنشاء كائن من الاتصال بقاعدة البيانات
$db = new DatabaseConnection();
$taskManager = new TaskManager($db->getConnection());

// التحقق من وجود معرف المهمة في الرابط
if (isset($_GET['task_id'])) {
    $taskId = $_GET['task_id'];

    // الحصول على تفاصيل المهمة
    $taskDetails = $taskManager->getTaskById($taskId);

    // التحقق من وجود المهمة
    if (!$taskDetails) {
        $message = "المهمة غير موجودة";
    }

    // إذا تم إرسال البيانات من النموذج، تحديث المهمة
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_task'])) {
        $title = $_POST['task_title'];
        $description = $_POST['task_description'];
        $assignedTo = $_POST['assigned_to'];
        $dueDate = $_POST['due_date'];
        $status = $_POST['status'];

        $taskManager->updateTask($taskId, $title, $description, $assignedTo, $dueDate, $status);
        $message = "تم تحديث المهمة بنجاح.";
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
    <title>تعديل المهمة</title>
    <style>
        /* نفس الـ CSS السابق */
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

        label {
            font-size: 14px;
            color: #333;
        }

        input[type="text"], input[type="date"], textarea, select {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            padding: 10px 20px;
            background-color: #27ae60;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 20px;
        }

        button:hover {
            background-color: #2ecc71;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>تعديل المهمة</h1>

    <!-- عرض رسالة في حالة وجود أي خطأ أو نجاح -->
    <?php if (isset($message)): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>

    <!-- نموذج تعديل المهمة -->
    <form action="" method="POST">
        <label for="task_title">عنوان المهمة:</label>
        <input type="text" id="task_title" name="task_title" value="<?php echo $taskDetails['title']; ?>" required>

        <label for="task_description">وصف المهمة:</label>
        <textarea id="task_description" name="task_description" required><?php echo $taskDetails['description']; ?></textarea>

        <label for="assigned_to">المكلف بالمهمة:</label>
        <select id="assigned_to" name="assigned_to" required>
            <?php
            // جلب المستخدمين المكلفين بالمهمة
            $users = $taskManager->getUsersByProject($_SESSION['project_id']);
            foreach ($users as $user):
            ?>
                <option value="<?php echo $user['user_id']; ?>" <?php echo ($user['user_id'] == $taskDetails['assigned_to']) ? 'selected' : ''; ?>>
                    <?php echo $user['name']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="due_date">تاريخ التسليم:</label>
        <input type="date" id="due_date" name="due_date" value="<?php echo $taskDetails['due_date']; ?>" required>

        <label for="status">الحالة:</label>
        <select id="status" name="status" required>
            <option value="pending" <?php echo ($taskDetails['status'] == 'pending') ? 'selected' : ''; ?>>قيد التنفيذ</option>
            <option value="completed" <?php echo ($taskDetails['status'] == 'completed') ? 'selected' : ''; ?>>مكتملة</option>
        </select>

        <button type="submit" name="update_task">تحديث المهمة</button>
    </form>
</div>

</body>
</html>
