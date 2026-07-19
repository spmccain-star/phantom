<?php
session_start();

$pass_file = __DIR__ . '/data/.adminpass';
$admin_password = file_exists($pass_file) ? trim(file_get_contents($pass_file)) : '';
$score_file = __DIR__ . '/data/score.json';

$msg = '';

// Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pin'])) {
    if ($admin_password && $_POST['pin'] === $admin_password) {
        $_SESSION['score_admin'] = true;
    } else {
        $msg = 'Wrong PIN.';
    }
}

// Save score
if (!empty($_SESSION['score_admin']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['score_text'])) {
    $data = json_encode([
        'score'     => trim($_POST['score_text']),
        'placement' => trim($_POST['score_placement']),
        'show'      => trim($_POST['score_show']),
        'updated'   => date('Y-m-d H:i'),
    ]);
    file_put_contents($score_file, $data);
    $msg = 'saved';
}

// Clear score
if (!empty($_SESSION['score_admin']) && isset($_POST['clear'])) {
    file_put_contents($score_file, '{}');
    $msg = 'cleared';
}

$score = file_exists($score_file) ? json_decode(file_get_contents($score_file), true) ?: [] : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
  <title>Score Update</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root { --red: #B01A1C; --bg: #111; --surface: #1C1C1E; --border: rgba(255,255,255,0.12); --text: #F2F0EA; --muted: #888; --gold: #FFD700; --radius: 14px; }
    body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; padding: 2rem 1rem 4rem; }
    h1 { font-size: 17px; font-weight: 700; margin-bottom: 1.5rem; color: var(--gold); letter-spacing: 0.02em; }
    .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.5rem; width: 100%; max-width: 360px; }
    label { display: block; font-size: 11px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 5px; }
    input[type=text], input[type=password] { width: 100%; background: #2C2C2E; border: 1px solid var(--border); border-radius: 10px; color: var(--text); font-size: 18px; font-weight: 600; padding: 14px 16px; outline: none; margin-bottom: 1rem; -webkit-appearance: none; }
    input[type=text]::placeholder, input[type=password]::placeholder { color: var(--muted); font-weight: 400; }
    input[type=text]:focus, input[type=password]:focus { border-color: rgba(176,26,28,0.6); }
    .row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .btn { width: 100%; padding: 15px; font-size: 16px; font-weight: 700; border: none; border-radius: 10px; cursor: pointer; -webkit-appearance: none; }
    .btn-primary { background: var(--red); color: #fff; margin-top: 0.25rem; }
    .btn-clear { background: #2C2C2E; color: var(--muted); font-size: 13px; font-weight: 600; margin-top: 0.75rem; padding: 11px; }
    .current { background: #1a1a10; border: 1px solid rgba(255,215,0,0.3); border-radius: var(--radius); padding: 1rem 1.25rem; margin-bottom: 1.5rem; width: 100%; max-width: 360px; }
    .current-score { font-size: 30px; font-weight: 800; color: var(--gold); line-height: 1; }
    .current-meta { font-size: 12px; color: var(--muted); margin-top: 4px; }
    .toast { background: #1a3a1a; border: 1px solid rgba(50,200,50,0.4); border-radius: 10px; padding: 12px 16px; margin-bottom: 1.25rem; width: 100%; max-width: 360px; font-size: 14px; color: #90EE90; font-weight: 600; text-align: center; }
    .toast.err { background: #3a1a1a; border-color: rgba(200,50,50,0.4); color: #EE9090; }
    .link { display: block; text-align: center; margin-top: 1.5rem; font-size: 12px; color: var(--muted); text-decoration: none; }
    .link:hover { color: var(--text); }
  </style>
</head>
<body>

<h1>Phantom · Score Update</h1>

<?php if (!empty($msg) && $msg === 'saved'): ?>
<div class="toast">Score saved</div>
<?php elseif (!empty($msg) && $msg === 'cleared'): ?>
<div class="toast">Score cleared</div>
<?php elseif (!empty($msg)): ?>
<div class="toast err"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<?php if (empty($_SESSION['score_admin'])): ?>

<div class="card">
  <label>Admin PIN</label>
  <form method="POST">
    <input type="password" name="pin" placeholder="Enter PIN" inputmode="numeric" autofocus autocomplete="off" />
    <button class="btn btn-primary" type="submit">Unlock</button>
  </form>
</div>

<?php else: ?>

<?php if (!empty($score['score'])): ?>
<div class="current">
  <div class="current-score"><?= htmlspecialchars($score['score']) ?></div>
  <div class="current-meta">
    <?= htmlspecialchars($score['placement'] ?? '') ?> place
    &nbsp;·&nbsp; <?= htmlspecialchars($score['show'] ?? '') ?>
    <?php if (!empty($score['updated'])): ?>&nbsp;·&nbsp; updated <?= htmlspecialchars($score['updated']) ?><?php endif; ?>
  </div>
</div>
<?php endif; ?>

<div class="card">
  <form method="POST">
    <label>Score</label>
    <input type="text" name="score_text" placeholder="e.g. 74.350" inputmode="decimal"
      value="<?= htmlspecialchars($score['score'] ?? '') ?>" autocomplete="off" />

    <div class="row">
      <div>
        <label>Placement</label>
        <input type="text" name="score_placement" placeholder="e.g. 8th" inputmode="text"
          value="<?= htmlspecialchars($score['placement'] ?? '') ?>" autocomplete="off" />
      </div>
      <div>
        <label>Show / City</label>
        <input type="text" name="score_show" placeholder="e.g. Rockford, Jul 3" inputmode="text"
          value="<?= htmlspecialchars($score['show'] ?? '') ?>" autocomplete="off" />
      </div>
    </div>

    <button class="btn btn-primary" type="submit">Save Score</button>
  </form>

  <form method="POST">
    <button class="btn btn-clear" type="submit" name="clear" value="1"
      onclick="return confirm('Clear the current score?')">Clear score</button>
  </form>
</div>

<a class="link" href="/admin.php">Full admin panel →</a>
<a class="link" href="/" style="margin-top:4px;">View site →</a>

<?php endif; ?>

</body>
</html>
