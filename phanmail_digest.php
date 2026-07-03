#!/usr/bin/env php
<?php
/**
 * phanmail_digest.php — weekly fan message digest for Matéo
 * Cron: 0 7 * * * /usr/bin/php /var/www/phantom/phanmail_digest.php >> /var/www/phantom/data/phanmail.log 2>&1
 *
 * Settings stored in data/phanmail_settings.json
 * Frequency: daily | weekly (Sunday) | biweekly (every other Sunday)
 * Only sends if there are new messages since the last digest.
 * Pass --force to send immediately regardless of schedule (for testing).
 */

$force = in_array('--force', $argv ?? []);
$log_file = __DIR__ . '/data/phanmail.log';

function pm_log(string $msg): void {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
    echo $line;
}

pm_log('Phanmail digest check' . ($force ? ' (--force)' : ''));

// Load settings
$settings_file = __DIR__ . '/data/phanmail_settings.json';
$settings = file_exists($settings_file) ? json_decode(file_get_contents($settings_file), true) ?: [] : [];

$recipient  = trim($settings['recipient_email'] ?? '');
$frequency  = $settings['frequency'] ?? 'weekly';
$last_sent  = $settings['last_sent'] ?? null;
$from_email = $settings['from_email'] ?? 'phanmail@phantom.agavelabs.dev';
$from_name  = $settings['from_name']  ?? 'Phanmail — Phantom Regiment';

if (!$recipient) {
    pm_log('No recipient email configured — skipping.');
    exit(0);
}

// Should we send today?
$now = time();
$should_send = false;

if ($force) {
    $should_send = true;
} elseif ($frequency === 'daily') {
    $should_send = !$last_sent || ($now - strtotime($last_sent)) >= 23 * 3600;
} elseif ($frequency === 'weekly') {
    $is_sunday   = date('w', $now) === '0';
    $should_send = $is_sunday && (!$last_sent || ($now - strtotime($last_sent)) >= 6 * 86400);
} elseif ($frequency === 'biweekly') {
    $is_sunday   = date('w', $now) === '0';
    $should_send = $is_sunday && (!$last_sent || ($now - strtotime($last_sent)) >= 13 * 86400);
}

if (!$should_send) {
    pm_log("Not sending today (frequency: $frequency, last sent: " . ($last_sent ?: 'never') . ")");
    exit(0);
}

// Query new messages since last digest
$db_path = __DIR__ . '/data/messages.db';
if (!file_exists($db_path)) {
    pm_log('No messages database found — skipping.');
    exit(0);
}
$db = new PDO('sqlite:' . $db_path);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($last_sent && !$force) {
    $stmt = $db->prepare("SELECT * FROM messages WHERE created_at > ? ORDER BY created_at ASC");
    $stmt->execute([$last_sent]);
} else {
    $stmt = $db->query("SELECT * FROM messages ORDER BY created_at ASC");
}
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($messages)) {
    pm_log('No new messages since last digest — not sending.');
    exit(0);
}

pm_log('New messages to send: ' . count($messages));

// Build subject
$count = count($messages);
$subject = $count === 1
    ? "1 new Phanmail message for Matéo"
    : "{$count} new Phanmail messages for Matéo";
if ($last_sent) {
    $subject .= ' — since ' . date('M j', strtotime($last_sent));
}

// Build HTML email
$since_label = $last_sent ? 'since ' . date('M j, Y', strtotime($last_sent)) : 'from all time';
$html = build_email_html($messages, $count, $since_label);

// Send
$smtp_host = trim($settings['smtp_host'] ?? '');
$smtp_port = (int)($settings['smtp_port'] ?? 587);
$smtp_user = trim($settings['smtp_user'] ?? '');
$smtp_pass = trim($settings['smtp_pass'] ?? '');

