<?php
$conn = new mysqli("localhost", "root", "", "secure_video_portal");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
