<?php
session_start();

$pass_file = __DIR__ . '/data/.adminpass';
$admin_password = file_exists($pass_file) ? trim(file_get_contents($pass_file)) : '';

$db = new PDO('sqlite:' . __DIR__ . '/data/messages.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("CREATE TABLE IF NOT EXISTS messages (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, message TEXT NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)");

// Login / logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($admin_password && $_POST['password'] === $admin_password) {
        $_SESSION['phantom_admin'] = true;
    } else {
        $login_error = 'Wrong password.';
    }
}
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: /admin.php');
    exit;
}

// Delete
if (!empty($_SESSION['phantom_admin']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $stmt = $db->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->execute([(int)$_POST['delete_id']]);
    header('Location: /admin.php');
    exit;
}

// Edit save
if (!empty($_SESSION['phantom_admin']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $stmt = $db->prepare("UPDATE messages SET name = ?, message = ? WHERE id = ?");
    $stmt->execute([trim($_POST['edit_name']), trim($_POST['edit_message']), (int)$_POST['edit_id']]);
    header('Location: /admin.php');
    exit;
}

// Delete image only
if (!empty($_SESSION['phantom_admin']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_image_id'])) {
    $row = $db->prepare("SELECT image_path FROM messages WHERE id = ?");
    $row->execute([(int)$_POST['delete_image_id']]);
    $r = $row->fetch(PDO::FETCH_ASSOC);
    if ($r && $r['image_path']) {
        $f = __DIR__ . '/data/uploads/' . basename($r['image_path']);
        if (file_exists($f)) unlink($f);
    }
    $db->prepare("UPDATE messages SET image_path = NULL WHERE id = ?")->execute([(int)$_POST['delete_image_id']]);
    header('Location: /admin.php');
    exit;
}

$messages = !empty($_SESSION['phantom_admin'])
    ? $db->query("SELECT * FROM messages ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC)
    : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin — Phantom Messages</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --page-bg: #111; --surface: #1C1C1E; --surface-2: #2C2C2E;
      --border: rgba(255,255,255,0.1); --text: #F2F0EA;
      --text-secondary: #A0A0A5; --text-muted: #666;
      --red: #B01A1C; --red-dark: #8B1416; --radius: 12px;
    }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: var(--page-bg); color: var(--text); min-height: 100vh; padding-bottom: 3rem; }

    .page-header { background: var(--surface); border-bottom: 1px solid var(--border); padding: 1rem 1.5rem; display: flex; align-items: center; justify-content: space-between; }
    .page-header h1 { font-size: 18px; font-weight: 700; }
    .logout-btn { font-size: 13px; color: var(--text-muted); text-decoration: none; }
    .logout-btn:hover { color: var(--text); }

    .content { max-width: 760px; margin: 0 auto; padding: 2rem 1.5rem; }

    .login-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 2rem; max-width: 360px; margin: 5rem auto; }
    .login-card h2 { font-size: 20px; margin-bottom: 1.25rem; }
    input[type=password] { width: 100%; background: var(--surface-2); border: 1px solid var(--border); border-radius: 8px; color: var(--text); font-size: 15px; padding: 11px 14px; outline: none; margin-bottom: 1rem; }
    input[type=password]:focus { border-color: rgba(176,26,28,0.6); }
    .btn-primary { background: var(--red); color: #fff; width: 100%; padding: 11px; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
    .btn-primary:hover { background: var(--red-dark); }
    .error { color: #E07070; font-size: 13px; margin-bottom: 0.75rem; }

    .stats { font-size: 13px; color: var(--text-muted); margin-bottom: 1.5rem; }
    .msg-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 1rem 1.25rem; margin-bottom: 0.75rem; }
    .msg-meta { display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 0.5rem; gap: 1rem; }
    .msg-name { font-weight: 700; font-size: 15px; }
    .msg-date { font-size: 12px; color: var(--text-muted); white-space: nowrap; }
    .msg-text { font-size: 14px; color: var(--text-secondary); line-height: 1.6; margin-bottom: 0.75rem; }
    .msg-img { max-width: 100%; max-height: 260px; object-fit: cover; border-radius: 8px; display: block; margin-bottom: 0.75rem; }
    .msg-img-row { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 0.75rem; }
    .msg-img-row img { max-width: 180px; max-height: 140px; object-fit: cover; border-radius: 8px; flex-shrink: 0; }
    .msg-actions { display: flex; gap: 8px; }
    .btn-sm { padding: 6px 14px; font-size: 12px; font-weight: 600; border: none; border-radius: 6px; cursor: pointer; }
    .btn-edit { background: var(--surface-2); color: var(--text); }
    .btn-edit:hover { background: #3a3a3c; }
    .btn-delete { background: rgba(176,26,28,0.2); color: #E07070; }
    .btn-delete:hover { background: rgba(176,26,28,0.4); }

    .edit-form { display: none; margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid var(--border); }
    .edit-form.open { display: block; }
    .edit-form input, .edit-form textarea { width: 100%; background: var(--surface-2); border: 1px solid var(--border); border-radius: 8px; color: var(--text); font-size: 14px; font-family: inherit; padding: 9px 12px; outline: none; margin-bottom: 8px; }
    .edit-form textarea { min-height: 80px; resize: vertical; line-height: 1.5; }
    .edit-form input:focus, .edit-form textarea:focus { border-color: rgba(176,26,28,0.5); }
    .edit-actions { display: flex; gap: 8px; }
    .btn-save { background: var(--red); color: #fff; }
    .btn-save:hover { background: var(--red-dark); }
    .btn-cancel { background: var(--surface-2); color: var(--text-secondary); }

    .empty { text-align: center; padding: 3rem; color: var(--text-muted); font-size: 15px; }
  </style>
</head>
<body>

<?php if (empty($_SESSION['phantom_admin'])): ?>

<div style="padding:1rem 1.5rem;background:var(--surface);border-bottom:1px solid var(--border);">
  <strong style="font-size:16px;">Phantom Admin</strong>
</div>
<div class="content">
  <div class="login-card">
    <h2>Sign in</h2>
    <?php if (!empty($login_error)): ?>
    <div class="error"><?= htmlspecialchars($login_error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <input type="password" name="password" placeholder="Password" autofocus required />
      <button class="btn-primary" type="submit">Enter</button>
    </form>
  </div>
</div>

<?php else: ?>

<div class="page-header">
  <h1>Message Board Admin</h1>
  <a class="logout-btn" href="/admin.php?logout=1">Sign out</a>
</div>

<div class="content">
  <div class="stats">
    <?= count($messages) ?> message<?= count($messages) !== 1 ? 's' : '' ?> &nbsp;·&nbsp;
    <a href="/messages.php" style="color:var(--text-muted);">View public page</a>
  </div>

  <?php if (empty($messages)): ?>
  <div class="empty">No messages yet.</div>
  <?php else: ?>
  <?php foreach ($messages as $msg): ?>
  <div class="msg-card">
    <div class="msg-meta">
      <span class="msg-name"><?= htmlspecialchars($msg['name']) ?></span>
      <span class="msg-date"><?= date('M j, Y g:i A', strtotime($msg['created_at'])) ?></span>
    </div>
    <div class="msg-text"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
    <?php if (!empty($msg['image_path'])): ?>
    <div class="msg-img-row">
      <img src="/data/uploads/<?= htmlspecialchars($msg['image_path']) ?>" alt="">
      <form method="POST" onsubmit="return confirm('Remove this image?')">
        <input type="hidden" name="delete_image_id" value="<?= $msg['id'] ?>">
        <button class="btn-sm btn-delete" type="submit" style="margin-top:4px;">Remove image</button>
      </form>
    </div>
    <?php endif; ?>
    <div class="msg-actions">
      <button class="btn-sm btn-edit" onclick="toggleEdit(<?= $msg['id'] ?>)">Edit</button>
      <form method="POST" style="display:inline" onsubmit="return confirm('Delete this message?')">
        <input type="hidden" name="delete_id" value="<?= $msg['id'] ?>">
        <button class="btn-sm btn-delete" type="submit">Delete</button>
      </form>
    </div>
    <div class="edit-form" id="edit-<?= $msg['id'] ?>">
      <form method="POST">
        <input type="hidden" name="edit_id" value="<?= $msg['id'] ?>">
        <input type="text" name="edit_name" value="<?= htmlspecialchars($msg['name']) ?>" placeholder="Name" required>
        <textarea name="edit_message" required><?= htmlspecialchars($msg['message']) ?></textarea>
        <div class="edit-actions">
          <button class="btn-sm btn-save" type="submit">Save</button>
          <button class="btn-sm btn-cancel" type="button" onclick="toggleEdit(<?= $msg['id'] ?>)">Cancel</button>
        </div>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php endif; ?>

<script>
  function toggleEdit(id) {
    document.getElementById('edit-' + id).classList.toggle('open');
  }
</script>
</body>
</html>
