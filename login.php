<?php
require 'db.php';
require 'auth.php';
require_guest();

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Server-side validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id, name, password FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        // Compare the typed password with the saved hash
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true); // new session id after login (security)
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];

            header('Location: index.php');
            exit;
        }

        $errors[] = 'Incorrect email or password.';
    }
}

$title = 'Log in';
require 'header.php';
?>

<div class="form-page">
    <h1>Log in</h1>

    <?php if (isset($_GET['registered'])): ?>
        <p class="notice">Account created. You can log in now.</p>
    <?php endif; ?>

    <?php if ($errors): ?>
        <ul class="errors">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" class="card">
        <label>Email
            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
        </label>

        <label>Password
            <input type="password" name="password" required>
        </label>

        <button type="submit" class="button">Log in</button>
        <p class="hint">No account yet? <a href="register.php">Register</a></p>
    </form>
</div>

<?php require 'footer.php'; ?>