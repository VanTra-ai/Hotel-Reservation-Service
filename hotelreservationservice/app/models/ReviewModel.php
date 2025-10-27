<?php
// app/models/ReviewModel.php
class ReviewModel
{
    private $conn;
    private $table_name = "review";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Lấy danh sách đánh giá theo ID khách sạn
    public function getReviewsByHotelId($hotelId)
    {
        // Thêm JOIN với booking và room để lấy thông tin ngữ cảnh
        $query = "SELECT r.*, a.username, 
                         b.check_in_date, b.check_out_date,
                         room.room_type
                  FROM " . $this->table_name . " r 
                  JOIN account a ON r.account_id = a.id 
                  LEFT JOIN booking b ON r.booking_id = b.id -- Sử dụng LEFT JOIN để không bị lỗi nếu booking_id là NULL
                  LEFT JOIN room ON b.room_id = room.id
                  WHERE r.hotel_id = :hotel_id 
                  ORDER BY r.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':hotel_id', $hotelId);
        $stmt->execute();

        $reviews = $stmt->fetchAll(PDO::FETCH_OBJ);

        // Tính số đêm ở cho mỗi review (tương tự như trong BookingModel)
        foreach ($reviews as $review) {
            if ($review->check_in_date && $review->check_out_date) {
                $check_in = strtotime($review->check_in_date);
                $check_out = strtotime($review->check_out_date);
                $diff = $check_out - $check_in;
                $nights = max(1, round($diff / (60 * 60 * 24)));
                $review->nights = $nights;
            } else {
                $review->nights = null; // Hoặc giá trị mặc định
            }
        }

        return $reviews;
    }

    /**
     * Thêm một đánh giá mới, bao gồm cả điểm AI và văn bản đánh giá.
     */
    public function addReview(
        int $hotelId,
        int $accountId,
        ?int $bookingId,
        string $comment,
        float $ratingStaff,
        float $ratingAmenities,
        float $ratingCleanliness,
        float $ratingComfort,
        float $ratingValue,
        float $ratingLocation,
        float $ratingWifi,
        ?float $aiRating,
        ?string $ratingText
    ) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (hotel_id, account_id, booking_id, 
                   rating_staff, rating_amenities, rating_cleanliness, rating_comfort, 
                   rating_value, rating_location, rating_wifi,
                   ai_rating, rating_text, comment) 
                  VALUES (:hotel_id, :account_id, :booking_id,
                          :rating_staff, :rating_amenities, :rating_cleanliness, :rating_comfort, 
                          :rating_value, :rating_location, :rating_wifi,
                          :ai_rating, :rating_text, :comment)";

        try {
            $stmt = $this->conn->prepare($query);

            // Bind ID và Comment
            $stmt->bindParam(':hotel_id', $hotelId, PDO::PARAM_INT);
            $stmt->bindParam(':account_id', $accountId, PDO::PARAM_INT);
            $stmt->bindParam(':booking_id', $bookingId, PDO::PARAM_INT); // bookingId có thể là NULL

            // Bind 7 điểm chi tiết (user input)
            $stmt->bindParam(':rating_staff', $ratingStaff);
            $stmt->bindParam(':rating_amenities', $ratingAmenities);
            $stmt->bindParam(':rating_cleanliness', $ratingCleanliness);
            $stmt->bindParam(':rating_comfort', $ratingComfort);
            $stmt->bindParam(':rating_value', $ratingValue);
            $stmt->bindParam(':rating_location', $ratingLocation);
            $stmt->bindParam(':rating_wifi', $ratingWifi);

            // Bind AI result
            $stmt->bindParam(':ai_rating', $aiRating);
            $stmt->bindParam(':rating_text', $ratingText);
            $stmt->bindParam(':comment', $comment);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Add review error: " . $e->getMessage());
            return false;
        }
    }
}
