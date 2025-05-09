<?php
session_start();  // يجب أن تكون هذه السطر في بداية الملف

// تضمين الاتصال بقاعدة البيانات وكلاسات FileManager و TaskManager و Comment
include 'Database.php';
include 'FileManager.php';
include 'TaskManager.php';
include 'Comment.php'; // تضمين كلاس التعليقات

// التحقق إذا كان المستخدم مسجلاً دخوله
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");  // إعادة التوجيه إلى صفحة تسجيل الدخول إذا لم يكن المستخدم مسجلاً دخوله
    exit();
}

// إنشاء كائن من الاتصال بقاعدة البيانات
$db = new DatabaseConnection();
$fileManager = new FileManager($db->getConnection());
$taskManager = new TaskManager($db->getConnection());

// التحقق إذا تم إرسال الملف عبر POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['task_file'])) {
    $taskId = $_POST['task_id'];  // معرف المهمة التي يتم رفع الملف لها
    $file = $_FILES['task_file'];  // ملف المهمة
    $uploadedBy = $_SESSION['user_id'];  // معرف المستخدم الذي رفع الملف (يجب أن يكون موجود في الجلسة)

    // التحقق إذا كان المستخدم هو من يرفع الملف للمهمة الخاصة به
    $task = $taskManager->getTaskById($taskId);  // جلب المهمة باستخدام ID المهمة
    if ($task && $task['assigned_to'] == $uploadedBy) {
        // رفع الملف عبر كلاس FileManager
        $result = $fileManager->uploadTaskFile($taskId, $file, $uploadedBy);

        if ($result === true) {
            echo "<div class='message'>تم رفع الملف بنجاح!</div>";
        } else {
            echo "<div class='error-message'>حدث خطأ في رفع الملف: " . $result . "</div>";
        }
    } else {
        echo "<div class='error-message'>لا يمكنك رفع الملف لمهمة لا تخصك.</div>";
    }
}

// التحقق إذا تم إرسال تعليق عبر POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment_text'])) {
    $taskId = $_POST['task_id'];  // معرف المهمة
    $commentText = $_POST['comment_text'];  // نص التعليق
    $userId = $_SESSION['user_id'];  // معرف المستخدم الذي يضيف التعليق

    // التحقق إذا كان المستخدم هو المعني بالمهمة
    $task = $taskManager->getTaskById($taskId);  // جلب المهمة باستخدام ID المهمة
    if ($task && $task['assigned_to'] == $userId) {
        // إنشاء كائن من كلاس Comment
        $comment = new Comment(null, $taskId, $userId, $commentText);

        // إضافة التعليق إلى قاعدة البيانات
        if ($comment->saveToDatabase($db)) {
            echo "<div class='message'>تم إضافة التعليق بنجاح!</div>";
        } else {
            echo "<div class='error-message'>حدث خطأ في إضافة التعليق.</div>";
        }
    } else {
        echo "<div class='error-message'>لا يمكنك إضافة تعليق لمهمة لا تخصك.</div>";
    }
}

// جلب المهام الخاصة بالمشروع بناءً على project_id فقط
$projectId = 1;  // معرف المشروع (يمكن تغييره ديناميكيًا حسب المشروع المطلوب)
$userId = $_SESSION['user_id'];  // معرف المستخدم الذي تم تسجيل دخوله

// جلب المهام الخاصة بالمشروع
$tasks = $taskManager->getTasksByProject($projectId);  // جلب المهام الخاصة بالمشروع فقط
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رفع ملفات المهام - لوحة التحكم</title>
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

        input[type="file"], input[type="hidden"], button, textarea {
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

        .comment-section {
            margin-top: 20px;
        }

        .comment {
            background-color: #f0f0f0;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
        }

        .comment-user {
            font-weight: bold;
        }

        textarea {
            height: 100px;
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
            <th>التعليقات</th>
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
            <td>
                <!-- نموذج إضافة تعليق لكل مهمة -->
                <form action="dashboard_file.php" method="POST">
                    <input type="hidden" name="task_id" value="<?php echo $task['task_id']; ?>">
                    <textarea name="comment_text" placeholder="أضف تعليقك هنا..." required></textarea>
                    <button type="submit">إضافة تعليق</button>
                </form>

                <!-- عرض التعليقات المرتبطة بالمهمة -->
                <div class="comment-section">
                    <?php
                    // استرجاع التعليقات المتعلقة بالمهمة
                    $comments = $taskManager->getCommentsByTask($task['task_id']);
                    foreach ($comments as $comment):
                    ?>
                        <div class="comment">
                            <div class="comment-user"><?php echo "المستخدم " . $comment['user_id']; ?></div>
                            <div class="comment-text"><?php echo $comment['comment_text']; ?></div>
                            <div class="comment-timestamp"><?php echo $comment['timestamp']; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
