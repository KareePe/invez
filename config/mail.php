<?php
/**
 * Minimal SMTP sender.
 *
 * Written by hand rather than pulled in as a dependency: this project has no
 * Composer setup, and the only thing it ever sends is a single HTML mail over
 * implicit TLS (port 465) with AUTH LOGIN. If the mailing needs grow beyond
 * that — attachments, multiple recipients, DKIM signing — replace this with
 * PHPMailer rather than extending it.
 */

$_mail_env = parse_ini_file(__DIR__ . '/../.env');

define('APP_URL',        rtrim($_mail_env['APP_URL'] ?? '', '/'));
define('SMTP_HOST',      $_mail_env['SMTP_HOST']      ?? '');
define('SMTP_PORT',      (int)($_mail_env['SMTP_PORT'] ?? 465));
define('SMTP_USER',      $_mail_env['SMTP_USER']      ?? '');
define('SMTP_PASS',      $_mail_env['SMTP_PASS']      ?? '');
define('SMTP_FROM_NAME', $_mail_env['SMTP_FROM_NAME'] ?? 'INVEZ');

unset($_mail_env);

/**
 * Read one SMTP reply and check it starts with the expected code.
 * Multi-line replies use "250-" for continuation and "250 " on the last line.
 */
function smtp_expect($socket, string $code, ?string &$reply = ''): bool
{
    $reply = '';
    while (($line = fgets($socket, 1024)) !== false) {
        $reply .= $line;
        if (strlen($line) >= 4 && $line[3] === ' ') break;   // final line
    }
    return strncmp($reply, $code, strlen($code)) === 0;
}

function smtp_send_line($socket, string $line): void
{
    fwrite($socket, $line . "\r\n");
}

/**
 * Send an HTML email. Returns true on success; on failure it logs the reason
 * and returns false — callers must never surface SMTP details to visitors.
 */
function send_mail(string $to, string $subject, string $html_body): bool
{
    if (SMTP_HOST === '' || SMTP_USER === '') {
        error_log('send_mail: SMTP is not configured in .env');
        return false;
    }

    // Guard against header injection via a crafted recipient address.
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        error_log('send_mail: refusing to send to an invalid address');
        return false;
    }

    $socket = @stream_socket_client(
        'ssl://' . SMTP_HOST . ':' . SMTP_PORT,
        $errno,
        $errstr,
        15,
        STREAM_CLIENT_CONNECT
    );

    if (!$socket) {
        error_log("send_mail: connection failed ($errno) $errstr");
        return false;
    }

    stream_set_timeout($socket, 15);

    $ok = true;
    $fail = function (string $step, string $reply) use (&$ok) {
        error_log('send_mail: ' . $step . ' rejected — ' . trim($reply));
        $ok = false;
    };

    $reply = '';
    if (!smtp_expect($socket, '220', $reply)) $fail('greeting', $reply);

    // EHLO host must be a name, not an email address.
    if ($ok) {
        smtp_send_line($socket, 'EHLO ' . SMTP_HOST);
        if (!smtp_expect($socket, '250', $reply)) $fail('EHLO', $reply);
    }

    if ($ok) {
        smtp_send_line($socket, 'AUTH LOGIN');
        if (!smtp_expect($socket, '334', $reply)) $fail('AUTH LOGIN', $reply);
    }
    if ($ok) {
        smtp_send_line($socket, base64_encode(SMTP_USER));
        if (!smtp_expect($socket, '334', $reply)) $fail('username', $reply);
    }
    if ($ok) {
        smtp_send_line($socket, base64_encode(SMTP_PASS));
        if (!smtp_expect($socket, '235', $reply)) $fail('password', $reply);
    }

    if ($ok) {
        smtp_send_line($socket, 'MAIL FROM:<' . SMTP_USER . '>');
        if (!smtp_expect($socket, '250', $reply)) $fail('MAIL FROM', $reply);
    }
    if ($ok) {
        smtp_send_line($socket, 'RCPT TO:<' . $to . '>');
        // 251 = "will forward", also a success.
        if (!smtp_expect($socket, '25', $reply)) $fail('RCPT TO', $reply);
    }
    if ($ok) {
        smtp_send_line($socket, 'DATA');
        if (!smtp_expect($socket, '354', $reply)) $fail('DATA', $reply);
    }

    if ($ok) {
        // Subject is base64 word-encoded so Thai text survives; the body is
        // base64 too, which also sidesteps SMTP dot-stuffing and line-length limits.
        $headers = [
            'Date: '                      . date('r'),
            'From: '                      . '=?UTF-8?B?' . base64_encode(SMTP_FROM_NAME) . '?= <' . SMTP_USER . '>',
            'To: '                        . $to,
            'Subject: '                   . '=?UTF-8?B?' . base64_encode($subject) . '?=',
            'Message-ID: <'               . bin2hex(random_bytes(16)) . '@' . SMTP_HOST . '>',
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
        ];

        $message = implode("\r\n", $headers) . "\r\n\r\n"
                 . chunk_split(base64_encode($html_body), 76, "\r\n");

        fwrite($socket, $message . "\r\n.\r\n");
        if (!smtp_expect($socket, '250', $reply)) $fail('message body', $reply);
    }

    smtp_send_line($socket, 'QUIT');
    fclose($socket);

    return $ok;
}

/**
 * Build the password reset email body. Kept here so the wording lives next to
 * the sender rather than inside the page that triggers it.
 */
function password_reset_email_body(string $reset_link, int $valid_minutes = 60): string
{
    $link = htmlspecialchars($reset_link, ENT_QUOTES, 'UTF-8');

    return '<!DOCTYPE html>
<html lang="th">
<body style="margin:0;padding:24px;background:#fafaf8;font-family:Arial,Helvetica,sans-serif;color:#1a1714;">
  <div style="max-width:520px;margin:0 auto;background:#ffffff;border:1px solid #e8e4df;border-radius:12px;padding:32px;">
    <h1 style="margin:0 0 8px;font-size:20px;color:#1a1714;">ตั้งรหัสผ่านใหม่</h1>
    <p style="margin:0 0 20px;font-size:14px;line-height:1.6;color:#6b5f52;">
      เราได้รับคำขอตั้งรหัสผ่านใหม่สำหรับบัญชี INVEZ ของคุณ
      กดปุ่มด้านล่างเพื่อตั้งรหัสผ่านใหม่ ลิงก์นี้ใช้ได้ ' . $valid_minutes . ' นาที และใช้ได้เพียงครั้งเดียว
    </p>
    <p style="margin:0 0 24px;">
      <a href="' . $link . '" style="display:inline-block;background:#c9a96e;color:#ffffff;text-decoration:none;font-weight:bold;font-size:14px;padding:12px 24px;border-radius:8px;">ตั้งรหัสผ่านใหม่</a>
    </p>
    <p style="margin:0 0 20px;font-size:12px;line-height:1.6;color:#9d8f82;">
      หากปุ่มกดไม่ได้ ให้คัดลอกลิงก์นี้ไปวางในเบราว์เซอร์:<br>
      <span style="color:#6b5f52;word-break:break-all;">' . $link . '</span>
    </p>
    <p style="margin:0;padding-top:20px;border-top:1px solid #f0ebe3;font-size:12px;line-height:1.6;color:#9d8f82;">
      หากคุณไม่ได้เป็นผู้ขอ ไม่ต้องดำเนินการใดๆ รหัสผ่านเดิมของคุณยังใช้งานได้ตามปกติ
    </p>
  </div>
</body>
</html>';
}
