<?php
echo "Debug: register.php is being executed.<br>";

$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "registration_system";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . " (errno: " . $conn->connect_errno . ")");
} else {
    echo "Debug: Connection successful.<br>";
}

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo "Debug: Form has been submitted.<br>";
    $id = sanitize_input($_POST['id']);
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = sanitize_input($_POST['password']);

    echo "Debug: Inputs sanitized: ID=$id, Username=$username, Email=$email.<br>";

    if (empty($id) || empty($username) || empty($email) || empty($password)) {
        echo "All fields are required.";
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format.";
        exit();
    }
    if (strlen($password) < 6) {
        echo "Password must be at least 6 characters long.";
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    echo "Debug: Password hashed.<br>";

    $check_email_query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($check_email_query);
    if (!$stmt) {
        echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        exit();
    }
    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        exit();
    }
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo "Email already exists.";
        $stmt->close();
        exit();
    }
    $stmt->close();
    echo "Debug: Email check passed.<br>";

    $insert_query = "INSERT INTO users (id, username, email, password) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    if (!$stmt) {
        echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        exit();
    }
    $stmt->bind_param("isss", $id, $username, $email, $hashed_password);
    if (!$stmt->execute()) {
        echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        $stmt->close();
        exit();
    }

    echo "Registration successful!";
    $stmt->close();
}

$conn->close();
?>

