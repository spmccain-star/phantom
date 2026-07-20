<?php
/**
 * Upvote endpoint: POST s=<source> f=<relpath> dir=1|-1 → {"votes": n}
 * Only real photos from configured sources can be voted on.
 */

require __DIR__ . '/photos.php';
require __DIR__ . '/votes.php';

header('Content-Type: application/json');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo '{"error":"POST only"}';
    exit;
}

$source = (string)($_POST['s'] ?? '');
$rel    = (string)($_POST['f'] ?? '');
$dir    = (int)($_POST['dir'] ?? 0);

if (!in_array($dir, [1, -1], true) || $rel === '' || str_contains($rel, '..')) {
    http_response_code(400);
    echo '{"error":"bad request"}';
    exit;
}

$base = phantom_photo_dir($source);
$path = $base !== null ? realpath($base . '/' . $rel) : false;
if ($path === false || !str_starts_with($path, $base . DIRECTORY_SEPARATOR) || !is_file($path)) {
    http_response_code(404);
    echo '{"error":"unknown photo"}';
    exit;
}

echo json_encode(['votes' => phantom_vote_adjust(phantom_vote_key($source, $rel), $dir)]);
