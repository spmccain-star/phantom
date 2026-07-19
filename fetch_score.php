#!/usr/bin/env php
<?php
/**
 * fetch_score.php — fetch Phantom Regiment's latest DCI score
 * Cron: 0 4 * * * /usr/bin/php /var/www/phantom/fetch_score.php >> /var/www/phantom/data/fetch_score.log 2>&1
 *
 * Strategy (new, post-api.dci.org decommission):
 *   1. POST to dci.org/wp-admin/admin-ajax.php?action=score_events (nonce from page HTML)
 *   2. Parse HTML response to find competition slugs
 *   3. Fetch /scores/recap/<slug>/ and parse for Phantom's total + placement
 *   4. Fall back to /scores/final-scores/<slug>/ if recap not available
 */

$SCORE_FILE  = __DIR__ . '/data/score.json';
$HIST_FILE   = __DIR__ . '/data/scores_history.json';
$CORPS_NAME  = 'Phantom';
$SEASON      = '2026';

function log_msg(string $msg): void {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
}

function dci_fetch(string $url, array $post = [], array $extra_headers = []): ?string {
    $headers = array_merge([
        'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
        'Accept: text/html,application/xhtml+xml,application/json,*/*',
        'Accept-Language: en-US,en;q=0.9',
        'Referer: https://www.dci.org/scores/',
    ], $extra_headers);

    $opts = ['http' => [
        'method'          => $post ? 'POST' : 'GET',
        'header'          => implode("\r\n", $headers),
        'timeout'         => 25,
        'follow_location' => 1,
        'ignore_errors'   => true,
    ]];
    if ($post) {
        $opts['http']['content'] = http_build_query($post);
        $opts['http']['header'] .= "\r\nContent-Type: application/x-www-form-urlencoded";
    }

    $result = @file_get_contents($url, false, stream_context_create($opts));
    // Cloudflare challenge detection
    if ($result && strpos($result, 'cf-browser-verification') !== false) {
        log_msg('WARNING: Cloudflare challenge detected');
        return null;
    }
    return $result ?: null;
}

function ordinal(int $n): string {
    $s = ['th','st','nd','rd'];
    $v = $n % 100;
    return $n . ($s[($v - 20) % 10] ?? $s[$v] ?? $s[0]);
}

// ── 1. Get fresh nonce from scores page ───────────────────────────────────────
log_msg('Fetching nonce from dci.org/scores/');
$scores_page = dci_fetch('https://www.dci.org/scores/');
$nonce = null;
if ($scores_page) {
    if (preg_match('/"nonce"\s*:\s*"([a-f0-9]+)"/', $scores_page, $m)) {
        $nonce = $m[1];
        log_msg("Nonce: {$nonce}");
    } else {
        log_msg('WARNING: nonce not found in page HTML');
    }
}

// ── 2. Get list of scored competitions ────────────────────────────────────────
$comp_slugs = [];

if ($nonce) {
    log_msg('Fetching score_events AJAX');
    $ajax_url = 'https://www.dci.org/wp-admin/admin-ajax.php';
    $page = 1;
    while (true) {
        $resp = dci_fetch($ajax_url, [
            'action'         => 'score_events',
            'nonce'          => $nonce,
            'post_type'      => 'competition',
            'posts_per_page' => 10,
            'paged'          => $page,
            'filter_season'  => $SEASON,
        ], ['Accept: application/json']);

        if (!$resp) { log_msg("AJAX page {$page}: no response"); break; }

        $data = json_decode($resp, true);
        if (!isset($data['data']['content'])) {
            log_msg("AJAX page {$page}: unexpected format — " . substr($resp, 0, 80));
            break;
        }
        $html = $data['data']['content'];
        if (strpos($html, 'No score found') !== false) {
            log_msg("AJAX page {$page}: no scores yet");
            break;
        }

        // Extract event slugs from href="/scores/final-scores/<slug>/" links
        preg_match_all('#/scores/(?:final-scores|recap)/([a-z0-9\-]+)/#', $html, $m);
        $found = array_unique($m[1] ?? []);
        if (empty($found)) { log_msg("AJAX page {$page}: no slugs found in HTML"); break; }
        $comp_slugs = array_merge($comp_slugs, $found);
        log_msg("AJAX page {$page}: found " . count($found) . " slug(s)");

        $total = (int)($data['data']['total_pages'] ?? 1);
        if ($page >= $total) break;
        $page++;
        usleep(500000);
    }
    $comp_slugs = array_unique($comp_slugs);
}

