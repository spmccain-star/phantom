#!/usr/bin/env php
<?php
/**
 * fetch_score.php — auto-fetch Phantom Regiment's latest DCI score + caption data
 * Runs nightly via cron: 0 23 * * * /usr/bin/php /var/www/phantom/fetch_score.php
 *
 * Data source: Competition Suite bridge API (no auth required)
 * Saves result to data/score.json (same format as manual admin entry)
 * Appends rich data to data/scores_history.json (captions, leaderboard)
 */

$ORG_ID     = '96b77ec2-333e-41e9-8d7d-806a8cbe116b';
$CORPS_NAME = 'Phantom Regiment';
$SCORE_FILE = __DIR__ . '/data/score.json';
$LOG_FILE   = __DIR__ . '/data/fetch_score.log';

function cs_get(string $endpoint, array $params): ?array {
    $url = 'https://bridge.competitionsuite.com/api/orgscores/' . $endpoint . '/jsonp?' . http_build_query($params);
    $ctx = stream_context_create(['http' => [
        'timeout' => 15,
        'header'  => "accept: application/json\r\nAccepts: application/json\r\nUser-Agent: PhantomFanSite/1.0\r\n",
    ]]);
    $raw = @file_get_contents($url, false, $ctx);
    if (!$raw) return null;
    $raw = preg_replace('/^jQuery\(|\);?$/', '', trim($raw));
    return json_decode($raw, true) ?: null;
}

function log_msg(string $msg): void {
    global $LOG_FILE;
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
    file_put_contents($LOG_FILE, $line, FILE_APPEND);
    echo $line;
}

/**
 * Parse a Competition Suite recap HTML page.
 * HTML structure: one value per <tr> (single <td>); a corps block starts with
 * a 2-cell row [corps name, first sub-score]. Decimal values are caption totals.
 * Returns: { captions: {ge1,ge2,visual,music,percussion}, leaderboard: [{name,total,rank}] }
 */
function parse_recap(string $url, string $phantom_search = 'Phantom'): ?array {
    $ctx = stream_context_create(['http' => [
        'timeout' => 20,
        'header'  => "User-Agent: PhantomFanSite/1.0\r\nAccept: text/html\r\n",
    ]]);
    $html = @file_get_contents($url, false, $ctx);
    if (!$html) return null;

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    libxml_clear_errors();

    $leaderboard   = [];
    $current_corps = null;
    $current_vals  = [];

    foreach ($dom->getElementsByTagName('tr') as $row) {
        $cells = [];
        foreach ($row->getElementsByTagName('td') as $td) {
            $text = preg_replace('/[\x{00A0}\x{200B}]/u', '', trim($td->textContent));
            $text = trim($text);
            if ($text !== '') $cells[] = $text;
        }
        if (empty($cells)) continue;

        if (count($cells) >= 2 && !is_numeric($cells[0]) && is_numeric($cells[1])) {
            if ($current_corps !== null) {
                $leaderboard[] = ['name' => $current_corps] + extract_caption_data($current_vals);
            }
            $current_corps = $cells[0];
            $current_vals  = [(float)$cells[1]];
            for ($c = 2; $c < count($cells); $c++) {
                if (is_numeric($cells[$c])) $current_vals[] = (float)$cells[$c];
            }
        } elseif ($current_corps !== null && count($cells) === 1 && is_numeric($cells[0])) {
            $current_vals[] = (float)$cells[0];
        }
    }
    if ($current_corps !== null) {
        $leaderboard[] = ['name' => $current_corps] + extract_caption_data($current_vals);
    }

    if (empty($leaderboard)) return null;

    usort($leaderboard, fn($a, $b) => ($b['total'] ?? 0) <=> ($a['total'] ?? 0));
    foreach ($leaderboard as $i => &$corps) { $corps['rank'] = $i + 1; }
    unset($corps);

    $phantom_captions = null;
    foreach ($leaderboard as $corps) {
        if (stripos($corps['name'], $phantom_search) !== false) {
            $phantom_captions = [
                'ge1'        => $corps['ge1'],
                'ge2'        => $corps['ge2'],
                'visual'     => $corps['visual'],
                'music'      => $corps['music'],
                'percussion' => $corps['percussion'],
            ];
            break;
        }
    }

    $slim = array_map(fn($c) => ['name' => $c['name'], 'total' => $c['total'], 'rank' => $c['rank']], $leaderboard);
    return ['captions' => $phantom_captions, 'leaderboard' => $slim];
}

/**
 * Extract caption data from a flat list of numeric values from one corps recap block.
 * Caption totals are the decimal-valued entries (raw sub-scores are integers/ranks).
 * Order: GE1 tot, GE2 tot, VA tot, MA tot, MP tot (percussion), subtotal, total.
 */
