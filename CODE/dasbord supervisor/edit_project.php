<?php
// بدء الجلسة
session_start();

// التحقق من أن المستخدم هو مشرف
if ($_SESSION['role'] != 'supervis') {
    header("Location: index.php");  // إعادة التوجيه إلى صفحة تسجيل الدخول إذا لم يكن مشرف
    exit();
}

// الاتصال بقاعدة البيانات
include 'Database.php';  // تأكد من أنك تستخدم الملف المناسب للاتصال بقاعدة البيانات
$db = new DatabaseConnection();
$connection = $db->getConnection();  // الحصول على الاتصال بقاعدة البيانات

// التحقق من وجود معرف المشروع في الرابط
if (isset($_GET['project_id'])) {
    $project_id = $_GET['project_id'];

    try {
        // استعلام لجلب تفاصيل المشروع
        $query = "SELECT * FROM projects WHERE project_id = :project_id";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
        $stmt->execute();
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        // التحقق من وجود المشروع
        if (!$project) {
            throw new Exception("المشروع غير موجود.");
        }
    } catch (PDOException $e) {
        // في حالة حدوث خطأ في الاتصال أو الاستعلام
        die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
    } catch (Exception $e) {
        // في حالة حدوث خطأ عام مثل عدم وجود المشروع
        die($e->getMessage());
    }
} else {
    die("المشروع غير موجود.");
}

// استعلام لاختيار الطلبة لتعيين قائد الفريق
$query = "SELECT user_id, name FROM users WHERE role = 'student'";
try {
    $stmt = $connection->prepare($query);
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("خطأ في استعلام جلب الطلبة: " . $e->getMessage());
}

// معالجة البيانات عند تقديم النموذج
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $leader_id = $_POST['leader_id'];  // القائد الذي تم اختياره

    try {
        // استعلام لتحديث بيانات المشروع في قاعدة البيانات
        $query = "UPDATE projects 
                  SET name = :name, description = :description, start_date = :start_date, end_date = :end_date, leader_id = :leader_id 
                  WHERE project_id = :project_id";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->bindParam(':leader_id', $leader_id);
        $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $message = "تم تحديث المشروع بنجاح!";
        } else {
            throw new Exception("حدث خطأ أثناء تحديث المشروع.");
        }
    } catch (PDOException $e) {
        // في حالة حدوث خطأ في الاستعلام
        $message = "خطأ في استعلام التحديث: " . $e->getMessage();
    } catch (Exception $e) {
        // في حالة حدوث خطأ عام
        $message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل المشروع</title>
    <link rel="stylesheet" href="style.css"> <!-- تأكد من أن ملف الـ CSS موجود هنا -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            color: #333;
            margin: 0;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #007bff;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-size: 16px;
            color: #555;
            margin-bottom: 5px;
            display: block;
        }

        input, select, textarea {
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

        .error-message {
            color: red;
            text-align: center;
            font-size: 18px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>تعديل مشروع</h2>

        <?php if (isset($message)): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="name">اسم المشروع:</label>
                <input type="text" name="name" id="name" value="<?php echo $project['name']; ?>" required>
            </div>

            <div class="form-group">
                <label for="description">وصف المشروع:</label>
                <textarea name="description" id="description" required><?php echo $project['description']; ?></textarea>
            </div>

            <div class="form-group">
                <label for="start_date">تاريخ بداية المشروع:</label>
                <input type="date" name="start_date" id="start_date" value="<?php echo $project['start_date']; ?>" required>
            </div>

            <div class="form-group">
                <label for="end_date">تاريخ نهاية المشروع:</label>
                <input type="date" name="end_date" id="end_date" value="<?php echo $project['end_date']; ?>" required>
            </div>

            <div class="form-group">
                <label for="leader_id">اختيار قائد الفريق:</label>
                <select name="leader_id" id="leader_id" required>
                    <option value="">اختر قائد الفريق</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo $student['user_id']; ?>" <?php echo $project['leader_id'] == $student['user_id'] ? 'selected' : ''; ?>>
                            <?php echo $student['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit">تحديث المشروع</button>
        </form>
    </div>
</body>
</html>
