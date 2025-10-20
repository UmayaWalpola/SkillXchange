<?php
class User extends Database {

    // 🔹 Register Organization
    public function registerOrganization($name, $email, $password, $certPath) {
        $sql = "INSERT INTO users (username, email, password, role, org_cert)
                VALUES (:name, :email, :password, 'organization', :cert)";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':password', password_hash($password, PASSWORD_BCRYPT));
        $stmt->bindValue(':cert', $certPath);
        return $stmt->execute();
    }

    // 🔹 Register Individual
    public function registerIndividual($name, $email, $password) {
        $sql = "INSERT INTO users (username, email, password, role)
                VALUES (:name, :email, :password, 'individual')";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':password', password_hash($password, PASSWORD_BCRYPT));
        return $stmt->execute();
    }

    // 🔹 Login (shared for both roles)
    public function login($email, $password) {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    // 🔹 Find user by ID (optional helper)
    public function getUserById($id) {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
}
