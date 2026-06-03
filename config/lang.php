<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['lang'])) $_SESSION['lang'] = 'th';

function lang(): string { return $_SESSION['lang'] ?? 'th'; }
function is_en(): bool  { return lang() === 'en'; }

function t(string $th, string $en): string {
    return is_en() ? $en : $th;
}

// Get field value from DB row — prefer _en when lang=en and value exists
function tf(array $row, string $field): string {
    if (is_en() && isset($row[$field . '_en']) && $row[$field . '_en'] !== '') {
        return $row[$field . '_en'];
    }
    return (string)($row[$field] ?? '');
}
