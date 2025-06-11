<?php
// التأكد من أن المستخدم هو مشرف
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: login.php");  // إعادة التوجيه إلى صفحة تسجيل الدخول إذا لم يكن مشرف
    exit();
}

// الاتصال بقاعدة البيانات
include 'Database.php';  // تأكد من استخدام الملف المناسب للاتصال بقاعدة البيانات
require_once 'User.php'; // استدعاء كلاس المستخدم

$db = new DatabaseConnection();
$userObj = new User($db);

$message = '';
if (isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];
    
    try {
        if ($userObj->delete($deleteId)) {
            $message = "تم حذف المشرف بنجاح!";
        } else {
            $message = "حدث خطأ أثناء حذف المشرف.";
        }
    } catch (Exception $e) {
        $message = "خطأ: " . $e->getMessage();
    }
}

// جلب قائمة المشرفين
$connection = $db->getConnection();
$query = "SELECT * FROM users WHERE role = 'supervis'";
$stmt = $connection->prepare($query);
$stmt->execute();
$supervisors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>قائمة المشرفين</title>
    <style>
        /* نفس التنسيق السابق */
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #ccc;
        }

        th, td {
            padding: 10px;
            text-align: center;
        }

        .delete-btn {
            color: white;
            background-color: red;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .delete-btn:hover {
            background-color: darkred;
        }

        .message {
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
            color: green;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>قائمة المشرفين</h2>

    <?php if ($message): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <!-- عرض قائمة المشرفين -->
    <table>
        <thead>
            <tr>
                <th>الرقم</th>
                <th>الاسم</th>
                <th>البريد الإلكتروني</th>
                <th>الإجراء</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($supervisors as $supervisor) : ?>
                <tr>
                    <td><?= htmlspecialchars($supervisor['user_id']); ?></td>
                    <td><?= htmlspecialchars($supervisor['name']); ?></td>
                    <td><?= htmlspecialchars($supervisor['email']); ?></td>
                    <td>
                        <a href="?delete_id=<?= htmlspecialchars($supervisor['user_id']); ?>" onclick="return confirm('هل أنت متأكد من حذف هذا المشرف؟');">
                            <button class="delete-btn">حذف</button>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
