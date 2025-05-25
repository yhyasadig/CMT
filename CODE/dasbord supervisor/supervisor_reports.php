<?php
session_start();
require_once 'Database.php';
require_once 'Report.php';

// التحقق من أن المستخدم مسجل كمشرف
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'supervis') {
    die("غير مصرح بالدخول. يجب أن تكون مشرفًا.");
}

$db = new DatabaseConnection();
$conn = $db->getConnection();
$report = new Report($conn);

// جلب التقارير التي تخص هذا المشرف
$supervisorId = $_SESSION['user_id'];
$reports = $report->getReportsForSupervisor($supervisorId);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تقارير الطلاب المستلمة</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma;
            background-color: #f1f1f1;
            padding: 30px;
        }
        .container {
            background: #fff;
            padding: 20px;
            max-width: 900px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 14px;
            border-bottom: 1px solid #ccc;
            text-align: center;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        a.btn {
            background-color: #28a745;
            padding: 6px 12px;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 13px;
        }
        a.btn:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>قائمة التقارير المستلمة من الطلاب</h2>

    <?php if (empty($reports)): ?>
        <p style="text-align:center; color: gray;">لا توجد تقارير حالياً.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>عنوان التقرير</th>
                <th>مرسل التقرير</th>
                <th>التاريخ</th>
                <th>الملف</th>
                <th>عرض</th>
            </tr>
            <?php foreach ($reports as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['report_title']) ?></td>
                    <td><?= htmlspecialchars($r['sender_name']) ?></td>
                    <td><?= date('Y-m-d H:i', strtotime($r['created_at'])) ?></td>
                    <td>
                        <?php if ($r['file_name']): ?>
                            <a href="uploads/reports/<?= $r['file_name'] ?>" class="btn" target="_blank">📄 تحميل</a>
                        <?php else: ?>
                            لا يوجد
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="view_report.php?id=<?= $r['report_id'] ?>" class="btn">👁️ عرض</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
