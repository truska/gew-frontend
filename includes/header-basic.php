<?php
$baseURL = cms_base_url();
$logo = trim((string) cms_pref('prefLogo', 'green-energy-wind-logo.jpg'));
$telephone = cms_tel_data('prefTel1', 'prefTelIntCode', '');
$email = trim((string) cms_pref('prefEmail', ''));
$fontAwesomeVersion = trim((string) cms_pref('prefFontAwesomeVersion', '6.5.2'));
if (!preg_match('/^6\.\d+\.\d+$/', $fontAwesomeVersion)) {
  $fontAwesomeVersion = '6.5.2';
}
$cmsImagesPath = __DIR__ . '/lib/cms_images.php';
if (file_exists($cmsImagesPath)) {
  require_once $cmsImagesPath;
}
?>
<!doctype html>
<html lang="en">
<head>
  <?php include __DIR__ . '/metadata.php'; ?>
  <?php include __DIR__ . '/google.php'; ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/<?php echo rawurlencode($fontAwesomeVersion); ?>/css/all.min.css">
  <link rel="stylesheet" href="<?php echo cms_h($baseURL); ?>/css/gew-basic.css?v=<?php echo filemtime(__DIR__ . '/../css/gew-basic.css'); ?>">
  <?php $siteCssPath = __DIR__ . '/../css/site.css'; if (file_exists($siteCssPath)): ?>
    <link rel="stylesheet" href="<?php echo cms_h($baseURL); ?>/css/site.css?v=<?php echo filemtime($siteCssPath); ?>">
  <?php endif; ?>
  <?php if (function_exists('cms_magictoolbox_assets_html')): ?>
    <?php echo cms_magictoolbox_assets_html('magiczoomplus'); ?>
  <?php endif; ?>
</head>
<body>
<header class="site-header">
  <div class="container header-top">
    <div class="row align-items-center gx-3 gy-lg-3 w-100">
      <div class="col-12 col-lg-4 text-center text-lg-start">
        <a href="/" class="brand">
          <img src="/filestore/images/logos/<?php echo rawurlencode($logo); ?>" alt="<?php echo cms_h((string) cms_pref('prefSiteName', 'Green Energy Wind')); ?>">
        </a>
      </div>
      <div class="d-none d-lg-flex col-lg-4 affiliate-logos justify-content-center" aria-label="Affiliated organisations">
        <img src="/filestore/images/logos/affiliate-placeholder.svg" alt="Affiliate logo placeholder">
        <img src="/filestore/images/logos/affiliate-placeholder.svg" alt="Affiliate logo placeholder">
      </div>
      <div class="d-none d-lg-block col-lg-4">
        <div class="contact-details">
          <?php if ($telephone['display'] !== ''): ?>
            <a href="tel:<?php echo cms_h($telephone['dial']); ?>" aria-label="Telephone <?php echo cms_h($telephone['display']); ?>"><i class="fa-solid fa-phone" aria-hidden="true"></i><span><?php echo cms_h($telephone['display']); ?></span></a>
          <?php endif; ?>
          <?php if ($email !== ''): ?>
            <a href="mailto:<?php echo cms_h($email); ?>" aria-label="Email <?php echo cms_h($email); ?>"><i class="fa-solid fa-envelope" aria-hidden="true"></i><span><?php echo cms_h($email); ?></span></a>
          <?php endif; ?>
          <a href="/contact-itfix" class="contact-cta" aria-label="Contact us"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i><span>Contact us</span></a>
        </div>
      </div>
    </div>
  </div>
  <nav class="navbar navbar-expand-lg" aria-label="Main navigation">
    <div class="container">
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainMenu" aria-controls="mainMenu" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
      <div class="mobile-nav-contact d-flex d-lg-none" aria-label="Contact links">
        <?php if ($telephone['display'] !== ''): ?><a href="tel:<?php echo cms_h($telephone['dial']); ?>" aria-label="Telephone <?php echo cms_h($telephone['display']); ?>"><i class="fa-solid fa-phone" aria-hidden="true"></i></a><?php endif; ?>
        <?php if ($email !== ''): ?><a href="mailto:<?php echo cms_h($email); ?>" aria-label="Email <?php echo cms_h($email); ?>"><i class="fa-solid fa-envelope" aria-hidden="true"></i></a><?php endif; ?>
        <a href="/contact-itfix" aria-label="Contact us"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i></a>
      </div>
      <div class="collapse navbar-collapse" id="mainMenu">
        <ul class="navbar-nav">
          <?php foreach ($menuTree as $item): $children = $item['children']; ?>
            <li class="nav-item<?php echo $children ? ' dropdown' : ''; ?>">
              <a class="nav-link<?php echo $children ? ' dropdown-toggle' : ''; ?>" href="<?php echo cms_h(gew_menu_url($item)); ?>"<?php echo $children ? ' role="button" data-bs-toggle="dropdown" aria-expanded="false"' : ''; ?>><?php echo cms_h((string) $item['label']); ?></a>
              <?php if ($children): ?><ul class="dropdown-menu"><?php foreach ($children as $child): ?><li><a class="dropdown-item" href="<?php echo cms_h(gew_menu_url($child)); ?>"><?php echo cms_h((string) $child['label']); ?></a></li><?php endforeach; ?></ul><?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </nav>
</header>
<?php
if (empty($pageNotFound)) {
  $announcementLayout = __DIR__ . '/layouts/announcement.php';
  if (file_exists($announcementLayout)) {
    include $announcementLayout;
  }
}
?>
