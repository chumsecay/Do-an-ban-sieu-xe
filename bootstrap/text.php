<?php
declare(strict_types=1);

if (!function_exists('stripVietnameseDiacritics')) {
    function stripVietnameseDiacritics(string $text): string
    {
        if ($text === '') {
            return '';
        }

        $text = str_replace(['đ', 'Đ'], ['d', 'D'], $text);

        if (function_exists('transliterator_transliterate')) {
            $converted = transliterator_transliterate('NFD; [:Nonspacing Mark:] Remove; NFC; Latin-ASCII', $text);
            if (is_string($converted) && $converted !== '') {
                $text = $converted;
            }
        } elseif (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
            if ($converted !== false && $converted !== '') {
                $text = $converted;
            }
        }

        $text = preg_replace('/[^\x20-\x7E]/', '', $text) ?? $text;
        return $text;
    }
}

if (!function_exists('normalizeSearchText')) {
    function normalizeSearchText(string $text): string
    {
        $text = stripVietnameseDiacritics(trim($text));
        if ($text === '') {
            return '';
        }

        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', ' ', $text) ?? '';
        $text = preg_replace('/\s+/', ' ', $text) ?? '';
        return trim($text);
    }
}

if (!function_exists('searchLikeNormalized')) {
    function searchLikeNormalized(string $haystack, string $needle): bool
    {
        $needle = normalizeSearchText($needle);
        if ($needle === '') {
            return true;
        }

        return str_contains(normalizeSearchText($haystack), $needle);
    }
}

if (!function_exists('searchFilterRowsByKeyword')) {
    function searchFilterRowsByKeyword(array $rows, array $fields, string $keyword): array
    {
        $keyword = normalizeSearchText($keyword);
        if ($keyword === '' || !$rows || !$fields) {
            return $rows;
        }

        $filtered = [];
        foreach ($rows as $row) {
            foreach ($fields as $field) {
                $value = isset($row[$field]) ? (string)$row[$field] : '';
                if ($value !== '' && searchLikeNormalized($value, $keyword)) {
                    $filtered[] = $row;
                    continue 2;
                }
            }
        }

        return $filtered;
    }
}