function extract_caption_data(array $values): array {
    $decimals = [];
    foreach ($values as $v) {
        if ($v != (int)$v) $decimals[] = round($v, 3);
    }
    return [
        'ge1'        => $decimals[0] ?? null,
        'ge2'        => $decimals[1] ?? null,
        'visual'     => $decimals[2] ?? null,
        'music'      => $decimals[3] ?? null,
        'percussion' => $decimals[4] ?? null,
        'subtotal'   => $decimals[5] ?? null,
        'total'      => $decimals[6] ?? ($decimals[5] ?? null),
    ];
}

function ordinal(int $n): string {
    $s = ['th','st','nd','rd'];
    $v = $n % 100;
    return $n . ($s[($v - 20) % 10] ?? $s[$v] ?? $s[0]);
}

function send_score_alerts(string $score, string $placement, string $show): void {
    $db_file = __DIR__ . '/data/messages.db';
    if (!file_exists($db_file)) {
        log_msg('Alerts: no messages.db found, skipping');
        return;
    }

    $pdo = new PDO('sqlite:' . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT email, token FROM score_alerts WHERE active=1");
    $subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($subscribers)) {
        log_msg('Alerts: no active subscribers');
        return;
    }

    $site_url    = 'https://phantom.agavelabs.dev';
    $results_url = $site_url . '/index.php#results';
    $subject     = 'New Phantom Regiment score: ' . $score;

    $sent = 0;
    foreach ($subscribers as $row) {
        $email     = $row['email'];
        $token     = $row['token'];
        $unsub_url = $site_url . '/alerts.php?unsubscribe=' . urlencode($token);

        // Plain text body
        $text_body = implode("\n", [
            'Phantom Regiment — New Score Posted',
            '',
            'Score:     ' . $score,
            'Placement: ' . $placement,
            'Show:      ' . $show,
            '',
            'Full results: ' . $results_url,
            '',
            '---',
            'To stop receiving these alerts, unsubscribe here:',
            $unsub_url,
        ]);

        // HTML body
        $html_body = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>'
            . 'body{background:#111111;color:#F0F0F0;font-family:Roboto,Arial,sans-serif;margin:0;padding:0}'
            . '.wrap{max-width:480px;margin:40px auto;padding:0 16px}'
            . '.card{background:#1A1A1A;border:1px solid #2C2C2C;border-radius:8px;padding:32px}'
            . '.label{font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#9E9E9E;margin-bottom:4px}'
            . '.score{font-size:48px;font-weight:700;color:#B01A1C;line-height:1;margin-bottom:4px}'
            . '.placement{font-size:20px;color:#F0F0F0;margin-bottom:20px}'
            . '.show-name{font-size:14px;color:#9E9E9E;margin-bottom:28px}'
            . '.btn{display:inline-block;background:#B01A1C;color:#fff;text-decoration:none;'
            .      'font-weight:700;font-size:13px;letter-spacing:1px;text-transform:uppercase;'
            .      'padding:12px 24px;border-radius:4px}'
            . '.footer{margin-top:28px;font-size:12px;color:#9E9E9E;border-top:1px solid #2C2C2C;padding-top:16px}'
            . '.unsub{color:#9E9E9E}'
            . '</style></head><body><div class="wrap"><div class="card">'
            . '<div class="label">Phantom Regiment 2026</div>'
            . '<div class="score">' . htmlspecialchars($score) . '</div>'
            . '<div class="placement">' . htmlspecialchars($placement) . ' Place</div>'
            . '<div class="show-name">' . htmlspecialchars($show) . '</div>'
            . '<a href="' . htmlspecialchars($results_url) . '" class="btn">View Full Results</a>'
            . '<div class="footer">'
            . 'You\'re receiving this because you subscribed to score alerts at phantom.agavelabs.dev.<br>'
            . '<a href="' . htmlspecialchars($unsub_url) . '" class="unsub">Unsubscribe</a>'
            . '</div></div></div></body></html>';

        $boundary = 'boundary_' . bin2hex(random_bytes(8));
        $headers  = implode("\r\n", [
            'From: Phantom Regiment Alerts <noreply@phantom.agavelabs.dev>',
            'Reply-To: noreply@phantom.agavelabs.dev',
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        ]);

        $body = "--{$boundary}\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: 7bit\r\n\r\n"
            . $text_body . "\r\n"
            . "--{$boundary}\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: 7bit\r\n\r\n"
            . $html_body . "\r\n"
            . "--{$boundary}--";

        if (@mail($email, $subject, $body, $headers)) {
            $sent++;
        } else {
            log_msg('Alerts: mail() failed for ' . $email);
        }
    }

    log_msg("Alerts: sent {$sent}/" . count($subscribers) . ' emails');
}

