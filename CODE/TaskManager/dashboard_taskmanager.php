<?php
session_start();

include 'Database.php';
include 'FileManager.php';
include 'TaskManager.php';
include 'Comment.php';
include 'Rating.php';
include 'Notifications.php';
include 'RatingFactory.php'; //   باترين ديزان
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] != 'supervis' && $_SESSION['role'] != 'automember') {
    echo "<div class='error-message'>لا يمكنك إضافة التقييم. يجب أن تكون مشرفًا أو عضوًا مساعدًا.</div>";
    exit();
}

$projectId = $_SESSION['project_id'];

$db = new DatabaseConnection();
$fileManager = new FileManager($db->getConnection());
$taskManager = new TaskManager($db->getConnection());
$notificationManager = new Notifications($db);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_task'])) {
    $taskTitle = $_POST['task_title'];
    $taskDescription = $_POST['task_description'];
    $assignedTo = $_POST['assigned_to'];
    $dueDate = $_POST['due_date'];
    try {
        $taskManager->addTask($projectId, $taskTitle, $taskDescription, $assignedTo, $dueDate);
    } catch (Exception $e) {
        echo "<div class='error-message'>حدث خطأ في إضافة المهمة: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_rating'])) {
    $taskId = $_POST['task_id'];
    $score = $_POST['rating_score'];
    $userId = $_SESSION['user_id'];
    $userRole = $_SESSION['role'];

    if ($userRole == 'supervis' || $userRole == 'automember') {
        try {
            $files = $fileManager->getFilesByTask($taskId);
            if (empty($files)) {
                echo "<div class='error-message'>لا يمكنك تقييم المهمة قبل رفع الملف.</div>";
            } else {
                $timestamp = date('Y-m-d H:i:s');
                $ratingManager = RatingFactory::createRating(null, $taskId, $userId, $score, $timestamp);
                if ($ratingManager->saveToDatabase($db->getConnection())) {
                    $message = "تم إضافة تقييم لمهمتك.";
                    $notificationManager->sendNotification($userId, $message);
                    echo "<div class='message'>تم إضافة التقييم بنجاح!</div>";
                } else {
                    echo "<div class='error-message'>حدث خطأ في إضافة التقييم.</div>";
                }
            }
        } catch (Exception $e) {
            echo "<div class='error-message'>حدث خطأ في النظام: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } else {
        echo "<div class='error-message'>لا يمكنك إضافة التقييم. يجب أن تكون مشرفًا أو قائد الفريق.</div>";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment_text'])) {
    $taskId = $_POST['task_id'];
    $commentText = $_POST['comment_text'];
    $userId = $_SESSION['user_id'];
    $userRole = $_SESSION['role'];

    try {
        if ($userRole == 'supervis' || $userRole == 'automember') {
            $comment = new Comment(null, $taskId, $userId, $commentText);
            if ($comment->saveToDatabase($db)) {
                echo "<div class='message'>تم إضافة التعليق بنجاح!</div>";
            } else {
                echo "<div class='error-message'>حدث خطأ في إضافة التعليق.</div>";
            }
        } else {
            $task = $taskManager->getTaskById($taskId);
            if ($task && $task['assigned_to'] == $userId) {
                $comment = new Comment(null, $taskId, $userId, $commentText);
                if ($comment->saveToDatabase($db)) {
                    echo "<div class='message'>تم إضافة التعليق بنجاح!</div>";
                } else {
                    echo "<div class='error-message'>حدث خطأ في إضافة التعليق.</div>";
                }
            } else {
                echo "<div class='error-message'>لا يمكنك إضافة تعليق لمهمة لا تخصك.</div>";
            }
        }
    } catch (Exception $e) {
        echo "<div class='error-message'>حدث خطأ في النظام: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

try {
    $tasks = $taskManager->getTasksByProject($projectId);
} catch (Exception $e) {
    echo "<div class='error-message'>حدث خطأ في جلب المهام: " . htmlspecialchars($e->getMessage()) . "</div>";
    $tasks = [];
}

try {
    $users = $taskManager->getUsersByProject($projectId);
} catch (Exception $e) {
    echo "<div class='error-message'>حدث خطأ في جلب المستخدمين: " . htmlspecialchars($e->getMessage()) . "</div>";
    $users = [];
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
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
        }
        button {
            padding: 10px 20px;
            background-color: #27ae60;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
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
            <?php
            try {
                foreach ($users as $user) {
                    $userId = $user['user_id'];
                    $userName = $user['name'];
                    echo "<option value='" . htmlspecialchars($userId) . "'>" . htmlspecialchars($userName) . "</option>";
                }
            } catch (Exception $e) {
                echo "<option disabled>حدث خطأ في تحميل المستخدمين</option>";
            }
            ?>
        </select>

        <label for="due_date">تاريخ التسليم:</label>
        <input type="date" id="due_date" name="due_date" required>
        
        <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($projectId); ?>">
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
            <td><?php echo htmlspecialchars($task['title']); ?></td>
            <td>
                <?php
                $assignedTo = $task['assigned_to'];
                try {
                    $query = "SELECT name FROM users WHERE user_id = :assignedTo";
                    $stmt = $db->getConnection()->prepare($query);
                    $stmt->bindParam(':assignedTo', $assignedTo);
                    $stmt->execute();
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    echo htmlspecialchars($user['name']);
                } catch (Exception $e) {
                    echo "خطأ في جلب اسم المستخدم";
                }
                ?>
            </td>
            <td><?php echo htmlspecialchars($task['due_date']); ?></td>
            <td><?php echo htmlspecialchars($task['status']); ?></td>
            <td>
                <a href="task_details.php?task_id=<?php echo urlencode($task['task_id']); ?>">عرض التفاصيل</a> | 
                <a href="edit_task.php?task_id=<?php echo urlencode($task['task_id']); ?>">تعديل</a> | 
                <a href="delete_task.php?task_id=<?php echo urlencode($task['task_id']); ?>">حذف</a>
            </td>
            <td>
                <form action="dashboard_taskmanager.php" method="POST">
                    <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task['task_id']); ?>">
                    <label for="rating_score">التقييم (من 1 إلى 100):</label>
                    <input type="number" id="rating_score" name="rating_score" min="1" max="100" required>
                    <button type="submit" name="submit_rating">إضافة التقييم</button>
                </form>
            </td>
            <td>
                <form action="dashboard_taskmanager.php" method="POST">
                    <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task['task_id']); ?>">
                    <textarea name="comment_text" placeholder="أضف تعليقك هنا..." required></textarea>
                    <button type="submit">إضافة تعليق</button>
                </form>

                <div class="comment-section">
                    <?php
                    try {
                        $comments = $taskManager->getCommentsByTask($task['task_id']);
                        foreach ($comments as $comment):
                    ?>
                        <div class="comment">
                            <div class="comment-user"><?php echo "المستخدم: " . htmlspecialchars($comment['user_name']); ?></div>
                            <div class="comment-text"><?php echo htmlspecialchars($comment['comment_text']); ?></div>
                            <div class="comment-timestamp"><?php echo htmlspecialchars($comment['timestamp']); ?></div>
                        </div>
                    <?php
                        endforeach;
                    } catch (Exception $e) {
                        echo "<div class='error-message'>حدث خطأ في جلب التعليقات.</div>";
                    }
                    ?>
                </div>
            </td>
            <td>
                <?php
                try {
                    $files = $fileManager->getFilesByTask($task['task_id']);
                    foreach ($files as $file) {
                        echo "<p><a href='" . htmlspecialchars($file['file_path']) . "' target='_blank'>" . htmlspecialchars($file['file_name']) . "</a></p>";
                    }
                } catch (Exception $e) {
                    echo "<p>خطأ في تحميل الملفات</p>";
                }
                ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
