<?php
session_start();
include "config.php";
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
  header("Location: index.php");
  exit();
}

// Add new class
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_class') {
  $name = $_POST['class_name'];
  $monthYear = $_POST['month_year'];
  $stmt = $conn->prepare("INSERT INTO class (name, month_year) VALUES (?, ?)");
  $stmt->bind_param("ss", $name, $monthYear);
  $stmt->execute();
  $success = "Class added!";
}

// Assign videos to class
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'assign_videos') {
  $classId = $_POST['class_id'];
  $videoIds = $_POST['video_ids'] ?? [];

  $stmt = $conn->prepare("INSERT IGNORE INTO class_video (class_id, video_id) VALUES (?, ?)");
  foreach ($videoIds as $vid) {
    $stmt->bind_param("ii", $classId, $vid);
    $stmt->execute();
  }
  $success = "Videos assigned to class!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Class Management</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Roboto', sans-serif; background-color: #f5f7fa; padding: 30px; }
    h2, h3 { color: #2c3e50; }
    form { margin-bottom: 30px; }
    input[type="text"], select { padding: 10px; width: 300px; margin-bottom: 10px; border-radius: 6px; border: 1px solid #ccc; }
    button { background-color: #3498db; color: white; padding: 10px 16px; border: none; border-radius: 6px; cursor: pointer; }
    button:hover { background-color: #2980b9; }
    .success { color: green; font-weight: bold; }
  </style>
</head>
<body>
  <h2>Class Scheduling & Video Assignment</h2>

  <?php if (isset($success)) echo "<p class='success'>{$success}</p>"; ?>

  <h3>Add New Class</h3>
  <form method="POST">
    <input type="hidden" name="action" value="add_class">
    <input type="text" name="class_name" placeholder="Class Name" required><br>
    <input type="text" name="month_year" placeholder="Month-Year (e.g. June 2025)" required><br>
    <button type="submit">Add Class</button>
  </form>

  <h3>Assign Videos to Class</h3>
  <form method="POST">
    <input type="hidden" name="action" value="assign_videos">
    <select name="class_id" required>
      <option value="">Select Class</option>
      <?php
      $classes = $conn->query("SELECT * FROM class ORDER BY id DESC");
      while ($c = $classes->fetch_assoc()):
      ?>
        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['month_year']) ?>)</option>
      <?php endwhile; ?>
    </select><br>

    <h4>Select Videos</h4>
    <?php
    $videos = $conn->query("SELECT * FROM video ORDER BY id DESC");
    while ($v = $videos->fetch_assoc()):
    ?>
      <label>
        <input type="checkbox" name="video_ids[]" value="<?= $v['id'] ?>"> <?= htmlspecialchars($v['name']) ?>
      </label><br>
    <?php endwhile; ?>

    <button type="submit">Assign Videos</button>
  </form>
</body>
</html>