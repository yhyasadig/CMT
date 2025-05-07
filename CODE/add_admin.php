<?php
// إعداد الاتصال بقاعدة البيانات
include 'Database.php'; // تأكد من أن ملف قاعدة البيانات مدمج

// تعريف بيانات الاتصال بقاعدة البيانات
$db = new DatabaseConnection();
$connection = $db->getConnection();  // الحصول على الاتصال بقاعدة البيانات

// بيانات الحساب المشرف
$name = "Admin User";
$email = "admin@example.com";
$password = "admin_password"; // كلمة المرور للمشرف

// تشفير كلمة المرور
$hashedPassword = password_hash($password, PASSWORD_DEFAULT); // تشفير كلمة المرور باستخدام password_hash()

// استعلام لإضافة الحساب المشرف
$query = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, 'admin')";
$params = [
    ':name' => $name,
    ':email' => $email,
    ':password' => $hashedPassword
];

// تنفيذ الاستعلام
try {
    $stmt = $connection->prepare($query);
    if ($stmt->execute($params)) {
        echo "تم إضافة حساب المشرف بنجاح!";
    } else {
        echo "حدث خطأ في إضافة الحساب.";
    }
} catch (PDOException $e) {
    echo "خطأ في الاستعلام: " . $e->getMessage();
}
?>
