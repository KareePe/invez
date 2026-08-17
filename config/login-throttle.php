<?php
/**
 * Login throttling, keyed on the client IP.
 *
 * Used by both login.php (scope "member") and admin/login.php (scope "admin"),
 * which are counted separately so a locked-out member login does not also lock
 * the admin panel.
 *
 * Requires config/db.php to be loaded first.
 */

// After this many failures from one IP within the block window, that IP is
// refused until the failures age out. Successful logins clear the counter, so
// a legitimate user who mistypes a few times is never affected.
const LOGIN_MAX_FAILURES = 10;
const LOGIN_BLOCK_HOURS  = 24;

function login_client_ip(): string
{
    // REMOTE_ADDR only. Forwarded-for headers are attacker-controlled unless a
    // trusted proxy is known to sit in front, and this deploys to plain shared
    // hosting where it does not.
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/** True when this IP has failed too often and should be refused outright. */
function login_is_blocked(string $scope): bool
{
    // Opportunistic cleanup — no cron needed, and cheap with the index.
    db()->prepare('DELETE FROM login_attempts WHERE created_at < DATE_SUB(NOW(), INTERVAL ? HOUR)')
       ->execute([LOGIN_BLOCK_HOURS]);

    $stmt = db()->prepare(
        'SELECT COUNT(*) FROM login_attempts
         WHERE ip = ? AND scope = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? HOUR)'
    );
    $stmt->execute([login_client_ip(), $scope, LOGIN_BLOCK_HOURS]);

    return $stmt->fetchColumn() >= LOGIN_MAX_FAILURES;
}

function login_record_failure(string $scope): void
{
    db()->prepare('INSERT INTO login_attempts (ip, scope) VALUES (?, ?)')
       ->execute([login_client_ip(), $scope]);
}

function login_clear_failures(string $scope): void
{
    db()->prepare('DELETE FROM login_attempts WHERE ip = ? AND scope = ?')
       ->execute([login_client_ip(), $scope]);
}
