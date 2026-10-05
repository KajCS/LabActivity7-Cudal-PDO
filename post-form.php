<?php
require 'db.php';
require 'auth.php';
require_login();

// ?id=5 means EDIT post 5, no id means ADD a new post
$id = (int) ($_GET['id'] ?? 0);
$errors = [];
$post = [
    'title' => '',
    'body' => '',
];

// Editing: load the post, but ONLY if it belongs to the logged-in user (requirement 9)
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = :id AND user_id = :user_id');
    $stmt->execute(['id' => $id, 'user_id' => $_SESSION['user_id']]);
    $post = $stmt->fetch();

    if (!$post) {
        die('Post not found, or you are not allowed to edit it.');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post['title'] = trim($_POST['title'] ?? '');
    $post['body'] = trim($_POST['body'] ?? '');

    // Server-side validation
    if ($post['title'] === '') {
        $errors[] = 'Title is required.';
    } elseif (mb_strlen($post['title']) > 255) {
        $errors[] = 'Title must be 255 characters or less.';
    }

    if ($post['body'] === '') {
        $errors[] = 'Post content is required.';
    } elseif (mb_strlen($post['body']) > 10000) {
        $errors[] = 'Post content must be 10,000 characters or less.';
    }

    if (!$errors) {
        if ($id > 0) {
            // UPDATE: also set updated_at, which shows the "Edited" tag
            $stmt = $pdo->prepare(
                'UPDATE posts SET title = :title, body = :body, updated_at = NOW()
                 WHERE id = :id AND user_id = :user_id'
            );
            $stmt->execute([
                'title' => $post['title'],
                'body' => $post['body'],
                'id' => $id,
                'user_id' => $_SESSION['user_id'],
            ]);

            header('Location: post.php?id=' . $id);
            exit;
        }

        // CREATE (requirement 7)
        $stmt = $pdo->prepare(
            'INSERT INTO posts (user_id, title, body) VALUES (:user_id, :title, :body)'
        );
        $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'title' => $post['title'],
            'body' => $post['body'],
        ]);

        header('Location: index.php');
        exit;
    }
}

$title = $id > 0 ? 'Edit post' : 'New post';
?>

<div class="form-page wide">
    <h1><?= $title ?></h1>

    <?php if ($errors): ?>
        <ul class="errors">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" class="card">
        <label>Title
            <input type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>" maxlength="255" required>
        </label>

        <label>Content
            <textarea name="body" rows="10" maxlength="10000" required><?= htmlspecialchars($post['body']) ?></textarea>
        </label>

        <button type="submit" class="button"><?= $id > 0 ? 'Save changes' : 'Publish' ?></button>
        <a class="button outline" href="<?= $id > 0 ? 'post.php?id=' . $id : 'index.php' ?>">Cancel</a>
    </form>
</div>
