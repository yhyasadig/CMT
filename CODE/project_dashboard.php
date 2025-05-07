<?php
session_start(); // تأكد من بدء الجلسة

// التأكد من أن المستخدم متصل وله صلاحيات للوصول إلى هذه الصفحة
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");  // التوجيه إلى صفحة تسجيل الدخول إذا لم يكن متصلاً
    exit();
}

// الاتصال بقاعدة البيانات
include 'Database.php';
$db = new DatabaseConnection();
$connection = $db->getConnection();

$project_id = $_GET['project_id']; // الحصول على project_id من الرابط

// جلب معلومات المشروع بناءً على project_id
$query = "SELECT * FROM projects WHERE project_id = :project_id"; // جلب المشروع بناءً على project_id
$stmt = $connection->prepare($query);
$stmt->bindParam(":project_id", $project_id);
$stmt->execute();
$project = $stmt->fetch(PDO::FETCH_ASSOC);

// التحقق من وجود المشروع
if (!$project) {
    header("Location: projects_list.php");  // إعادة التوجيه إلى قائمة المشاريع إذا لم يكن هناك مشروع
    exit();
}

// جلب المهام الخاصة بالمشروع
$query_tasks = "SELECT * FROM tasks WHERE project_id = :project_id";
$stmt_tasks = $connection->prepare($query_tasks);
$stmt_tasks->bindParam(":project_id", $project_id);
$stmt_tasks->execute();
$tasks = $stmt_tasks->fetchAll(PDO::FETCH_ASSOC);

// جلب ملفات المشروع
$query_files = "SELECT * FROM task_files WHERE project_id = :project_id";
$stmt_files = $connection->prepare($query_files);
$stmt_files->bindParam(":project_id", $project_id);
$stmt_files->execute();
$files = $stmt_files->fetchAll(PDO::FETCH_ASSOC);

// جلب التعليقات الخاصة بالمشروع
$query_comments = "SELECT * FROM project_comments WHERE project_id = :project_id";
$stmt_comments = $connection->prepare($query_comments);
$stmt_comments->bindParam(":project_id", $project_id);
$stmt_comments->execute();
$comments = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل المشروع</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        h1 {
            color: #333;
        }

        .project-details {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .task-item, .file-item, .comment-item {
            background-color: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .task-item h3, .file-item h3, .comment-item h3 {
            margin-bottom: 10px;
        }

        .button-container {
            margin-top: 30px;
        }

        .button-container a {
            padding: 10px 20px;
            margin: 10px;
            background-color: #008CBA;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .button-container a:hover {
            background-color: #007B8F;
        }

        .file-upload, .comment-section {
            margin-top: 30px;
        }

        .file-upload form, .comment-section form {
            display: flex;
            flex-direction: column;
        }

        .file-upload input, .comment-section textarea {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .file-upload button, .comment-section button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border-radius: 5px;
        }

        .back-button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }

        .back-button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

    <h1>تفاصيل المشروع</h1>

    <div class="project-details">
        <h2><?= htmlspecialchars($project['name']); ?></h2>
        <p><strong>الوصف:</strong> <?= htmlspecialchars($project['description']); ?></p>
        <p><strong>الموعد النهائي:</strong> <?= htmlspecialchars($project['end_date']); ?></p>
    </div>

    <div class="task-container">
        <h2>المهام</h2>
        <?php foreach ($tasks as $task): ?>
            <div class="task-item">
                <h3><?= htmlspecialchars($task['task_name']); ?></h3>
                <p><strong>الوصف:</strong> <?= htmlspecialchars($task['description']); ?></p>
                <p><strong>الموعد النهائي:</strong> <?= htmlspecialchars($task['due_date']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="file-container">
        <h2>الملفات</h2>
        <?php foreach ($files as $file): ?>
            <div class="file-item">
                <h3><?= htmlspecialchars($file['file_name']); ?></h3>
                <a href="uploads/<?= htmlspecialchars($file['file_path']); ?>" target="_blank">تحميل الملف</a>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="comment-container">
        <h2>التعليقات</h2>
        <?php foreach ($comments as $comment): ?>
            <div class="comment-item">
                <h3>من <?= htmlspecialchars($comment['user_name']); ?></h3>
                <p><?= htmlspecialchars($comment['comment']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="file-upload">
        <h3>رفع ملف جديد</h3>
        <form action="upload_file.php?project_id=<?= $project_id; ?>" method="POST" enctype="multipart/form-data">
            <input type="file" name="task_file" required>
            <button type="submit">رفع الملف</button>
        </form>
    </div>

    <div class="comment-section">
        <h3>إضافة تعليق جديد</h3>
        <form action="add_comment.php?project_id=<?= $project_id; ?>" method="POST">
            <textarea name="comment" placeholder="اكتب تعليقك هنا..." required></textarea>
            <button type="submit">إضافة تعليق</button>
        </form>
    </div>

    <div class="button-container">
        <a href="supervisors_dashboard.php" class="back-button">الرجوع إلى لوحة التحكم</a>
    </div>

</body>
</html>
