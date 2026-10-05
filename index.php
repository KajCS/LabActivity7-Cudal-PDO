<?php
require 'db.php';
require 'auth.php';
require_login();

// News feed: every post from every user, newest first (requirement 6)
$stmt = $pdo->query(
    'SELECT posts.id, posts.user_id, posts.title, posts.body,
            posts.created_at, posts.updated_at, users.name AS author
     FROM posts
     INNER JOIN users ON users.id = posts.user_id
     ORDER BY posts.created_at DESC, posts.id DESC'
);
$posts = $stmt->fetchAll();

$title = 'Home';
require 'header.php';
?>

<div class="page-head">
    <div>
        <h1>News feed</h1>
        <p>Latest posts from everyone.</p>
    </div>
    <a class="button" href="post-form.php">New post</a>
</div>

<?php if (count($posts) === 0): ?>
    <p>No posts yet. Be the first to write one.</p>
<?php endif; ?>

<?php foreach ($posts as $post): ?>
    <article class="card post">
        <h2><a href="post.php?id=<?= $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a></h2>

        <p class="meta">
            By <?= htmlspecialchars($post['author']) ?>
            · <?= date('M j, Y g:i A', strtotime($post['created_at'])) ?>
            <?php if ($post['updated_at'] !== null): ?>
                <span class="tag">Edited</span>
            <?php endif; ?>
        </p>

        <p><?= nl2br(htmlspecialchars($post['body'])) ?></p>

        <p class="actions">
            <a href="post.php?id=<?= $post['id'] ?>">Comments</a>
            <?php if ($post['user_id'] == $_SESSION['user_id']): ?>
                <a href="post-form.php?id=<?= $post['id'] ?>">Edit</a>
            <?php endif; ?>
        </p>
    </article>
<?php endforeach; ?>

<?php require 'footer.php'; ?>