<?php
declare(strict_types=1);

function parseAssignmentDate(string $input): ?DateTimeImmutable {
    $input = trim($input);
    if (preg_match('~^(\d{1,2})\s*[-/]\s*(\d{1,2})\s*[-/]\s*(\d{4})$~', $input, $parts)) {
        [, $day, $month, $year] = $parts;
    } elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $input, $parts)) {
        // Accept submissions from forms opened before the date format changed.
        [, $year, $month, $day] = $parts;
    } else {
        return null;
    }
    if ((int)$year < 1000 || !checkdate((int)$month, (int)$day, (int)$year)) {
        return null;
    }
    return DateTimeImmutable::createFromFormat('!Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day)) ?: null;
}
