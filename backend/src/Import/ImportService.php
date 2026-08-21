<?php

declare(strict_types=1);

namespace MoodleImportApp\Import;

use MoodleImportApp\Database\UserRepository;
use MoodleImportApp\Csv\CsvParser;
use RuntimeException;

class ImportService
{
    private UserRepository $userRepository;
    private UserTransformer $transformer;
    private UserValidator $validator;

    public function __construct(
        UserRepository $userRepository,
        UserTransformer $transformer,
        UserValidator $validator
    ) {
        $this->userRepository = $userRepository;
        $this->transformer = $transformer;
        $this->validator = $validator;
    }

    /**
     * @return array{
     *   total: int,
     *   valid: int,
     *   invalid: int,
     *   results: list<array{row: list{string, string, string}, isValid: bool, errors: list<string>}>
     * }
     */
    public function processFile(string $filePath, bool $dryRun = false): array
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException("File not found: $filePath");
        }

        $parser = new CsvParser($filePath);
        $rows = $parser->getUsers();
        
        $total = count($rows);
        $validCount = 0;
        $invalidCount = 0;
        $results = [];
        $seenEmails = [];

        foreach ($rows as $index => $row) {
            $transformedRow = $this->transformer->transform($row);
            $validation = $this->validator->validate($transformedRow, $seenEmails);

            if ($validation['isValid']) {
                $validCount++;
                $seenEmails[] = strtolower($transformedRow[2]);
                if (!$dryRun) {
                    $this->userRepository->insertUser($transformedRow);
                }
            } else {
                $invalidCount++;
            }

            $results[] = [
                'row' => $transformedRow,
                'isValid' => $validation['isValid'],
                'errors' => $validation['errors']
            ];
        }

        return [
            'total' => $total,
            'valid' => $validCount,
            'invalid' => $invalidCount,
            'results' => $results
        ];
    }
}