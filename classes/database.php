<?php
class database {
    function opencon(): PDO {
        try {
            return new PDO(
                dsn: 'mysql:host=localhost;dbname=dbs_josh',
                username: 'root',
                password: '',
                options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    function signupUser($firstname, $lastname, $birthday, $sex, $phone, $email, $username, $password, $profile_picture_path) {
        try {
            $con = $this->opencon();
            $con->beginTransaction();

            // Insert into users table
            $stmt = $con->prepare("
                INSERT INTO users (user_FN, user_LN, user_birthday, user_sex, user_email, user_phone, user_username, user_password)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$firstname, $lastname, $birthday, $sex, $email, $phone, $username, $password]);
            $userId = $con->lastInsertId();

            // Insert into users_pictures table
            $stmt = $con->prepare("
                INSERT INTO users_pictures (user_id, user_pic_url)
                VALUES (?, ?)
            ");
            $stmt->execute([$userId, $profile_picture_path]);

            $con->commit();
            return $userId;
        } catch (PDOException $e) {
            $con->rollBack();
            error_log("Signup error: " . $e->getMessage());
            return false;
        }
    }

    function insertAddress($userId, $street, $barangay, $city, $province) {
        try {
            $con = $this->opencon();
            $con->beginTransaction();

            // Insert into address table
            $stmt = $con->prepare("
                INSERT INTO address (ba_street, ba_barangay, ba_city, ba_province)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$street, $barangay, $city, $province]);
            $addressId = $con->lastInsertId();

            // Insert into users_address table
            $stmt = $con->prepare("
                INSERT INTO users_address (user_id, address_id)
                VALUES (?, ?)
            ");
            $stmt->execute([$userId, $addressId]);

            $con->commit();
            return true;
        } catch (PDOException $e) {
            $con->rollBack();
            error_log("Address insert error: " . $e->getMessage());
            return false;
        }
    }

    function checkUserExists($email, $username) {
        try {
            $con = $this->opencon();
            $stmt = $con->prepare("
                SELECT COUNT(*) FROM users WHERE user_email = ? OR user_username = ?
            ");
            $stmt->execute([$email, $username]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Check user error: " . $e->getMessage());
            return false;
        }
    }
}