<?php
session_start();  // يجب أن تكون هذه السطر في بداية الملف

// تضمين الاتصال بقاعدة البيانات وكلاسات FileManager و TaskManager و Comment و Rating و Notifications
include 'Database.php';
include 'FileManager.php';
include 'TaskManager.php';
include 'Comment.php';  // تضمين كلاس التعليقات
include 'Rating.php';   // تضمين كلاس التقييم
include 'Notifications.php'; // تضمين كلاس الإشعارات

// التحقق إذا كان المستخدم مسجلاً دخوله
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");  // إعادة التوجيه إلى صفحة تسجيل الدخول إذا لم يكن المستخدم مسجلاً دخوله
    exit();
}

// التحقق إذا كان المستخدم هو مشرف أو عضو مساعد
if ($_SESSION['role'] != 'supervis' && $_SESSION['role'] != 'automember') {
    echo "<div class='error-message'>لا يمكنك إضافة التقييم. يجب أن تكون مشرفًا أو عضوًا مساعدًا.</div>";
    exit();
}

$projectId = $_SESSION['project_id'];  // الحصول على projectId من الجلسة

// إنشاء كائن من الاتصال بقاعدة البيانات
$db = new DatabaseConnection();
$fileManager = new FileManager($db->getConnection());
$taskManager = new TaskManager($db->getConnection());
$notificationManager = new Notifications($db);  // إنشاء كائن من كلاس Notifications

// التحقق إذا تم إرسال البيانات من النموذج، إضافة المهمة
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_task'])) {
    $title = $_POST['task_title'];
    $description = $_POST['task_description'];
    $assignedTo = $_POST['assigned_to'];
    $dueDate = $_POST['due_date'];
    $taskManager->addTask($projectId, $title, $description, $assignedTo, $dueDate);
}

// التحقق إذا تم إرسال تقييم عبر POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_rating'])) {
    $taskId = $_POST['task_id'];  // معرف المهمة
    $score = $_POST['rating_score'];  // درجة التقييم
    $userId = $_SESSION['user_id'];  // معرف المستخدم الذي يضيف التقييم (المشرف)

    // التحقق إذا كان المستخدم مشرفًا أو قائد الفريق
    $userRole = $_SESSION['role']; // دور المستخدم
    if ($userRole == 'supervis' || $userRole == 'automember') {
        
        // التحقق إذا كانت المهمة تحتوي على ملف قبل السماح بالتقييم
        $files = $fileManager->getFilesByTask($taskId);
        if (empty($files)) {
            echo "<div class='error-message'>لا يمكنك تقييم المهمة قبل رفع الملف.</div>";
        } else {
            // إنشاء كائن من كلاس Rating
            $timestamp = date('Y-m-d H:i:s'); // الوقت الحالي
            $ratingManager = new Rating(null, $taskId, $userId, $score, $timestamp);
            
            // إضافة التقييم إلى قاعدة البيانات باستخدام saveToDatabase
            if ($ratingManager->saveToDatabase($db->getConnection())) {
                // إرسال إشعار للمستخدم الذي تم تقييمه
                $message = "تم إضافة تقييم لمهمتك.";
                $notificationManager->sendNotification($userId, $message); // إرسال إشعار
                echo "<div class='message'>تم إضافة التقييم بنجاح!</div>";
            } else {
                echo "<div class='error-message'>حدث خطأ في إضافة التقييم.</div>";
            }
        }
    } else {
        echo "<div class='error-message'>لا يمكنك إضافة التقييم. يجب أن تكون مشرفًا أو قائد الفريق.</div>";
    }
}

