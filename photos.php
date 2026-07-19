<?php
/**
 * Shared photo-source config + discovery for the "Latest photos" strip.
 *
 * Each source lists candidate directories — the first one that exists on the
 * server wins, so paths can differ between local dev and CT 101 without code
 * changes. A source whose directories are all missing is silently skipped.
 */

function phantom_photo_sources(): array {
    return [
        // Photos dropped directly on the phantom site + admin-uploaded gallery
        // (both survive deploys — deploy.sh excludes uploads/ and data/ from rsync --delete).
        'site' => [
            'label' => null, // no badge for our own uploads
            'dirs'  => [
                __DIR__ . '/uploads',
                __DIR__ . '/data/gallery',   // admin Photo Gallery uploads
            ],
        ],
        // Photos submitted through the site's Phanmail message board (name + photo).
        'phanmail' => [
            'label' => 'Phanmail',
            'dirs'  => [
                __DIR__ . '/data/uploads',   // where message-board photos are stored
                // legacy/standalone locations, kept in case a separate app is added later
                '/var/www/phanmail/uploads',
                '/var/www/phanmail/data/uploads',
            ],
        ],
    ];
}

function phantom_photo_dir(string $source): ?string {
    foreach (phantom_photo_sources()[$source]['dirs'] ?? [] as $dir) {
        if (is_dir($dir)) {
            $real = realpath($dir);
            if ($real !== false) return $real;
        }
    }
    return null;
}

/**
 * Newest images across all sources, newest first.
 * Scans up to two directory levels deep (PhanMail may bucket by date).
 * Each entry: source, label, rel (path relative to the source dir),
 * mtime, url (full size), thumb (downscaled).
 */
function phantom_latest_photos(int $limit = 12): array {
    $exts   = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $photos = [];

    foreach (phantom_photo_sources() as $key => $src) {
        $base = phantom_photo_dir($key);
        if ($base === null) continue;

        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS)
        );
        $it->setMaxDepth(2);

        foreach ($it as $file) {
            if (!$file->isFile()) continue;
            if (!in_array(strtolower($file->getExtension()), $exts, true)) continue;
            if ($file->getFilename()[0] === '.') continue;

            $rel = ltrim(substr($file->getPathname(), strlen($base)), '/');
            $qs  = 's=' . rawurlencode($key) . '&f=' . rawurlencode($rel);
            $photos[] = [
                'source' => $key,
                'label'  => $src['label'],
                'rel'    => $rel,
                'mtime'  => $file->getMTime(),
                'url'    => '/photo.php?' . $qs,
                'thumb'  => '/photo.php?' . $qs . '&w=480',
            ];
        }
    }

    usort($photos, fn($a, $b) => $b['mtime'] <=> $a['mtime']);
    return array_slice($photos, 0, $limit);
}
