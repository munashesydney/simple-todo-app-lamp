<?php
// Establish a global database connection
$mysqli = mysqli_connect("localhost", "root", "letmein", "todolistdb");

if (!$mysqli) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
}

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and retrieve the 'request' parameter
    $request = filter_input(INPUT_POST, 'request', FILTER_SANITIZE_STRING);

    // Handle the request based on its value
    if ($request) {
        switch ($request) {
            case 'login':
                login();
                break;
            case 'register':
                register();
                break;
            case 'logout':
                logout();
                break;
            case 'add_task':
                addTask();
                break;
            case 'get_tasks':
                getTasks();
                break;
            case 'edit_task':
                editTask();
                break;
            case 'delete_task':
                deleteTask();
                break;
            default:
                echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Request parameter missing']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

// Function for user login
function login()
{
    global $mysqli;

    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    if (!$username || !$password) {
        echo json_encode(['status' => 'error', 'message' => 'Username or password missing']);
        return;
    }

    $query = "SELECT * FROM Users WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($mysqli, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'message' => 'Login successful', 'user_id' => $user['id'], 'firstname' => $user['first_name'], 'lastname' => $user['last_name']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
    }
}

// Function for user registration
function register()
{
    global $mysqli;
    try{
        
    $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
    $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    $date = date('Y-m-d H:i:s');

    if (!$username || !$password || !$email) {
        echo json_encode(['status' => 'error', 'message' => 'Username or password missing']);
        return;
    }
  

    $query = "INSERT INTO Users (username, email, password, first_name, last_name, created_at) VALUES ('$username', '$email', '$password','$first_name', '$last_name', '$date')";
    if (mysqli_query($mysqli, $query)) {
        echo json_encode(['status' => 'success', 'message' => 'User registered successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Registration failed']);
    }
    }catch(Exception $e){
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

// Function for user logout
function logout()
{
    // Example logic for logout (usually handled in the session context)
    echo json_encode(['status' => 'success', 'message' => 'Logout successful']);
}

function addTask()
{
    global $mysqli;

    $userId = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $dueDate = filter_input(INPUT_POST, 'due_date', FILTER_SANITIZE_STRING);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

    // Validate required fields
    if (!$userId || !$title || !$status) {
        echo json_encode(['status' => 'error', 'message' => 'User ID, title, and status are required']);
        return;
    }

    // SQL query to insert the task
    $query = "INSERT INTO ToDoList (user_id, title, description, due_date, status) 
              VALUES ('$userId', '$title', '$description', '$dueDate', '$status')";

    if (mysqli_query($mysqli, $query)) {
        echo json_encode(['status' => 'success', 'message' => 'Task added successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add task: ' . mysqli_error($mysqli)]);
    }
}

function getTasks()
{
    global $mysqli;

    // Query to fetch all tasks
    $query = "SELECT id, title, due_date, status FROM ToDoList";
    $result = mysqli_query($mysqli, $query);

    if (!$result) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch tasks: ' . mysqli_error($mysqli)]);
        return;
    }

    // Generate HTML
    $html = '';
    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row['id'];
        $title = htmlspecialchars($row['title']);
        $dueDate = $row['due_date'] ? htmlspecialchars($row['due_date']) : 'No due date';
        $status = htmlspecialchars($row['status']);
        $statusClass = ($status === 'Pending') ? 'bg-warning text-dark' : 'bg-success text-white';

        $html .= '
            <li class="list-group-item">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <strong>' . $title . '</strong>
                  <span class="badge ' . $statusClass . ' ms-2">' . $status . '</span>
                  <small class="text-muted">(Due: ' . $dueDate . ')</small>
                </div>
                <div>
                  <button class="btn btn-sm btn-warning me-2 edit-btn" data-id="' . $id . '">Edit</button>
                  <button class="btn btn-sm btn-danger delete-btn" data-id="' . $id . '">Delete</button>
                </div>
              </div>
            </li>';
    }

    echo $html;
}

function editTask()
{
    global $mysqli;

    // Retrieve and sanitize POST inputs
    $taskId = filter_input(INPUT_POST, 'task_id', FILTER_SANITIZE_NUMBER_INT);
    $newTitle = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);

    if (!$taskId || !$newTitle) {
        echo json_encode(['status' => 'error', 'message' => 'Task ID and title are required']);
        return;
    }

    // SQL query to update the task's title
    $query = "UPDATE ToDoList SET title = '$newTitle', updated_at = NOW() WHERE id = '$taskId'";
    if (mysqli_query($mysqli, $query)) {
        echo json_encode(['status' => 'success', 'message' => 'Task updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update task: ' . mysqli_error($mysqli)]);
    }
}

function deleteTask()
{
    global $mysqli;

    // Retrieve and sanitize POST inputs
    $taskId = filter_input(INPUT_POST, 'task_id', FILTER_SANITIZE_NUMBER_INT);

    if (!$taskId) {
        echo json_encode(['status' => 'error', 'message' => 'Task ID is required']);
        return;
    }

    // SQL query to delete the task
    $query = "DELETE FROM ToDoList WHERE id = '$taskId'";
    if (mysqli_query($mysqli, $query)) {
        echo json_encode(['status' => 'success', 'message' => 'Task deleted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete task: ' . mysqli_error($mysqli)]);
    }
}



?>
