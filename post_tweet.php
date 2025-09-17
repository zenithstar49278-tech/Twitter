<?php
// post_tweet.php
session_start();
require_once 'db_connect.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['content'])) {
    $pdo = getDB();
    $stmt = $pdo->prepare("INSERT INTO tweets (user_id, content) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], $_POST['content']]);
}
?>
