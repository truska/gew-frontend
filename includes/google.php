<?php
/**
 * Optional Google Analytics integration.
 *
 * Analytics is loaded only when enabled in preferences and the visitor has
 * explicitly accepted optional cookies through the site consent control.
 */

$googleAnalyticsEnabled = strcasecmp((string) cms_pref('prefGoogleAnalyticsOn', 'No'), 'Yes') === 0;
$googleAnalyticsId = trim((string) cms_pref('prefGoogleAnalyticsCode', ''));
$googleConsent = json_decode((string) ($_COOKIE['gew_cookie_consent'] ?? ''), true);
$googleAnalyticsConsented = is_array($googleConsent)
  && (int) ($googleConsent['version'] ?? 0) === 2
  && ($googleConsent['optional'] ?? false) === true;

if (!$googleAnalyticsEnabled || $googleAnalyticsId === '' || !$googleAnalyticsConsented) {
    return;
}

if (!preg_match('/^(G|GT|AW|DC)-[A-Z0-9-]+$/i', $googleAnalyticsId)) {
    return;
}
?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo rawurlencode($googleAnalyticsId); ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('consent', 'default', {
    analytics_storage: 'granted',
    ad_storage: 'denied',
    ad_user_data: 'denied',
    ad_personalization: 'denied'
  });
  gtag('config', <?php echo json_encode($googleAnalyticsId, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>);
</script>
