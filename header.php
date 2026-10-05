<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> · Blog</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <a class="brand" href="index.php">Blog</a>
        <nav>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="index.php">Home</a>
                <a href="post-form.php">New post</a>
                <span>Hi, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                <form method="post" action="logout.php" class="inline">
                    <button type="submit" class="link">Log out</button>
                </form>
            <?php else: ?>
                <a href="login.php">Log in</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>