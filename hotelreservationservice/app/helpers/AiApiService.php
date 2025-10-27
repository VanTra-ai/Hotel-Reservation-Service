<?php
// app/helpers/AiApiService.php

class AiApiService
{
    private string $apiUrl = 'http://127.0.0.1:5000/predict'; // Có thể đưa ra file config
    private int $connectTimeout = 5; // Giây
    private int $timeout = 10; // Giây

    /**
     * Gửi yêu cầu dự đoán điểm đánh giá đến API Python.
     *
     * @param string $comment Bình luận của người dùng.
     * @param array $hotelInfo Mảng 7 điểm số của khách sạn.
     * @param array $reviewInfo Mảng 3 thông tin về review (room_type_id, duration_id, group_id).
     * @return float|null Điểm số dự đoán hoặc null nếu lỗi.
     */
    public function getPredictedRating(string $comment, array $hotelInfo, array $reviewInfo): ?float
    {
        $postData = [
            'comment' => $comment,
            'hotel_info' => $hotelInfo,
            'review_info' => $reviewInfo
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch); // Lấy lỗi cURL nếu có
        curl_close($ch);

        if ($error) {
             error_log("AI API cURL Error: " . $error);
             return null;
        }

        if ($http_code == 200) {
            $result = json_decode($response, true);
             // Kiểm tra xem 'predicted_score' có tồn tại và là số không
             if (isset($result['predicted_score']) && is_numeric($result['predicted_score'])) {
                  return (float)$result['predicted_score'];
              } else {
                  error_log("AI API response invalid: Missing or non-numeric 'predicted_score'. Response: " . $response);
                 return null;
              }
        } else {
            error_log("AI API call failed with HTTP code {$http_code}. Response: " . $response);
            return null;
        }
    }
}