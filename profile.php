<?php
// profile.php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>location.href = 'login.php';</script>";
    exit;
}

require_once 'db_connect.php';
$pdo = getDB();
$user_id = $_SESSION['user_id'];
$profile_user_id = isset($_GET['id']) ? $_GET['id'] : $user_id;
$is_own_profile = $profile_user_id == $user_id;

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$profile_user_id]);
$user = $stmt->fetch();

$followers = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE followed_id = ?");
$followers->execute([$profile_user_id]);
$followers_count = $followers->fetchColumn();

$following = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE follower_id = ?");
$following->execute([$profile_user_id]);
$following_count = $following->fetchColumn();

$followed = $pdo->prepare("SELECT * FROM follows WHERE follower_id = ? AND followed_id = ?");
$followed->execute([$user_id, $profile_user_id]);
$is_followed = $followed->fetch() ? true : false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?php echo $user['username']; ?></title>
    <style>
        /* Beautiful CSS */
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f5f8fa; color: #14171a; margin: 0; padding: 0; }
        header { background: white; border-bottom: 1px solid #e6ecf0; padding: 10px; text-align: center; }
        .nav { display: flex; justify-content: space-around; max-width: 600px; margin: auto; }
        .nav a { color: #1da1f2; text-decoration: none; font-weight: bold; }
        .profile-header { background: white; padding: 20px; max-width: 600px; margin: 0 auto; border-bottom: 1px solid #e6ecf0; }
        .profile-header img { width: 100px; height: 100px; border-radius: 50%; }
        .profile-info { margin-top: 10px; }
        .profile-stats { display: flex; justify-content: space-around; margin: 10px 0; }
        #profile-feed { max-width: 600px; margin: 0 auto; }
        .tweet { background: white; border-bottom: 1px solid #e6ecf0; padding: 10px; display: flex; flex-direction: column; }
        /* Reuse tweet styles from index */
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
        button.follow-btn { background: #1da1f2; color: white; border: none; padding: 8px 16px; border-radius: 20px; font-weight: bold; cursor: pointer; }
        button.follow-btn:hover { background: #0c84d3; }
        button.follow-btn.unfollow { background: #e0245e; }
        @media (max-width: 600px) { .profile-header { padding: 10px; } img { width: 80px; height: 80px; } }
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
    <div class="profile-header">
        <img src="<?php echo $user['profile_pic']; ?>" alt="profile">
        <div class="profile-info">
            <h2><?php echo $user['username']; ?></h2>
            <p><?php echo $user['bio'] ?? 'No bio yet.'; ?></p>
        </div>
        <div class="profile-stats">
            <span><strong><?php echo $following_count; ?></strong> Following</span>
            <span><strong><?php echo $followers_count; ?></strong> Followers</span>
        </div>
        <?php if ($is_own_profile): ?>
            <button onclick="editProfile()">Edit Profile</button>
        <?php else: ?>
            <button class="follow-btn <?php echo $is_followed ? 'unfollow' : ''; ?>" onclick="toggleFollow(<?php echo $profile_user_id; ?>)">
                <?php echo $is_followed ? 'Unfollow' : 'Follow'; ?>
            </button>
        <?php endif; ?>
    </div>
    <div id="profile-feed"></div>
    <div id="edit-modal" class="edit-modal">
        <div class="edit-modal-content">
            <h2>Edit Profile</h2>
            <input type="text" id="edit-bio" value="<?php echo $user['bio'] ?? ''; ?>" placeholder="Bio">
            <!-- For simplicity, no pic upload -->
            <button onclick="saveProfile()">Save</button>
            <button onclick="closeModal()">Cancel</button>
        </div>
    </div>
    <script>
        let editingTweetId = null;
        function loadProfileFeed() {
            fetch('get_profile_tweets.php?id=<?php echo $profile_user_id; ?>')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('profile-feed').innerHTML = html;
                });
        }
        function toggleFollow(userId) {
            fetch('follow_user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `user_id=${userId}`
            }).then(() => location.reload()); // Reload to update button
        }
        function editProfile() {
            document.getElementById('edit-modal').style.display = 'flex';
        }
        function saveProfile() {
            const bio = document.getElementById('edit-bio').value;
            fetch('edit_profile.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `bio=${encodeURIComponent(bio)}`
            }).then(() => {
                closeModal();
                location.reload();
            });
        }
        function closeModal() {
            document.getElementById('edit-modal').style.display = 'none';
        }
        function likeTweet(tweetId) {
            fetch('like_tweet.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `tweet_id=${tweetId}`
            }).then(() => loadProfileFeed());
        }
        function commentTweet(tweetId) {
            const comment = prompt('Enter your comment:');
            if (comment) {
                fetch('comment_tweet.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `tweet_id=${tweetId}&content=${encodeURIComponent(comment)}`
                }).then(() => loadProfileFeed());
            }
        }
        function editTweet(tweetId, content) {
            editingTweetId = tweetId;
            document.getElementById('edit-content').value = content; // Note: edit-modal has textarea id='edit-content' but here modal is for profile, adjust if needed
            document.getElementById('edit-modal').style.display = 'flex';
        }
        // For profile edit modal, it's input, but for tweet it's textarea. For simplicity, I used same modal, but adjust id if conflict. Here I used edit-bio for profile.
        // Add textarea id='edit-content' to modal if needed, but since separate functions, ok.
        // Wait, modal has only for profile now, add textarea for tweet edit.
        // To fix, add <textarea id="edit-content" style="display:none;"></textarea> to modal, and show/hide based on context, but for simplicity, separate modals or adjust.
        // For now, assume user adds <textarea id="edit-content"></textarea> to modal for tweet edit, but since profile modal has input, I changed modal for profile.
        // Actually, to make it work, I'll add the textarea to the modal and use it for tweets, for profile use the input.
        function saveEdit() {
            const content = document.getElementById('edit-content').value;
            fetch('edit_tweet.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `tweet_id=${editingTweetId}&content=${encodeURIComponent(content)}`
            }).then(() => {
                closeModal();
                loadProfileFeed();
            });
        }
        function deleteTweet(tweetId) {
            if (confirm('Are you sure?')) {
                fetch('delete_tweet.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `tweet_id=${tweetId}`
                }).then(() => loadProfileFeed());
            }
        }
        loadProfileFeed();
    </script>
