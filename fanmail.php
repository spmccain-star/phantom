<?php
$data_dir   = __DIR__ . '/data';
$upload_dir = $data_dir . '/uploads';
if (!is_dir($data_dir))   mkdir($data_dir,   0755, true);
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

$db = new PDO('sqlite:' . $data_dir . '/messages.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("CREATE TABLE IF NOT EXISTS messages (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, message TEXT NOT NULL, image_path TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)");
try { $db->exec("ALTER TABLE messages ADD COLUMN image_path TEXT"); } catch (Exception $e) {}

function process_image_fanmail(string $tmp_path, string $mime, string $upload_dir): ?string {
    $out_name = bin2hex(random_bytes(12)) . '.jpg';
    $out_path = $upload_dir . '/' . $out_name;
    $max_w = 1200; $quality = 82;
    if (in_array($mime, ['image/heic','image/heif'])) {
        exec("convert " . escapeshellarg($tmp_path) . " -auto-orient -resize {$max_w}x{$max_w}\\> -quality $quality " . escapeshellarg($out_path) . " 2>&1", $o, $code);
        return ($code === 0 && file_exists($out_path)) ? $out_name : null;
    }
    $src = match($mime) {
        'image/jpeg' => @imagecreatefromjpeg($tmp_path),
        'image/png'  => @imagecreatefrompng($tmp_path),
        'image/gif'  => @imagecreatefromgif($tmp_path),
        'image/webp' => @imagecreatefromwebp($tmp_path),
        default      => null,
    };
    if (!$src) return null;
    if (function_exists('exif_read_data') && $mime === 'image/jpeg') {
        $exif = @exif_read_data($tmp_path);
        $src = match($exif['Orientation'] ?? 1) { 3=>imagerotate($src,180,0), 6=>imagerotate($src,-90,0), 8=>imagerotate($src,90,0), default=>$src };
    }
    $ow = imagesx($src); $oh = imagesy($src);
    $nw = $ow > $max_w ? $max_w : $ow;
    $nh = (int)round($oh * $nw / $ow);
    $dst = imagecreatetruecolor($nw, $nh);
    imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $ow, $oh);
    imagejpeg($dst, $out_path, $quality);
    imagedestroy($src); imagedestroy($dst);
    return file_exists($out_path) ? $out_name : null;
}

$error = ''; $success = false;
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
            $mime    = mime_content_type($_FILES['photo']['tmp_name']);
            $allowed = ['image/jpeg','image/png','image/gif','image/webp','image/heic','image/heif'];
            if ($_FILES['photo']['size'] > 20 * 1024 * 1024) {
                $error = 'Photo must be under 20 MB.';
            } elseif (!in_array($mime, $allowed)) {
                $error = 'Unsupported image type.';
            } else {
                $image_path = process_image_fanmail($_FILES['photo']['tmp_name'], $mime, $upload_dir);
                if (!$image_path) $error = 'Could not process the image. Please try a different photo.';
            }
        }
        if (!$error) {
            $db->prepare("INSERT INTO messages (name, message, image_path) VALUES (?, ?, ?)")->execute([$name, $message, $image_path]);
            $success = true;
        }
    }
}

