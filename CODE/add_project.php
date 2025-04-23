<?php
// بدء الجلسة
session_start();

// التحقق من أن المستخدم هو مشرف
if ($_SESSION['role'] != 'supervis') {
    header("Location: login.php");  // إعادة التوجيه إلى صفحة تسجيل الدخول إذا لم يكن مشرف
    exit();
}

// الاتصال بقاعدة البيانات
include 'Database.php';  // تأكد من أنك تستخدم الملف المناسب للاتصال بقاعدة البيانات
$db = new DatabaseConnection();
$connection = $db->getConnection();  // الحصول على الاتصال بقاعدة البيانات

// استعلام لاختيار الطلبة
$query = "SELECT user_id, name FROM users WHERE role = 'student'";
$stmt = $connection->prepare($query);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// معالجة البيانات عند تقديم النموذج
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $leader_id = $_POST['leader_id'];  // الطالب الذي سيتم اختياره كقائد الفريق

    try {
        // استعلام لإدخال بيانات المشروع في قاعدة البيانات
        $query = "INSERT INTO projects (name, description, start_date, end_date, leader_id) 
                  VALUES (:name, :description, :start_date, :end_date, :leader_id)";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->bindParam(':leader_id', $leader_id);

        if ($stmt->execute()) {
            $project_id = $connection->lastInsertId();  // الحصول على آخر معرف مشروع تم إضافته

            // تحديث دور قائد الفريق إلى "automember"
            $updateRoleQuery = "UPDATE users SET role = 'automember', project_id = :project_id WHERE user_id = :leader_id";
            $updateRoleStmt = $connection->prepare($updateRoleQuery);
            $updateRoleStmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
            $updateRoleStmt->bindParam(':leader_id', $leader_id, PDO::PARAM_INT);
            $updateRoleStmt->execute();

            $message = "تم إضافة المشروع بنجاح!";
        } else {
            $message = "حدث خطأ في إضافة المشروع.";
        }
    } catch (PDOException $e) {
        $message = "خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة مشروع جديد</title>
    <link rel="stylesheet" href="style.css"> <!-- تأكد من أن ملف الـ CSS موجود هنا -->
</head>
<body>
    <div class="container">
        <h2>إضافة مشروع جديد</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="name">اسم المشروع:</label>
                <input type="text" name="name" id="name" required>
            </div>

            <div class="form-group">
                <label for="description">وصف المشروع:</label>
                <textarea name="description" id="description" required></textarea>
            </div>

            <div class="form-group">
                <label for="start_date">تاريخ بداية المشروع:</label>
                <input type="date" name="start_date" id="start_date" required>
            </div>

            <div class="form-group">
                <label for="end_date">تاريخ نهاية المشروع:</label>
                <input type="date" name="end_date" id="end_date" required>
            </div>

            <div class="form-group">
                <label for="leader_id">اختيار قائد الفريق:</label>
                <select name="leader_id" id="leader_id" required>
                    <option value="">اختر قائد الفريق</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo $student['user_id']; ?>"><?php echo $student['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit">إضافة المشروع</button>
        </form>

        <?php if (isset($message)): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>
    </div>

    <!-- إضافة CSS هنا إذا لزم الأمر -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            font-size: 14px;
            color: #333;
        }

        input, textarea, select {
            width: 100%;
            padding: 12px;
            font-size: 14px;
            margin-top: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
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
            margin-top: 20px;
        }

        button:hover {
            background-color: #218838;
        }

        .message {
            color: green;
            text-align: center;
            font-size: 18px;
            margin-top: 20px;
        }
    </style>
</body>
</html>