</body>
</html>
<?php
// Add <textarea id="edit-content" style="display:none;"></textarea> to modal if needed, but to make it, modify the modal in HTML:
?>
<!-- Modify modal in profile.php HTML -->
<div id="edit-modal" class="edit-modal">
    <div class="edit-modal-content">
        <h2 id="modal-title">Edit</h2>
        <textarea id="edit-content" style="display:none;"></textarea>
        <input type="text" id="edit-bio" style="display:none;" placeholder="Bio">
        <button onclick="saveEdit()">Save</button>
        <button onclick="closeModal()">Cancel</button>
    </div>
</div>
<script>
// Adjust functions
function editProfile() {
    document.getElementById('modal-title').innerText = 'Edit Profile';
    document.getElementById('edit-bio').style.display = 'block';
    document.getElementById('edit-content').style.display = 'none';
    document.getElementById('edit-bio').value = '<?php echo addslashes($user['bio'] ?? ''); ?>';
    document.getElementById('edit-modal').style.display = 'flex';
    document.querySelector('[onclick="saveEdit()"]').onclick = saveProfile; // Change save button to saveProfile
}
function editTweet(tweetId, content) {
    editingTweetId = tweetId;
    document.getElementById('modal-title').innerText = 'Edit Tweet';
    document.getElementById('edit-content').style.display = 'block';
    document.getElementById('edit-bio').style.display = 'none';
    document.getElementById('edit-content').value = content;
    document.getElementById('edit-modal').style.display = 'flex';
    document.querySelector('[onclick="saveEdit()"]').onclick = saveEdit; // Change to saveEdit
}
</script>
