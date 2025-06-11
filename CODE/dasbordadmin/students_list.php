<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'Database.php';
require_once 'User.php';

$db = new DatabaseConnection();
$userObj = new User($db);

$message = '';

try {
    // حذف طالب بناءً على المعرف القادم من الرابط
    if (isset($_GET['delete_id'])) {
        $deleteId = (int)$_GET['delete_id'];

        if ($userObj->deleteStudentById($deleteId)) {
            $message = "تم حذف الطالب بنجاح!";
        } else {
            $message = "حدث خطأ أثناء حذف الطالب.";
        }
    }

    // جلب جميع الطلاب
    $students = $userObj->getAllStudents();
} catch (PDOException $e) {
    // معالجة الخطأ
    $message = "حدث خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage();
    $students = []; // لتفادي خطأ في حالة عدم تعريف $students
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>قائمة الطلبة</title>
    <style>
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
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>قائمة الطلبة</h2>

    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

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
            <?php foreach ($students as $student) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($student['user_id']); ?></td>
                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                    <td>
                        <a href="?delete_id=<?php echo (int)$student['user_id']; ?>" onclick="return confirm('هل أنت متأكد من حذف هذا الطالب؟');">
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