try {
    $photos = $db->query("SELECT id, name, message, image_path, created_at FROM messages WHERE image_path IS NOT NULL ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $photos = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Phanmail — Phantom Regiment 2026</title>
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
    .back-btn { color: var(--text-secondary); text-decoration: none; font-size: 14px; display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
    .back-btn:hover { color: var(--text); }
    .page-header h1 { font-family: 'Playfair Display', Georgia, serif; font-size: 20px; font-weight: 700; }
    .page-header .count { font-size: 13px; color: var(--text-muted); margin-left: auto; }

    .page-content { max-width: 1100px; margin: 0 auto; padding: 2rem 1.5rem; }

    .photo-grid { columns: 2; column-gap: 12px; }
    @media (min-width: 600px)  { .photo-grid { columns: 3; } }
    @media (min-width: 900px)  { .photo-grid { columns: 4; } }

    .photo-item { break-inside: avoid; margin-bottom: 12px; position: relative; border-radius: 12px; overflow: hidden; cursor: pointer; background: var(--surface-2); }
    .photo-item img { width: 100%; display: block; transition: transform 0.3s ease; }
    .photo-item:hover img { transform: scale(1.03); }
    .photo-caption { position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0) 50%); display: flex; flex-direction: column; justify-content: flex-end; padding: 12px; opacity: 0; transition: opacity 0.2s; }
    .photo-item:hover .photo-caption { opacity: 1; }
    .caption-name { font-size: 13px; font-weight: 700; color: #fff; line-height: 1.3; }
    .caption-msg  { font-size: 12px; color: rgba(255,255,255,0.75); line-height: 1.4; margin-top: 3px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

    .empty-state { text-align: center; padding: 5rem 1rem; color: var(--text-muted); }
    .empty-state p { font-size: 16px; margin-bottom: 1.25rem; }
    .empty-state a { color: var(--text-secondary); text-decoration: none; font-weight: 600; }
    .empty-state a:hover { color: var(--text); }

    /* Lightbox */
    .lightbox { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.94); z-index: 1000; flex-direction: column; align-items: center; justify-content: center; padding: 1rem; }
    .lightbox.open { display: flex; }
    .lightbox-img { max-width: 100%; max-height: 80vh; border-radius: 10px; object-fit: contain; }
    .lightbox-info { margin-top: 1rem; text-align: center; }
    .lightbox-name { font-weight: 700; font-size: 16px; color: #fff; }
    .lightbox-msg  { font-size: 14px; color: rgba(255,255,255,0.65); margin-top: 5px; max-width: 480px; line-height: 1.5; }
    .lightbox-close { position: fixed; top: 1rem; right: 1.25rem; background: none; border: none; color: white; font-size: 2rem; cursor: pointer; opacity: 0.7; line-height: 1; }
    .lightbox-close:hover { opacity: 1; }
    .lightbox-nav { position: fixed; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.12); border: none; color: #fff; font-size: 1.5rem; width: 44px; height: 44px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.15s; }
    .lightbox-nav:hover { background: rgba(255,255,255,0.25); }
    .lightbox-nav.prev { left: 1rem; }
    .lightbox-nav.next { right: 1rem; }

    .form-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 4px 16px rgba(0,0,0,0.4); }
    .form-card h2 { font-family: 'Playfair Display', Georgia, serif; font-size: 20px; margin-bottom: 0.3rem; }
    .form-card .subtitle { font-size: 14px; color: var(--text-secondary); margin-bottom: 1.25rem; line-height: 1.5; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
    @media (max-width: 540px) { .form-row { grid-template-columns: 1fr; } }
    .form-group { margin-bottom: 0.85rem; }
    label { display: block; font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 5px; }
    input[type=text], textarea { width: 100%; background: var(--surface-2); border: 1px solid var(--border); border-radius: 10px; color: var(--text); font-size: 15px; font-family: inherit; padding: 11px 13px; outline: none; transition: border-color 0.15s; }
    input[type=text]:focus, textarea:focus { border-color: rgba(176,26,28,0.6); }
    textarea { resize: vertical; min-height: 80px; line-height: 1.6; }
    .char-count { font-size: 11px; color: var(--text-muted); text-align: right; margin-top: 3px; }
    .photo-upload { background: var(--surface-2); border: 2px dashed var(--border); border-radius: 10px; padding: 0.9rem; text-align: center; cursor: pointer; position: relative; transition: border-color 0.15s; }
    .photo-upload:hover { border-color: rgba(176,26,28,0.5); }
    .photo-upload input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
    .photo-upload-label { font-size: 13px; color: var(--text-secondary); }
    .photo-upload-sub { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
    .photo-preview { display: none; margin-top: 0.6rem; }
    .photo-preview img { max-width: 100%; max-height: 160px; border-radius: 8px; object-fit: cover; }
    .submit-btn { width: 100%; padding: 13px; background: var(--red); color: #fff; border: none; border-radius: 10px; font-size: 15px; font-weight: 600; cursor: pointer; transition: background 0.15s; margin-top: 0.5rem; }
    .submit-btn:hover { background: var(--red-dark); }
    .alert { padding: 11px 13px; border-radius: 10px; font-size: 14px; margin-bottom: 1rem; }
    .alert-error   { background: rgba(176,26,28,0.15); border: 1px solid rgba(176,26,28,0.3); color: #E07070; }
    .alert-success { background: rgba(125,217,162,0.12); border: 1px solid rgba(125,217,162,0.3); color: #7DD9A2; }

    .section-label { font-size: 11px; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 1rem; }
  </style>
</head>
<body>

<div class="page-header">
  <a class="back-btn" href="/">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    Back
  </a>
  <h1>Phanmail</h1>
  <?php if ($photos): ?>
  <span class="count"><?= count($photos) ?> photo<?= count($photos) !== 1 ? 's' : '' ?></span>
  <?php endif; ?>
</div>

<div class="page-content">

  <div class="form-card">
    <h2>Send Matéo a Phanmail</h2>
    <p class="subtitle">Drop him a note — add a photo and it'll show up in the gallery below.</p>

    <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
    <div class="alert alert-success">Sent! <?= $image_path ? 'Your photo is in the gallery.' : 'Message delivered.' ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <div class="form-row">
        <div class="form-group">
          <label for="name">Your name</label>
          <input type="text" id="name" name="name" placeholder="Name" maxlength="80" autocomplete="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Photo <span style="font-weight:400;text-transform:none;letter-spacing:0;font-size:10px;">(optional)</span></label>
          <div class="photo-upload">
            <input type="file" name="photo" id="photo-input" accept="image/jpeg,image/png,image/gif,image/webp,image/heic,image/heif,.heic,.heif">
            <div class="photo-upload-label" id="upload-label">Tap to add a photo</div>
            <div class="photo-upload-sub">JPEG, PNG, WebP, HEIC · 20 MB max</div>
          </div>
          <div class="photo-preview" id="photo-preview">
            <img id="preview-img" src="" alt="Preview">
          </div>
        </div>
      </div>
      <div class="form-group">
        <label for="message">Message</label>
        <textarea id="message" name="message" placeholder="We're so proud of you…" maxlength="500" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
        <div class="char-count"><span id="char-count">0</span> / 500</div>
      </div>
      <button class="submit-btn" type="submit">Send message</button>
    </form>
  </div>

  <?php if (!empty($photos)): ?>
  <div class="section-label"><?= count($photos) ?> photo<?= count($photos) !== 1 ? 's' : '' ?> from fans</div>
  <?php endif; ?>

<?php if (empty($photos)): ?>
  <div class="empty-state">
    <p>No photos yet — be the first to send one!</p>
    <a href="/messages.php">Leave Matéo a message</a>
  </div>
<?php else: ?>
  <div class="photo-grid">
    <?php foreach ($photos as $i => $p): ?>
    <div class="photo-item" onclick="openLightbox(<?= $i ?>)">
      <img src="/data/uploads/<?= htmlspecialchars($p['image_path']) ?>" alt="Photo from <?= htmlspecialchars($p['name']) ?>" loading="lazy">
      <div class="photo-caption">
        <div class="caption-name"><?= htmlspecialchars($p['name']) ?></div>
        <div class="caption-msg"><?= htmlspecialchars($p['message']) ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

</div>

<!-- Lightbox -->
<div class="lightbox" id="lightbox" onclick="handleLightboxClick(event)">
  <button class="lightbox-close" onclick="closeLightbox()">&#215;</button>
  <button class="lightbox-nav prev" onclick="event.stopPropagation(); navLightbox(-1)">&#8592;</button>
  <img class="lightbox-img" id="lb-img" src="" alt="">
  <div class="lightbox-info">
    <div class="lightbox-name" id="lb-name"></div>
    <div class="lightbox-msg"  id="lb-msg"></div>
  </div>
  <button class="lightbox-nav next" onclick="event.stopPropagation(); navLightbox(1)">&#8594;</button>
</div>

<script>
  var ta = document.getElementById('message');
  var cc = document.getElementById('char-count');
  if (ta && cc) { ta.addEventListener('input', function(){ cc.textContent = ta.value.length; }); }

  document.getElementById('photo-input').addEventListener('change', function() {
    var file = this.files[0]; if (!file) return;
    var reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('preview-img').src = e.target.result;
      document.getElementById('photo-preview').style.display = 'block';
      document.getElementById('upload-label').textContent = file.name;
    };
    reader.readAsDataURL(file);
  });

  var photos = <?= json_encode(array_map(function($p) {
    return [
      'src'  => '/data/uploads/' . $p['image_path'],
      'name' => $p['name'],
      'msg'  => $p['message'],
    ];
  }, $photos)) ?>;

  var cur = 0;

  function openLightbox(idx) {
    cur = idx;
    render();
    document.getElementById('lightbox').classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function closeLightbox() {
    document.getElementById('lightbox').classList.remove('open');
    document.body.style.overflow = '';
  }
  function navLightbox(dir) {
    cur = (cur + dir + photos.length) % photos.length;
    render();
  }
  function render() {
    document.getElementById('lb-img').src  = photos[cur].src;
    document.getElementById('lb-name').textContent = photos[cur].name;
    document.getElementById('lb-msg').textContent  = photos[cur].msg;
    document.querySelector('.lightbox-nav.prev').style.display = photos.length < 2 ? 'none' : '';
    document.querySelector('.lightbox-nav.next').style.display = photos.length < 2 ? 'none' : '';
  }
  function handleLightboxClick(e) {
    if (e.target === document.getElementById('lightbox')) closeLightbox();
  }
  document.addEventListener('keydown', function(e) {
    if (!document.getElementById('lightbox').classList.contains('open')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft')  navLightbox(-1);
    if (e.key === 'ArrowRight') navLightbox(1);
  });
</script>
</body>
</html>
