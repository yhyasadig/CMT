<?php
// بدء الجلسة
session_start();

// التحقق من أن المستخدم هو قائد الفريق
if ($_SESSION['role'] != 'automember') {
    header("Location: index.php");  // إعادة التوجيه إلى صفحة تسجيل الدخول إذا لم يكن قائد الفريق
    exit();
}

// الاتصال بقاعدة البيانات
include 'Database.php';  // تأكد من أنك تستخدم الملف المناسب للاتصال بقاعدة البيانات
$db = new DatabaseConnection();
$connection = $db->getConnection();  // الحصول على الاتصال بقاعدة البيانات

// التحقق من وجود معرف المشروع في الجلسة
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];  // الحصول على ID المستخدم من الجلسة

    try {
        // استعلام لجلب تفاصيل المشروع باستخدام leader_id
        $query = "SELECT * FROM projects WHERE leader_id = :user_id";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        // التحقق من وجود المشروع
        if (!$project) {
            $message = "لا يوجد مشروع مرتبط بحسابك."; // رسالة في حالة عدم وجود مشروع
        }
    } catch (PDOException $e) {
        // في حالة حدوث خطأ في الاتصال أو الاستعلام
        die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
    } catch (Exception $e) {
        // في حالة حدوث خطأ عام مثل عدم وجود المشروع
        die($e->getMessage());
    }
} else {
    $message = "لا يوجد مشروع مرتبط بحسابك."; // رسالة في حالة عدم وجود مشروع
}

// استعلام لاختيار الطلبة لتعيينهم أعضاء في الفريق
$query = "SELECT user_id, name FROM users WHERE role = 'student' AND project_id IS NULL"; // الحصول على الطلبة غير المنضمين لمشاريع
$stmt = $connection->prepare($query);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// معالجة البيانات عند تقديم النموذج (إضافة أعضاء للمشروع)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $team_members = $_POST['team_members'];  // أعضاء الفريق الذين تم اختيارهم

    try {
        // إضافة الطلبة إلى المشروع
        foreach ($team_members as $member_id) {
            $query_add_members = "UPDATE users SET project_id = :project_id WHERE user_id = :user_id";
            $stmt_add_members = $connection->prepare($query_add_members);
            $stmt_add_members->bindParam(':project_id', $project['project_id'], PDO::PARAM_INT);
            $stmt_add_members->bindParam(':user_id', $member_id, PDO::PARAM_INT);
            $stmt_add_members->execute();
        }

        $message = "تم إضافة الأعضاء للمشروع بنجاح!";
    } catch (PDOException $e) {
        $message = "خطأ في استعلام إضافة الأعضاء: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة أعضاء الفريق للمشروع</title>
    <style>
        /* إضافة تنسيق للسايد بار */
        .sidebar {
            width: 250px;
            height: 100%;
            background-color: #333;
            padding-top: 20px;
            position: fixed;
            top: 0;
            left: 0;
            color: white;
            text-align: center;
        }

        .sidebar a {
            display: block;
            padding: 10px;
            color: white;
            text-decoration: none;
            font-size: 18px;
        }

        .sidebar a:hover {
            background-color: #575757;
        }

        .main-content {
            margin-left: 260px;
            padding: 20px;
        }

        h2 {
            color: #333;
        }

        table {
            width: 100%;
            border: 1px solid #ccc;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            text-align: center;
        }

        .message {
            color: green;
            text-align: center;
            font-size: 18px;
            margin-top: 20px;
        }

        .error-message {
            color: red;
            text-align: center;
            font-size: 18px;
            margin-top: 20px;
        }

        select {
            width: 100%;
            padding: 12px;
            font-size: 14px;
            margin-top: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

    <!-- السايد بار -->
    <div class="sidebar">
        <h2>لوحة تحكم قائد الفريق</h2>
        <a href="student_dashboard.php">الصفحة الرئيسية</a>
        <a href="project_details.php">تفاصيل المشروع</a>
     
    </div>

    <!-- محتوى الصفحة الرئيسية -->
    <div class="main-content">
        <h2>إضافة أعضاء الفريق للمشروع</h2>

        <?php if (isset($message)): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>

        <?php if (isset($project)): ?>
            <h3>إضافة أعضاء الفريق</h3>
            <form method="POST" action="">

                <label for="team_members">اختر الأعضاء:</label><br>
                <select name="team_members[]" id="team_members" multiple required>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo $student['user_id']; ?>"><?php echo $student['name']; ?></option>
                    <?php endforeach; ?>
                </select><br><br>

                <button type="submit">إضافة الأعضاء</button>
            </form>
        <?php else: ?>
            <p class="error-message">لا يوجد مشروع مرتبط بحسابك. تأكد من أن المشرف قد أضافك للمشروع.</p>
        <?php endif; ?>

    </div>

</body>
</html>
