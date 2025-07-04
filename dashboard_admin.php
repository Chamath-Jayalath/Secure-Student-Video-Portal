<?php
session_start();
include "config.php";
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
  header("Location: index.php");
  exit();
}

// Add new video
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
  $newId = $_POST['youtube_id'];
  $name = $_POST['video_name'];
  $stmt = $conn->prepare("INSERT INTO video (youtube_id, name) VALUES (?, ?)");
  $stmt->bind_param("ss", $newId, $name);
  $stmt->execute();
  $success = "New video added!";
}

// Update video
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
  $id = $_POST['video_id'];
  $newId = $_POST['youtube_id'];
  $name = $_POST['video_name'];
  $stmt = $conn->prepare("UPDATE video SET youtube_id = ?, name = ? WHERE id = ?");
  $stmt->bind_param("ssi", $newId, $name, $id);
  $stmt->execute();
  $success = "Video updated!";
}

// Delete video
if (isset($_GET['delete'])) {
  $deleteId = $_GET['delete'];
  $stmt = $conn->prepare("DELETE FROM video WHERE id = ?");
  $stmt->bind_param("i", $deleteId);
  $stmt->execute();
  $success = "Video deleted!";
}

// Assign Videos to Class
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'assign_videos') {
  $classId = $_POST['class_id'];
  $videoIds = $_POST['video_ids'] ?? [];
  $stmt = $conn->prepare("INSERT IGNORE INTO class_video (class_id, video_id) VALUES (?, ?)");
  foreach ($videoIds as $vid) {
    $stmt->bind_param("ii", $classId, $vid);
    $stmt->execute();
  }
  $success = "Videos assigned to class!";
}

// Get current video (latest by id desc)
$result = $conn->query("SELECT youtube_id FROM video ORDER BY id DESC LIMIT 1");
$row = $result->fetch_assoc();
$current = $row['youtube_id'] ?? "";

