<?php
header('Content-Type: application/json');
header('Cache-Control: no-store');
$f = __DIR__ . '/data/score.json';
echo file_exists($f) ? file_get_contents($f) : '{}';
