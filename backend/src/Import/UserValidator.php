<?php

declare(strict_types=1);

namespace MoodleImportApp\Import;

use MoodleImportApp\Database\UserRepository;

class UserValidator
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param list{string, string, string} $userData [name, surname, email]
     * @param array<string> $seenEmails
     * @return array{isValid: bool, errors: list<string>}
     */
    public function validate(array $userData, array $seenEmails = []): array
    {
        [$name, $surname, $email] = $userData;
        $errors = [];

        if ($name === '') {
            $errors[] = 'Name is required.';
        }

        if ($surname === '') {
            $errors[] = 'Surname is required.';
        }

        if ($email === '') {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = sprintf('Invalid email address format: %s', $email);
        } elseif (in_array(strtolower($email), $seenEmails, true) || !$this->userRepository->isEmailUnique($email)) {
            $errors[] = sprintf('Email address already exists: %s', $email);
        }

        return [
            'isValid' => empty($errors),
            'errors' => $errors,
        ];
    }
}