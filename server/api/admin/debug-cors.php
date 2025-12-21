<?php
header('Content-Type: text/plain');
echo "Origin: " . ($_SERVER['HTTP_ORIGIN'] ?? 'none') . "\n";
echo "Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "Headers sent: " . (headers_sent() ? 'yes' : 'no') . "\n";
echo "\nAll headers:\n";
foreach (headers_list() as $header) {
    echo "- $header\n";
}