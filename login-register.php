
<?php
session_start();

require_once 'config.php';

// REGISTER
if (isset($_POST['register'])) {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Check whether email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['register_error'] = "Email already registered!";
        $_SESSION['active_form'] = "register";
        header("Location: index.php");
        exit();
    }

    // Save the new user
    $stmt = $conn->prepare(
        "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)"
    );

    $stmt->bind_param("ssss", $name, $email, $password, $role);
    $stmt->execute();

    $_SESSION['login_error'] = "Registration successful! Please log in.";
    $_SESSION['active_form'] = "login";

    header("Location: index.php");
    exit();
}

// LOGIN
if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            // Redirect according to user role
            if ($user['role'] == 'kitchen') {
                header("Location: kitchen-dashboard.php");
            }
            elseif ($user['role'] == 'waiter') {
                header("Location: waiter-dashboard.php");
            }
            elseif ($user['role'] == 'admin') {
                header("Location: admin-dashboard.php");
            }
            else {
                header("Location: home.php");
            }

            exit();
        }
    }

    $_SESSION['login_error'] = "Incorrect email or password.";
    $_SESSION['active_form'] = "login";

    header("Location: index.php");
    exit();
}
?>