if ($smtp_host && $smtp_user) {
    pm_log("Sending via SMTP: {$smtp_host}:{$smtp_port}");
    $ok = smtp_send(
        ['host' => $smtp_host, 'port' => $smtp_port, 'user' => $smtp_user, 'pass' => $smtp_pass,
         'from_email' => $from_email, 'from_name' => $from_name],
        $recipient, $subject, $html
    );
} else {
    pm_log("Sending via PHP mail()");
    $headers  = "From: {$from_name} <{$from_email}>\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PhantomPhanmail/1.0\r\n";
    $ok = mail($recipient, $subject, $html, $headers);
}

if ($ok) {
    pm_log("Sent to {$recipient} ✓");
    $settings['last_sent'] = date('Y-m-d H:i:s');
    file_put_contents($settings_file, json_encode($settings, JSON_PRETTY_PRINT));
} else {
    pm_log("FAILED to send email");
    exit(1);
}

// ─────────────────────────────────────────────────────────────

function build_email_html(array $messages, int $count, string $since_label): string {
    $rows = '';
    foreach ($messages as $msg) {
        $name    = htmlspecialchars($msg['name']);
        $text    = nl2br(htmlspecialchars($msg['message']));
        $date    = date('M j, Y', strtotime($msg['created_at']));
        $has_photo = !empty($msg['image_path']);
        $photo_note = $has_photo
            ? '<p style="font-size:12px;color:#FFD700;margin-top:8px;">📷 Includes a photo — <a href="https://phantom.agavelabs.dev/fanmail.php" style="color:#FFD700;">view it on the site</a></p>'
            : '';
        $rows .= <<<MSG
        <tr>
          <td style="padding:0 0 16px 0;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#1C1C1E;border:1px solid rgba(255,255,255,0.08);border-radius:12px;overflow:hidden;">
              <tr>
                <td style="padding:16px 20px;">
                  <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                      <td style="font-size:15px;font-weight:700;color:#F2F0EA;">{$name}</td>
                      <td align="right" style="font-size:12px;color:#666360;">{$date}</td>
                    </tr>
                  </table>
                  <p style="font-size:15px;color:#A8A49C;line-height:1.65;margin-top:8px;">{$text}</p>
                  {$photo_note}
                </td>
              </tr>
            </table>
          </td>
        </tr>
MSG;
    }

    $plural = $count === 1 ? 'message' : 'messages';
    $year = date('Y');

    return <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Phanmail Digest</title>
</head>
<body style="margin:0;padding:0;background:#111111;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#111111;padding:24px 16px;">
    <tr>
      <td align="center">
        <table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;">

          <!-- Header -->
          <tr>
            <td style="padding:0 0 24px 0;">
              <table width="100%" cellpadding="0" cellspacing="0" style="background:#1A1A1A;border:1px solid rgba(255,255,255,0.08);border-radius:16px;overflow:hidden;">
                <tr>
                  <td style="background:#B01A1C;padding:4px 0;"></td>
                </tr>
                <tr>
                  <td style="padding:28px 28px 24px;">
                    <p style="font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:rgba(176,26,28,0.8);margin:0 0 6px;">Phantom Regiment 2026</p>
                    <h1 style="font-size:32px;font-weight:900;color:#F2F0EA;margin:0 0 8px;letter-spacing:-0.02em;">Phanmail</h1>
                    <p style="font-size:15px;color:#A8A49C;margin:0;line-height:1.5;">
                      Matéo has <strong style="color:#F2F0EA;">{$count} new fan {$plural}</strong> {$since_label}.
                    </p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Messages -->
          <tr>
            <td>
              <table width="100%" cellpadding="0" cellspacing="0">
                {$rows}
              </table>
            </td>
          </tr>

          <!-- CTA -->
          <tr>
            <td style="padding:8px 0 24px;">
              <table width="100%" cellpadding="0" cellspacing="0" style="background:#1A1A1A;border:1px solid rgba(255,255,255,0.08);border-radius:12px;">
                <tr>
                  <td style="padding:20px 24px;" align="center">
                    <p style="font-size:14px;color:#A8A49C;margin:0 0 14px;">View photos, the full message board, and season updates at</p>
                    <a href="https://phantom.agavelabs.dev" style="display:inline-block;background:#B01A1C;color:#ffffff;text-decoration:none;font-size:14px;font-weight:700;padding:11px 24px;border-radius:20px;">
                      phantom.agavelabs.dev →
                    </a>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td align="center" style="padding:8px 0 0;">
              <p style="font-size:12px;color:#555;margin:0;line-height:1.6;">
                Phanmail digest · <a href="https://phantom.agavelabs.dev/admin.php" style="color:#555;">Manage settings</a>
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
}

