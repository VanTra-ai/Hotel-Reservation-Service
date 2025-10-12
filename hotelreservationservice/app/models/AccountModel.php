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

    /**
     * Lưu tài khoản mới (password sẽ được hash ở đây).
     * Trả về true/false. Nếu false, có thể kiểm tra lỗi server log.
     */
    public function save(string $username, string $fullName, string $email, string $password, string $role = 'user'): bool
    {
        try {
            $query = "INSERT INTO " . $this->table_name . " (username, fullname, email, password, role)
                      VALUES (:username, :fullname, :email, :password, :role)";
            $stmt = $this->conn->prepare($query);

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $username = htmlspecialchars(strip_tags($username));
            $fullName = htmlspecialchars(strip_tags($fullName));
            $email = htmlspecialchars(strip_tags($email));
            $role = htmlspecialchars(strip_tags($role));

            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":fullname", $fullName);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":password", $hashed_password);
            $stmt->bindParam(":role", $role);

            return $stmt->execute();
        } catch (PDOException $e) {
            // TODO: Log $e->getMessage() vào file log nếu cần
            return false;
        }
    }

    /**
     * Lấy account theo email (hoặc null nếu không tồn tại)
     */
    public function getAccountByEmail(string $email): ?object
    {
        $query = "SELECT id, username, fullname, email, password, role
                  FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $email = htmlspecialchars(strip_tags($email));
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return $row ?: null;
    }

    /**
     * Lấy account theo username (hoặc null nếu không tồn tại)
     */
    public function getAccountByUsername(string $username): ?object
    {
        $query = "SELECT id, username, fullname, email, password, role
                  FROM " . $this->table_name . " WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $username = htmlspecialchars(strip_tags($username));
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return $row ?: null;
    }

    /**
     * Lấy account theo id
     */
    public function getAccountById(int $id): ?object
    {
        $query = "SELECT id, username, fullname, email, password, role
                  FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return $row ?: null;
    }

    /**
     * Cập nhật mật khẩu (tham số $new_password nên đã được hash trước khi gọi)
     */
    public function updatePassword(string $username, string $new_password_hashed): bool
    {
        try {
            $query = "UPDATE " . $this->table_name . " SET password = :password WHERE username = :username";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':password', $new_password_hashed);
            $stmt->bindParam(':username', $username);
            return $stmt->execute();
        } catch (PDOException $e) {
            // TODO: log error
            return false;
        }
    }

    /**
     * Kiểm tra tồn tại username/email (tiện dụng)
     */
    public function existsByUsername(string $username): bool
    {
        $stmt = $this->conn->prepare("SELECT 1 FROM " . $this->table_name . " WHERE username = :username LIMIT 1");
        $username = htmlspecialchars(strip_tags($username));
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return (bool)$stmt->fetchColumn();
    }

    public function existsByEmail(string $email): bool
    {
        $stmt = $this->conn->prepare("SELECT 1 FROM " . $this->table_name . " WHERE email = :email LIMIT 1");
        $email = htmlspecialchars(strip_tags($email));
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return (bool)$stmt->fetchColumn();
    }
}
