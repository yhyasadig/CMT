<?php
session_start();
require_once 'Database.php';
require_once 'Report.php';

$db = new DatabaseConnection();
$conn = $db->getConnection();
$report = Report::getInstance($conn); // ✅ استخدام getInstance بدلاً من new Report

$msg = null;

// عند إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senderId = $_SESSION['user_id'] ?? null;
    $userRole = $_SESSION['role'] ?? null;
    $receiverId = $_POST['receiver_id'] ?? null;
    $title = $_POST['title'] ?? '';
    $body = $_POST['body'] ?? '';
    $fileName = null;

    if (!$senderId || !$receiverId || empty($title) || empty($body)) {
        $msg = "❌ الرجاء ملء جميع الحقول المطلوبة.";
    } else {
        // معالجة رفع الملف
        if (isset($_FILES['report_file']) && $_FILES['report_file']['error'] === 0) {
            $uploadDir = 'uploads/reports/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $originalName = basename($_FILES['report_file']['name']);
            $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $originalName);
            $destination = $uploadDir . $fileName;

            if (!move_uploaded_file($_FILES['report_file']['tmp_name'], $destination)) {
                $msg = "❌ فشل في رفع الملف.";
                $fileName = null;
            }
        }

        // إرسال التقرير
        $result = $report->addReport($senderId, $receiverId, $userRole, $title, $body, $fileName);
        $msg = $result ? "✅ تم إرسال التقرير بنجاح" : "❌ حدث خطأ أثناء إرسال التقرير.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>إرسال تقرير</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(to right, #f8f9fa, #e2e6ea);
            padding: 30px;
        }

        .form-container {
            background: #fff;
            padding: 25px;
            max-width: 600px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #333;
        }

        input, textarea, select {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            background-color: #fdfdfd;
        }

        .btn {
            background-color: #28a745;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn:hover {
            background-color: #218838;
        }

        .msg {
            text-align: center;
            font-weight: bold;
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
            background: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>إرسال تقرير إلى مشرف</h2>

    <?php if ($msg): ?>
        <p class='msg'><?= $msg ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label for="receiver_id">اختر المشرف المستلم:</label>
        <select name="receiver_id" id="receiver_id" required>
            <option value="">-- اختر مشرفاً --</option>
            <?php
            $stmt = $conn->prepare("SELECT user_id, name FROM users WHERE role = 'supervisor'");
            $stmt->execute();
            $supervisors = $stmt->fetchAll();
            foreach ($supervisors as $supervisor) {
                echo "<option value='{$supervisor['user_id']}'>{$supervisor['name']}</option>";
            }
            ?>
        </select>

        <label for="title">عنوان التقرير:</label>
        <input type="text" name="title" id="title" required>

        <label for="body">محتوى التقرير:</label>
        <textarea name="body" id="body" rows="6" required></textarea>

        <label for="report_file">رفع ملف التقرير (PDF أو Word):</label>
        <input type="file" name="report_file" id="report_file" accept=".pdf,.doc,.docx">

        <button class="btn" type="submit">إرسال التقرير</button>
    </form>
</div>

</body>
</html>
