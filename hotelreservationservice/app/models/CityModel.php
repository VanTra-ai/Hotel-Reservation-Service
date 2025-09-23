<?php
class CityModel
{
    private $conn;
    private $table_name = "city";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getCities()
    {
        // Cập nhật câu truy vấn để lấy thêm cột 'image'
        $query = "SELECT id, name, image FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $result;
    }

    public function getCityById($id)
    {
        // Cập nhật câu truy vấn để lấy thêm cột 'image'
        $query = "SELECT id, name, image FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result =  $stmt->fetch(PDO::FETCH_OBJ);
        return $result;
    }

    public function getCityByName($name)
{
    $query = "SELECT id, name, image 
              FROM " . $this->table_name . " 
              WHERE LOWER(name) LIKE LOWER(:name) 
              LIMIT 1";
    $stmt = $this->conn->prepare($query);
    $search = "%" . $name . "%";  // tìm gần đúng
    $stmt->bindParam(':name', $search);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_OBJ);
}

    // Bạn cũng cần cập nhật hàm addCity để có thể thêm image
    public function addCity($name, $image)
    {
        $query = "INSERT INTO " . $this->table_name . " (name, image) VALUES (:name, :image)";
        $stmt = $this->conn->prepare($query);

        $name = htmlspecialchars(strip_tags($name));
        $image = htmlspecialchars(strip_tags($image));

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':image', $image);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Tương tự, cập nhật hàm updateCity
    public function updateCity($id, $name, $image)
    {
        $query = "UPDATE " . $this->table_name . " SET name = :name, image = :image WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $name = htmlspecialchars(strip_tags($name));
        $image = htmlspecialchars(strip_tags($image));

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':image', $image);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Hàm deleteCity giữ nguyên vì nó không liên quan đến cột 'image'
    public function deleteCity($id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
