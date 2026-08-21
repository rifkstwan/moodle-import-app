<?php

declare(strict_types=1);

namespace MoodleImportApp\Import;

class UserTransformer
{
    /**
     * Transform a raw CSV row by capitalizing name and surname using MB_CASE_TITLE.
     *
     * @param list{string, string, string} $userData [name, surname, email]
     * @return list{string, string, string}           [name, surname, email]
     */
    public function transform(array $userData): array
    {
        [$name, $surname, $email] = $userData;

        return [
            mb_convert_case(trim($name), MB_CASE_TITLE, 'UTF-8'),
            mb_convert_case(trim($surname), MB_CASE_TITLE, 'UTF-8'),
            mb_strtolower(trim($email), 'UTF-8'),
        ];
    }
}
