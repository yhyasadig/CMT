<?php
// التأكد من أن المستخدم هو مشرف
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: login.php");  // إعادة التوجيه إلى صفحة تسجيل الدخول إذا لم يكن مشرف
    exit();
}

// معالجة البيانات عند تقديم النموذج
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // تشفير كلمة المرور
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // تشفير كلمة المرور باستخدام password_hash()

    // الاتصال بقاعدة البيانات
    include 'Database.php';
    $db = new DatabaseConnection();
    $connection = $db->getConnection();  // الحصول على الاتصال بقاعدة البيانات

    // استعلام لإدخال بيانات المشرف إلى قاعدة البيانات
    $query = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, 'supervis')";
    $params = [
        ':name' => $name,
        ':email' => $email,
        ':password' => $hashedPassword
    ];

    // تنفيذ الاستعلام
    try {
        $stmt = $connection->prepare($query);
        if ($stmt->execute($params)) {
            $message = "تم إضافة المشرف بنجاح!";
        } else {
            $message = "حدث خطأ في إضافة المشرف.";
        }
    } catch (PDOException $e) {
        $message = "خطأ في الاستعلام: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة مشرف</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            color: #333;
        }

        .container {
            max-width: 500px;
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

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            font-size: 16px;
            color: #555;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .form-group input:focus {
            border-color: #007bff;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        .error-message {
            color: red;
            text-align: center;
            margin-top: 10px;
        }

        .success-message {
            color: green;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>إضافة مشرف جديد</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="name">الاسم:</label>
                <input type="text" name="name" id="name" required>
            </div>
            <div class="form-group">
                <label for="email">البريد الإلكتروني:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="password">كلمة المرور:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit">إضافة المشرف</button>
        </form>

        <?php
        if (isset($message)) {
            if (strpos($message, 'تم') !== false) {
                echo "<p class='success-message'>$message</p>";
            } else {
                echo "<p class='error-message'>$message</p>";
            }
        }
        ?>
    </div>

</body>
</html>
