<?php
require 'db.php';
require 'auth.php';
require_login();

$id = (int) ($_GET['id'] ?? 0);
$errors = [];

// Load the comment, but ONLY if it belongs to the logged-in user (requirement 10)
$stmt = $pdo->prepare('SELECT * FROM comments WHERE id = :id AND user_id = :user_id');
$stmt->execute(['id' => $id, 'user_id' => $_SESSION['user_id']]);
$comment = $stmt->fetch();

if (!$comment) {
    die('Comment not found, or you are not allowed to edit it.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment['body'] = trim($_POST['body'] ?? '');

    // Server-side validation
    if ($comment['body'] === '') {
        $errors[] = 'Comment cannot be empty.';
    } elseif (mb_strlen($comment['body']) > 2000) {
        $errors[] = 'Comment must be 2,000 characters or less.';
    }

    if (!$errors) {
        // updated_at = NOW() shows the "Edited" tag
        $stmt = $pdo->prepare(
            'UPDATE comments SET body = :body, updated_at = NOW()
             WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute([
            'body' => $comment['body'],
            'id' => $id,
            'user_id' => $_SESSION['user_id'],
        ]);

        header('Location: post.php?id=' . $comment['post_id'] . '#comments');
        exit;
    }
}

$title = 'Edit comment';
require 'header.php';
?>

<div class="form-page wide">
    <h1>Edit comment</h1>

    <?php if ($errors): ?>
        <ul class="errors">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" class="card">
        <label>Comment
            <textarea name="body" rows="4" maxlength="2000" required><?= htmlspecialchars($comment['body']) ?></textarea>
        </label>

        <button type="submit" class="button">Save changes</button>
        <a class="button outline" href="post.php?id=<?= $comment['post_id'] ?>#comments">Cancel</a>
    </form>
</div>

<?php require 'footer.php'; ?>