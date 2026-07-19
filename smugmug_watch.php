#!/usr/bin/env php
<?php
/**
 * smugmug_watch.php — check phantomregiment.smugmug.com/2026 for new albums
 * Cron: 0 9 * * * /usr/bin/php /var/www/phantom/smugmug_watch.php >> /var/www/phantom/data/smugmug.log 2>&1
 */

$data_file    = __DIR__ . '/data/smugmug_albums.json';
$check_file   = __DIR__ . '/data/smugmug_check.json';
$smug_url     = 'https://phantomregiment.smugmug.com/2026';
$notify_email = 'spmccain@gmail.com';

// Reuse SMTP settings from phanmail if configured
$pm_file = __DIR__ . '/data/phanmail_settings.json';
$pm      = file_exists($pm_file) ? json_decode(file_get_contents($pm_file), true) ?: [] : [];

function sm_log(string $msg): void {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
}

sm_log('SmugMug check starting');

// ── Fetch the page ────────────────────────────────────────────────────────────
$ctx  = stream_context_create(['http' => [
    'method'  => 'GET',
    'header'  => "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36\r\nAccept: text/html,application/xhtml+xml\r\n",
    'timeout' => 25,
    'follow_location' => 1,
]]);
$html = @file_get_contents($smug_url, false, $ctx);
if (!$html) {
    sm_log('ERROR: Could not fetch SmugMug page');
    exit(1);
}
sm_log('Fetched ' . number_format(strlen($html)) . ' bytes');

// ── Extract albums ────────────────────────────────────────────────────────────
$albums = [];

// Pattern 1: JSON urlPath values embedded in the page data
if (preg_match_all('/"urlPath"\s*:\s*"(\/2026\/[^"\/]+\/?)"/', $html, $m)) {
    foreach ($m[1] as $p) $albums[rtrim($p, '/')] = true;
}

// Pattern 2: href links to /2026/SomeName or /2026/SomeName-n-ID
if (preg_match_all('#href="(https?://phantomregiment\.smugmug\.com)?(/2026/[A-Za-z0-9][A-Za-z0-9%\-]+)/?["\?]#', $html, $m)) {
    foreach ($m[2] as $p) $albums[rtrim($p, '/')] = true;
}

// Pattern 3: SmugMug node data blobs — "path":"/2026/..."
if (preg_match_all('/"(?:path|UrlPath|urlPath|WebUri)"\s*:\s*"(\/2026\/[A-Za-z0-9%\-]+)"/', $html, $m)) {
    foreach ($m[1] as $p) $albums[rtrim($p, '/')] = true;
}

$found = array_keys($albums);
sm_log('Extracted ' . count($found) . ' album path(s) from page');

// ── If no albums parsed, fall back to page-content hash ──────────────────────
$use_hash = empty($found);
if ($use_hash) {
    sm_log('No album links found — using page-content hash fallback');
    // Strip scripts/styles to reduce noise
    $stripped = preg_replace('#<(script|style)[^>]*>.*?</\1>#si', '', $html);
    $stripped = preg_replace('/\s+/', ' ', $stripped);
    $hash     = md5($stripped);
    $found    = ['__hash__:' . $hash];
}

// ── Compare with previous state ───────────────────────────────────────────────
$prev       = file_exists($data_file) ? json_decode(file_get_contents($data_file), true) ?: [] : [];
$prev_items = $prev['items'] ?? [];
$new_items  = array_values(array_diff($found, $prev_items));

sm_log('Previous items: ' . count($prev_items) . '  New: ' . count($new_items));

