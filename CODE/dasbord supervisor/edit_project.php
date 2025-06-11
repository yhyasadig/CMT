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
include 'Project.php'; // استيراد كلاس Project

try {
    // الاتصال بقاعدة البيانات
    $db = new DatabaseConnection();
    $connection = $db->getConnection();  // الحصول على الاتصال بقاعدة البيانات

    // التحقق من وجود معرف المشروع في الرابط
    if (isset($_GET['project_id'])) {
        $project_id = $_GET['project_id'];

        // إنشاء كائن من كلاس Project
        $project = new Project($db, $project_id); // تمرير كائن DatabaseConnection هنا
        $projectDetails = $project->getProjectDetails();  // جلب تفاصيل المشروع

        // التحقق من وجود المشروع
        if (!$projectDetails) {
            die("المشروع غير موجود.");
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
        $leader_id = $_POST['leader_id'] ?: null;  // في حال عدم تحديد قائد، سيتم تعيينه إلى null

        // تحديث بيانات المشروع باستخدام كلاس Project
        $project->setProjectName($name);
        $project->setProjectDescription($description);
        $project->setStartDate($start_date);
        $project->setEndDate($end_date);
        $project->setLeaderId($leader_id);

        try {
            // تنفيذ التحديث
            if ($project->updateProjectDetails()) {
                $message = "تم تحديث المشروع بنجاح!";
            } else {
                $message = "حدث خطأ أثناء تحديث المشروع.";
            }
        } catch (Exception $ex) {
            $message = "خطأ أثناء تحديث المشروع: " . $ex->getMessage();
        }
    }

} catch (PDOException $e) {
    die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
} catch (Exception $ex) {
    die("حدث خطأ: " . $ex->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل المشروع</title>
    <link rel="stylesheet" href="style.css"> <!-- رابط ملف CSS -->

    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f8ff;
            color: #333;
            margin: 0;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #007bff;
        }

        .form-group {
            margin-bottom: 15px;
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

        .form-group select:invalid {
            border-color: red;
        }
    </style>

    <script>
        // تحقق من أن جميع الحقول تمت تعبئتها قبل تقديم النموذج
        function validateForm() {
            let name = document.getElementById('name').value;
            let description = document.getElementById('description').value;
            let start_date = document.getElementById('start_date').value;
            let end_date = document.getElementById('end_date').value;

            if (!name || !description || !start_date || !end_date) {
                alert("الرجاء تعبئة جميع الحقول.");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>تعديل مشروع</h2>

        <?php if (isset($message)): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>

        <form method="POST" action="" onsubmit="return validateForm()">
            <div class="form-group">
                <label for="name">اسم المشروع:</label>
                <input type="text" name="name" id="name" value="<?php echo $projectDetails['name']; ?>" required>
            </div>

            <div class="form-group">
                <label for="description">وصف المشروع:</label>
                <textarea name="description" id="description" required><?php echo $projectDetails['description']; ?></textarea>
            </div>

            <div class="form-group">
                <label for="start_date">تاريخ بداية المشروع:</label>
                <input type="date" name="start_date" id="start_date" value="<?php echo $projectDetails['start_date']; ?>" required>
            </div>

            <div class="form-group">
                <label for="end_date">تاريخ نهاية المشروع:</label>
                <input type="date" name="end_date" id="end_date" value="<?php echo $projectDetails['end_date']; ?>" required>
            </div>

            <div class="form-group">
                <label for="leader_id">اختيار قائد الفريق (يمكنك تركه كما هو):</label>
                <select name="leader_id" id="leader_id">
                    <option value="">اختر قائد الفريق (أو اتركه كما هو)</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo $student['user_id']; ?>" <?php echo $projectDetails['leader_id'] == $student['user_id'] ? 'selected' : ''; ?>>
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
