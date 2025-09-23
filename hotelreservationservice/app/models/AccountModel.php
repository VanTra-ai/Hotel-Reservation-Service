<?php
// app/models/AccountModel.php

class AccountModel
{
    private PDO $conn;
    private string $table_name = "account";

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    public function save(string $username, string $fullName, string $password, string $role = 'user'): bool
    {
        $query = "INSERT INTO " . $this->table_name . " SET username=:username, fullname=:fullname, password=:password, role=:role";
        $stmt = $this->conn->prepare($query);

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $username = htmlspecialchars(strip_tags($username));
        $fullName = htmlspecialchars(strip_tags($fullName));
        $role = htmlspecialchars(strip_tags($role));

        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":fullname", $fullName);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":role", $role);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Thêm kiểu trả về 'object|null'
    public function getAccountByUsername(string $username): ?object
    {
        $query = "SELECT id, username, fullname, password, role FROM " . $this->table_name . " WHERE username = :username LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $username = htmlspecialchars(strip_tags($username));
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return $row ?: null;
    }

    // Thêm phương thức mới để cập nhật mật khẩu
    public function updatePassword($username, $new_password)
    {
        $query = "UPDATE " . $this->table_name . " SET password = :password WHERE username = :username";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':password', $new_password);
        $stmt->bindParam(':username', $username);

        return $stmt->execute();
    }
}
