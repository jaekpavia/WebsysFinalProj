<?php

session_start();
require_once '../config.php';

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    


    $checkUsername = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $checkUsername->bind_param("s", $username);
    $checkUsername->execute();
    $result = $checkUsername->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['register_error'] = 'Username is already exists!';
        $_SESSION['active_form'] = 'register';
        header("Location: login.php");
        exit();
    }


    $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $result = $checkEmail->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['register_error'] = 'Email is already registered!';
        $_SESSION['active_form'] = 'register';
        header("Location: login.php");
        exit();
    }

   $insertUser = $conn->prepare("INSERT INTO users (username, name, email, password) VALUES (?, ?, ?, ?)");
   $insertUser->bind_param("ssss", $username, $name, $email, $hashedPassword);

    if ($insertUser->execute()) {
        $_SESSION['success_message'] = 'Registration successful!';
        $_SESSION['active_form'] = 'login';
        header("Location: login.php");
        exit();
    }

    $_SESSION['register_error'] = 'Registration failed!';
    $_SESSION['active_form'] = 'register';
    header("Location: login.php");
    exit();
}

if (isset($_POST['login'])) {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $checkUser = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $checkUser->bind_param("s", $username);
    $checkUser->execute();

    $result = $checkUser->get_result();

    if ($result->num_rows > 0) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];

            header("Location: ../admin/dashboard.php");
            exit();
        }
    }

    $_SESSION['login_error'] = 'Incorrect username or password';
    $_SESSION['active_form'] = 'login';

    header("Location: login.php");
    exit();
}