/**
 * Pure-PHP SMTP client — no external dependencies.
 * Supports port 465 (SSL) and 587 (STARTTLS).
 */
function smtp_send(array $cfg, string $to, string $subject, string $html): bool {
    $host = $cfg['host'];
    $port = (int)($cfg['port'] ?? 587);
    $user = $cfg['user'];
    $pass = $cfg['pass'];
    $from_addr = $cfg['from_email'];
    $from_name = $cfg['from_name'];

    $prefix = ($port === 465) ? 'ssl://' : '';
    $sock   = @fsockopen($prefix . $host, $port, $errno, $errstr, 15);
    if (!$sock) {
        pm_log("SMTP connect failed: {$errstr} ({$errno})");
        return false;
    }
    stream_set_timeout($sock, 15);

    $r = fn() => rtrim(fgets($sock, 512));
    $w = function(string $s) use ($sock) { fwrite($sock, $s . "\r\n"); };

    $r(); // 220 greeting

    $w("EHLO localhost");
    while (true) { $l = $r(); if ($l[3] === ' ') break; } // consume multi-line EHLO

    if ($port === 587) {
        $w("STARTTLS");
        $r(); // 220 ready
        if (!stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            pm_log("STARTTLS failed");
            fclose($sock);
            return false;
        }
        $w("EHLO localhost");
        while (true) { $l = $r(); if ($l[3] === ' ') break; }
    }

    if ($user && $pass) {
        $w("AUTH LOGIN");
        $r();
        $w(base64_encode($user));
        $r();
        $w(base64_encode($pass));
        $auth = $r(); // 235 = success
        if (strpos($auth, '235') !== 0) {
            pm_log("SMTP auth failed: {$auth}");
            fclose($sock);
            return false;
        }
    }

    $w("MAIL FROM:<{$from_addr}>");
    $r();
    $w("RCPT TO:<{$to}>");
    $r();
    $w("DATA");
    $r(); // 354

    // Build the MIME message
    $boundary = bin2hex(random_bytes(8));
    $plain    = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html));
    $plain    = html_entity_decode($plain, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    $name_enc = '=?UTF-8?B?' . base64_encode($from_name) . '?=';
    $subj_enc = '=?UTF-8?B?' . base64_encode($subject) . '?=';

    $msg  = "Date: " . date('r') . "\r\n";
    $msg .= "From: {$name_enc} <{$from_addr}>\r\n";
    $msg .= "To: {$to}\r\n";
    $msg .= "Subject: {$subj_enc}\r\n";
    $msg .= "MIME-Version: 1.0\r\n";
    $msg .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
    $msg .= "\r\n";
    $msg .= "--{$boundary}\r\n";
    $msg .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $msg .= "Content-Transfer-Encoding: 8bit\r\n";
    $msg .= "\r\n" . $plain . "\r\n";
    $msg .= "--{$boundary}\r\n";
    $msg .= "Content-Type: text/html; charset=UTF-8\r\n";
    $msg .= "Content-Transfer-Encoding: base64\r\n";
    $msg .= "\r\n" . chunk_split(base64_encode($html)) . "\r\n";
    $msg .= "--{$boundary}--\r\n";

    // Dot-stuff and write body
    $lines = explode("\r\n", $msg);
    foreach ($lines as $line) {
        $w($line[0] === '.' ? '.' . $line : $line);
    }
    $w(".");

    $resp = $r(); // 250 OK
    $w("QUIT");
    $r();
    fclose($sock);

    pm_log("SMTP response: {$resp}");
    return strpos($resp, '250') === 0;
}
