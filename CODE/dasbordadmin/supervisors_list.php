<?php
// التأكد من أن المستخدم هو مشرف
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: login.php");  // إعادة التوجيه إلى صفحة تسجيل الدخول إذا لم يكن مشرف
    exit();
}

// الاتصال بقاعدة البيانات
include 'Database.php';  // تأكد من استخدام الملف المناسب للاتصال بقاعدة البيانات
$db = new DatabaseConnection();
$connection = $db->getConnection();  // الحصول على الاتصال بقاعدة البيانات

// حذف مشرف من قاعدة البيانات
if (isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];

    // استعلام لحذف المشرف
    $query = "DELETE FROM users WHERE user_id = :id AND role = 'supervis'";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':id', $deleteId, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        $message = "تم حذف المشرف بنجاح!";
    } else {
        $message = "حدث خطأ أثناء حذف المشرف.";
    }
}

// استعلام لجلب قائمة المشرفين (المستخدمين الذين لديهم role = 'supervis')
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

        .error-message {
            color: red;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>قائمة المشرفين</h2>

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
                    <td><?php echo $supervisor['user_id']; ?></td>
                    <td><?php echo $supervisor['name']; ?></td>
                    <td><?php echo $supervisor['email']; ?></td>
                    <td>
                        <a href="?delete_id=<?php echo $supervisor['user_id']; ?>">
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
