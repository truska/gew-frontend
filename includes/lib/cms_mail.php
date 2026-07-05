<?php
/**
 * Public-site mail transport.
 *
 * This deliberately has no dependency on WCCMS. Server-specific sender
 * values may be supplied by ../private/email.php; PHP's configured mail
 * transport handles delivery.
 */

$privateEmailConfig = dirname(__DIR__, 2) . '/../private/email.php';
if (is_file($privateEmailConfig)) {
  require_once $privateEmailConfig;
}

if (!function_exists('cms_send_mail')) {
  function cms_send_mail(string $to, string $subject, string $htmlBody, string $textBody = '', string $scope = 'web', bool $debug = false): bool {
    global $MAIL_FROM, $MAIL_FROM_NAME, $MAIL_REPLY_TO, $MAIL_BCC;

    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
      return false;
    }

    $from = trim((string) cms_pref('prefEmailSendFrom', $MAIL_FROM ?? 'no-reply@localhost', $scope));
    if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
      $from = 'no-reply@localhost';
    }
    $fromName = trim((string) ($MAIL_FROM_NAME ?? cms_pref('prefSiteName', 'Website', $scope)));
    $replyTo = trim((string) ($MAIL_REPLY_TO ?? $from));
    if (!filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
      $replyTo = $from;
    }

    $boundary = 'cms_' . bin2hex(random_bytes(12));
    $headers = [
      'MIME-Version: 1.0',
      'From: ' . str_replace(["\r", "\n"], '', $fromName) . ' <' . $from . '>',
      'Reply-To: ' . $replyTo,
      'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
    ];
    if (!empty($MAIL_BCC)) {
      $headers[] = 'Bcc: ' . str_replace(["\r", "\n"], '', (string) $MAIL_BCC);
    }

    $plainBody = $textBody !== '' ? $textBody : html_entity_decode(strip_tags($htmlBody), ENT_QUOTES, 'UTF-8');
    $message = '--' . $boundary . "\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\n"
      . $plainBody . "\r\n--" . $boundary . "\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n"
      . $htmlBody . "\r\n--" . $boundary . '--';

    return mail($to, str_replace(["\r", "\n"], '', $subject), $message, implode("\r\n", $headers));
  }
}
