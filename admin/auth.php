<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('display_errors', 0);
    session_set_cookie_params([
        'httponly' => true,
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (empty($_SESSION['admin_id'])) {
    header('Location: login');
    exit;
}

// Base path from project root to use in asset URLs
$_base_path = rtrim(str_replace('\\', '/', str_replace(
    realpath($_SERVER['DOCUMENT_ROOT']),
    '',
    realpath(dirname(__DIR__))
)), '/');

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify(): void {
    if (!isset($_POST['csrf_token']) || !hash_equals(
        $_SESSION['csrf_token'] ?? '',
        $_POST['csrf_token']
    )) {
        http_response_code(403);
        die('CSRF token mismatch');
    }
}

function flash(string $type, string $msg): void {
    $_SESSION['flash'][$type] = $msg;
}

function log_admin_activity(string $action, string $entity, int $entity_id, string $label = ''): void {
    try {
        db()->prepare(
            'INSERT INTO admin_activity_log (admin_id, admin_name, action, entity, entity_id, label) VALUES (?,?,?,?,?,?)'
        )->execute([
            $_SESSION['admin_id'] ?? 0,
            $_SESSION['admin_name'] ?? '',
            $action,
            $entity,
            $entity_id,
            mb_substr($label, 0, 255),
        ]);
    } catch (Throwable $e) {
        error_log('log_admin_activity failed: ' . $e->getMessage());
    }
}

function admin_pagination(int $total, int $per_page, int $page, string $base_url): string {
    $pages = max(1, (int)ceil($total / $per_page));
    if ($pages <= 1) return '';
    $sep  = str_contains($base_url, '?') ? '&' : '?';
    $from = ($page - 1) * $per_page + 1;
    $to   = min($page * $per_page, $total);

    $btn = function(int $p, string $label, bool $disabled, bool $active) use ($base_url, $sep): string {
        if ($disabled) return "<span class=\"px-3 py-1.5 text-xs text-gray-300 select-none\">{$label}</span>";
        if ($active)   return "<span class=\"px-3 py-1.5 text-xs bg-[#1a1714] text-white rounded-lg font-medium\">{$label}</span>";
        return "<a href=\"{$base_url}{$sep}page={$p}\" class=\"px-3 py-1.5 text-xs bg-white border border-gray-200 text-gray-600 rounded-lg hover:border-gray-400 transition-colors\">{$label}</a>";
    };

    $html  = '<div class="flex items-center justify-between mt-4 flex-wrap gap-3">';
    $html .= "<p class=\"text-xs text-gray-400\">{$from}–{$to} จาก {$total} รายการ</p>";
    $html .= '<div class="flex items-center gap-1.5">';
    $html .= $btn($page - 1, '←', $page <= 1, false);

    $prev = null;
    for ($i = 1; $i <= $pages; $i++) {
        if ($i === 1 || $i === $pages || ($i >= $page - 2 && $i <= $page + 2)) {
            if ($prev !== null && $i - $prev > 1) {
                $html .= '<span class="px-1 text-xs text-gray-400">…</span>';
            }
            $html .= $btn($i, (string)$i, false, $i === $page);
            $prev = $i;
        }
    }

    $html .= $btn($page + 1, '→', $page >= $pages, false);
    $html .= '</div></div>';
    return $html;
}
