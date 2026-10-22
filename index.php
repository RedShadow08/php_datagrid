<?php
$host = "mysql";
$user = getenv('MYSQL_USER') ?: "demo_user";
$pass = getenv('MYSQL_PASSWORD') ?: "demopass123";
$db   = getenv('MYSQL_DATABASE') ?: "demo_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("MySQL connection failed: " . $conn->connect_error);
}

// Create the table on first run
$conn->query("CREATE TABLE IF NOT EXISTS guestbook (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name']) && !empty($_POST['message'])) {
    $stmt = $conn->prepare("INSERT INTO guestbook (name, message) VALUES (?, ?)");
    $stmt->bind_param("ss", $_POST['name'], $_POST['message']);
    $stmt->execute();
    header("Location: /");   // avoid re-submit on refresh
    exit;
}

$entries = $conn->query("SELECT * FROM guestbook ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Docker Demo Guestbook</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 40px auto; padding: 0 15px; }
        .status { background: #e8f5e9; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 14px; }
        input, textarea { width: 100%; padding: 8px; margin: 5px 0 10px; box-sizing: border-box; }
        button { padding: 8px 20px; background: #2196f3; color: #fff; border: 0; cursor: pointer; }
        .entry { border-bottom: 1px solid #ddd; padding: 10px 0; }
        .entry small { color: #888; }
    </style>
</head>
<body>
    <h1>Guestbook</h1>

    <div class="status">
        PHP <?= phpversion() ?> | MySQL <?= $conn->server_info ?> | Server: <?= gethostname() ?>
    </div>

    <form method="POST">
        <input type="text" name="name" placeholder="Your name" required>
        <textarea name="message" placeholder="Your message" rows="3" required></textarea>
        <button type="submit">Sign guestbook</button>
    </form>

    <h2>Entries</h2>
    <?php while ($row = $entries->fetch_assoc()): ?>
        <div class="entry">
            <strong><?= htmlspecialchars($row['name']) ?></strong>
            <small><?= $row['created_at'] ?></small>
            <p><?= nl2br(htmlspecialchars($row['message'])) ?></p>
        </div>
    <?php endwhile; ?>
</body>
</html>
