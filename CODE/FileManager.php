<?php

class FileManager {
    private $db; // متغير لتخزين اتصال قاعدة البيانات

    // بناء الكلاس مع تمرير الاتصال بقاعدة البيانات
    public function __construct($db) {
        $this->db = $db;
    }

    // رفع ملف المهمة
    public function uploadTaskFile($taskId, $file, $uploadedBy) {
        // التأكد من أن الملف تم رفعه بنجاح
        if ($file['error'] == UPLOAD_ERR_OK) {
            $fileName = basename($file['name']);
            $uploadDir = 'uploads/task_files/';
            $filePath = $uploadDir . $fileName;

            // التأكد من أن المجلد موجود وإذا لم يكن، يتم إنشاؤه
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // نقل الملف من المجلد المؤقت إلى المجلد النهائي
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                // إضافة تفاصيل الملف إلى قاعدة البيانات
                $this->addFileToDatabase($taskId, $fileName, $filePath, $uploadedBy);
                return true;
            } else {
                return "فشل في تحميل الملف.";
            }
        } else {
            return "خطأ في تحميل الملف: " . $file['error'];
        }
    }

    // إضافة تفاصيل الملف إلى قاعدة البيانات
    private function addFileToDatabase($taskId, $fileName, $filePath, $uploadedBy) {
        try {
            $query = "INSERT INTO task_files (task_id, file_name, uploaded_by, file_path, upload_date) 
                      VALUES (:task_id, :file_name, :uploaded_by, :file_path, NOW())";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':task_id', $taskId);
            $stmt->bindParam(':file_name', $fileName);
            $stmt->bindParam(':uploaded_by', $uploadedBy);
            $stmt->bindParam(':file_path', $filePath);
            $stmt->execute();
        } catch (Exception $e) {
            echo "خطأ في إضافة الملف إلى قاعدة البيانات: " . $e->getMessage();
        }
    }

    // جلب الملفات المرتبطة بمهمة معينة
    public function getFilesByTask($taskId) {
        try {
            $query = "SELECT * FROM task_files WHERE task_id = :task_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':task_id', $taskId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo "خطأ في جلب الملفات: " . $e->getMessage();
            return false;
        }
    }

    // حذف ملف
    public function deleteFile($fileId) {
        try {
            // جلب مسار الملف من قاعدة البيانات
            $query = "SELECT file_path FROM task_files WHERE file_id = :file_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':file_id', $fileId);
            $stmt->execute();
            $file = $stmt->fetch(PDO::FETCH_ASSOC);

            // التأكد من وجود الملف
            if ($file && unlink($file['file_path'])) {
                // حذف السجل من قاعدة البيانات
                $queryDelete = "DELETE FROM task_files WHERE file_id = :file_id";
                $stmtDelete = $this->db->prepare($queryDelete);
                $stmtDelete->bindParam(':file_id', $fileId);
                $stmtDelete->execute();
                return true;
            } else {
                return "فشل في حذف الملف.";
            }
        } catch (Exception $e) {
            echo "خطأ في حذف الملف: " . $e->getMessage();
            return false;
        }
    }
}

?>
