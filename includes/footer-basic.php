<?php
$footerAddress = array_values(array_filter([
  trim((string) cms_pref('prefAddress1', '')),
  trim((string) cms_pref('prefAddress2', '')),
  trim((string) cms_pref('prefTown', '')),
  trim((string) cms_pref('prefCounty', '')),
  trim((string) cms_pref('prefPostcode', '')),
  trim((string) cms_pref('prefCountry', '')),
], static fn(string $part): bool => $part !== ''));
$footerTelephone = cms_tel_data('prefTel1', 'prefTelIntCode', '');
$footerEmail = trim((string) cms_pref('prefEmail', ''));
$footerLogo = trim((string) cms_pref('prefLogo1', 'green-energy-wind-name-logo.png'));
$footerSocials = [];
$footerSocialIcons = [
  'facebook-f', 'instagram', 'linkedin-in', 'x-twitter', 'youtube',
  'tiktok', 'pinterest-p', 'threads', 'whatsapp',
];
if (isset($pdo) && $pdo instanceof PDO) {
  try {
    $footerSocialStatement = $pdo->query(
      "SELECT name, url, icon
       FROM socials
       WHERE showonweb = 'Yes' AND archived = 0
       ORDER BY sort ASC, id ASC"
    );
    foreach ($footerSocialStatement ? $footerSocialStatement->fetchAll(PDO::FETCH_ASSOC) : [] as $footerSocial) {
      $socialUrl = trim((string) ($footerSocial['url'] ?? ''));
      $socialIcon = trim((string) ($footerSocial['icon'] ?? ''));
      if (!filter_var($socialUrl, FILTER_VALIDATE_URL)
        || !in_array(strtolower((string) parse_url($socialUrl, PHP_URL_SCHEME)), ['http', 'https'], true)
        || !in_array($socialIcon, $footerSocialIcons, true)) {
        continue;
      }
      $footerSocials[] = $footerSocial;
    }
  } catch (PDOException $exception) {
    $footerSocials = [];
  }
}
?>
<footer class="site-footer">
  <div class="container footer-main">
    <div class="row g-4">
      <div class="col-12 col-sm-6 col-lg-3">
        <h3 class="footer-heading">Useful links</h3>
        <ul class="footer-links">
          <?php foreach (array_slice(array_values($menuTree), 0, 5) as $footerItem): ?>
            <li><a href="<?php echo cms_h(gew_menu_url($footerItem)); ?>"><?php echo cms_h((string) $footerItem['label']); ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="col-12 col-sm-6 col-lg-3">
        <h3 class="footer-heading">Information</h3>
        <p class="footer-muted">Further information and links will follow.</p>
      </div>
      <div class="col-12 col-sm-6 col-lg-3">
        <h3 class="footer-heading">Contact us</h3>
        <?php if ($footerAddress): ?><address><?php echo implode('<br>', array_map('cms_h', $footerAddress)); ?></address><?php endif; ?>
        <?php if ($footerTelephone['display'] !== ''): ?><a class="footer-contact-link" href="tel:<?php echo cms_h($footerTelephone['dial']); ?>"><i class="fa-solid fa-phone" aria-hidden="true"></i><?php echo cms_h($footerTelephone['display']); ?></a><?php endif; ?>
        <?php if ($footerEmail !== ''): ?><a class="footer-contact-link" href="mailto:<?php echo cms_h($footerEmail); ?>"><i class="fa-solid fa-envelope" aria-hidden="true"></i><?php echo cms_h($footerEmail); ?></a><?php endif; ?>
      </div>
      <div class="col-12 col-sm-6 col-lg-3">
        <h3 class="footer-heading">Follow us</h3>
        <?php if ($footerSocials): ?>
          <div class="footer-network-links" aria-label="Social media links to follow">
            <?php foreach ($footerSocials as $footerSocial): ?>
              <a
                href="<?php echo cms_h((string) $footerSocial['url']); ?>"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="Follow us on <?php echo cms_h((string) $footerSocial['name']); ?>"
                title="<?php echo cms_h((string) $footerSocial['name']); ?>"
              ><i class="fa-brands fa-<?php echo cms_h((string) $footerSocial['icon']); ?>" aria-hidden="true"></i></a>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="footer-muted">Social links coming soon.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="footer-feature">
    <div class="container">
      <div class="row align-items-center g-4">
        <div class="col-12 col-lg-6 testimonial-preview">
          <h3 class="footer-heading">Testimonials</h3>
          <?php if ($footerTestimonial):
            $testimonialHeading = trim((string) ($footerTestimonial['heading'] ?? ''));
            $testimonialText = trim((string) ($footerTestimonial['shorttext'] ?? ''));
            $testimonialName = trim((string) ($footerTestimonial['name'] ?? ''));
            $testimonialCompany = trim((string) ($footerTestimonial['company'] ?? ''));
            $testimonialAttribution = implode(', ', array_values(array_filter([$testimonialName, $testimonialCompany], static fn(string $value): bool => $value !== '')));
          ?>
            <?php if ($testimonialHeading !== ''): ?><h6><?php echo cms_h($testimonialHeading); ?></h6><?php endif; ?>
            <?php if ($testimonialText !== ''): ?>
              <blockquote class="testimonial-quote">
                <i class="fa-solid fa-quote-left" aria-hidden="true"></i>
                <p><?php echo nl2br(cms_h($testimonialText)); ?></p>
              </blockquote>
            <?php endif; ?>
            <?php if ($testimonialAttribution !== ''): ?><p class="testimonial-name"><em><?php echo cms_h($testimonialAttribution); ?></em></p><?php endif; ?>
          <?php endif; ?>
          <?php $footerTestimonialId = (int) ($footerTestimonial['id'] ?? 0); ?>
          <a href="/testimonials<?php echo $footerTestimonialId > 0 ? '#testimonial-' . $footerTestimonialId : ''; ?>">Read this testimonial</a>
        </div>
        <div class="col-6 col-lg-3 footer-affiliates" aria-label="Affiliated organisations">
          <img src="/filestore/images/logos/affiliate-placeholder.svg" alt="Affiliate logo placeholder">
          <img src="/filestore/images/logos/affiliate-placeholder.svg" alt="Affiliate logo placeholder">
        </div>
        <div class="col-6 col-lg-3 footer-secondary-logo">
          <img src="/filestore/images/logos/<?php echo rawurlencode($footerLogo); ?>" alt="<?php echo cms_h((string) cms_pref('prefSiteName', 'Green Energy Wind')); ?>">
        </div>
      </div>
    </div>
  </div>

  <div class="footer-bottom">
    <div class="container footer-bottom-inner">
      <div class="footer-legal">
        <span>&copy; <?php echo date('Y'); ?> <?php echo cms_h((string) cms_pref('prefCompanyName', 'Green Energy Wind')); ?></span>
        <span aria-hidden="true">|</span><a href="/privacy">Privacy</a>
        <span aria-hidden="true">|</span><a href="/terms-and-conditions">T&amp;Cs</a>
        <span aria-hidden="true">|</span><a href="/gdpr">GDPR</a>
        <span aria-hidden="true">|</span><a href="/cookies">Cookies</a>
        <span aria-hidden="true">|</span><button type="button" class="footer-cookie-settings" id="cookieSettingsButton">Cookie settings</button>
      </div>
      <div class="footer-credit">Site design and hosting by <a href="https://truska.com" target="_blank" rel="noopener">truska.com</a></div>
    </div>
  </div>
