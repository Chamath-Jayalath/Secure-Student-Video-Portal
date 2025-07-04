<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['user_id'];
// Use first_name + last_name for display
$first_name = $_SESSION['first_name'] ?? '';
$last_name = $_SESSION['last_name'] ?? '';
$display_name = trim($first_name . ' ' . $last_name);
if (empty($display_name)) {
    $display_name = 'Student';
}

// Fetch classes student has access to
$stmt = $conn->prepare("SELECT c.id, c.name FROM class c JOIN user_class uc ON c.id = uc.class_id WHERE uc.user_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$classes_result = $stmt->get_result();

// Get selected video id from URL (used only for sidebar active highlight)
$selected_video_id = isset($_GET['video']) && is_numeric($_GET['video']) ? intval($_GET['video']) : null;
?>
<!DOCTYPE html>
<html>
<head>
  <title>Student Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
  <style>
    body {
  margin: 0;
  font-family: 'Roboto', sans-serif;
  background: #f2f2f2;
  color: #333;
}

.wrapper {
  display: flex;
  min-height: 100vh;
}

.sidebar {
  width: 260px;
  background: #3f51b5;
  color: white;
  padding: 20px;
  overflow-y: auto;
  box-shadow: 2px 0 8px rgba(0,0,0,0.15);
  display: flex;
  flex-direction: column;
}

.sidebar h2 {
  font-size: 20px;
  margin-bottom: 20px;
  font-weight: 700;
  letter-spacing: 0.05em;
  user-select: none;
}

.class-name {
  cursor: pointer;
  padding: 10px 12px;
  margin-bottom: 6px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 6px;
  user-select: none;

  display: flex;
  justify-content: space-between;
  align-items: center;

  transition: background-color 0.3s ease;
}

.class-name:hover {
  background: rgba(255, 255, 255, 0.2);
}

.video-list {
  display: none;
  margin-left: 12px;
  margin-bottom: 20px;
}

.video-list a {
  display: block;
  color: white;
  padding: 7px 12px;
  margin-bottom: 6px;
  text-decoration: none;
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.15);
  font-size: 14px;
  transition: background-color 0.3s ease;
}

.video-list a:hover {
  background: rgba(255, 255, 255, 0.3);
}

.video-list a.active {
  background: #303f9f;
  font-weight: 600;
}

.main {
  flex: 1;
  padding: 40px;
  background: white;
  overflow-y: auto;
  box-shadow: inset 0 0 15px rgb(0 0 0 / 0.05);
  border-radius: 8px;
}

.topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.topbar h1 {
  margin: 0;
  font-weight: 700;
  color: #3f51b5;
  user-select: none;
}

.btn {
  background: #3f51b5;
  color: white;
  border: none;
  padding: 10px 16px;
  border-radius: 6px;
  cursor: pointer;
  display: inline-block;
  margin-bottom: 12px;
  font-weight: 600;
  text-align: center;
  text-decoration: none;
  transition: background-color 0.3s ease;
}

.btn:hover {
  background: #303f9f;
}

  </style>
</head>
<body>
<div class="wrapper">
  <div class="sidebar">
    <h2>📚 Your Classes</h2>

    <?php
    // Loop classes, then for each class fetch its videos (your original logic)
    while ($class = $classes_result->fetch_assoc()):
        $class_id = $class['id'];
        $stmt2 = $conn->prepare("SELECT v.id, v.name FROM class_video cv JOIN video v ON cv.video_id = v.id WHERE cv.class_id = ? ORDER BY v.id DESC");
        $stmt2->bind_param("i", $class_id);
        $stmt2->execute();
        $videos_result = $stmt2->get_result();
    ?>
      <div class="class-wrapper">
        <div class="class-name" data-classid="<?= $class_id ?>">
          <?= htmlspecialchars($class['name']) ?>
          <span class="toggle-icon">▶</span>
        </div>
        <div class="video-list" id="videos-for-<?= $class_id ?>">
          <?php while ($video = $videos_result->fetch_assoc()): ?>
            <a href="?video=<?= $video['id'] ?>" data-videoid="<?= $video['id'] ?>" class="<?= $selected_video_id === intval($video['id']) ? 'active' : '' ?>">
              <?= htmlspecialchars($video['name']) ?>
            </a>
          <?php endwhile; ?>
          <?php if ($videos_result->num_rows === 0): ?>
            <small style="color:#ccc; padding-left:10px;">No videos assigned.</small>
          <?php endif; ?>
        </div>
      </div>
    <?php endwhile; ?>

    <hr>
    <a href="buy_course.php" class="btn">🔑 Buy New Course</a>
    <a href="logout.php" class="btn">🚪 Logout</a>
  </div>

  <div class="main">
    <div class="topbar">
      <h1>Welcome, <?= htmlspecialchars($display_name) ?></h1>
    </div>

    <?php
    // Include your existing player.php, passing video id via $_GET['video']
    if ($selected_video_id) {
        include 'player.php';
    } else {
        echo "<p>Please select a video from the sidebar to watch.</p>";
    }
    ?>
  </div>
</div>

<script>
  // Toggle expand/collapse video lists
  document.querySelectorAll('.class-name').forEach(function(elem) {
    elem.addEventListener('click', function() {
      const classId = this.getAttribute('data-classid');
      const videoList = document.getElementById('videos-for-' + classId);
      const icon = this.querySelector('.toggle-icon');
      if (videoList.style.display === 'block') {
        videoList.style.display = 'none';
        icon.textContent = '▶';
      } else {
        videoList.style.display = 'block';
        icon.textContent = '▼';
      }
    });
  });

  // Highlight active video link & expand its class on page load
  (function() {
    const activeLink = document.querySelector('.video-list a.active');
    if (activeLink) {
      const parentList = activeLink.closest('.video-list');
      if (parentList) {
        parentList.style.display = 'block';
        const classDiv = parentList.previousElementSibling;
        if (classDiv && classDiv.classList.contains('class-name')) {
          const icon = classDiv.querySelector('.toggle-icon');
          if (icon) icon.textContent = '▼';
        }
      }
    }
  })();
</script>
</body>
</html>
