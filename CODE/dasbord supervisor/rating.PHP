<?php
class Rating {
    private $rating_id;      // معرّف التقييم
    private $task_id;        // معرّف المهمة المرتبطة بالتقييم
    private $user_id;        // معرّف المستخدم (المشرف) الذي قام بالتقييم
    private $score;          // درجة التقييم من 1 إلى 100
    private $timestamp;      // تاريخ ووقت إضافة التقييم

    // مُنشئ الكلاس لتحديد القيم الأولية
    public function __construct($rating_id = null, $task_id, $user_id, $score, $timestamp = null) {
        $this->rating_id = $rating_id;
        $this->task_id = $task_id;
        $this->user_id = $user_id;
        $this->score = $score;
        $this->timestamp = $timestamp ? $timestamp : date('Y-m-d H:i:s'); // تعيين timestamp إلى الوقت الحالي إذا لم يُعطى
    }

    // دالة لإرجاع معرّف التقييم
    public function getRatingId() {
        return $this->rating_id;
    }

    // دالة لإرجاع معرّف المهمة
    public function getTaskId() {
        return $this->task_id;
    }

    // دالة لإرجاع معرّف المستخدم الذي قام بالتقييم
    public function getUserId() {
        return $this->user_id;
    }

    // دالة لإرجاع درجة التقييم
    public function getScore() {
        return $this->score;
    }

    // دالة لإرجاع تاريخ ووقت إضافة التقييم
    public function getTimestamp() {
        return $this->timestamp;
    }

    // دالة لحفظ التقييم في قاعدة البيانات (تحديث إذا كان التقييم موجودًا)
    public function saveToDatabase($db) {
        // التحقق من وجود تقييم سابق للمهمة بواسطة نفس المستخدم
        $query = "SELECT * FROM ratings WHERE task_id = :task_id AND user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':task_id', $this->task_id);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->execute();

        // إذا وجد تقييم سابق، نقوم بتحديثه
        if ($stmt->rowCount() > 0) {
            $query = "UPDATE ratings SET score = :score, timestamp = :timestamp WHERE task_id = :task_id AND user_id = :user_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':score', $this->score);
            $stmt->bindParam(':timestamp', $this->timestamp);
            $stmt->bindParam(':task_id', $this->task_id);
            $stmt->bindParam(':user_id', $this->user_id);
            return $stmt->execute();
        } else {
            // إذا لم يوجد تقييم سابق، نقوم بإدخال التقييم الجديد
            $query = "INSERT INTO ratings (task_id, user_id, score, timestamp) 
                      VALUES (:task_id, :user_id, :score, :timestamp)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':task_id', $this->task_id);
            $stmt->bindParam(':user_id', $this->user_id);
            $stmt->bindParam(':score', $this->score);
            $stmt->bindParam(':timestamp', $this->timestamp);
            return $stmt->execute();
        }
    }

    // دالة لاسترجاع التقييمات المرتبطة بمهمة معينة
    public static function getRatingsByTaskId($db, $task_id) {
        $query = "SELECT * FROM ratings WHERE task_id = :task_id ORDER BY timestamp DESC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':task_id', $task_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // دالة لاسترجاع اسم المشرف بناءً على معرف المهمة
    public static function getSupervisorNameByTaskId($db, $task_id) {
        // استعلام SQL للربط بين جدول ratings و users واسترجاع اسم المشرف
        $query = "SELECT u.name AS supervisor_name 
                  FROM ratings r 
                  LEFT JOIN users u ON r.user_id = u.user_id 
                  WHERE r.task_id = :task_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':task_id', $task_id);
        $stmt->execute();

        // إرجاع اسم المشرف إذا تم العثور عليه
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['supervisor_name'] : null;
    }

    // دالة لتحديث التقييم
    public function updateRating($db, $newScore) {
        try {
            $this->score = $newScore;
            $query = "UPDATE ratings SET score = :score WHERE rating_id = :rating_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':score', $this->score);
            $stmt->bindParam(':rating_id', $this->rating_id);
            return $stmt->execute();
        } catch (Exception $e) {
            echo "Error updating rating: " . $e->getMessage();
            return false;
        }
    }

    // دالة لحذف التقييم
    public function deleteRating($db) {
        try {
            $query = "DELETE FROM ratings WHERE rating_id = :rating_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':rating_id', $this->rating_id);
            return $stmt->execute();
        } catch (Exception $e) {
            echo "Error deleting rating: " . $e->getMessage();
            return false;
        }
    }

    // دالة للتحقق من وجود تقييم سابق من نفس المستخدم للمهمة
    public static function checkIfRatingExists($db, $task_id, $user_id) {
        $query = "SELECT * FROM ratings WHERE task_id = :task_id AND user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':task_id', $task_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
?>
