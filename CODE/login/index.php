<?php
session_start(); // تأكد من بدء الجلسة
require_once 'Database.php';
require_once 'User.php';

// إنشاء الاتصال
$db = new DatabaseConnection();
$userManager = new User($db);

// فحص الطلب
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $loggedInUser = $userManager->login($email, $password);

        if ($loggedInUser) {
            // تخزين بيانات المستخدم في الجلسة
            $_SESSION['user_id'] = $loggedInUser['user_id']; // تخزين ID المستخدم
            $_SESSION['name'] = $loggedInUser['name']; // تخزين اسم المستخدم
            $_SESSION['role'] = $loggedInUser['role']; // تخزين دور المستخدم

            // توجيه المستخدم بناءً على الدور
            if ($_SESSION['role'] == 'admin') {
                // توجيه إلى لوحة تحكم المسؤول
                header("Location: dashboard.php");
            } elseif ($_SESSION['role'] == 'supervis') {
                // توجيه إلى لوحة تحكم المشرف
                header("Location: supervisors_dashboard.php");
            } elseif ($_SESSION['role'] == 'automember') {
                // توجيه إلى لوحة تحكم العضو
                header("Location: dashboard-automember.php");
            } elseif ($_SESSION['role'] == 'student') {
                // توجيه إلى لوحة تحكم الطالب
                header("Location: student_dashboard.php");
            } else {
                // في حالة لم يكن المستخدم "admin" أو "supervis" أو "automember" أو "student"
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "البريد الإلكتروني أو كلمة المرور غير صحيحة.";
        }
    } catch (Exception $e) {
        // يمكنك تسجيل الخطأ في ملف خاص مثلاً errors.log
        // error_log($e->getMessage(), 3, __DIR__ . '/errors.log');

        // رسالة عامة للمستخدم
        $error = "حدث خطأ داخلي، يرجى المحاولة لاحقًا.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f1f1f1;
        }

        .login-box {
            max-width: 400px;
            margin: 100px auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-weight: bold;
        }
    </style>
</head>
<body>

<!-- شريط التنقل -->
<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">نظام إدارة المشاريع</a>
    </div>
</nav>

<!-- صندوق تسجيل الدخول -->
<div class="login-box">
    <h3 class="text-center mb-4">تسجيل الدخول</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger text-center"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label for="email" class="form-label">البريد الإلكتروني</label>
            <input type="email" class="form-control" name="email" required placeholder="example@email.com">
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">كلمة المرور</label>
            <input type="password" class="form-control" name="password" required placeholder="••••••">
        </div>

        <button type="submit" class="btn btn-primary w-100">دخول</button>
    </form>

    <p class="mt-3 text-center">
        ليس لديك حساب؟ <a href="register.php">إنشاء حساب جديد</a>
    </p>
</div>

</body>
</html>
