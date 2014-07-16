<?php
/**
 * Created by PhpStorm.
 * User: MajdiH
 * Date: 27/06/2014
 * Time: 13:13
 */

require_once dirname(__FILE__) . '/../../settings/PassHash.php';
require_once dirname(__FILE__) . '/../../settings/DbConnect.php';
require_once dirname(__FILE__) . '/../models/User.php';


class UsersController {

    private $conn;

    function __construct() {
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    public function create($name, $email, $password){

        if (!$this->isUserExists($email)) {
            $password_hash = PassHash::hash($password);

            $api_key = $this->generateApiKey();

            $stmt = $this->conn->prepare("INSERT INTO users(name, email, password_hash, api_key, status) values(?, ?, ?, ?, 1)");
            $stmt->bind_param("ssss", $name, $email, $password_hash, $api_key);
            $result = $stmt->execute();
            $stmt->close();

            if ($result) {
                return USER_CREATED_SUCCESSFULLY;
            } else {
                return USER_CREATE_FAILED;
            }
        } else {
            return USER_ALREADY_EXISTED;
        }

        return false;
    }

    public function Login($email, $password) {
        $stmt = $this->conn->prepare("SELECT password_hash FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($password_hash);
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->fetch();
            $stmt->close();

            if (PassHash::check_password($password_hash, $password)) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            $stmt->close();
            return FALSE;
        }
    }

    public function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT name, email, api_key, status, created_at FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            $user = $stmt->get_result()->fetch_object();
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }
    public function isValidApiKey($api_key) {
        $stmt = $this->conn->prepare("SELECT id from users WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function getUserId($api_key) {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        if ($stmt->execute()) {
            $user_id = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $user_id;
        } else {
            return NULL;
        }
    }

    private function generateApiKey() {
        return md5(uniqid(rand(), true));
    }

    private function isUserExists($email) {
        $stmt = $this->conn->prepare("SELECT id from users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

} 