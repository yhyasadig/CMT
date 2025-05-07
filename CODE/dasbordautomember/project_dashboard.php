<?php
session_start();
include 'Database.php';
include 'User.php';
include 'TeamMember.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// الاتصال بقاعدة البيانات
$db = new DatabaseConnection();
$conn = $db->getConnection();

$user_id = $_SESSION['user_id']; // المستخدم المتصل

// التحقق إذا كان المستخدم عضوًا في المشروع
$query = "SELECT * FROM projects WHERE project_id = :project_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(":project_id", $_GET['project_id']);
$stmt->execute();
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    header("Location: projects_list.php");
    exit();
}

$teamMember = new TeamMember($db, $user_id, "", "", "", "", $_GET['project_id'], "");

// التحقق من أن المستخدم عضو في المشروع
$members = $teamMember->getTeamMembers($_GET['project_id']);
$isMember = false;
foreach ($members as $member) {
    if ($member['user_id'] == $user_id) {
        $isMember = true;
        break;
    }
}

if (!$isMember) {
    header("Location: not_assigned.php"); // الصفحة إذا لم يكن المستخدم عضو في المشروع
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الصفحة الرئيسية</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-container">
        <h1>مرحباً بك في المشروع</h1>
        <h2>المشاريع الحالية</h2>
        <table>
            <thead>
                <tr>
                    <th>اسم المشروع</th>
                    <th>الوصف</th>
                    <th>الموعد النهائي</th>
                    <th>أعضاء الفريق</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= htmlspecialchars($project['name']); ?></td>
                    <td><?= htmlspecialchars($project['description']); ?></td>
                    <td><?= htmlspecialchars($project['end_date']); ?></td>
                    <td>
                        <ul>
                            <?php foreach ($members as $member): ?>
                                <li><?= htmlspecialchars($member['name']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                    <td>
                        <a href="project_details.php?id=<?= $project['project_id']; ?>">تفاصيل</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
