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
        $query = "SELECT r.*, a.username 
                  FROM " . $this->table_name . " r 
                  JOIN account a ON r.account_id = a.id 
                  WHERE r.hotel_id = :hotel_id 
                  ORDER BY r.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':hotel_id', $hotelId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Thêm một đánh giá mới, bao gồm cả điểm AI và văn bản đánh giá.
     */
    public function addReview($hotelId, $accountId, $rating, $comment, $category, $ai_rating = null, $rating_text = null)
    {
        $query = "INSERT INTO " . $this->table_name . " 
                  (hotel_id, account_id, rating, ai_rating, rating_text, comment, category) 
                  VALUES (:hotel_id, :account_id, :rating, :ai_rating, :rating_text, :comment, :category)";

        try {
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':hotel_id', $hotelId);
            $stmt->bindParam(':account_id', $accountId);
            $stmt->bindParam(':rating', $rating);
            $stmt->bindParam(':ai_rating', $ai_rating);
            $stmt->bindParam(':rating_text', $rating_text);
            $stmt->bindParam(':comment', $comment);
            $stmt->bindParam(':category', $category);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Add review error: " . $e->getMessage());
            return false;
        }
    }

    // Lấy điểm trung bình theo category
    public function getAverageRatingsByCategory($hotelId)
    {
        $query = "SELECT category, ROUND(AVG(rating), 1) as avg_rating
                  FROM " . $this->table_name . " 
                  WHERE hotel_id = :hotel_id AND category IS NOT NULL
                  GROUP BY category";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':hotel_id', $hotelId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