// If AJAX gave nothing, try scraping the /scores/ page directly for links
if (empty($comp_slugs) && $scores_page) {
    preg_match_all('#/scores/(?:final-scores|recap)/([a-z0-9\-]+)/#', $scores_page, $m);
    $comp_slugs = array_unique($m[1] ?? []);
    if ($comp_slugs) log_msg('Extracted ' . count($comp_slugs) . ' slug(s) from scores page HTML');
}

if (empty($comp_slugs)) {
    log_msg('No competition slugs found — season may not have started yet');
    exit(0);
}

log_msg('Total competition slugs: ' . count($comp_slugs) . ' — ' . implode(', ', array_slice($comp_slugs, 0, 5)));

// ── 3. Find ALL competitions with Phantom, keep highest score ─────────────────
$found_score = null;
$all_found = [];

foreach ($comp_slugs as $slug) {
    // Try recap page first, then final-scores
    foreach (['recap', 'final-scores'] as $type) {
        $url = "https://www.dci.org/scores/{$type}/{$slug}/";
        log_msg("Fetching {$url}");
        $html = dci_fetch($url);
        if (!$html) { log_msg("  → no response"); continue; }
        if (strpos($html, 'Phantom') === false) { log_msg("  → Phantom not in page"); break; }

        // Parse ALL corps scores from recap table
        // DCI recap HTML: each corps has a <tr> with <td class="sticky-td">Corps Name</td>
        // followed by nested sub-tables for captions, and <td class="data data-total"> for total+rank.
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);

        // Helper: extract grand total score + rank from a recap row.
        // DCI recap rows have multiple data-total cells (one per caption group + grand total).
        // Grand total is always the highest value; rank comes from its span[1].
        $extract_row = function(DOMNode $row) use ($xpath): ?array {
            $total_cells = $xpath->query(".//td[contains(@class,'data-total')]", $row);
            $best_total = null; $best_rank = null;
            foreach ($total_cells as $tc) {
                $spans = $tc->getElementsByTagName('span');
                if ($spans->length >= 1) {
                    $v = (float)trim($spans->item(0)->textContent);
                    if ($v > ($best_total ?? 0)) {
                        $best_total = $v;
                        $best_rank = ($spans->length >= 2) ? (int)trim($spans->item(1)->textContent) : null;
                    }
                }
            }
            if (!$best_total) {
                preg_match_all('/\b(\d{2,3}\.\d+)\b/', $row->textContent, $vm);
                $decimals = array_filter(array_map('floatval', $vm[1] ?? []), fn($v) => $v > 50);
                if ($decimals) $best_total = max($decimals);
            }
            return $best_total ? ['total' => $best_total, 'rank' => $best_rank] : null;
        };

        // Build full leaderboard for this show
        $show_leaderboard = [];
        $all_name_cells = $xpath->query("//td[contains(@class,'sticky-td')]");
        foreach ($all_name_cells as $nc) {
            $corps_name = trim($nc->textContent);
            if (!$corps_name) continue;
            $row = $nc->parentNode;
            $data = $extract_row($row);
            if ($data) {
                $show_leaderboard[] = ['name' => $corps_name, 'total' => $data['total'], 'rank' => $data['rank']];
            }
        }

        // Sort by total descending, assign rank if missing
        usort($show_leaderboard, fn($a, $b) => $b['total'] <=> $a['total']);
        foreach ($show_leaderboard as $ri => &$rc) {
            if (!$rc['rank']) $rc['rank'] = $ri + 1;
        }
        unset($rc);
        log_msg("Leaderboard: " . count($show_leaderboard) . " corps");

        // Find Phantom's entry
        $phantom_entry = null;
        foreach ($show_leaderboard as $rc) {
            if (stripos($rc['name'], 'Phantom') !== false) { $phantom_entry = $rc; break; }
        }
        if (!$phantom_entry) { log_msg("  → Phantom not found in leaderboard"); break; }

        $total = $phantom_entry['total'];
        $rank  = $phantom_entry['rank'];

        // Show name from page <title>
        $show = '';
        $title_el = $dom->getElementsByTagName('title');
        if ($title_el->length) {
            $raw = $title_el->item(0)->textContent;
            $raw = preg_replace('/\s*[\|–\-]\s*(DCI|Drum Corps|Recap|Score).*$/i', '', $raw);
            $show = trim($raw);
        }
        if (!$show) $show = ucwords(str_replace('-', ' ', preg_replace('/^2026-/', '', $slug)));

        $found_score = [
            'score'       => number_format($total, 3),
            'placement'   => ordinal($rank),
            'show'        => $show,
            'updated'     => date('Y-m-d H:i'),
            'auto'        => true,
            'slug'        => $slug,
            'leaderboard' => $show_leaderboard,
        ];
        log_msg("FOUND: {$found_score['score']} | {$found_score['placement']} | {$show}");
        $all_found[] = $found_score;
        break; // move to next slug
    }
    usleep(300000);
}

