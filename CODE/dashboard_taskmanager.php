<?php
// تضمين الاتصال بقاعدة البيانات وكلاسات FileManager و TaskManager و TeamMember
include 'Database.php';
include 'TaskManager.php';

// إنشاء كائن من الاتصال بقاعدة البيانات
$db = new DatabaseConnection();
$taskManager = new TaskManager($db->getConnection());

// إذا تم إرسال البيانات من النموذج، إضافة المهمة
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_task'])) {
    $projectId = $_POST['project_id'];
    $title = $_POST['task_title'];
    $description = $_POST['task_description'];
    $assignedTo = $_POST['assigned_to'];
    $dueDate = $_POST['due_date'];
    $taskManager->addTask($projectId, $title, $description, $assignedTo, $dueDate);
}

// جلب المهام الخاصة بالمشروع
$tasks = $taskManager->getTasksByProject(1); // هنا نستخدم ID مشروع ثابت على سبيل المثال

// جلب المستخدمين المرتبطين بالمشروع عبر كلاس TeamMember
$users = $taskManager->getUsersByProject(1);  // جلب أعضاء المشروع مع ID المشروع
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم لإدارة المهام</title>
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

        label {
            display: block;
            margin-top: 10px;
            color: #34495e;
            font-size: 14px;
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
        <h1>إدارة المهام</h1>

        <!-- نموذج إضافة مهمة جديدة -->
        <form action="" method="POST">
            <label for="task_title">عنوان المهمة:</label>
            <input type="text" id="task_title" name="task_title" required>

            <label for="task_description">وصف المهمة:</label>
            <textarea id="task_description" name="task_description" required></textarea>

            <label for="assigned_to">المكلف بالمهمة:</label>
            <select id="assigned_to" name="assigned_to" required>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user['user_id']; ?>"><?php echo $user['name']; ?></option>
                <?php endforeach; ?>
            </select>

            <label for="due_date">تاريخ التسليم:</label>
            <input type="date" id="due_date" name="due_date" required>
            
            <input type="hidden" name="project_id" value="1"> <!-- ID المشروع ثابت هنا -->
            <button type="submit" name="add_task">إضافة المهمة</button>
        </form>

        <!-- عرض قائمة المهام -->
        <h2>قائمة المهام</h2>
        <table>
            <tr>
                <th>العنوان</th>
                <th>المكلف بالمهمة</th>
                <th>التاريخ النهائي</th>
                <th>الحالة</th>
                <th>الإجراءات</th>
            </tr>
            <?php foreach ($tasks as $task): ?>
            <tr>
                <td><?php echo $task['title']; ?></td>
                <td>
                    <?php
                    // جلب اسم المستخدم المكلف بالمهمة
                    $assignedTo = $task['assigned_to'];
                    $query = "SELECT name FROM users WHERE user_id = :assigned_to";
                    $stmt = $connection->prepare($query);
                    $stmt->bindParam(':assigned_to', $assignedTo);
                    $stmt->execute();
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    echo $user['name'];
                    ?>
                </td>
                <td><?php echo $task['due_date']; ?></td>
                <td><?php echo $task['status']; ?></td>
                <td>
                    <a href="task_details.php?task_id=<?php echo $task['task_id']; ?>">عرض التفاصيل</a> |
                    <a href="edit_task.php?task_id=<?php echo $task['task_id']; ?>">تعديل</a> |
                    <a href="delete_task.php?task_id=<?php echo $task['task_id']; ?>">حذف</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

</body>
</html>
