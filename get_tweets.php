<?php
// get_tweets.php - Returns HTML for feed
session_start();
require_once 'db_connect.php';
$pdo = getDB();
$user_id = $_SESSION['user_id'];

// Get tweets from followed users and own
$stmt = $pdo->prepare("
    SELECT t.*, u.username, u.profile_pic,
    (SELECT COUNT(*) FROM likes l WHERE l.tweet_id = t.id) AS like_count,
    (SELECT COUNT(*) FROM comments c WHERE c.tweet_id = t.id) AS comment_count,
    (SELECT COUNT(*) FROM likes l WHERE l.tweet_id = t.id AND l.user_id = ?) AS user_liked
    FROM tweets t
    JOIN users u ON t.user_id = u.id
    WHERE t.user_id = ? OR t.user_id IN (SELECT followed_id FROM follows WHERE follower_id = ?)
    ORDER BY t.created_at DESC
");
$stmt->execute([$user_id, $user_id, $user_id]);
$tweets = $stmt->fetchAll();

foreach ($tweets as $tweet) {
    $liked = $tweet['user_liked'] > 0 ? 'liked' : '';
    echo "<div class='tweet'>
        <div class='tweet-header'>
            <img src='{$tweet['profile_pic']}' alt='profile'>
            <span>{$tweet['username']}</span>
            <span class='tweet-time'> · {$tweet['created_at']}</span>
        </div>
        <div class='tweet-content'>{$tweet['content']}</div>
        <div class='tweet-actions'>
            <button onclick='commentTweet({$tweet['id']})'>Comment ({$tweet['comment_count']})</button>
            <button class='$liked' onclick='likeTweet({$tweet['id']})'>Like ({$tweet['like_count']})</button>";
    if ($tweet['user_id'] == $user_id) {
        echo "<button onclick='editTweet({$tweet['id']}, \"".addslashes($tweet['content'])."\")'>Edit</button>
              <button onclick='deleteTweet({$tweet['id']})'>Delete</button>";
    }
    echo "</div>";
    // Comments
    $cstmt = $pdo->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.tweet_id = ? ORDER BY c.created_at ASC");
    $cstmt->execute([$tweet['id']]);
    $comments = $cstmt->fetchAll();
    if (!empty($comments)) {
        echo "<div class='comments'>";
        foreach ($comments as $comment) {
            echo "<div class='comment'><strong>{$comment['username']}</strong>: {$comment['content']}</div>";
        }
        echo "</div>";
    }
    echo "</div>";
}
?>
