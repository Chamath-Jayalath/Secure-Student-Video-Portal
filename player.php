<?php
if (!isset($conn)) {
  include "config.php"; // Ensure DB is available
}

$defaultYoutubeId = "";

// Check if ?video= param is set (video id)
if (isset($_GET['video']) && is_numeric($_GET['video'])) {
  $video_id = intval($_GET['video']);
  $stmt = $conn->prepare("SELECT youtube_id FROM video WHERE id = ?");
  $stmt->bind_param("i", $video_id);
  $stmt->execute();
  $res = $stmt->get_result();

  if ($row = $res->fetch_assoc()) {
    $defaultYoutubeId = $row['youtube_id'];
  }
}
// Else check if ?class= param is set (class id)
elseif (isset($_GET['class']) && is_numeric($_GET['class'])) {
  $class_id = intval($_GET['class']);
  $stmt = $conn->prepare("SELECT v.youtube_id 
                          FROM video v 
                          JOIN class_video cv ON cv.video_id = v.id 
                          WHERE cv.class_id = ? 
                          ORDER BY v.id DESC LIMIT 1");
  $stmt->bind_param("i", $class_id);
  $stmt->execute();
  $res = $stmt->get_result();

  if ($row = $res->fetch_assoc()) {
    $defaultYoutubeId = $row['youtube_id'];
  }
}

// If still empty, fallback to last video in DB
if (empty($defaultYoutubeId)) {
  $result = $conn->query("SELECT youtube_id FROM video ORDER BY id DESC LIMIT 1");
  $row = $result->fetch_assoc();
  $defaultYoutubeId = $row['youtube_id'] ?? '';
}
?>



<link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
<div class="video-wrapper" id="wrapper" style="max-width:900px;margin:30px auto;">
  <div id="player" data-plyr-provider="youtube" data-plyr-embed-id="<?= $defaultYoutubeId ?>"></div>
  <div class="block-overlay block-channel"></div>
  <div class="block-overlay block-copy"></div>
  <div class="pause-shield" id="pauseShield"></div>
</div>

<?php if (!empty($youtubeIds)): ?>
  <div style="max-width:900px;margin:10px auto 30px;text-align:center;">
    <?php foreach ($youtubeIds as $id): ?>
      
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<script src="https://cdn.plyr.io/3.7.8/plyr.js"></script>
<script>
  const player = new Plyr('#player', {
    youtube: { noCookie: true, rel: 0, modestbranding: 1 }
  });

  const pauseShield = document.getElementById("pauseShield");

  document.addEventListener('contextmenu', e => e.preventDefault());

  player.on('pause', () => pauseShield.style.display = 'block');
  player.on('play', () => pauseShield.style.display = 'none');

  pauseShield.addEventListener('click', () => player.play());

  function addFullscreenBlock() {
    const full = document.fullscreenElement || document.webkitFullscreenElement;
    if (full) {
      full.oncontextmenu = (e) => e.preventDefault();
      full.appendChild(pauseShield);
    }
  }

  document.addEventListener("fullscreenchange", addFullscreenBlock);
  document.addEventListener("webkitfullscreenchange", addFullscreenBlock);

  // Switch video on button click
  document.querySelectorAll('.btn-video').forEach(btn => {
    btn.addEventListener('click', function () {
      const videoId = this.getAttribute('data-id');
      player.source = {
        type: 'video',
        sources: [{
          src: videoId,
          provider: 'youtube'
        }]
      };
    });
  });
</script>

<style>
  .video-wrapper {
    position: relative;
    aspect-ratio: 16 / 9;
    border: 2px solid #ccc;
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
  }

  iframe.plyr__video-embed {
    width: 100%;
    height: 100%;
  }

  .block-overlay, .pause-shield {
    position: absolute;
    top: 0;
    height: 60px;
    z-index: 10;
    background: transparent;
    cursor: not-allowed;
  }

  .block-channel { left: 0; width: 120px; }
  .block-copy { right: 0; width: 100px; }

  .pause-shield {
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    z-index: 20;
    display: none;
  }

  * {
    user-select: none;
    -webkit-user-drag: none;
  }

  .btn-video {
    background: #3f51b5;
    color: white;
    border: none;
    padding: 8px 12px;
    margin: 5px;
    border-radius: 5px;
    cursor: pointer;
  }

  .btn-video:hover {
    background: #303f9f;
  }
</style>
