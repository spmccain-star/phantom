<?php
$db = new PDO('sqlite:' . __DIR__ . '/data/messages.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
  <title>Fanmail — Phantom Regiment 2026</title>
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
  </style>
</head>
<body>

<div class="page-header">
  <a class="back-btn" href="/">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    Back
  </a>
  <h1>Fanmail</h1>
  <?php if ($photos): ?>
  <span class="count"><?= count($photos) ?> photo<?= count($photos) !== 1 ? 's' : '' ?></span>
  <?php endif; ?>
</div>

<div class="page-content">

<?php if (empty($photos)): ?>
  <div class="empty-state">
    <p>No photos yet — be the first to send one!</p>
    <a href="/messages.php">Leave Mateo a message</a>
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
