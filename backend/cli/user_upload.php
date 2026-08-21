#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use MoodleImportApp\Config\Config;
use MoodleImportApp\Database\Database;
use MoodleImportApp\Database\UserRepository;
use MoodleImportApp\Import\UserTransformer;
use MoodleImportApp\Import\UserValidator;
use MoodleImportApp\Import\ImportService;

// Load config
try {
    Config::load(__DIR__ . '/../.env');
} catch (Exception $e) {
    // Fallback if .env doesn't exist during first run
}

$options = getopt('', ['file:', 'dry-run', 'create-table', 'help']);

if (isset($options['help'])) {
    echo "Usage: php user_upload.php [options]\n\n";
    echo "Options:\n";
    echo "  --file <filename>    CSV file to process\n";
    echo "  --dry-run            Parse and validate without importing\n";
    echo "  --create-table       Create/rebuild the users table\n";
    echo "  --help               Display available options\n";
    exit(0);
}

$db = new Database();
$repo = new UserRepository($db);

if (isset($options['create-table'])) {
    echo "Creating/rebuilding users table...\n";
    $repo->createTable();
    echo "Done.\n";
    if (!isset($options['file'])) {
        exit(0);
    }
}

if (!isset($options['file'])) {
    echo "Error: --file <filename> is required unless --create-table is used alone.\n";
    exit(1);
}

$file = $options['file'];
$dryRun = isset($options['dry-run']);

$service = new ImportService(
    $repo,
    new UserTransformer(),
    new UserValidator($repo)
);

try {
    echo $dryRun ? "[DRY RUN] Processing $file...\n" : "Processing $file...\n";
    
    $summary = $service->processFile($file, $dryRun);

    echo "\nSummary:\n";
    echo "Total records found: " . $summary['total'] . "\n";
    echo "Valid records:       " . $summary['valid'] . "\n";
    echo "Invalid records:     " . $summary['invalid'] . "\n";

    if ($summary['invalid'] > 0) {
        echo "\nErrors:\n";
        foreach ($summary['results'] as $idx => $res) {
            if (!$res['isValid']) {
                echo "Row " . ($idx + 2) . ": " . implode(', ', $res['errors']) . "\n";
            }
        }
    }

    if (!$dryRun && $summary['valid'] > 0) {
        echo "\nSuccessfully imported " . $summary['valid'] . " users.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}