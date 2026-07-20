<?php
/**
 * Vote storage for gallery photos.
 * Counts live in data/votes.json keyed by "source|relpath"; the data/ dir is
 * excluded from deploys (like uploads/) so votes survive each rsync.
 */

function phantom_votes_file(): string {
    return __DIR__ . '/data/votes.json';
}

function phantom_votes(): array {
    $f = phantom_votes_file();
    if (!is_file($f)) return [];
    $data = json_decode((string)@file_get_contents($f), true);
    return is_array($data) ? $data : [];
}

function phantom_vote_key(string $source, string $rel): string {
    return $source . '|' . $rel;
}

/** Atomically adjust a photo's vote count; returns the new count. */
function phantom_vote_adjust(string $key, int $dir): int {
    $file = phantom_votes_file();
    $parent = dirname($file);
    if (!is_dir($parent) && !@mkdir($parent, 0775, true)) return 0;

    $fh = fopen($file, 'c+');
    if ($fh === false) return 0;
    flock($fh, LOCK_EX);

    $votes = json_decode((string)stream_get_contents($fh), true);
    if (!is_array($votes)) $votes = [];
    $count = max(0, ($votes[$key] ?? 0) + $dir);
    if ($count === 0) {
        unset($votes[$key]);
    } else {
        $votes[$key] = $count;
    }

    ftruncate($fh, 0);
    rewind($fh);
    fwrite($fh, json_encode($votes));
    fflush($fh);
    flock($fh, LOCK_UN);
    fclose($fh);
    return $count;
}
