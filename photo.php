<?php
/**
 * Streams a photo from one of the configured sources (photos.php).
 * The PhanMail dir lives outside this docroot, so images are served
 * through here instead of by direct URL.
 *
 *   /photo.php?s=<source>&f=<relative path>        full size
 *   /photo.php?s=<source>&f=<relative path>&w=480  cached downscaled copy
 */

require __DIR__ . '/photos.php';

$source = (string)($_GET['s'] ?? '');
$rel    = (string)($_GET['f'] ?? '');

$base = phantom_photo_dir($source);
if ($base === null || $rel === '' || str_contains($rel, '..')) {
    http_response_code(404);
    exit;
}

$path = realpath($base . '/' . $rel);
if ($path === false || !str_starts_with($path, $base . DIRECTORY_SEPARATOR) || !is_file($path)) {
    http_response_code(404);
    exit;
}

$mime = [
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'gif'  => 'image/gif',
    'webp' => 'image/webp',
][strtolower(pathinfo($path, PATHINFO_EXTENSION))] ?? null;
if ($mime === null) {
    http_response_code(404);
    exit;
}

$width = isset($_GET['w']) ? max(80, min(1600, (int)$_GET['w'])) : 0;
if ($width > 0 && $mime !== 'image/gif' && function_exists('imagecreatefromstring')) {
    $thumb = phantom_thumb($path, $width);
    if ($thumb !== null) {
        phantom_send($thumb, 'image/jpeg');
    }
}
phantom_send($path, $mime);

/** Returns the path of a cached JPEG thumbnail, or null to fall back to the original. */
function phantom_thumb(string $path, int $width): ?string {
    $dir = sys_get_temp_dir() . '/phantom-thumbs';
    if (!is_dir($dir) && !@mkdir($dir, 0775, true)) return null;

    $cache = $dir . '/' . md5($path . '|' . filemtime($path) . '|' . $width) . '.jpg';
    if (is_file($cache)) return $cache;

    $data = @file_get_contents($path);
    if ($data === false) return null;
    $img = @imagecreatefromstring($data);
    if ($img === false) return null;

    $w = imagesx($img);
    $h = imagesy($img);
    if ($w <= $width) { // already small enough
        imagedestroy($img);
        return null;
    }

    $nh    = (int)round($h * $width / $w);
    $small = imagescale($img, $width, $nh, IMG_BICUBIC);
    imagedestroy($img);
    if ($small === false) return null;

    $ok = imagejpeg($small, $cache, 82);
    imagedestroy($small);
    return $ok ? $cache : null;
}

/** Streams a file with cache headers and exits. */
function phantom_send(string $path, string $mime): never {
    $mtime = filemtime($path);
    $etag  = '"' . md5($path . '|' . $mtime . '|' . filesize($path)) . '"';

    if (($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
        http_response_code(304);
        exit;
    }

    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($path));
    header('Cache-Control: public, max-age=3600');
    header('ETag: ' . $etag);
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');
    readfile($path);
    exit;
}