// Get all videos
$videos = $conn->query("SELECT * FROM video ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard | Video Management</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    /* RESET */
    *, *::before, *::after {
      box-sizing: border-box;
    }
    body, h1, h2, h3, p, ul, li, table, tr, td, th, form, input, button, select, a {
      margin: 0; padding: 0; border: 0; outline: none; font-family: 'Inter', sans-serif;
    }
    body {
      background-color: #f9fafb;
      color: #111827;
      display: flex;
      height: 100vh;
      overflow: hidden;
      font-size: 16px;
      line-height: 1.5;
    }

    /* SIDEBAR */
    .sidebar {
      width: 260px;
      background-color: #1e293b;
      color: #e0e7ff;
      display: flex;
      flex-direction: column;
      padding: 30px 20px;
      overflow-y: auto;
      flex-shrink: 0;
    }
    .sidebar h2 {
      font-weight: 700;
      font-size: 24px;
      margin-bottom: 30px;
      border-bottom: 2px solid #6366f1;
      padding-bottom: 10px;
      letter-spacing: 0.05em;
    }
    .sidebar a {
      display: block;
      padding: 12px 14px;
      margin-bottom: 12px;
      border-radius: 8px;
      text-decoration: none;
      color: #c7d2fe;
      font-weight: 500;
      transition: background-color 0.3s ease, color 0.3s ease;
      user-select: none;
    }
    .sidebar a:hover,
    .sidebar a:focus {
      background-color: #6366f1;
      color: white;
      outline-offset: 2px;
    }

    /* MAIN CONTENT */
    .main-content {
      flex: 1;
      overflow-y: auto;
      padding: 40px 50px;
      background-color: #ffffff;
      box-shadow: inset 0 0 15px rgb(0 0 0 / 0.05);
      border-radius: 10px 0 0 10px;
      display: flex;
      flex-direction: column;
    }

    /* TOPBAR */
    .topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 35px;
    }
    .topbar h2 {
      font-weight: 700;
      font-size: 28px;
      color: #111827;
    }
    .topbar .btn {
      background-color: #ef4444;
      color: #fff;
      padding: 10px 22px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      box-shadow: 0 4px 6px rgb(239 68 68 / 0.4);
      transition: background-color 0.3s ease;
      user-select: none;
      margin-left: 15px;
    }
    .topbar .btn:hover,
    .topbar .btn:focus {
      background-color: #dc2626;
      box-shadow: 0 6px 9px rgb(220 38 38 / 0.6);
    }
    .topbar .btn-group {
      display: flex;
      align-items: center;
    }

    /* SUCCESS MESSAGE */
    .success {
      background-color: #dcfce7;
      color: #22c55e;
      padding: 14px 18px;
      border-radius: 8px;
      font-weight: 600;
      margin-bottom: 30px;
      box-shadow: 0 0 15px rgb(34 197 94 / 0.2);
      user-select: none;
    }

    /* SECTIONS */
    .section {
      margin-bottom: 50px;
    }
    .section h3 {
      font-size: 22px;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 25px;
      border-bottom: 2px solid #6366f1;
      padding-bottom: 8px;
    }

    /* FORM FIELDS */
    form input[type="text"],
    form select {
      width: 100%;
      max-width: 350px;
      padding: 12px 16px;
      font-size: 15px;
      border: 2px solid #d1d5db;
      border-radius: 10px;
      transition: border-color 0.3s ease;
      margin-bottom: 18px;
      font-weight: 500;
      color: #374151;
      user-select: text;
    }
    form input[type="text"]:focus,
    form select:focus {
      border-color: #6366f1;
      outline: none;
      box-shadow: 0 0 5px #6366f1aa;
    }

    /* BUTTONS */
    button {
      background-color: #6366f1;
      color: white;
      padding: 12px 26px;
      font-weight: 600;
      border-radius: 10px;
      border: none;
      cursor: pointer;
      user-select: none;
      transition: background-color 0.3s ease;
    }
    button:hover,
    button:focus {
      background-color: #4f46e5;
    }

    /* TABLE */
    table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0 10px;
      font-size: 15px;
      user-select: none;
    }
    th, td {
      text-align: left;
      padding: 12px 16px;
    }
    th {
      color: #4b5563;
      font-weight: 700;
      padding-bottom: 8px;
    }
    tr {
      background: #f3f4f6;
      border-radius: 10px;
      transition: background-color 0.3s ease;
    }
    tr:hover {
      background: #e0e7ff;
    }
    td {
      vertical-align: middle;
    }
    td form {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      align-items: center;
      margin: 0;
    }

    /* DELETE LINK */
    a.delete {
      background-color: #ef4444;
      color: white;
      padding: 8px 16px;
      border-radius: 10px;
      text-decoration: none;
      font-weight: 600;
      transition: background-color 0.3s ease;
      user-select: none;
    }
    a.delete:hover,
    a.delete:focus {
      background-color: #b91c1c;
    }

    /* IFRAME PLAYER */
    iframe {
      width: 100%;
      max-width: 700px;
      height: 395px;
      border-radius: 12px;
      border: none;
      margin-top: 12px;
      user-select: none;
      box-shadow: 0 0 20px rgb(99 102 241 / 0.15);
    }
    .video-name {
      margin-top: 10px;
      font-weight: 600;
      font-size: 18px;
      color: #1f2937;
      user-select: text;
    }

    /* LABELS FOR CHECKBOXES */
    label {
      display: block;
      user-select: none;
      margin-bottom: 8px;
      font-weight: 500;
      cursor: pointer;
      color: #374151;
    }
    input[type="checkbox"] {
      margin-right: 10px;
      cursor: pointer;
      accent-color: #6366f1;
    }

    /* Responsive */
    @media (max-width: 900px) {
      body {
        flex-direction: column;
        height: auto;
      }
      .sidebar {
        width: 100%;
        height: auto;
        flex-direction: row;
        overflow-x: auto;
        padding: 15px 10px;
      }
      .sidebar h2 {
        font-size: 18px;
        margin-bottom: 0;
        padding-bottom: 0;
        margin-right: 20px;
        border-bottom: none;
      }
      .sidebar a {
        margin-bottom: 0;
        margin-right: 15px;
        padding: 8px 14px;
        white-space: nowrap;
      }
      .main-content {
        padding: 20px 15px;
        border-radius: 0;
      }
      table, td, th {
        font-size: 14px;
      }
      iframe {
        max-width: 100%;
        height: 260px;
      }
    }
  </style>
