<?php
/**
 * alerts.php — Score alert subscription management
 * Handles subscribe + unsubscribe for Phantom Regiment score notifications
 */

$DB_FILE = __DIR__ . '/data/messages.db';

function get_db(string $path): PDO {
    $pdo = new PDO('sqlite:' . $path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE TABLE IF NOT EXISTS score_alerts (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        email      TEXT NOT NULL UNIQUE,
        token      TEXT NOT NULL UNIQUE,
        active     INTEGER NOT NULL DEFAULT 1,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");
    return $pdo;
}

$db      = get_db($DB_FILE);
$message = null;
$error   = null;

// Handle unsubscribe via token in query string
if (!empty($_GET['unsubscribe'])) {
    $token = trim($_GET['unsubscribe']);
    $stmt  = $db->prepare('UPDATE score_alerts SET active=0 WHERE token=?');
    $stmt->execute([$token]);
    if ($stmt->rowCount() > 0) {
        $message = 'unsubscribed';
    } else {
        $error = 'That unsubscribe link is invalid or already used.';
    }
}

// Handle subscription form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check for existing subscription
        $check = $db->prepare('SELECT active FROM score_alerts WHERE email=?');
        $check->execute([$email]);
        $row = $check->fetch(PDO::FETCH_ASSOC);

        if ($row !== false) {
            if ((int)$row['active'] === 1) {
                $message = 'already';
            } else {
                // Re-activate
                $token = bin2hex(random_bytes(16));
                $db->prepare('UPDATE score_alerts SET active=1, token=? WHERE email=?')
                   ->execute([$token, $email]);
                $message = 'subscribed';
            }
        } else {
            $token = bin2hex(random_bytes(16));
            $db->prepare('INSERT INTO score_alerts (email, token) VALUES (?, ?)')
               ->execute([$email, $token]);
            $message = 'subscribed';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Score Alerts — Phantom Regiment 2026</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --page-bg: #111111;
            --surface: #1A1A1A;
            --surface-2: #242424;
            --red: #B01A1C;
            --red-hover: #C41E20;
            --text: #F0F0F0;
            --text-secondary: #9E9E9E;
            --border: #2C2C2C;
            --success: #2E7D32;
            --success-text: #A5D6A7;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: var(--page-bg);
            color: var(--text);
            font-family: 'Roboto', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }

        .site-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .site-header a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 14px;
            letter-spacing: 0.5px;
        }

        .site-header a:hover { color: var(--text); }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 32px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.5);
        }

        .card-title {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.3px;
            margin-bottom: 8px;
            color: var(--text);
        }

        .card-subtitle {
            font-size: 14px;
            color: var(--text-secondary);
            line-height: 1.5;
            margin-bottom: 28px;
        }

        .alert {
            border-radius: 6px;
            padding: 14px 16px;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 24px;
        }

        .alert-success {
            background: rgba(46,125,50,0.15);
            border: 1px solid rgba(46,125,50,0.4);
            color: var(--success-text);
        }

        .alert-info {
            background: rgba(176,26,28,0.12);
            border: 1px solid rgba(176,26,28,0.35);
            color: #EF9A9A;
        }

        .alert-error {
            background: rgba(176,26,28,0.12);
            border: 1px solid rgba(176,26,28,0.35);
            color: #EF9A9A;
        }

        .alert strong { display: block; margin-bottom: 4px; font-weight: 700; }

        .form-group { margin-bottom: 20px; }

        label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }

        input[type="email"] {
            width: 100%;
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 4px;
            color: var(--text);
            font-family: 'Roboto', sans-serif;
            font-size: 16px;
            padding: 12px 14px;
            outline: none;
            transition: border-color 0.15s;
        }

        input[type="email"]:focus {
            border-color: var(--red);
        }

        input[type="email"]::placeholder {
            color: var(--text-secondary);
        }

        button[type="submit"] {
            width: 100%;
            background: var(--red);
            border: none;
            border-radius: 4px;
            color: #fff;
            cursor: pointer;
            font-family: 'Roboto', sans-serif;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 1.2px;
            padding: 13px;
            text-transform: uppercase;
            transition: background 0.15s;
        }

        button[type="submit"]:hover { background: var(--red-hover); }

        .divider {
            border: none;
            border-top: 1px solid var(--border);
            margin: 28px 0;
        }

        .note {
            font-size: 12px;
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 24px;
            font-size: 13px;
            color: var(--text-secondary);
            text-decoration: none;
        }

        .back-link:hover { color: var(--text); }
    </style>
</head>
<body>

<div class="site-header">
    <a href="/index.php">Phantom Regiment 2026 Fan Tracker</a>
</div>

<div class="card">
    <div class="card-title">Score Alerts</div>
    <div class="card-subtitle">Get an email whenever Phantom Regiment posts a new score this season.</div>

    <?php if ($message === 'subscribed'): ?>
        <div class="alert alert-success">
            <strong>You're subscribed!</strong>
            You'll receive an email each time a new Phantom Regiment score is posted. Check your spam folder if you don't see the first alert.
        </div>
        <p class="note">To unsubscribe at any time, use the link included at the bottom of every alert email.</p>

    <?php elseif ($message === 'already'): ?>
        <div class="alert alert-info">
            <strong>Already subscribed</strong>
            That email address is already signed up for score alerts.
        </div>
        <p class="note">Use the unsubscribe link in any alert email if you'd like to stop receiving notifications.</p>

    <?php elseif ($message === 'unsubscribed'): ?>
        <div class="alert alert-info">
            <strong>Unsubscribed</strong>
            You've been removed from score alerts. You won't receive any more emails.
        </div>
        <p class="note">Changed your mind? Enter your email below to re-subscribe.</p>
        <hr class="divider">
        <form method="POST" action="/alerts.php">
            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required>
            </div>
            <button type="submit">Subscribe</button>
        </form>

    <?php else: ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/alerts.php">
            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <button type="submit">Subscribe to Score Alerts</button>
        </form>

        <hr class="divider">

        <p class="note">Alerts are sent when a new competition score is posted. Each email includes an unsubscribe link. No account required.</p>
    <?php endif; ?>
</div>

<a class="back-link" href="/index.php">Back to scores</a>

</body>
</html>
