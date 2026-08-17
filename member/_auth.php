<?php
require_once(__DIR__ . '/../config/session.php');

// Compute base path before any redirect
$_member_base = rtrim(str_replace('\\', '/', str_replace(
    realpath($_SERVER['DOCUMENT_ROOT']),
    '',
    realpath(dirname(__DIR__))
)), '/');

if (empty($_SESSION['member_id'])) {
    header('Location: ' . $_member_base . '/login');
    exit;
}

function member_csrf_token(): string {
    if (empty($_SESSION['member_csrf'])) {
        $_SESSION['member_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['member_csrf'];
}

function member_csrf_verify(): void {
    if (!isset($_POST['csrf_token']) || !hash_equals(
        $_SESSION['member_csrf'] ?? '',
        $_POST['csrf_token']
    )) {
        http_response_code(403);
        die('Invalid token');
    }
}

function member_flash(string $type, string $msg): void {
    $_SESSION['member_flash'][$type] = $msg;
}

function member_pagination(int $total, int $per_page, int $page, string $base_url): string {
    $pages = max(1, (int)ceil($total / $per_page));
    if ($pages <= 1) return '';
    $sep  = str_contains($base_url, '?') ? '&' : '?';
    $from = ($page - 1) * $per_page + 1;
    $to   = min($page * $per_page, $total);

    $btn = function(int $p, string $label, bool $disabled, bool $active) use ($base_url, $sep): string {
        if ($disabled) return "<span class=\"px-3 py-1.5 text-xs text-[#c4b8ac] select-none\">{$label}</span>";
        if ($active)   return "<span class=\"px-3 py-1.5 text-xs bg-[#1a1714] text-white rounded-lg font-medium\">{$label}</span>";
        return "<a href=\"{$base_url}{$sep}page={$p}\" class=\"px-3 py-1.5 text-xs bg-white border border-[#e0dbd4] text-[#6b5f52] rounded-lg hover:border-[#c9a96e] transition-colors\">{$label}</a>";
    };

    $html  = '<div class="flex items-center justify-between mt-5 flex-wrap gap-3">';
    $html .= "<p class=\"text-xs text-[#9d8f82]\">{$from}–{$to} จาก {$total} รายการ</p>";
    $html .= '<div class="flex items-center gap-1.5">';
    $html .= $btn($page - 1, '←', $page <= 1, false);

    $prev = null;
    for ($i = 1; $i <= $pages; $i++) {
        if ($i === 1 || $i === $pages || ($i >= $page - 2 && $i <= $page + 2)) {
            if ($prev !== null && $i - $prev > 1) {
                $html .= '<span class="px-1 text-xs text-[#9d8f82]">…</span>';
            }
            $html .= $btn($i, (string)$i, false, $i === $page);
            $prev = $i;
        }
    }

    $html .= $btn($page + 1, '→', $page >= $pages, false);
    $html .= '</div></div>';
    return $html;
}
