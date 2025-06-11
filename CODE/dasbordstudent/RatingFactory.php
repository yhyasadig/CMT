<?php
require_once 'Rating.php';

class RatingFactory {
    /**
     * تنشئ كائن Rating جديد
     * 
     * @param int|null $rating_id  معرف التقييم (يمكن تركه null لإنشاء جديد)
     * @param int $task_id        معرف المهمة
     * @param int $user_id        معرف المستخدم الذي قيم
     * @param int $score          درجة التقييم (1-100)
     * @param string|null $timestamp (اختياري) وقت التقييم
     * 
     * @return Rating
     */
    public static function createRating($rating_id = null, $task_id, $user_id, $score, $timestamp = null): Rating {
        return new Rating($rating_id, $task_id, $user_id, $score, $timestamp);
    }
}
?>
