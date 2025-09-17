<?php
// index.php - Homepage
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>location.href = 'login.php';</script>";
    exit;
}

require_once 'db_connect.php';
$pdo = getDB();
$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Twitter Clone</title>
    <style>
        /* Beautiful CSS, Twitter-like */
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f5f8fa; color: #14171a; margin: 0; padding: 0; }
        header { background: white; border-bottom: 1px solid #e6ecf0; padding: 10px; text-align: center; }
        .nav { display: flex; justify-content: space-around; max-width: 600px; margin: auto; }
        .nav a { color: #1da1f2; text-decoration: none; font-weight: bold; }
        .tweet-box { background: white; border-bottom: 1px solid #e6ecf0; padding: 10px; max-width: 600px; margin: 0 auto; }
        .tweet-box textarea { width: 100%; border: none; resize: none; font-size: 18px; }
        .tweet-box button { background: #1da1f2; color: white; border: none; padding: 8px 16px; border-radius: 20px; font-weight: bold; cursor: pointer; float: right; }
        .tweet-box button:hover { background: #0c84d3; }
        #feed { max-width: 600px; margin: 0 auto; }
        .tweet { background: white; border-bottom: 1px solid #e6ecf0; padding: 10px; display: flex; flex-direction: column; }
        .tweet-header { display: flex; align-items: center; }
        .tweet-header img { width: 48px; height: 48px; border-radius: 50%; margin-right: 10px; }
        .tweet-header span { font-weight: bold; }
        .tweet-time { color: #657786; font-size: 14px; }
        .tweet-content { margin: 10px 0; }
        .tweet-actions { display: flex; justify-content: space-between; color: #657786; }
        .tweet-actions button { background: none; border: none; cursor: pointer; color: #657786; }
        .tweet-actions button:hover { color: #1da1f2; }
        .liked { color: #e0245e !important; }
        .comments { margin-top: 10px; border-top: 1px solid #e6ecf0; padding-top: 10px; }
        .comment { margin-bottom: 10px; }
        .edit-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; }
        .edit-modal-content { background: white; padding: 20px; border-radius: 8px; width: 80%; max-width: 500px; }
        .edit-modal textarea { width: 100%; }
        @media (max-width: 600px) { .tweet-box, #feed { padding: 5px; } .nav { flex-direction: column; } }
    </style>
</head>
<body>
    <header>
        <div class="nav">
            <a href="index.php">Home</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </header>
    <div class="tweet-box">
        <textarea id="tweet-content" placeholder="What's happening?"></textarea>
        <button onclick="postTweet()">Tweet</button>
    </div>
    <div id="feed"></div>
    <div id="edit-modal" class="edit-modal">
        <div class="edit-modal-content">
            <h2>Edit Tweet</h2>
            <textarea id="edit-content"></textarea>
            <button onclick="saveEdit()">Save</button>
            <button onclick="closeModal()">Cancel</button>
        </div>
    </div>
    <script>
        let editingTweetId = null;
        function loadFeed() {
            fetch('get_tweets.php')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('feed').innerHTML = html;
                });
        }
        function postTweet() {
            const content = document.getElementById('tweet-content').value;
            if (content.trim() === '') return;
            fetch('post_tweet.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `content=${encodeURIComponent(content)}`
            }).then(() => {
                document.getElementById('tweet-content').value = '';
                loadFeed();
            });
        }
        function likeTweet(tweetId) {
            fetch('like_tweet.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `tweet_id=${tweetId}`
            }).then(() => loadFeed());
        }
        function commentTweet(tweetId) {
            const comment = prompt('Enter your comment:');
            if (comment) {
                fetch('comment_tweet.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `tweet_id=${tweetId}&content=${encodeURIComponent(comment)}`
                }).then(() => loadFeed());
            }
        }
        function editTweet(tweetId, content) {
            editingTweetId = tweetId;
            document.getElementById('edit-content').value = content;
            document.getElementById('edit-modal').style.display = 'flex';
        }
        function saveEdit() {
            const content = document.getElementById('edit-content').value;
            fetch('edit_tweet.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `tweet_id=${editingTweetId}&content=${encodeURIComponent(content)}`
            }).then(() => {
                closeModal();
                loadFeed();
            });
        }
        function closeModal() {
            document.getElementById('edit-modal').style.display = 'none';
        }
        function deleteTweet(tweetId) {
            if (confirm('Are you sure?')) {
                fetch('delete_tweet.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `tweet_id=${tweetId}`
                }).then(() => loadFeed());
            }
        }
        setInterval(loadFeed, 5000); // Poll every 5 seconds for real-time
        loadFeed();
    </script>
</body>
</html>
