<?php
// Retain the standard footer styling and legal/cookie controls. Holding mode
// removes useful links and testimonials, then uses the fourth column for the
// corporate logo.
$hideFooterUsefulLinks = true;
$hideFooterFeature = true;
$hideFooterLegalLinks = true;
$disableCookieConsent = true;
$holdingFooterLogo = 'green-energy-wind-name-logo.png';
$holdingSiteName = (string) cms_pref('prefSiteName', 'Green Energy Wind');
$footerExtraColumnHtml = '<img src="/filestore/images/logos/'
  . rawurlencode($holdingFooterLogo)
  . '" alt="' . cms_h($holdingSiteName) . '">';
include __DIR__ . '/footer-basic.php';
