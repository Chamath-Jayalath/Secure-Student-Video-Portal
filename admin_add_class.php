<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_name = trim($_POST['name']);
    $num_keys = intval($_POST['num_keys']); // Get key count from admin input

    if ($num_keys < 1 || $num_keys > 1000) {
        $error = "Please enter a number between 1 and 1000.";
    } else {
        $stmt = $conn->prepare("INSERT INTO class (name) VALUES (?)");
        $stmt->bind_param("s", $class_name);

        if ($stmt->execute()) {
            $class_id = $stmt->insert_id;
            $failed = 0;

            for ($i = 0; $i < $num_keys; $i++) {
                $secret_key = bin2hex(random_bytes(8)); // 16-char key

                $insertKey = $conn->prepare("INSERT INTO class_keys (class_id, secret_key) VALUES (?, ?)");
                $insertKey->bind_param("is", $class_id, $secret_key);

                if (!$insertKey->execute()) {
                    $failed++;
                }
            }

            if ($failed > 0) {
                $error = "Class added, but $failed key(s) failed to generate.";
            } else {
                $success = "✅ Class '$class_name' added with $num_keys secret keys.";
            }
        } else {
            $error = "❌ Failed to add class.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Add Class</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f0f0f0; padding: 40px; }
    .form-box { max-width: 500px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px #ccc; }
    input[type="text"], input[type="number"] { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; }
    button { padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; }
    button:hover { background: #218838; }
    .message { margin-top: 20px; padding: 10px; border-radius: 5px; }
    .success { background: #d4edda; color: #155724; }
    .error { background: #f8d7da; color: #721c24; }
  </style>
</head>
<body>
<div class="form-box">
  <h2>Add New Class & Generate Keys</h2>
  <form method="POST">
    <label>Class Name:</label>
    <input type="text" name="name" required>

    <label>How many secret keys to generate?</label>
    <input type="number" name="num_keys" min="1" max="1000" required>

    <button type="submit">Create Class & Generate Keys</button>
  </form>

  <?php if ($success): ?>
    <div class="message success"><?= htmlspecialchars($success) ?></div>
  <?php elseif ($error): ?>
    <div class="message error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
</div>
</body>
</html>
