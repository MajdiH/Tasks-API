<?php
/**
 * Created by PhpStorm.
 * User: MajdiH
 * Date: 26/06/2014
 * Time: 20:31
 */

include_once dirname(__FILE__) . '/Config.php';

class DbConnect {

    private $conn;

    function __construct() {
    }

    function connect() {
        $this->conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

        if (mysqli_connect_errno()) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }

        return $this->conn;
    }

} 