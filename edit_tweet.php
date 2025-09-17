<?php
// edit_tweet.php
session_start();
require_once 'db_connect.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tweet_id']) && isset($_POST['content'])) {
    $pdo = getDB();
    $stmt = $pdo->prepare("UPDATE tweets SET content = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$_POST['content'], $_POST['tweet_id'], $_SESSION['user_id']]);
}
?>
