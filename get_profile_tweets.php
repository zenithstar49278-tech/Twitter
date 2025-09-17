<?php
// get_profile_tweets.php
session_start();
require_once 'db_connect.php';
$pdo = getDB();
$user_id = $_SESSION['user_id'];
$profile_id = $_GET['id'];

// Similar to get_tweets but only for this user
$stmt = $pdo->prepare("
    SELECT t.*, u.username, u.profile_pic,
    (SELECT COUNT(*) FROM likes l WHERE l.tweet_id = t.id) AS like_count,
    (SELECT COUNT(*) FROM comments c WHERE c.tweet_id = t.id) AS comment_count,
    (SELECT COUNT(*) FROM likes l WHERE l.tweet_id = t.id AND l.user_id = ?) AS user_liked
    FROM tweets t
    JOIN users u ON t.user_id = u.id
    WHERE t.user_id = ?
    ORDER BY t.created_at DESC
");
$stmt->execute([$user_id, $profile_id]);
$tweets = $stmt->fetchAll();

foreach ($tweets as $tweet) {
    $liked = $tweet['user_liked'] > 0 ? 'liked' : '';
    $own_tweet = $tweet['user_id'] == $user_id;
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
    if ($own_tweet) {
        echo "<button onclick='editTweet({$tweet['id']}, \"".addslashes($tweet['content'])."\")'>Edit</button>
              <button onclick='deleteTweet({$tweet['id']})'>Delete</button>";
    }
    echo "</div>";
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