</footer>

<?php
$footerDebugLoggedIn = function_exists('cms_is_logged_in')
  ? cms_is_logged_in()
  : !empty($_SESSION['cms_user']);

$footerDebugIp = function_exists('cms_frontend_edit_first_ip_value')
  ? cms_frontend_edit_first_ip_value((string) ($_SERVER['HTTP_CF_CONNECTING_IP'] ?? ''))
  : trim((string) ($_SERVER['HTTP_CF_CONNECTING_IP'] ?? ''));
if ($footerDebugIp === '') {
  $footerDebugIp = function_exists('cms_frontend_edit_first_ip_value')
    ? cms_frontend_edit_first_ip_value((string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''))
    : trim((string) explode(',', (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''))[0]);
}
if ($footerDebugIp === '') {
  $footerDebugIp = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
}

$footerDebugAllowedIps = [];
foreach (['prefAllowedIPs', 'prefAllowedIP', 'prefTruskaIP', 'prefCoderIP', 'prefClientIP', 'prefClient1IP'] as $preferenceName) {
  $preferenceValue = trim((string) cms_pref($preferenceName, ''));
  if ($preferenceValue === '') {
    continue;
  }
  foreach (preg_split('/[\s,;]+/', $preferenceValue, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $allowedIp) {
    $footerDebugAllowedIps[] = trim((string) $allowedIp);
  }
}

$footerDebugIpAllowed = $footerDebugIp !== '' && in_array($footerDebugIp, array_unique($footerDebugAllowedIps), true);
if ($footerDebugLoggedIn && $footerDebugIpAllowed) {
  include __DIR__ . '/footer-debug.php';
}
?>

<?php
$cookieConsentVersion = 2;
$cookieConsentStored = json_decode((string) ($_COOKIE['gew_cookie_consent'] ?? ''), true);
$cookieConsentPending = !is_array($cookieConsentStored)
  || (int) ($cookieConsentStored['version'] ?? 0) !== $cookieConsentVersion;
?>
<aside
  class="cookie-tool"
  id="cookieTool"
  role="dialog"
  aria-labelledby="cookieTitle"
  aria-describedby="cookieDescription"
  <?php if ($cookieConsentPending): ?>data-consent-pending="1" style="z-index:2147483647;display:block;visibility:visible;opacity:1;pointer-events:auto;transform:none"<?php else: ?>hidden<?php endif; ?>
>
  <div class="container cookie-tool-inner">
    <div class="cookie-tool-copy">
      <h2 id="cookieTitle">Your cookie choices</h2>
      <p id="cookieDescription">We use necessary cookies to make this website work. With your permission, we may also use optional cookies to improve the site.</p>
      <p class="cookie-tool-links">
        <a href="/cookies">Read our Cookie Policy</a>
        <span aria-hidden="true">|</span>
        <a href="https://ico.org.uk/for-the-public/online/cookies/" target="_blank" rel="noopener">General cookie information</a>
      </p>
    </div>
    <div class="cookie-tool-actions">
      <button type="button" class="cookie-decline" id="declineOptionalCookies">Necessary only</button>
      <button type="button" class="cookie-accept" id="acceptAllCookies">Accept all</button>
    </div>
  </div>
</aside>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
<?php
$cookieConsentScript = __DIR__ . '/../js/cookie-consent.js';
if (is_readable($cookieConsentScript)) {
  readfile($cookieConsentScript);
}
?>
</script>
<?php $announcementScriptVersion = @filemtime(__DIR__ . '/../js/announcement.js') ?: 1; ?>
<script src="/js/announcement.js?v=<?php echo rawurlencode((string) $announcementScriptVersion); ?>"></script>
</body>
</html>
