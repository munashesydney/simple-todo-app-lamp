<?php
// Establish a global database connection
$mysqli = mysqli_connect("localhost", "cs213user", "letmein", "testDB");

if (!$mysqli) {
    die("Database connection failed: " . mysqli_connect_error());
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

    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($mysqli, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Login successful']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
    }
}

// Function for user registration
function register()
{
    global $mysqli;

    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    if (!$username || !$password) {
        echo json_encode(['status' => 'error', 'message' => 'Username or password missing']);
        return;
    }

    $query = "INSERT INTO users (username, password) VALUES ('$username', '$password')";
    if (mysqli_query($mysqli, $query)) {
        echo json_encode(['status' => 'success', 'message' => 'User registered successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Registration failed']);
    }
}

// Function for user logout
function logout()
{
    // Example logic for logout (usually handled in the session context)
    echo json_encode(['status' => 'success', 'message' => 'Logout successful']);
}

?>
