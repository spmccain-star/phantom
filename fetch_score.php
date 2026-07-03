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
