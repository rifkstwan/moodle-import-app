<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use MoodleImportApp\Config\Config;
use MoodleImportApp\Database\Database;
use MoodleImportApp\Database\UserRepository;
use MoodleImportApp\Import\UserTransformer;
use MoodleImportApp\Import\UserValidator;
use MoodleImportApp\Import\ImportService;

// CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    Config::load(__DIR__ . '/../.env');
} catch (Exception $e) {}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$db = new Database();
$repo = new UserRepository($db);
$service = new ImportService(
    $repo,
    new UserTransformer(),
    new UserValidator($repo)
);

header('Content-Type: application/json');

try {
    if ($uri === '/api/parse' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_FILES['file'])) {
            throw new RuntimeException("No file uploaded");
        }
        
        $tmpPath = $_FILES['file']['tmp_name'];
        // Return preview (dry run)
        echo json_encode($service->processFile($tmpPath, true));
        exit;
    }

    if ($uri === '/api/import' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        // In a real app, we might use a session or hash to verify the same file.
        // For simplicity/challenge, we'll re-upload or send the file path if persistent.
        // Here, we assume the frontend sends the file again for the final import step
        // OR we just process the upload.
        if (!isset($_FILES['file'])) {
            throw new RuntimeException("No file uploaded for import");
        }

        $tmpPath = $_FILES['file']['tmp_name'];
        echo json_encode($service->processFile($tmpPath, false));
        exit;
    }

    http_response_code(404);
    echo json_encode(['error' => 'Not Found']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}