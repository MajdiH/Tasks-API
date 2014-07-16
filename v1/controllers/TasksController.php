<?php
/**
 * Created by PhpStorm.
 * User: MajdiH
 * Date: 27/06/2014
 * Time: 17:32
 */

require_once dirname(__FILE__) . '/../../settings/PassHash.php';
require_once dirname(__FILE__) . '/../../settings/DbConnect.php';
require_once dirname(__FILE__) . '/../models/Task.php';

class TasksController {

    private $conn;

    function __construct() {
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    public function create($user_id, $task) {
        $stmt = $this->conn->prepare("INSERT INTO tasks(task) VALUES(?)");
        $stmt->bind_param("s", $task);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            $new_task_id = $this->conn->insert_id;
            $res = $this->createUserTask($user_id, $new_task_id);
            if ($res) {
                return $new_task_id;
            } else {
                return NULL;
            }
        } else {
            return NULL;
        }
    }

    private function createUserTask($user_id, $task_id) {
        $stmt = $this->conn->prepare("INSERT INTO user_tasks(user_id, task_id) values(?, ?)");
        $stmt->bind_param("ii", $user_id, $task_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function getAllTasks() {
        $stmt = $this->conn->prepare("SELECT t.* FROM tasks t");
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;
    }

    public function getTasksByUser($user_id) {
        $stmt = $this->conn->prepare("SELECT t.* FROM tasks t, user_tasks ut WHERE t.id = ut.task_id AND ut.user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;
    }

    public function getTask($task_id) {
        $stmt = $this->conn->prepare("SELECT t.id, t.task, t.status, t.created_at from tasks t WHERE t.id = ?");
        $stmt->bind_param("i", $task_id);
        if ($stmt->execute()) {
            $task = $stmt->get_result()->fetch_object();
            $stmt->close();
            return $task;
        } else {
            return NULL;
        }
    }

    public function updateTask($user_id, $task_id, $task, $status) {
        $stmt = $this->conn->prepare("UPDATE tasks t, user_tasks ut set t.task = ?, t.status = ? WHERE t.id = ? AND t.id = ut.task_id AND ut.user_id = ?");
        $stmt->bind_param("siii", $task, $status, $task_id, $user_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    public function deleteTask($user_id, $task_id) {
        $stmt = $this->conn->prepare("DELETE t FROM tasks t, user_tasks ut WHERE t.id = ? AND ut.task_id = t.id AND ut.user_id = ?");
        $stmt->bind_param("ii", $task_id, $user_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

} 