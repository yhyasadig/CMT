<?php
require_once 'Database.php';
require_once 'User.php';

$db = new DatabaseConnection();
$userManager = new User($db);

$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // تم تعيين الدور إلى "student" بشكل ثابت، حيث يتم إنشاؤه كطالب فقط
    $role = 'student';

    try {
        // إنشاء حساب جديد باستخدام كلاس User
        $created = $userManager->create($name, $email, $password, $role, null);

        if ($created) {
            $success = "تم إنشاء الحساب بنجاح. يمكنك الآن تسجيل الدخول.";
            // إعادة توجيه المستخدم إلى الصفحة الرئيسية بعد نجاح التسجيل
            header("Location: index.php");
            exit(); // تأكد من إنهاء التنفيذ بعد إعادة التوجيه
        } else {
            $error = "حدث خطأ أثناء إنشاء الحساب. قد يكون البريد الإلكتروني مستخدم من قبل.";
        }
    } catch (PDOException $e) {
        $error = "خطأ في قاعدة البيانات: " . $e->getMessage();
    } catch (Exception $e) {
        $error = "حدث خطأ غير متوقع: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إنشاء حساب</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f1f1f1;
        }

        .register-box {
            max-width: 500px;
            margin: 80px auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">نظام إدارة المشاريع</a>
    </div>
</nav>

<div class="register-box">
    <h3 class="text-center mb-4">إنشاء حساب جديد</h3>

    <?php if ($success): ?>
        <div class="alert alert-success text-center"><?php echo $success; ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger text-center"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label">الاسم الكامل</label>
            <input type="text" class="form-control" name="name" required>
        </div>

        <div class="mb-3">
            <label class="form-label">البريد الإلكتروني</label>
            <input type="email" class="form-control" name="email" required>
        </div>

        <div class="mb-3">
            <label class="form-label">كلمة المرور</label>
            <input type="password" class="form-control" name="password" required>
        </div>

        <button type="submit" class="btn btn-success w-100">إنشاء الحساب</button>
    </form>
</div>

</body>
</html>
