<?php

session_start();
require_once '../config.php';

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = "admin";

    $checkEmail = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $result = $checkEmail->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['register_error'] = 'Email is already registered!';
        $_SESSION['active_form'] = 'register';
        header("Location: login.php");
        exit();
    }

    $insertUser = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $insertUser->bind_param("ssss", $name, $email, $password, $role);

    if ($insertUser->execute()) {
        $_SESSION['active_form'] = 'login';
        header("Location: login.php");
        exit();
    }

    $_SESSION['register_error'] = 'Registration failed. Please try again.';
    $_SESSION['active_form'] = 'register';
    header("Location: login.php");
    exit();
}

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $checkUser = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $checkUser->bind_param("s", $email);
    $checkUser->execute();
    $result = $checkUser->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            header("Location: ../admin/dashboard.php");
            exit();
        }
    }

    $_SESSION['login_error'] = 'Incorrect email or password';
    $_SESSION['active_form'] = 'login';
    header("Location: login.php");
    exit();
}

header("Location: login.php");
exit();

?>