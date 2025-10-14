<?php
// app/helpers/RatingHelper.php

class RatingHelper
{
    /**
     * Ngân hàng các cụm từ đánh giá, phân loại theo ngưỡng điểm.
     * Được sắp xếp từ cao xuống thấp.
     */
    private static $ratingMap = [
        ['threshold' => 9.8, 'texts' => ["Xuất sắc", "Hoàn hảo", "Trên cả tuyệt vời", "Trải nghiệm 10/10"]],
        ['threshold' => 9.0, 'texts' => ["Tuyệt vời", "Rất ấn tượng", "Trên cả mong đợi", "Cực kỳ hài lòng"]],
        ['threshold' => 8.5, 'texts' => ["Rất tốt", "Đáng tiền", "Khá ấn tượng", "Sẽ quay lại"]],
        ['threshold' => 8.0, 'texts' => ["Tốt", "Ổn", "Đúng như mong đợi", "Không có vấn đề gì"]],
        ['threshold' => 7.0, 'texts' => ["Hài lòng", "Khá ổn", "Chấp nhận được", "Phù hợp giá tiền"]],
        ['threshold' => 6.0, 'texts' => ["Tạm được", "Bình thường", "Có vài điểm cần cải thiện"]],
        ['threshold' => 5.0, 'texts' => ["Trung bình", "Không tệ", "Tạm ổn"]]
    ];

    /**
     * Chuyển đổi điểm số thành một văn bản đánh giá NGẪU NHIÊN và phù hợp.
     * @param float|null $score Điểm số từ AI.
     * @return string Văn bản đánh giá.
     */
    public static function getTextFromScore($score)
    {
        if ($score === null || !is_numeric($score)) {
            return "Chưa có đánh giá";
        }

        // Ép kiểu score về float để đảm bảo so sánh chính xác
        $score = (float)$score;

        foreach (self::$ratingMap as $rule) {
            if ($score >= $rule['threshold']) {
                $texts = $rule['texts'];
                $randomKey = array_rand($texts);
                return $texts[$randomKey];
            }
        }

        return "Cần cải thiện";
    }
}