// ── Notify if anything new ────────────────────────────────────────────────────
if (!empty($new_items) && !(count($new_items) === 1 && str_starts_with($new_items[0], '__hash__:'))) {
    $count   = count($new_items);
    $subject = $count . ' new album' . ($count > 1 ? 's' : '') . ' on Phantom Regiment SmugMug!';

    $lines = '';
    foreach ($new_items as $path) {
        if (str_starts_with($path, '__hash__:')) continue;
        $lines .= "\n  • https://phantomregiment.smugmug.com{$path}";
    }

    $body_plain = "New photos of Matéo may be available!\n\n"
        . "New album(s) detected on phantomregiment.smugmug.com/2026:{$lines}\n\n"
        . "View all 2026 photos: {$smug_url}\n\n"
        . "— Phantom fan site";

    $body_html = '<!DOCTYPE html><html><body style="background:#111;color:#F2F0EA;font-family:sans-serif;padding:24px;">'
        . '<div style="max-width:520px;margin:0 auto;">'
        . '<p style="font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(176,26,28,.8);margin:0 0 6px;">Phantom Regiment 2026</p>'
        . '<h1 style="font-size:24px;font-weight:900;color:#F2F0EA;margin:0 0 16px;">New SmugMug album'
        . ($count > 1 ? 's' : '') . ' detected</h1>'
        . '<p style="color:#A8A49C;font-size:15px;margin:0 0 18px;">New content was found at <strong style="color:#F2F0EA;">phantomregiment.smugmug.com/2026</strong>. Matéo photos may have been posted!</p>'
        . '<ul style="padding:0;list-style:none;margin:0 0 18px;">';
    foreach ($new_items as $path) {
        if (str_starts_with($path, '__hash__:')) continue;
        $url = 'https://phantomregiment.smugmug.com' . $path;
        $label = trim(str_replace(['/2026/', '-'], ['', ' '], $path));
        $body_html .= '<li style="margin-bottom:8px;"><a href="' . htmlspecialchars($url)
            . '" style="color:#7DD9A2;text-decoration:none;font-size:14px;">' . htmlspecialchars($label) . ' →</a></li>';
    }
    $body_html .= '</ul>'
        . '<a href="' . $smug_url . '" style="display:inline-block;background:#B01A1C;color:#fff;text-decoration:none;font-size:14px;font-weight:700;padding:10px 20px;border-radius:18px;">View 2026 Gallery →</a>'
        . '</div></body></html>';

    $sent = false;
    $smtp_host = trim($pm['smtp_host'] ?? '');
    $smtp_user = trim($pm['smtp_user'] ?? '');
    if ($smtp_host && $smtp_user) {
        sm_log("Sending via SMTP: {$smtp_host}:{$pm['smtp_port']}");
        $sent = smtp_send_sm(
            ['host'=>$smtp_host,'port'=>(int)($pm['smtp_port']??587),'user'=>$smtp_user,'pass'=>$pm['smtp_pass']??'',
             'from_email'=>$pm['from_email']??'noreply@phantom.agavelabs.dev','from_name'=>'Phantom Fan Site'],
            $notify_email, $subject, $body_html, $body_plain
        );
    } else {
        sm_log('Sending via PHP mail()');
        $headers  = "From: Phantom Fan Site <noreply@phantom.agavelabs.dev>\r\n";
        $headers .= "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
        $sent = mail($notify_email, $subject, $body_html, $headers);
    }
    sm_log($sent ? "Email sent to {$notify_email}" : 'ERROR: Email failed');
} elseif ($use_hash) {
    // Hash changed (page content changed but no album links found)
    $prev_hash = '';
    foreach ($prev_items as $item) {
        if (str_starts_with($item, '__hash__:')) { $prev_hash = $item; break; }
    }
    if ($prev_hash && $found[0] !== $prev_hash) {
        sm_log('Page content changed (hash) — sending heads-up');
        $subject = 'SmugMug 2026 page may have new content';
        $body    = "The content of phantomregiment.smugmug.com/2026 has changed.\n\nCheck it manually: {$smug_url}\n\n— Phantom fan site";
        $headers = "From: Phantom Fan Site <noreply@phantom.agavelabs.dev>\r\n";
        mail($notify_email, $subject, $body, $headers);
        sm_log('Hash-change email sent');
    } else {
        sm_log('No changes detected (hash unchanged)');
    }
} else {
    sm_log('No new albums');
}

// ── Persist state ─────────────────────────────────────────────────────────────
$all_items = array_values(array_unique(array_merge($prev_items, $found)));
file_put_contents($data_file, json_encode([
    'items'      => $all_items,
    'last_check' => date('Y-m-d H:i:s'),
    'last_count' => count(array_filter($found, fn($i) => !str_starts_with($i, '__hash__:'))),
], JSON_PRETTY_PRINT));

// Also update the admin check timestamp
file_put_contents($check_file, json_encode(['last_checked' => date('Y-m-d H:i:s')], JSON_PRETTY_PRINT));
sm_log('Done');

// ── Minimal SMTP helper (mirrors phanmail_digest.php) ────────────────────────
function smtp_send_sm(array $cfg, string $to, string $subject, string $html, string $plain): bool {
    $prefix = ($cfg['port'] === 465) ? 'ssl://' : '';
    $sock   = @fsockopen($prefix . $cfg['host'], $cfg['port'], $errno, $errstr, 15);
    if (!$sock) return false;
    stream_set_timeout($sock, 15);
    $r = fn() => rtrim(fgets($sock, 512));
    $w = fn(string $s) => fwrite($sock, $s . "\r\n");
    $r();
    $w("EHLO localhost");
    while (true) { $l = $r(); if (strlen($l) > 3 && $l[3] === ' ') break; }
    if ($cfg['port'] === 587) {
        $w("STARTTLS"); $r();
        if (!stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) { fclose($sock); return false; }
        $w("EHLO localhost");
        while (true) { $l = $r(); if (strlen($l) > 3 && $l[3] === ' ') break; }
    }
    $w("AUTH LOGIN"); $r();
    $w(base64_encode($cfg['user'])); $r();
    $w(base64_encode($cfg['pass']));
    $auth = $r();
    if (strpos($auth, '235') !== 0) { fclose($sock); return false; }
    $w("MAIL FROM:<{$cfg['from_email']}>"); $r();
    $w("RCPT TO:<{$to}>"); $r();
    $w("DATA"); $r();
    $boundary = bin2hex(random_bytes(8));
    $ne = '=?UTF-8?B?' . base64_encode($cfg['from_name']) . '?=';
    $se = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $msg  = "Date: " . date('r') . "\r\nFrom: {$ne} <{$cfg['from_email']}>\r\nTo: {$to}\r\nSubject: {$se}\r\n";
    $msg .= "MIME-Version: 1.0\r\nContent-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n\r\n";
    $msg .= "--{$boundary}\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n{$plain}\r\n";
    $msg .= "--{$boundary}\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n" . chunk_split(base64_encode($html)) . "\r\n";
    $msg .= "--{$boundary}--\r\n";
    foreach (explode("\r\n", $msg) as $line) $w(strlen($line) && $line[0] === '.' ? '.' . $line : $line);
    $w(".");
    $resp = $r(); $w("QUIT"); $r(); fclose($sock);
    return strpos($resp, '250') === 0;
}
