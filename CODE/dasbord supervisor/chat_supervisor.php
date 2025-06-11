<?php
session_start();
require_once 'Chat.php';
require_once 'Database.php';

try {
    // تحقق من تسجيل الدخول فقط (بدون التحقق من leader_id)
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'supervis') {
        throw new Exception("الدخول مرفوض.");
    }

    $dbConnection = (new DatabaseConnection())->getConnection();
    $chatService = new Chat();
    $supervisorId = $_SESSION['user_id'];

    // جلب كل المشاريع
    $stmt = $dbConnection->prepare("SELECT * FROM projects");
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $selectedProjectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : null;
    $messages = $selectedProjectId ? $chatService->getMessagesByProject($selectedProjectId) : [];

} catch (Exception $e) {
    echo "<p style='color:red; text-align:center; font-weight:bold;'>{$e->getMessage()}</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>دردشة المشرف</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #e9ecef;
        }

        .container {
            max-width: 850px;
            margin: 40px auto;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 25px;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-box {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 6px;
            background-color: #f9f9f9;
            margin-top: 20px;
        }

        .message {
            padding: 10px 15px;
            margin: 10px 0;
            border-radius: 10px;
            max-width: 70%;
            word-wrap: break-word;
        }

        .sent {
            background-color: #d1ecf1;
            margin-left: auto;
        }

        .received {
            background-color: #f8d7da;
            margin-right: auto;
        }

        .sender-name {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        .chat-form {
            margin-top: 20px;
        }

        textarea {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            resize: none;
        }

        button {
            margin-top: 10px;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background-color: #0056b3;
        }

        select {
            padding: 6px 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="top-bar">
        <h2>دردشات كل المشاريع</h2>
        <form method="get">
            <label for="project">اختر المشروع:</label>
            <select name="project_id" id="project" onchange="this.form.submit()">
                <option value="">-- اختر مشروعًا --</option>
                <?php foreach ($projects as $project): ?>
                    <option value="<?= $project['project_id'] ?>" <?= ($selectedProjectId == $project['project_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($project['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if ($selectedProjectId): ?>
        <div class="chat-box">
            <?php foreach ($messages as $message): ?>
                <?php $isOwn = ($message['sender_id'] == $_SESSION['user_id']); ?>
                <div class="message <?= $isOwn ? 'sent' : 'received' ?>">
                    <span class="sender-name"><?= htmlspecialchars($message['sender_name']) ?> (<?= $message['role'] ?>)</span>
                    <?= nl2br(htmlspecialchars($message['message'])) ?>
                    <small><?= $message['created_at'] ?></small>
                </div>
            <?php endforeach; ?>
        </div>

        <form method="post" action="send_chat.php" class="chat-form">
            <input type="hidden" name="project_id" value="<?= $selectedProjectId ?>">
            <textarea name="message" rows="3" placeholder="اكتب رسالتك هنا..." required></textarea>
            <button type="submit">إرسال</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
