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

$transformer = new UserTransformer();
$validator = new UserValidator(new MockRepo());

echo "--- RUNNING LOGIC TESTS ---\n";

// Test Transformation
$raw = ['john', 'smith', 'JOHN@EXAMPLE.COM'];
$transformed = $transformer->transform($raw);
assert($transformed[0] === 'John', 'Name capitalization failed');
assert($transformed[1] === 'Smith', 'Surname capitalization failed');
assert($transformed[2] === 'john@example.com', 'Email lowercase failed');
echo "[PASS] UserTransformer logic\n";

// Test Validation
$valid = $validator->validate(['John', 'Smith', 'john@example.com']);
assert($valid['isValid'] === true, 'Valid record rejected');

$invalid = $validator->validate(['John', 'Smith', 'invalid-email']);
assert($invalid['isValid'] === false, 'Invalid email accepted');
assert($invalid['errors'][0] === 'Invalid email address format: invalid-email');

$duplicate = $validator->validate(['John', 'Smith', 'john@example.com'], ['john@example.com']);
assert($duplicate['isValid'] === false, 'In-batch duplicate email accepted');
assert($duplicate['errors'][0] === 'Email address already exists: john@example.com');

$missingField = $validator->validate(['', 'Smith', 'john@example.com']);
assert($missingField['isValid'] === false, 'Missing name accepted');
assert($missingField['errors'][0] === 'Name is required.');
echo "[PASS] UserValidator logic\n";

echo "\n--- ALL INTERNAL LOGIC TESTS PASSED ---\n";