// التحقق إذا تم إرسال تعليق عبر POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment_text'])) {
    $taskId = $_POST['task_id'];  // معرف المهمة
    $commentText = $_POST['comment_text'];  // نص التعليق
    $userId = $_SESSION['user_id'];  // معرف المستخدم الذي يضيف التعليق

    // التحقق إذا كان المستخدم مشرفًا أو قائد الفريق
    $userRole = $_SESSION['role']; // دور المستخدم
    if ($userRole == 'supervis' || $userRole == 'automember') {
        // السماح للمشرفين بإضافة التعليقات حتى لو لم يكونوا المكلفين بالمهمة
        $comment = new Comment(null, $taskId, $userId, $commentText);

        // إضافة التعليق إلى قاعدة البيانات
        if ($comment->saveToDatabase($db)) {
            echo "<div class='message'>تم إضافة التعليق بنجاح!</div>";
        } else {
            echo "<div class='error-message'>حدث خطأ في إضافة التعليق.</div>";
        }
    } else {
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
}

// جلب المهام الخاصة بالمشروع
$tasks = $taskManager->getTasksByProject($projectId); // استخدام project_id من الجلسة

// جلب المستخدمين المرتبطين بنفس project_id و role = 'student' أو role = 'automember' في الجلسة
$users = $taskManager->getUsersByProject($projectId);  // جلب المستخدمين بناءً على project_id
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
    <h1>إدارة المهام</h1>

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
        
        <input type="hidden" name="project_id" value="<?php echo $projectId; ?>">
        <button type="submit" name="add_task">إضافة المهمة</button>
    </form>

    <h2>قائمة المهام</h2>
    <table>
        <tr>
            <th>العنوان</th>
            <th>المكلف بالمهمة</th>
            <th>التاريخ النهائي</th>
            <th>الحالة</th>
            <th>الإجراءات</th>
            <th>التقييم</th>
            <th>التعليقات</th>
            <th>الملفات</th>
        </tr>
        <?php foreach ($tasks as $task): ?>
        <tr>
            <td><?php echo $task['title']; ?></td>
            <td><?php
                $assignedTo = $task['assigned_to'];
                $query = "SELECT name FROM users WHERE user_id = :assigned_to";
                $stmt = $db->getConnection()->prepare($query);
                $stmt->bindParam(':assigned_to', $assignedTo);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                echo $user['name']; ?></td>
            <td><?php echo $task['due_date']; ?></td>
            <td><?php echo $task['status']; ?></td>
            <td><a href="task_details.php?task_id=<?php echo $task['task_id']; ?>">عرض التفاصيل</a> | <a href="edit_task.php?task_id=<?php echo $task['task_id']; ?>">تعديل</a> | <a href="delete_task.php?task_id=<?php echo $task['task_id']; ?>">حذف</a></td>
            <td>
                <form action="dashboard_taskmanager.php" method="POST">
                    <input type="hidden" name="task_id" value="<?php echo $task['task_id']; ?>">
                    <label for="rating_score">التقييم (من 1 إلى 100):</label>
                    <input type="number" id="rating_score" name="rating_score" min="1" max="100" required>
                    <button type="submit" name="submit_rating">إضافة التقييم</button>
                </form>
            </td>
            <td>
                <form action="dashboard_taskmanager.php" method="POST">
                    <input type="hidden" name="task_id" value="<?php echo $task['task_id']; ?>">
                    <textarea name="comment_text" placeholder="أضف تعليقك هنا..." required></textarea>
                    <button type="submit">إضافة تعليق</button>
                </form>

                <div class="comment-section">
                    <?php
                    $comments = $taskManager->getCommentsByTask($task['task_id']);
                    foreach ($comments as $comment):
                    ?>
                        <div class="comment">
                            <div class="comment-user"><?php echo "المستخدم: " . $comment['user_name']; ?></div>
                            <div class="comment-text"><?php echo $comment['comment_text']; ?></div>
                            <div class="comment-timestamp"><?php echo $comment['timestamp']; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </td>
            <td>
                <?php
                $files = $fileManager->getFilesByTask($task['task_id']);
                foreach ($files as $file) {
                    echo "<p><a href='" . $file['file_path'] . "' target='_blank'>" . $file['file_name'] . "</a></p>";
                }
                ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