if (empty($all_found)) {
    log_msg('Phantom Regiment not found in any competition recap');
    exit(0);
}

// Pick the highest-scoring show (most recent performance in a season)
usort($all_found, fn($a, $b) => (float)$b['score'] <=> (float)$a['score']);
$found_score = $all_found[0];
log_msg('Best score across ' . count($all_found) . ' show(s): ' . $found_score['score'] . ' at ' . $found_score['show']);

// ── 4. Save if changed ────────────────────────────────────────────────────────
$existing = file_exists($SCORE_FILE) ? json_decode(file_get_contents($SCORE_FILE), true) : [];
if (($existing['score'] ?? '') === $found_score['score'] && ($existing['show'] ?? '') === $found_score['show']) {
    log_msg('Score unchanged — no update needed');
    exit(0);
}

file_put_contents($SCORE_FILE, json_encode($found_score, JSON_PRETTY_PRINT));
log_msg('Saved score.json');

// Notify subscribers
try {
    $db_file = __DIR__ . '/data/messages.db';
    if (file_exists($db_file)) {
        $pdo  = new PDO('sqlite:' . $db_file);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $subs = @$pdo->query("SELECT email, token FROM score_alerts WHERE active=1")?->fetchAll(PDO::FETCH_ASSOC) ?? [];
        log_msg('Subscribers: ' . count($subs));
        foreach ($subs as $row) {
            $unsub = 'https://phantom.agavelabs.dev/alerts.php?unsubscribe=' . urlencode($row['token']);
            $subject = 'Phantom Regiment score: ' . $found_score['score'];
            $body = "Score: {$found_score['score']}\nPlacement: {$found_score['placement']}\nShow: {$found_score['show']}\n\nhttps://phantom.agavelabs.dev/#results\n\nUnsubscribe: {$unsub}";
            $hdrs = "From: Phantom Regiment Alerts <noreply@phantom.agavelabs.dev>\r\n";
            @mail($row['email'], $subject, $body, $hdrs);
        }
    }
} catch (Throwable $e) {
    log_msg('Alert send error (non-fatal): ' . $e->getMessage());
}

// Append ALL found shows to history
$history = file_exists($HIST_FILE) ? json_decode(file_get_contents($HIST_FILE), true) ?: [] : [];
foreach ($all_found as $fs) {
    $already = false;
    foreach ($history as &$h) {
        if ($h['show'] === $fs['show']) { $h = array_merge($h, $fs); $already = true; break; }
    }
    unset($h);
    if (!$already) $history[] = array_merge(['date' => date('Y-m-d')], $fs);
}
file_put_contents($HIST_FILE, json_encode($history, JSON_PRETTY_PRINT));
log_msg('Done. History: ' . count($history) . ' show(s)');
