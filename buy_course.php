<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$success = $error = "";
$show_form = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = intval($_POST['class_id']);
    $entered_key = trim($_POST['secret_key']);

    // Validate entered key for the class and unused
    $stmt = $conn->prepare("SELECT ck.id, c.name 
                            FROM class_keys ck 
                            JOIN class c ON c.id = ck.class_id 
                            WHERE ck.class_id = ? AND ck.secret_key = ? AND ck.used_by IS NULL");
    $stmt->bind_param("is", $class_id, $entered_key);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        // Check if already unlocked
        $check = $conn->prepare("SELECT * FROM user_class WHERE user_id=? AND class_id=?");
        $check->bind_param("ii", $student_id, $class_id);
        $check->execute();

        if ($check->get_result()->num_rows == 0) {
            // Add to user_class
            $add = $conn->prepare("INSERT INTO user_class (user_id, class_id) VALUES (?, ?)");
            $add->bind_param("ii", $student_id, $class_id);
            if ($add->execute()) {
                // Mark key as used
                $update = $conn->prepare("UPDATE class_keys SET used_by = ?, used_at = NOW() WHERE id = ?");
                $update->bind_param("ii", $student_id, $row['id']);
                $update->execute();

                $success = "✅ You unlocked '{$row['name']}' successfully! Redirecting to dashboard...";
                // Redirect after 2 seconds
                header("refresh:2;url=dashboard_user.php");
            } else {
                $error = "❌ Unlock failed. Try again.";
                $show_form = $class_id;
            }
        } else {
            $error = "⚠️ You already unlocked this course.";
        }
    } else {
        $error = "❌ Invalid or already-used key!";
        $show_form = $class_id;
    }
}

// Fetch all classes
$courses = $conn->query("SELECT id, name FROM class ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Buy Course 🔐</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f0f2f5;
      padding: 40px 20px;
      max-width: 700px;
      margin: 0 auto;
    }
    h2 {
      text-align: center;
      margin-bottom: 30px;
      color: #333;
    }
    .btn-back {
      display: inline-block;
      margin-bottom: 30px;
      padding: 10px 20px;
      background: #3f51b5;
      color: white;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 600;
      transition: background-color 0.3s ease;
    }
    .btn-back:hover {
      background: #2c3e9e;
    }
    .card {
      background: white;
      padding: 20px 25px;
      margin-bottom: 20px;
      border-radius: 12px;
      box-shadow: 0 3px 8px rgba(0,0,0,0.1);
      transition: box-shadow 0.3s ease;
    }
    .card:hover {
      box-shadow: 0 6px 14px rgba(0,0,0,0.15);
    }
    .card strong {
      font-size: 18px;
      color: #222;
      display: block;
      margin-bottom: 12px;
      cursor: pointer;
    }
    form {
      display: none;
      margin-top: 12px;
    }
    form.active {
      display: block;
    }
    input[type="text"] {
      padding: 10px 14px;
      width: calc(100% - 100px);
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 15px;
      outline-color: #3f51b5;
      transition: border-color 0.3s ease;
    }
    input[type="text"]:focus {
      border-color: #3f51b5;
    }
    button {
      background-color: #3f51b5;
      color: white;
      border: none;
      padding: 10px 18px;
      border-radius: 6px;
      font-size: 15px;
      cursor: pointer;
      margin-left: 8px;
      transition: background-color 0.3s ease;
    }
    button:hover {
      background-color: #2c3e9e;
    }
    .success {
      color: #2e7d32;
      font-weight: 600;
      margin-bottom: 20px;
      text-align: center;
      font-size: 17px;
    }
    .error {
      color: #c62828;
      font-weight: 600;
      margin-bottom: 20px;
      text-align: center;
      font-size: 17px;
    }
  </style>
</head>
<body>

  <a href="dashboard_user.php" class="btn-back">← Back to Dashboard</a>

  <h2>Buy Course 🔐</h2>

  <?php if ($success): ?>
    <p class="success"><?= htmlspecialchars($success) ?></p>
  <?php endif; ?>

  <?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <?php while ($row = $courses->fetch_assoc()): ?>
    <div class="card">
      <strong><?= htmlspecialchars($row['name']) ?></strong>
      <form method="POST" class="<?= $show_form == $row['id'] ? 'active' : '' ?>">
        <input type="hidden" name="class_id" value="<?= $row['id'] ?>">
        <input type="text" name="secret_key" placeholder="Enter Secret Key" required autocomplete="off" />
        <button type="submit">Unlock</button>
      </form>
    </div>
  <?php endwhile; ?>

  <script>
    // Show form when course name clicked
    document.querySelectorAll('.card strong').forEach(el => {
      el.addEventListener('click', () => {
        const form = el.nextElementSibling;
        if (form) {
          // Hide all forms
          document.querySelectorAll('form').forEach(f => f.classList.remove('active'));
          // Show clicked form
          form.classList.add('active');
          form.querySelector('input[name="secret_key"]').focus();
        }
      });
    });
  </script>
</body>
</html>
