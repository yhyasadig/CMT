<?php
session_start();
<<<<<<< HEAD
require_once 'Report.php';

// تحقق من تسجيل الدخول كمشرف
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'supervis') {
    die("يجب تسجيل الدخول كمشرف.");
}

// التحقق من وجود report_id صحيح
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("تقرير غير صالح.");
=======
require_once 'Database.php';
require_once 'Report.php';

// ✅ تحقق من تسجيل الدخول كمشرف
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'supervis') {
    die("⚠️ يجب تسجيل الدخول كمشرف.");
}

// ✅ التحقق من وجود report_id صحيح
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("⚠️ تقرير غير صالح.");
>>>>>>> 2c437069192c41dc67c3eef3ba98c09f930e22d9
}

$reportId = $_GET['id'];

<<<<<<< HEAD
// الحصول على نسخة الكلاس singleton
$report = Report::getInstance();

try {
    // جلب التقرير
    $data = $report->getReportById($reportId);
    if (!$data) {
        die("لم يتم العثور على التقرير.");
    }

    // التأكد من وجود حقل الحالة في نتيجة التقرير
    if (!isset($data['status'])) {
        $data['status'] = 'قيد المراجعة'; // احتياطي لو لم يكن الحقل موجوداً
    }

    // تحديث الحالة إلى "تم الاطلاع عليه" إن لم تكن كذلك
    if ($data['status'] !== 'تم الاطلاع عليه') {
        $conn = $report->getDbConnection();
        $update = $conn->prepare("UPDATE reports SET status = 'تم الاطلاع عليه' WHERE report_id = :id");
        $update->execute([':id' => $reportId]);
        $data['status'] = 'تم الاطلاع عليه';
    }
} catch (PDOException $e) {
    die("حدث خطأ في قاعدة البيانات: " . htmlspecialchars($e->getMessage()));
} catch (Exception $e) {
    die("حدث خطأ غير متوقع: " . htmlspecialchars($e->getMessage()));
=======
$db = new DatabaseConnection();
$conn = $db->getConnection();
$report = new Report($conn);

// ✅ جلب التقرير
$data = $report->getReportById($reportId);
if (!$data) {
    die("❌ لم يتم العثور على التقرير.");
}

// ✅ التأكد من وجود حقل الحالة في نتيجة التقرير
if (!isset($data['status'])) {
    $data['status'] = 'قيد المراجعة'; // احتياطي لو كان السطر السابق ALTER TABLE لم يُنفذ
}

// ✅ تحديث الحالة إلى "تم الاطلاع عليه" إن لم تكن كذلك
if ($data['status'] !== 'تم الاطلاع عليه') {
    $update = $conn->prepare("UPDATE reports SET status = 'تم الاطلاع عليه' WHERE report_id = :id");
    $update->execute([':id' => $reportId]);
    $data['status'] = 'تم الاطلاع عليه';
>>>>>>> 2c437069192c41dc67c3eef3ba98c09f930e22d9
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تفاصيل التقرير</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma;
            background-color: #f9f9f9;
            padding: 30px;
        }
        .container {
            background: #fff;
            padding: 25px;
            max-width: 700px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .field {
            margin: 15px 0;
        }
        .label {
            font-weight: bold;
            color: #555;
        }
        .value {
            margin-top: 5px;
            color: #222;
            padding: 10px;
            background: #f1f1f1;
            border-radius: 6px;
        }
        .download-btn {
            display: inline-block;
            margin-top: 15px;
            background-color: #007bff;
            color: white;
            padding: 10px 16px;
            border-radius: 5px;
            text-decoration: none;
        }
        .download-btn:hover {
            background-color: #0056b3;
        }
        .back {
            margin-top: 20px;
            text-align: center;
        }
        .back a {
            color: #007bff;
            text-decoration: none;
        }
        .back a:hover {
            text-decoration: underline;
        }
        .status-value {
            color: <?= ($data['status'] === 'تم الاطلاع عليه') ? '#28a745' : '#6c757d' ?>;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>تفاصيل التقرير</h2>

    <div class="field">
        <div class="label">عنوان التقرير:</div>
        <div class="value"><?= htmlspecialchars($data['report_title']) ?></div>
    </div>

    <div class="field">
        <div class="label">مرسل التقرير:</div>
<<<<<<< HEAD
        <div class="value"><?= htmlspecialchars($data['sender_name']) ?> (<?= htmlspecialchars($data['user_role'] ?? '') ?>)</div>
=======
        <div class="value"><?= htmlspecialchars($data['sender_name']) ?> (<?= htmlspecialchars($data['user_role']) ?>)</div>
>>>>>>> 2c437069192c41dc67c3eef3ba98c09f930e22d9
    </div>

    <div class="field">
        <div class="label">تاريخ الإرسال:</div>
        <div class="value"><?= date('Y-m-d H:i', strtotime($data['created_at'])) ?></div>
    </div>

    <div class="field">
        <div class="label">الحالة:</div>
        <div class="value status-value"><?= htmlspecialchars($data['status']) ?></div>
    </div>

    <div class="field">
        <div class="label">محتوى التقرير:</div>
        <div class="value"><?= nl2br(htmlspecialchars($data['report_body'])) ?></div>
    </div>

    <?php if (!empty($data['file_name'])): ?>
        <div class="field">
            <div class="label">الملف المرفق:</div>
<<<<<<< HEAD
            <a href="uploads/reports/<?= htmlspecialchars($data['file_name']) ?>" class="download-btn" target="_blank">تحميل الملف</a>
=======
            <a href="uploads/reports/<?= $data['file_name'] ?>" class="download-btn" target="_blank">📥 تحميل الملف</a>
>>>>>>> 2c437069192c41dc67c3eef3ba98c09f930e22d9
        </div>
    <?php endif; ?>

    <div class="back">
<<<<<<< HEAD
        <a href="supervisor_reports.php">الرجوع إلى قائمة التقارير</a>
=======
        <a href="supervisor_reports.php">← الرجوع إلى قائمة التقارير</a>
>>>>>>> 2c437069192c41dc67c3eef3ba98c09f930e22d9
    </div>
</div>

</body>
</html>
