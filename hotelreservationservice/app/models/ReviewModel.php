<?php
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
        $query = "SELECT r.*, a.username FROM " . $this->table_name . " r JOIN account a ON r.account_id = a.id WHERE r.hotel_id = :hotel_id ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':hotel_id', $hotelId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Thêm một đánh giá mới
    public function addReview($hotelId, $accountId, $rating, $comment, $category)
{
    $query = "INSERT INTO " . $this->table_name . " 
              (hotel_id, account_id, rating, comment, category) 
              VALUES (:hotel_id, :account_id, :rating, :comment, :category)";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':hotel_id', $hotelId);
    $stmt->bindParam(':account_id', $accountId);
    $stmt->bindParam(':rating', $rating);
    $stmt->bindParam(':comment', $comment);
    $stmt->bindParam(':category', $category);
    return $stmt->execute();
}
public function getAverageRatingsByCategory($hotelId)
{
    $query = "SELECT category, ROUND(AVG(rating), 1) as avg_rating
              FROM " . $this->table_name . " 
              WHERE hotel_id = :hotel_id
              GROUP BY category";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':hotel_id', $hotelId);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
}
