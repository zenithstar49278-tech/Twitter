<?php
// register.php
session_start();
require_once 'db_connect.php';

if (isset($_SESSION['user_id'])) {
    echo "<script>location.href = 'index.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $pdo = getDB();
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    try {
        $stmt->execute([$username, $email, $password]);
        echo "<script>location.href = 'login.php';</script>";
    } catch (PDOException $e) {
        $error = "Username or email already exists.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        /* Beautiful CSS inspired by Twitter */
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f5f8fa; color: #14171a; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 50px auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        h1 { color: #1da1f2; text-align: center; }
        form { display: flex; flex-direction: column; }
        input { margin: 10px 0; padding: 10px; border: 1px solid #e6ecf0; border-radius: 4px; font-size: 16px; }
        button { background: #1da1f2; color: white; border: none; padding: 10px; border-radius: 20px; font-weight: bold; cursor: pointer; }
        button:hover { background: #0c84d3; }
        .error { color: red; text-align: center; }
        @media (max-width: 600px) { .container { margin: 20px; padding: 15px; } }
    </style>
</head>
<body>
    <div class="container">
        <h1>Register for Twitter Clone</h1>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>
