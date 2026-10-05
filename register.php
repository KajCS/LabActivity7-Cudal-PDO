<?php
require 'db.php';
require 'auth.php';
require_guest();

$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Server-side validation
    if ($name === '') {
        $errors[] = 'Name is required.';
    } elseif (mb_strlen($name) > 100) {
        $errors[] = 'Name must be 100 characters or less.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    // Email must not be taken (email is UNIQUE in the table)
    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            $errors[] = 'That email is already registered.';
        }
    }

    if (!$errors) {
        $stmt = $pdo->prepare(
            'INSERT INTO users (name, email, password) VALUES (:name, :email, :password)'
        );
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        header('Location: login.php?registered=1');
        exit;
    }
}

$title = 'Register';
require 'header.php';
?>

<div class="form-page">
    <h1>Create an account</h1>

    <?php if ($errors): ?>
        <ul class="errors">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" class="card">
        <label>Name
            <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" maxlength="100" required>
        </label>

        <label>Email
            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" maxlength="255" required>
        </label>

        <label>Password
            <input type="password" name="password" minlength="8" required>
        </label>

        <label>Confirm password
            <input type="password" name="confirm_password" minlength="8" required>
        </label>

        <button type="submit" class="button">Register</button>
        <p class="hint">Already have an account? <a href="login.php">Log in</a></p>
    </form>
</div>
