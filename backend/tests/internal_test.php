<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use MoodleImportApp\Database\UserRepository;
use MoodleImportApp\Import\UserTransformer;
use MoodleImportApp\Import\UserValidator;

// Mock Repository for unit testing
class MockRepo extends UserRepository {
    public function __construct() {}
    public function isEmailUnique(string $email): bool { return true; }
}

function assertTrue(bool $condition, string $message): void {
    if (!$condition) {
        throw new \Exception("FAILED: $message");
    }
}

$transformer = new UserTransformer();
$validator = new UserValidator(new MockRepo());

$hasFailures = false;

echo "--- RUNNING LOGIC TESTS ---\n";

// Test Transformation
try {
    $raw = ['john', 'smith', 'JOHN@EXAMPLE.COM'];
    $transformed = $transformer->transform($raw);
    assertTrue($transformed[0] === 'John', 'Name capitalization failed');
    assertTrue($transformed[1] === 'Smith', 'Surname capitalization failed');
    assertTrue($transformed[2] === 'john@example.com', 'Email lowercase failed');
    echo "[PASS] UserTransformer logic\n";
} catch (\Throwable $e) {
    echo "[FAIL] UserTransformer logic - " . $e->getMessage() . "\n";
    $hasFailures = true;
}

// Test Validation
try {
    $valid = $validator->validate(['John', 'Smith', 'john@example.com']);
    assertTrue($valid['isValid'] === true, 'Valid record rejected');

    $invalid = $validator->validate(['John', 'Smith', 'invalid-email']);
    assertTrue($invalid['isValid'] === false, 'Invalid email accepted');
    assertTrue($invalid['errors'][0] === 'Invalid email address format: invalid-email', 'Invalid email error message mismatch');

    $duplicate = $validator->validate(['John', 'Smith', 'john@example.com'], ['john@example.com']);
    assertTrue($duplicate['isValid'] === false, 'In-batch duplicate email accepted');
    assertTrue($duplicate['errors'][0] === 'Email address already exists: john@example.com', 'Duplicate email error message mismatch');

    $missingField = $validator->validate(['', 'Smith', 'john@example.com']);
    assertTrue($missingField['isValid'] === false, 'Missing name accepted');
    assertTrue($missingField['errors'][0] === 'Name is required.', 'Missing name error message mismatch');
    echo "[PASS] UserValidator logic\n";
} catch (\Throwable $e) {
    echo "[FAIL] UserValidator logic - " . $e->getMessage() . "\n";
    $hasFailures = true;
}

if ($hasFailures) {
    echo "\n--- SOME INTERNAL LOGIC TESTS FAILED ---\n";
    exit(1);
}

echo "\n--- ALL INTERNAL LOGIC TESTS PASSED ---\n";
exit(0);