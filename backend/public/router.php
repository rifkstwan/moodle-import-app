<?php
// Router script for PHP built-in server
// Routes all non-file requests to index.php

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve existing files directly (css, js, images, etc.)
$file = __DIR__ . $uri;
if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    return false;
}

// Route everything else through index.php
require_once __DIR__ . '/index.php';
