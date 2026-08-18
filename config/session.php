<?php
/**
 * Single place that starts the session.
 *
 * Every entry point must require this *before* anything else touches
 * $_SESSION. Calling session_start() directly elsewhere creates the cookie
 * without these flags, because PHP's own defaults leave httponly, secure and
 * samesite unset.
 */
if (session_status() === PHP_SESSION_NONE) {
    ini_set('display_errors', 0);

    session_set_cookie_params([
        'httponly' => true,
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);

    // Refuse session IDs the server never issued, so an attacker cannot plant
    // a known ID in the victim's browser and ride it after they log in.
    ini_set('session.use_strict_mode', '1');

    session_start();
}