// ──────────────────────────────────────────────────────────────

log_msg('Starting score fetch...');

$season = cs_get('GetCompetitionsByOrganization', ['organization' => $ORG_ID]);
if (!$season) {
    log_msg('ERROR: Could not reach Competition Suite API');
    exit(1);
}

$competitions = $season['competitions'] ?? [];
log_msg('Competitions found: ' . count($competitions));

if (empty($competitions)) {
    log_msg('No competitions released yet — season may not have started');
    exit(0);
}

// Walk competitions newest-first, find latest one with a Phantom score
$found       = null;
$found_round = null;

foreach (array_reverse($competitions) as $comp) {
    $guid = $comp['competitionGuid'] ?? '';
    if (!$guid) continue;

    $detail = cs_get('GetCompetition', ['competition' => $guid]);
    if (!$detail) continue;

    foreach ($detail['rounds'] ?? [] as $round) {
        foreach ($round['performances'] ?? [] as $perf) {
            if (stripos($perf['name'] ?? '', 'Phantom') !== false) {
                $found = [
                    'score'     => number_format((float)$perf['score'], 3),
                    'placement' => ordinal((int)$perf['rank']),
                    'show'      => $detail['name'] . ', ' . date('M j', strtotime($detail['competitionDate'])),
                    'updated'   => date('Y-m-d H:i'),
                    'auto'      => true,
                ];
                $found_round = $round;
                log_msg(sprintf('Found: %s — %.3f, rank %s at %s',
                    $perf['name'], $perf['score'], $perf['rank'], $detail['name']));
                break 3;
            }
        }
    }
    usleep(300000);
}

if (!$found) {
    log_msg('Phantom Regiment not found in any released competition');
    exit(0);
}

$existing = file_exists($SCORE_FILE) ? json_decode(file_get_contents($SCORE_FILE), true) : [];
if (($existing['score'] ?? '') === $found['score'] && ($existing['show'] ?? '') === $found['show']) {
    log_msg('Score unchanged — no update needed');
    exit(0);
}

file_put_contents($SCORE_FILE, json_encode($found, JSON_PRETTY_PRINT));
log_msg('Saved: ' . $found['score'] . ' | ' . $found['placement'] . ' | ' . $found['show']);

// Notify subscribers of the new score
try {
    send_score_alerts($found['score'], $found['placement'], $found['show']);
} catch (Throwable $e) {
    log_msg('Alert send error (non-fatal): ' . $e->getMessage());
}

// Fetch caption breakdown from recap URL
$recap_data = null;
$recap_url  = $found_round['fullRecapUrl'] ?? '';
if ($recap_url) {
    log_msg('Fetching recap: ' . $recap_url);
    $recap_data = parse_recap($recap_url);
    if ($recap_data) {
        $caps = $recap_data['captions'] ?? [];
        log_msg(sprintf('Captions — GE1:%.2f GE2:%.2f VA:%.2f MA:%.2f Perc:%.2f',
            $caps['ge1'] ?? 0, $caps['ge2'] ?? 0, $caps['visual'] ?? 0,
            $caps['music'] ?? 0, $caps['percussion'] ?? 0));
        log_msg('Leaderboard: ' . count($recap_data['leaderboard'] ?? []) . ' corps');
    } else {
        log_msg('Recap parse returned no data');
    }
} else {
    log_msg('No recap URL in round data');
}

// Append/update history (deduplicate by show name)
$hist_file = __DIR__ . '/data/scores_history.json';
$history   = file_exists($hist_file) ? json_decode(file_get_contents($hist_file), true) ?: [] : [];
$already   = false;
foreach ($history as &$h) {
    if ($h['show'] === $found['show']) {
        $h['score']     = $found['score'];
        $h['placement'] = $found['placement'];
        if ($recap_data) {
            $h['captions']    = $recap_data['captions'];
            $h['leaderboard'] = $recap_data['leaderboard'];
        }
        $already = true;
        log_msg('Updated existing history entry for: ' . $found['show']);
        break;
    }
}
unset($h);

if (!$already) {
    $entry = [
        'date'      => date('Y-m-d'),
        'score'     => $found['score'],
        'placement' => $found['placement'],
        'show'      => $found['show'],
    ];
    if ($recap_data) {
        $entry['captions']    = $recap_data['captions'];
        $entry['leaderboard'] = $recap_data['leaderboard'];
    }
    $history[] = $entry;
    log_msg('Appended to history (' . count($history) . ' total)');
}

file_put_contents($hist_file, json_encode($history, JSON_PRETTY_PRINT));
log_msg('Done.');
