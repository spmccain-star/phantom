<?php
$data_dir   = __DIR__ . '/data';
$upload_dir = $data_dir . '/uploads';
if (!is_dir($data_dir))   mkdir($data_dir,   0755, true);
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

$db = new PDO('sqlite:' . $data_dir . '/messages.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("CREATE TABLE IF NOT EXISTS messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    message TEXT NOT NULL,
    image_path TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");
// Add image_path column to existing DBs that predate this feature
try { $db->exec("ALTER TABLE messages ADD COLUMN image_path TEXT"); } catch (Exception $e) {}

function process_image(string $tmp_path, string $mime, string $upload_dir): ?string {
    $out_name = bin2hex(random_bytes(12)) . '.jpg';
    $out_path = $upload_dir . '/' . $out_name;
    $max_w    = 1200;
    $quality  = 82;

    // HEIC — use ImageMagick
    if (in_array($mime, ['image/heic', 'image/heif']) || str_ends_with(strtolower($tmp_path), '.heic')) {
        $escaped_in  = escapeshellarg($tmp_path);
        $escaped_out = escapeshellarg($out_path);
        exec("convert $escaped_in -auto-orient -resize {$max_w}x{$max_w}\\> -quality $quality $escaped_out 2>&1", $out, $code);
        return ($code === 0 && file_exists($out_path)) ? $out_name : null;
    }

    // GD for JPEG, PNG, GIF, WebP
    $src = match($mime) {
        'image/jpeg' => @imagecreatefromjpeg($tmp_path),
        'image/png'  => @imagecreatefrompng($tmp_path),
        'image/gif'  => @imagecreatefromgif($tmp_path),
        'image/webp' => @imagecreatefromwebp($tmp_path),
        default      => null,
    };
    if (!$src) return null;

    // Auto-rotate from EXIF
    if (function_exists('exif_read_data') && in_array($mime, ['image/jpeg'])) {
        $exif = @exif_read_data($tmp_path);
        $orientation = $exif['Orientation'] ?? 1;
        $src = match($orientation) {
            3 => imagerotate($src, 180, 0),
            6 => imagerotate($src, -90, 0),
            8 => imagerotate($src, 90, 0),
            default => $src,
        };
    }

    $ow = imagesx($src); $oh = imagesy($src);
    if ($ow > $max_w) {
        $nw = $max_w; $nh = (int)round($oh * $max_w / $ow);
    } else {
        $nw = $ow; $nh = $oh;
    }
    $dst = imagecreatetruecolor($nw, $nh);
    // White background for PNGs with transparency
    imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $ow, $oh);
    imagejpeg($dst, $out_path, $quality);
    imagedestroy($src); imagedestroy($dst);
    return file_exists($out_path) ? $out_name : null;
}

