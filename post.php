<?php
require 'db.php';
require 'auth.php';
require_login();

$id = (int) ($_GET['id'] ?? 0);
$errors = [];
$commentBody = '';

// Load the post and its author
$stmt = $pdo->prepare(
    'SELECT posts.*, users.name AS author
     FROM posts
     INNER JOIN users ON users.id = posts.user_id
     WHERE posts.id = :id'
);
$stmt->execute(['id' => $id]);
$post = $stmt->fetch();

if (!$post) {
    die('Post not found.');
}

// Add a comment (requirement 8)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commentBody = trim($_POST['body'] ?? '');

    if ($commentBody === '') {
        $errors[] = 'Comment cannot be empty.';
    } elseif (mb_strlen($commentBody) > 2000) {
        $errors[] = 'Comment must be 2,000 characters or less.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare(
            'INSERT INTO comments (post_id, user_id, body) VALUES (:post_id, :user_id, :body)'
        );
        $stmt->execute([
            'post_id' => $id,
            'user_id' => $_SESSION['user_id'],
            'body' => $commentBody,
        ]);

        header('Location: post.php?id=' . $id . '#comments');
        exit;
    }
}

// Load the comments, oldest first
$stmt = $pdo->prepare(
    'SELECT comments.*, users.name AS author
     FROM comments
     INNER JOIN users ON users.id = comments.user_id
     WHERE comments.post_id = :post_id
     ORDER BY comments.created_at ASC, comments.id ASC'
);
$stmt->execute(['post_id' => $id]);
$comments = $stmt->fetchAll();

$title = $post['title'];
require 'header.php';
?>

<article class="card post">
    <h1><?= htmlspecialchars($post['title']) ?></h1>

    <p class="meta">
        By <?= htmlspecialchars($post['author']) ?>
        · <?= date('M j, Y g:i A', strtotime($post['created_at'])) ?>
        <?php if ($post['updated_at'] !== null): ?>
            <span class="tag">Edited</span>
        <?php endif; ?>
    </p>

    <p><?= nl2br(htmlspecialchars($post['body'])) ?></p>

    <?php if ($post['user_id'] == $_SESSION['user_id']): ?>
        <p class="actions"><a href="post-form.php?id=<?= $post['id'] ?>">Edit post</a></p>
    <?php endif; ?>
</article>

<h2 id="comments">Comments (<?= count($comments) ?>)</h2>

<?php foreach ($comments as $comment): ?>
    <div class="card comment">
        <p class="meta">
            <?= htmlspecialchars($comment['author']) ?>
            · <?= date('M j, Y g:i A', strtotime($comment['created_at'])) ?>
            <?php if ($comment['updated_at'] !== null): ?>
                <span class="tag">Edited</span>
            <?php endif; ?>
        </p>

        <p><?= nl2br(htmlspecialchars($comment['body'])) ?></p>

        <?php if ($comment['user_id'] == $_SESSION['user_id']): ?>
            <p class="actions"><a href="comment-edit.php?id=<?= $comment['id'] ?>">Edit</a></p>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<?php if ($errors): ?>
    <ul class="errors">
        <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" class="card">
    <label>Add a comment
        <textarea name="body" rows="3" maxlength="2000" required><?= htmlspecialchars($commentBody) ?></textarea>
    </label>
    <button type="submit" class="button">Comment</button>
</form>

