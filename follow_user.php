<?php
// follow_user.php
session_start();
require_once 'db_connect.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'])) {
    $pdo = getDB();
    $followed_id = $_POST['user_id'];
    $follower_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT * FROM follows WHERE follower_id = ? AND followed_id = ?");
    $stmt->execute([$follower_id, $followed_id]);
    if ($stmt->fetch()) {
        // Unfollow
        $stmt = $pdo->prepare("DELETE FROM follows WHERE follower_id = ? AND followed_id = ?");
        $stmt->execute([$follower_id, $followed_id]);
    } else {
        // Follow
        $stmt = $pdo->prepare("INSERT INTO follows (follower_id, followed_id) VALUES (?, ?)");
        $stmt->execute([$follower_id, $followed_id]);
    }
}
?>
