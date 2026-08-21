<?php

declare(strict_types=1);

namespace MoodleImportApp\Csv;

use League\Csv\Map;
use League\Csv\Reader;
use MoodleImportApp\Config\Config;

class CsvParser
{
    private Reader $reader;

    /**
     * @param string $filePath Path to CSV file
     */
    public function __construct(string $filePath)
    {
        $reader = Reader::createFromPath($filePath, 'r');
        $reader->setHeaderOffset(0); // Skip header row

        $this->reader = $reader;
    }

    /**
     * Return original filename (extension stripped for internal use)
     */
    public function getBaseName(): string
    {
        return pathinfo($this->reader->getStreamSource(), PATHINFO_FILENAME);
    }

    /**
     * Import finished:
     *  - final user records
     */
    public function getUsers(): array
    {
        $users = [];

        foreach ($this->reader->getRecords() as $row) {
            $name    = trim($row['name'] ?? '');
            $surname = trim($row['surname'] ?? '');
            $email   = trim($row['email'] ?? '');

            if ($name === '' && $surname === '' && $email === '') {
                continue; // skip fully empty rows
            }

            $users[] = [$name, $surname, $email];
        }

        return $users;
    }
}