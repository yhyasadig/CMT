<?php
session_start();
require_once 'Chat.php';

// تحقق من أن المستخدم مسجل الدخول
try {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        throw new Exception("الدخول مرفوض.");
    }

    $senderId = $_SESSION['user_id'];
    $role = $_SESSION['role'];
    $message = isset($_POST['message']) ? trim($_POST['message']) : null;

    // تحديد project_id
    if ($role === 'supervisor') {
        // المشرف يرسل مع project_id في الفورم
        if (!isset($_POST['project_id'])) {
            throw new Exception("يجب تحديد المشروع.");
        }
        $projectId = (int) $_POST['project_id'];
    } else {
        // الطالب لديه مشروع واحد فقط في الجلسة
        if (!isset($_SESSION['project_id'])) {
            throw new Exception("لم يتم ربطك بأي مشروع.");
        }
        $projectId = $_SESSION['project_id'];
    }

    // إرسال الرسالة
    if (empty($message)) {
        throw new Exception("نص الرسالة مطلوب.");
    }

    // داخل try-catch لإرسال الرسالة
    try {
        $chat = new Chat();
        $chat->sendMessage($projectId, $senderId, $message);

        // إعادة التوجيه حسب الدور
        if ($role === 'supervisor') {
            header("Location: chat_supervisor.php?project_id=$projectId");
        } else {
            header("Location: chat_student.php");
        }
        exit();

    } catch (Exception $e) {
        throw new Exception("حدث خطأ أثناء إرسال الرسالة: " . $e->getMessage());
    }

} catch (Exception $e) {
    die($e->getMessage());
}
?>
