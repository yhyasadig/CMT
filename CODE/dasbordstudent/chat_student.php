<?php
session_start();
require_once 'Chat.php';

try {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['project_id'])) {
        throw new Exception("غير مصرح لك بالدخول.");
    }

    $chatService = new Chat();
    $projectId = $_SESSION['project_id'];
    $currentUserId = $_SESSION['user_id'];
    $messages = $chatService->getMessagesByProject($projectId);

} catch (Exception $e) {
    echo "<p style='text-align:center; color:red; font-weight:bold;'>{$e->getMessage()}</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>دردشة الطالب</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #e9ecef;
            margin: 0;
            padding: 0;
        }

        .chat-container {
            max-width: 850px;
            margin: 40px auto;
            background-color: #ffffff;
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .top-bar button {
            padding: 8px 14px;
            font-size: 14px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .top-bar button:hover {
            background-color: #5a6268;
        }

        .chat-box {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #e0e0e0;
            padding: 15px;
            background-color: #fafafa;
            border-radius: 6px;
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
            text-align: right;
        }

        .received {
            background-color: #f8d7da;
            margin-right: auto;
            text-align: right;
        }

        .message small {
            display: block;
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }

        .sender-name {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }

        .chat-form {
            margin-top: 20px;
        }

        textarea {
            width: 100%;
            resize: none;
            padding: 10px;
            font-size: 14px;
            border-radius: 6px;
            border: 1px solid #ccc;
            margin-bottom: 10px;
        }

        button[type="submit"] {
            padding: 10px 15px;
            background-color: #007bff;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="chat-container">
    <div class="top-bar">
        <h2>دردشة مشروعك</h2>
        <button onclick="window.history.back();">رجوع</button>
    </div>

    <div class="chat-box">
        <?php foreach ($messages as $message): ?>
            <?php
                $isOwnMessage = ($message['sender_id'] == $currentUserId);
                $messageClass = $isOwnMessage ? 'sent' : 'received';
            ?>
            <div class="message <?= $messageClass ?>">
                <span class="sender-name">
                    <?= htmlspecialchars($message['sender_name']) ?> (<?= $message['role'] ?>)
                </span>
                <?= nl2br(htmlspecialchars($message['message'])) ?>
                <small><?= $message['created_at'] ?></small>
            </div>
        <?php endforeach; ?>
    </div>

    <form method="post" action="send_chat.php" class="chat-form">
        <textarea name="message" rows="3" placeholder="اكتب رسالتك هنا..." required></textarea>
        <button type="submit">إرسال</button>
    </form>
</div>

</body>
</html>
