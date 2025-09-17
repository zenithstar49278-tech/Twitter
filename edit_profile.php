<?php
// edit_profile.php
session_start();
require_once 'db_connect.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bio'])) {
    $pdo = getDB();
    $stmt = $pdo->prepare("UPDATE users SET bio = ? WHERE id = ?");
    $stmt->execute([$_POST['bio'], $_SESSION['user_id']]);
}
?>