$error   = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!$name || !$message) {
        $error = 'Please fill in both fields.';
    } elseif (mb_strlen($name) > 80) {
        $error = 'Name is too long.';
    } elseif (mb_strlen($message) > 500) {
        $error = 'Message must be 500 characters or fewer.';
    } else {
        $image_path = null;

        if (!empty($_FILES['photo']['tmp_name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $file   = $_FILES['photo'];
            $size   = $file['size'];
            $mime   = mime_content_type($file['tmp_name']);
            $allowed = ['image/jpeg','image/png','image/gif','image/webp','image/heic','image/heif'];

            if ($size > 20 * 1024 * 1024) {
                $error = 'Photo must be under 20 MB.';
            } elseif (!in_array($mime, $allowed)) {
                $error = 'Unsupported image type. Please upload a JPEG, PNG, GIF, WebP, or HEIC photo.';
            } else {
                $image_path = process_image($file['tmp_name'], $mime, $upload_dir);
                if (!$image_path) $error = 'Could not process the image. Please try a different photo.';
            }
        }

        if (!$error) {
            $stmt = $db->prepare("INSERT INTO messages (name, message, image_path) VALUES (?, ?, ?)");
            $stmt->execute([$name, $message, $image_path]);
            $success = true;
        }
    }
}

$messages = $db->query("SELECT * FROM messages ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Messages for Mateo — Phantom Regiment 2026</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --page-bg: #111; --surface: #1C1C1E; --surface-2: #2C2C2E;
      --border: rgba(255,255,255,0.1); --text: #F2F0EA;
      --text-secondary: #A0A0A5; --text-muted: #666;
      --red: #B01A1C; --red-dark: #8B1416; --radius: 14px;
    }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: var(--page-bg); color: var(--text); min-height: 100vh; padding-bottom: 4rem; }

    .page-header { background: var(--surface); border-bottom: 1px solid var(--border); padding: 1rem 1.5rem; display: flex; align-items: center; gap: 1rem; }
    .back-btn { color: var(--text-secondary); text-decoration: none; font-size: 14px; display: flex; align-items: center; gap: 6px; }
    .back-btn:hover { color: var(--text); }
    .page-header h1 { font-family: 'Playfair Display', Georgia, serif; font-size: 20px; font-weight: 700; }

    .page-content { max-width: 700px; margin: 0 auto; padding: 2rem 1.5rem; }

    .form-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.5rem; margin-bottom: 2.5rem; box-shadow: 0 4px 16px rgba(0,0,0,0.4); }
    .form-card h2 { font-family: 'Playfair Display', Georgia, serif; font-size: 22px; margin-bottom: 0.4rem; }
    .form-card .subtitle { font-size: 14px; color: var(--text-secondary); margin-bottom: 1.5rem; line-height: 1.5; }
    .form-group { margin-bottom: 1.1rem; }
    label { display: block; font-size: 12px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 6px; }
    input[type=text], textarea {
      width: 100%; background: var(--surface-2); border: 1px solid var(--border); border-radius: 10px;
      color: var(--text); font-size: 15px; font-family: inherit; padding: 12px 14px; outline: none; transition: border-color 0.15s;
    }
    input[type=text]:focus, textarea:focus { border-color: rgba(176,26,28,0.6); }
    textarea { resize: vertical; min-height: 100px; line-height: 1.6; }
    .char-count { font-size: 12px; color: var(--text-muted); text-align: right; margin-top: 4px; }

    .photo-upload { background: var(--surface-2); border: 2px dashed var(--border); border-radius: 10px; padding: 1.1rem; text-align: center; cursor: pointer; transition: border-color 0.15s; position: relative; }
    .photo-upload:hover { border-color: rgba(176,26,28,0.5); }
    .photo-upload input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
    .photo-upload-icon { color: var(--text-muted); margin-bottom: 6px; }
    .photo-upload-label { font-size: 14px; color: var(--text-secondary); }
    .photo-upload-sub { font-size: 12px; color: var(--text-muted); margin-top: 3px; }
    .photo-preview { display: none; margin-top: 0.75rem; }
    .photo-preview img { max-width: 100%; max-height: 200px; border-radius: 8px; object-fit: cover; }

    .submit-btn { width: 100%; padding: 14px; background: var(--red); color: #fff; border: none; border-radius: 10px; font-size: 15px; font-weight: 600; cursor: pointer; transition: background 0.15s; margin-top: 0.5rem; }
    .submit-btn:hover { background: var(--red-dark); }
    .alert { padding: 12px 14px; border-radius: 10px; font-size: 14px; margin-bottom: 1rem; }
    .alert-error { background: rgba(176,26,28,0.15); border: 1px solid rgba(176,26,28,0.3); color: #E07070; }
    .alert-success { background: rgba(125,217,162,0.12); border: 1px solid rgba(125,217,162,0.3); color: #7DD9A2; }

    .messages-header { display: flex; align-items: baseline; gap: 10px; margin-bottom: 1.25rem; }
    .messages-header h2 { font-family: 'Playfair Display', Georgia, serif; font-size: 20px; }
    .msg-count { font-size: 13px; color: var(--text-muted); }
    .empty-state { text-align: center; padding: 3rem 1rem; color: var(--text-muted); font-size: 15px; }

    .message-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; margin-bottom: 0.75rem; }
    .message-card-body { padding: 1.1rem 1.25rem; }
    .message-meta { display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 0.5rem; }
    .message-name { font-weight: 700; font-size: 15px; }
    .message-date { font-size: 12px; color: var(--text-muted); }
    .message-text { font-size: 15px; color: var(--text-secondary); line-height: 1.65; }
    .message-img { width: 100%; max-height: 360px; object-fit: cover; display: block; margin-top: 0.85rem; border-radius: 8px; }
  </style>
</head>
<body>

<div class="page-header">
  <a class="back-btn" href="/">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    Back
  </a>
  <h1>Messages for Mateo</h1>
  <a href="/fanmail.php" style="margin-left:auto;font-size:13px;color:var(--text-muted);text-decoration:none;display:flex;align-items:center;gap:5px;">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
    Fanmail
  </a>
</div>

<div class="page-content">

  <div class="form-card">
    <h2>Leave Mateo a message</h2>
    <p class="subtitle">Mateo is performing with Phantom Regiment this summer. Drop him a note of encouragement — he'll see every one.</p>

    <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
    <div class="alert alert-success">Message sent! Mateo will see it.</div>
    <?php endif; ?>

    <form method="POST" action="/messages.php" enctype="multipart/form-data">
      <div class="form-group">
        <label for="name">Your name</label>
        <input type="text" id="name" name="name" placeholder="e.g. Grandma Carol" maxlength="80" autocomplete="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="message">Message</label>
        <textarea id="message" name="message" placeholder="We're so proud of you…" maxlength="500" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
        <div class="char-count"><span id="char-count">0</span> / 500</div>
      </div>
      <div class="form-group">
        <label>Photo <span style="font-weight:400;text-transform:none;letter-spacing:0;color:var(--text-muted);font-size:11px;">(optional)</span></label>
        <div class="photo-upload" id="upload-zone">
          <input type="file" name="photo" id="photo-input" accept="image/jpeg,image/png,image/gif,image/webp,image/heic,image/heif,.heic,.heif">
          <div class="photo-upload-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
          </div>
          <div class="photo-upload-label">Tap to add a photo</div>
          <div class="photo-upload-sub">JPEG, PNG, WebP, or HEIC · max 20 MB</div>
        </div>
        <div class="photo-preview" id="photo-preview">
          <img id="preview-img" src="" alt="Preview">
        </div>
      </div>
      <button class="submit-btn" type="submit">Send message</button>
    </form>
  </div>

  <div id="messages">
    <div class="messages-header">
      <h2>All messages</h2>
      <span class="msg-count"><?= count($messages) ?> message<?= count($messages) !== 1 ? 's' : '' ?></span>
    </div>

    <?php if (empty($messages)): ?>
    <div class="empty-state">No messages yet — be the first!</div>
    <?php else: ?>
    <?php foreach ($messages as $msg): ?>
    <div class="message-card">
      <div class="message-card-body">
        <div class="message-meta">
          <span class="message-name"><?= htmlspecialchars($msg['name']) ?></span>
          <span class="message-date"><?= date('M j, Y', strtotime($msg['created_at'])) ?></span>
        </div>
        <div class="message-text"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
        <?php if (!empty($msg['image_path'])): ?>
        <img class="message-img" src="/data/uploads/<?= htmlspecialchars($msg['image_path']) ?>" alt="Photo from <?= htmlspecialchars($msg['name']) ?>" loading="lazy">
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>

</div>

<script>
  var ta = document.getElementById('message');
  var cc = document.getElementById('char-count');
  function updateCount() { cc.textContent = ta.value.length; }
  ta.addEventListener('input', updateCount);
  updateCount();

  document.getElementById('photo-input').addEventListener('change', function() {
    var file = this.files[0];
    if (!file) return;
    var preview = document.getElementById('photo-preview');
    var img = document.getElementById('preview-img');
    var reader = new FileReader();
    reader.onload = function(e) {
      img.src = e.target.result;
      preview.style.display = 'block';
      document.querySelector('.photo-upload-label').textContent = file.name;
    };
    reader.readAsDataURL(file);
  });
</script>
</body>
</html>
