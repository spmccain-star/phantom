#!/usr/bin/env php
<?php
/**
 * fetch_score.php — auto-fetch Phantom Regiment's latest DCI score
 * Runs nightly via cron: 0 23 * * * /usr/bin/php /var/www/phantom/fetch_score.php
 *
 * Data source: Competition Suite bridge API (no auth required)
 * Saves result to data/score.json (same format as manual admin entry)
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
    // Strip JSONP wrapper if present (jQuery(...);)
    $raw = preg_replace('/^jQuery\(|\);?$/', '', trim($raw));
    return json_decode($raw, true) ?: null;
}

function log_msg(string $msg): void {
    global $LOG_FILE;
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
    file_put_contents($LOG_FILE, $line, FILE_APPEND);
    echo $line;
}

log_msg('Starting score fetch...');

// 1. Get list of competitions this season
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

// 2. Walk competitions newest-first, find latest one with a Phantom score
$found = null;
foreach (array_reverse($competitions) as $comp) {
    $guid = $comp['competitionGuid'] ?? '';
    if (!$guid) continue;

    $detail = cs_get('GetCompetition', ['competition' => $guid]);
    if (!$detail) continue;

    foreach ($detail['rounds'] ?? [] as $round) {
        foreach ($round['performances'] ?? [] as $perf) {
            if (stripos($perf['name'] ?? '', 'Phantom') !== false) {
                $found = [
                    'score'       => number_format((float)$perf['score'], 3),
                    'placement'   => ordinal((int)$perf['rank']),
                    'show'        => $detail['name'] . ', ' . date('M j', strtotime($detail['competitionDate'])),
                    'updated'     => date('Y-m-d H:i'),
                    'auto'        => true,
                ];
                log_msg(sprintf('Found: %s — %.3f, rank %s at %s',
                    $perf['name'], $perf['score'], $perf['rank'], $detail['name']));
                break 3;
            }
        }
    }
    usleep(300000); // 300ms between requests to be polite
}

if (!$found) {
    log_msg('Phantom Regiment not found in any released competition');
    exit(0);
}

// 3. Only update if score changed
$existing = file_exists($SCORE_FILE) ? json_decode(file_get_contents($SCORE_FILE), true) : [];
if (($existing['score'] ?? '') === $found['score'] && ($existing['show'] ?? '') === $found['show']) {
    log_msg('Score unchanged — no update needed');
    exit(0);
}

file_put_contents($SCORE_FILE, json_encode($found, JSON_PRETTY_PRINT));
log_msg('Saved: ' . $found['score'] . ' | ' . $found['placement'] . ' | ' . $found['show']);

function ordinal(int $n): string {
    $s = ['th','st','nd','rd'];
    $v = $n % 100;
    return $n . ($s[($v - 20) % 10] ?? $s[$v] ?? $s[0]);
}
