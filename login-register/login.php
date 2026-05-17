<?php

session_start();

$errors = [
    'login' => $_SESSION['login_error'] ?? '',
    'register' => $_SESSION['register_error'] ?? ''
];

$activeForm = $_SESSION['active_form'] ?? 'login';

session_unset();

function showError($error) {
    return !empty($error) ? "<p class='error-message'>$error</p>" : '';
}

function isActiveForm($formName, $activeForm) {
    return $formName === $activeForm ? 'active' : '';
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Management</title>
    <link rel="stylesheet" href="../login-register/login.css">
</head>
<body>

    <div class="login-container">
        <div class="form-box <?= isActiveForm('login', $activeForm); ?>" id="login-form">
        <form action="login_register.php" method="POST">
            <h1>Room Grid</h1>
            <h2>Log In</h2>
            <?= showError($errors['login']); ?>
            <input type="email" id="email" name="email" placeholder="Email" required><br>
            <input type="password" id="password" name="password" placeholder="Password" required><br>
            <button name="login" type="submit">Log in</button>
           <p> Don't have an account? <a href="#" onclick="showForm('register-form')">Register</a></p>
        </form>
        </div>
      
        <div class="form-box <?= isActiveForm('register', $activeForm); ?>" id="register-form">
        <form action="login_register.php" method="POST">
            <h1>Room Grid</h1>
            <h2>Register</h2>
            <?= showError($errors['register']); ?>
            <input type="text" id="username" name="username" placeholder="Username" required><br>
            <input type="email" id="email" name="email" placeholder="Email" required><br>
            <input type="password" id="password" name="password" placeholder="Password" required><br>
            <select name="role" id="role">
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
            <button name="register" type="submit">Register</button>
           <p> Already have an account? <a href="#" onclick="showForm('login-form')">Log in</a></p>
        </form>
        </div>
    </div>
    <script src="../login-register/login.js"></script>
</body>
</html>