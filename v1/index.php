<?php
/**
 * Created by PhpStorm.
 * User: MajdiH
 * Date: 26/06/2014
 * Time: 20:29
 */

require_once dirname(__FILE__) . '/controllers/UsersController.php';
require_once dirname(__FILE__) . '/controllers/TasksController.php';
require '.././libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

// User id from db - Global Variable
$user_id = NULL;


/**
 * User Registration
 * url - /register
 * method - POST
 * params - name, email, password
 */
$app->post('/register', function() use ($app) {

    verifyRequiredParams(array('name', 'email', 'password'));

    $response = array();

    // reading post params
    $name = $app->request->post('name');
    $email = $app->request->post('email');
    $password = $app->request->post('password');

    validateEmail($email);

    $usersControllers = new UsersController();
    $res = $usersControllers->create($name, $email, $password);


    if ($res == USER_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["message"] = "You are successfully registered";
        echoRespnse(201, $response);
    } else if ($res == USER_CREATE_FAILED) {
        $response["error"] = true;
        $response["message"] = "Oops! An error occurred while registereing";
        echoRespnse(200, $response);
    } else if ($res == USER_ALREADY_EXISTED) {
        $response["error"] = true;
        $response["message"] = "Sorry, this email already existed";
        echoRespnse(200, $response);
    }
});

/**
* User Login
    * url - /login
* method - POST
* params - email, password
*/
$app->post('/login', function() use ($app) {
    verifyRequiredParams(array('email', 'password'));

    $email = $app->request()->post('email');
    $password = $app->request()->post('password');
    $response = array();

    $usersControllers = new UsersController();
    if ($usersControllers->Login($email, $password)) {
        $user = $usersControllers->getUserByEmail($email);

        if ($user != NULL) {
            $response["error"] = false;
            $response['name'] = $user->name;
            $response['email'] = $user->email;
            $response['apiKey'] = $user->api_key;
            $response['createdAt'] = $user->created_at;
        } else {
            // unknown error occurred
            $response['error'] = true;
            $response['message'] = "An error occurred. Please try again";
        }
    } else {
        // user credentials are wrong
        $response['error'] = true;
        $response['message'] = 'Login failed. Incorrect credentials';
    }

    echoRespnse(200, $response);
});




/**
 * Adding Middle Layer to authenticate every request
 * Checking if the request has valid api key in the 'Authorization' header
 */
function authenticate(\Slim\Route $route) {
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();

    if (isset($headers['Authorization'])) {
        $usersControllers = new UsersController();

        $api_key = $headers['Authorization'];
        if (!$usersControllers->isValidApiKey($api_key)) {
            $response["error"] = true;
            $response["message"] = "Access Denied. Invalid Api key";
            echoRespnse(401, $response);
            $app->stop();
        } else {
            global $user_id;
            $user = $usersControllers->getUserId($api_key);
            if ($user != NULL)
                $user_id = $user["id"];
        }
    } else {
        $response["error"] = true;
        $response["message"] = "Api key is misssing";
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * Creating new task in db
 * method POST
 * params - name
 * url - /tasks
 */
$app->post('/tasks', 'authenticate', function() use ($app) {
    verifyRequiredParams(array('task'));

    $response = array();
    $task = $app->request->post('task');

    global $user_id;
    $tasksControllers = new TasksController();

    $task_id = $tasksControllers->create($user_id, $task);

    if ($task_id != NULL) {
        $response["error"] = false;
        $response["message"] = "Task created successfully";
        $response["task_id"] = $task_id;
    } else {
        $response["error"] = true;
        $response["message"] = "Failed to create task. Please try again";
    }
    echoRespnse(201, $response);
});

/**
 * Listing all tasks
 * method GET
 * url /tasks
 */
$app->get('/tasks', function() {
    $response = array();
    $tasksControllers = new TasksController();

    $result = $tasksControllers->getAllTasks();

    $response["error"] = false;
    $response["tasks"] = array();

    while ($task = $result->fetch_object()) {
        $tmp = array();
        $tmp["id"] = $task->id;
        $tmp["task"] = $task->task;
        $tmp["status"] = $task->status;
        $tmp["createdAt"] = $task->created_at;
        array_push($response["tasks"], $tmp);
    }

    echoRespnse(200, $response);
});

/**
 * Listing all tasks of particual user
 * method GET
 * url /my
 */
$app->get('/my', 'authenticate', function() {
    global $user_id;
    $response = array();
    $tasksControllers = new TasksController();

    $result = $tasksControllers->getTasksByUser($user_id);

    $response["error"] = false;
    $response["tasks"] = array();

    while ($task = $result->fetch_object()) {
        $tmp = array();
        $tmp["id"] = $task->id;
        $tmp["task"] = $task->task;
        $tmp["status"] = $task->status;
        $tmp["createdAt"] = $task->created_at;
        array_push($response["tasks"], $tmp);
    }

    echoRespnse(200, $response);
});


/**
 * Listing single task
 * method GET
 * url /tasks/:id
 * Will return 404 if there is not task
 */
$app->get('/tasks/:id', function($task_id) {
    $response = array();
    $tasksControllers = new TasksController();

    $task = $tasksControllers->getTask($task_id);

    if ($task != NULL) {
        $response["error"] = false;
        $response["id"] = $task->id;
        $response["task"] = $task->task;
        $response["status"] = $task->status;
        $response["createdAt"] = $task->created_at;
        echoRespnse(200, $response);
    } else {
        $response["error"] = true;
        $response["message"] = "The requested resource doesn't exists";
        echoRespnse(404, $response);
    }
});

/**
 * Updating existing task
 * method PUT
 * params task, status
 * url - /tasks/:id
 */
$app->put('/tasks/:id', 'authenticate', function($task_id) use($app) {

    verifyRequiredParams(array('task', 'status'));

    global $user_id;
    $task = $app->request->put('task');
    $status = $app->request->put('status');

    $tasksControllers = new TasksController();
    $response = array();

    // updating task
    $result = $tasksControllers->updateTask($user_id, $task_id, $task, $status);
    if ($result) {
        $response["error"] = false;
        $response["message"] = "Task updated successfully";
    } else {
        $response["error"] = true;
        $response["message"] = "Task failed to update. Please try again!";
    }
    echoRespnse(200, $response);
});

/**
 * Deleting task. Users can delete only their tasks
 * method DELETE
 * url /tasks
 */
$app->delete('/tasks/:id', 'authenticate', function($task_id) use($app) {
    global $user_id;

    $tasksControllers = new TasksController();
    $response = array();
    $result = $tasksControllers->deleteTask($user_id, $task_id);
    if ($result) {
        // task deleted successfully
        $response["error"] = false;
        $response["message"] = "Task deleted succesfully";
    } else {
        // task failed to delete
        $response["error"] = true;
        $response["message"] = "Task failed to delete. Please try again!";
    }
    echoRespnse(200, $response);
});

/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * Validating email address
 */
function validateEmail($email) {
    $app = \Slim\Slim::getInstance();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["error"] = true;
        $response["message"] = 'Email address is not valid';
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * Echoing json response to client
 * @param String $status_code Http response code
 * @param Int $response Json response
 */
function echoRespnse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');

    echo json_encode($response);
}

$app->run();