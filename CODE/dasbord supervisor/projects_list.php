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

// حذف مشروع
if (isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];

    // استعلام لحذف المشروع
    $query = "DELETE FROM projects WHERE project_id = :project_id";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':project_id', $deleteId, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        $message = "تم حذف المشروع بنجاح!";
    } else {
        $message = "حدث خطأ أثناء حذف المشروع.";
    }
}

// تعديل مشروع
if (isset($_POST['update_project'])) {
    $project_id = $_POST['project_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $leader_id = $_POST['leader_id']; // القائد الذي تم اختياره

    // استعلام لتحديث المشروع
    $query = "UPDATE projects SET name = :name, description = :description, start_date = :start_date, end_date = :end_date, leader_id = :leader_id WHERE project_id = :project_id";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->bindParam(':leader_id', $leader_id);

    if ($stmt->execute()) {
        $message = "تم تحديث المشروع بنجاح!";
    } else {
        $message = "حدث خطأ أثناء تحديث المشروع.";
    }
}

// استعلام لجلب جميع المشاريع مع اسم القائد
$query = "SELECT p.project_id, p.name AS project_name, p.description, p.start_date, p.end_date, u.name AS leader_name
          FROM projects p
          JOIN users u ON p.leader_id = u.user_id";
$stmt = $connection->prepare($query);
$stmt->execute();
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض المشاريع</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            color: #333;
        }

        .container {
            max-width: 1000px;
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

        .action-btn {
            color: white;
            background-color: #007bff;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .action-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>قائمة المشاريع</h2>

    <!-- عرض قائمة المشاريع -->
    <table>
        <thead>
            <tr>
                <th>رقم المشروع</th>
                <th>اسم المشروع</th>
                <th>الوصف</th>
                <th>تاريخ البداية</th>
                <th>تاريخ النهاية</th>
                <th>قائد الفريق</th>
                <th>الإجراء</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($projects as $project): ?>
                <tr>
                    <td><?php echo $project['project_id']; ?></td>
                    <td><?php echo $project['project_name']; ?></td>
                    <td><?php echo $project['description']; ?></td>
                    <td><?php echo $project['start_date']; ?></td>
                    <td><?php echo $project['end_date']; ?></td>
                    <td><?php echo $project['leader_name']; ?></td>
                    <td>
                        <!-- أزرار التعديل والحذف -->
                        <a href="edit_project.php?project_id=<?php echo $project['project_id']; ?>">
                            <button class="action-btn">تعديل</button>
                        </a>
                        <a href="?delete_id=<?php echo $project['project_id']; ?>" onclick="return confirm('هل أنت متأكد من أنك تريد حذف هذا المشروع؟')">
                            <button class="action-btn" style="background-color: red;">حذف</button>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
