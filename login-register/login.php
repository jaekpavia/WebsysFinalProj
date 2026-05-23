<?php
session_start();

$errors = [
    'login' => $_SESSION['login_error'] ?? '',
    'register' => $_SESSION['register_error'] ?? ''
];

$activeForm = $_SESSION['active_form'] ?? 'login';

unset($_SESSION['login_error']);
unset($_SESSION['register_error']);
unset($_SESSION['active_form']);

function showError($error)
{
    if (!empty($error)) {
        return "<p class='error-message'>" . htmlspecialchars($error) . "</p>";
    }

    return '';
}

function isActiveForm($formName, $activeForm)
{
    return $formName === $activeForm ? 'active' : '';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEC-LEO Admin Login</title>
    <link rel="stylesheet" href="login.css">
</head>

<body>

    <div class="login-container">

        <div class="form-box <?= isActiveForm('login', $activeForm); ?>" id="login-form">
            <form action="login_register.php" method="POST">
                <h1>SEC-LEO Document Tracking System</h1>
                <h2>Admin Log In</h2>
                <p class="form-subtitle">Sign in to continue to your dashboard</p>

                <?= showError($errors['login']); ?>

                <input type="email" id="login-email" name="email" placeholder="Admin Email" required>
                <input type="password" id="login-password" name="password" placeholder="Password" required>

                <button name="login" type="submit">Log in</button>

                <p>
                    Don't have an admin account?
                    <a href="#" onclick="showForm('register-form'); return false;">Register</a>
                </p>
            </form>
        </div>

        <div class="form-box <?= isActiveForm('register', $activeForm); ?>" id="register-form">
            <form action="login_register.php" method="POST">
                <h1>SEC-LEO Document Tracking System</h1>
                <h2>Admin Register</h2>
                <p class="form-subtitle">Create an admin account to access the system</p>

                <?= showError($errors['register']); ?>

                <input type="text" id="register-name" name="name" placeholder="Admin Name" required>
                <input type="email" id="register-email" name="email" placeholder="Admin Email" required>
                <input type="password" id="register-password" name="password" placeholder="Password" required>

                <button name="register" type="submit">Register</button>

                <p>
                    Already have an admin account?
                    <a href="#" onclick="showForm('login-form'); return false;">Log in</a>
                </p>
            </form>
        </div>

    </div>

    <script src="login.js"></script>
</body>

</html>