</head>
<body>
  <nav class="sidebar" aria-label="Sidebar with video links">
    <h2>All Recordings</h2>
    <?php
      $videoList = $conn->query("SELECT id, name FROM video ORDER BY id DESC");
      while ($v = $videoList->fetch_assoc()):
    ?>
      <a href="#video<?= $v['id'] ?>"><?= htmlspecialchars($v['name']) ?></a>
    <?php endwhile; ?>
  </nav>

  <main class="main-content">
    <header class="topbar">
      <h2>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h2>
      <div class="btn-group" role="group" aria-label="User actions">
        <a href="logout.php" class="btn" role="button" tabindex="0">Logout</a>
        <a href="admin_add_class.php" class="btn" role="button" tabindex="0">Add Class</a>
      </div>
    </header>

    <?php if (isset($success)): ?>
      <div role="alert" class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <section class="section" aria-labelledby="add-recording-title">
      <h3 id="add-recording-title">Add New Recording</h3>
      <form method="POST" aria-describedby="add-desc">
        <input type="hidden" name="action" value="add" />
        <input type="text" name="video_name" placeholder="Recording Title" required aria-required="true" />
        <input type="text" name="youtube_id" placeholder="YouTube Video ID" required aria-required="true" />
        <button type="submit">Add</button>
      </form>
    </section>

    <section class="section" aria-labelledby="latest-recording-title">
      <h3 id="latest-recording-title">Latest Uploaded Recording</h3>
      <?php if ($current): ?>
        <div class="video-name"><?= htmlspecialchars($current) ?></div>
        <?php include "player.php"; ?>
      <?php else: ?>
        <p>No recordings available yet.</p>
      <?php endif; ?>
    </section>

    <section class="section" aria-labelledby="manage-recordings-title">
      <h3 id="manage-recordings-title">Manage Recordings</h3>
      <table role="grid" aria-describedby="manage-desc">
        <thead>
          <tr>
            <th scope="col">ID</th>
            <th scope="col">Title</th>
            <th scope="col">YouTube ID</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $videos = $conn->query("SELECT * FROM video ORDER BY id DESC");
          while ($video = $videos->fetch_assoc()):
          ?>
          <tr id="video<?= $video['id'] ?>">
            <form method="POST">
              <td><?= $video['id'] ?></td>
              <td><input type="text" name="video_name" value="<?= htmlspecialchars($video['name']) ?>" required aria-label="Video title for ID <?= $video['id'] ?>" /></td>
              <td><input type="text" name="youtube_id" value="<?= htmlspecialchars($video['youtube_id']) ?>" required aria-label="YouTube ID for video ID <?= $video['id'] ?>" /></td>
              <td>
                <input type="hidden" name="action" value="update" />
                <input type="hidden" name="video_id" value="<?= $video['id'] ?>" />
                <button type="submit" aria-label="Update video ID <?= $video['id'] ?>">Update</button>
                <a class="delete" href="?delete=<?= $video['id'] ?>" onclick="return confirm('Delete this recording?')" aria-label="Delete video ID <?= $video['id'] ?>">Delete</a>
              </td>
            </form>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </section>

    <section class="section" aria-labelledby="assign-videos-title">
      <h3 id="assign-videos-title">Assign Videos to Class</h3>
      <form method="POST" aria-describedby="assign-desc">
        <input type="hidden" name="action" value="assign_videos" />
        <label for="class_id" style="font-weight:600; margin-bottom: 8px; display: block;">Select Class</label>
        <select name="class_id" id="class_id" required aria-required="true" aria-label="Select Class">
          <option value="" disabled selected>Select Class</option>
          <?php
          $classes = $conn->query("SELECT * FROM class ORDER BY id DESC");
          while ($c = $classes->fetch_assoc()):
          ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
          <?php endwhile; ?>
        </select>

        <h4 style="margin-top: 20px; margin-bottom: 12px;">Select Videos</h4>
        <?php
        $videos = $conn->query("SELECT * FROM video ORDER BY id DESC");
        while ($v = $videos->fetch_assoc()):
        ?>
          <label>
            <input type="checkbox" name="video_ids[]" value="<?= $v['id'] ?>" />
            <?= htmlspecialchars($v['name']) ?>
          </label>
        <?php endwhile; ?>

        <button type="submit" style="margin-top: 18px;">Assign Videos</button>
      </form>
    </section>
    <section class="section" aria-labelledby="secret-keys-title">
  <h3 id="secret-keys-title">Class Secret Keys</h3>

  <style>
    .class-name {
      cursor: pointer;
      background: #f0f0f0;
      font-weight: bold;
      padding: 10px;
      border: 1px solid #ccc;
      margin-top: 5px;
    }
    .keys-container {
      display: none;
      margin-left: 20px;
      border-left: 2px solid #ccc;
      padding-left: 10px;
      margin-bottom: 10px;
    }
    .key-item {
      padding: 5px 0;
    }
  </style>

  <?php
  include "config.php";

  // Fetch all class names
  $classQuery = "SELECT id, name FROM class ORDER BY id DESC";
  $classResult = $conn->query($classQuery);

  while ($class = $classResult->fetch_assoc()):
    $class_id = $class['id'];
    $class_name = htmlspecialchars($class['name']);

    // Fetch all keys for this class
    $keyQuery = "SELECT secret_key FROM class_keys WHERE class_id = $class_id";
    $keyResult = $conn->query($keyQuery);
  ?>
    <div class="class-block">
      <div class="class-name" onclick="toggleKeys(this)"><?= $class_name ?></div>
      <div class="keys-container">
        <?php while ($key = $keyResult->fetch_assoc()): ?>
          <div class="key-item"><?= htmlspecialchars($key['secret_key']) ?></div>
        <?php endwhile; ?>
      </div>
    </div>
  <?php endwhile; ?>

  <script>
    function toggleKeys(element) {
      const container = element.nextElementSibling;
      container.style.display = container.style.display === "block" ? "none" : "block";
    }
  </script>

  <form method="post" action="export_keys_excel.php">
    <button type="submit" style="margin-top: 20px;">Export All Keys as Excel</button>
  </form>
</section>

  </main>
  
</body>
</html>
