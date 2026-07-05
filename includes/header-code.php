<?php
if (!function_exists('cms_h')) {
  function cms_h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
  }
}

if (!function_exists('cms_base_url')) {
  function cms_base_url() {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
      || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $scheme = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
    return $scheme . '://' . $host;
  }
}

$prefsPath = __DIR__ . '/prefs.php';
if (file_exists($prefsPath)) {
  require_once $prefsPath;
}

$cmsLogPath = __DIR__ . '/lib/cms_log.php';
if (file_exists($cmsLogPath)) {
  require_once $cmsLogPath;
}

$baseURL = $baseURL ?? cms_base_url();
$pageTitle = $pageTitle ?? ($rowpage['titletag'] ?? ($rowpage['name'] ?? 'Website'));
$useLegacyBootstrap = !file_exists(__DIR__ . '/menu.php');
?>
<!doctype html>
<html lang="en">
<head>
  <?php
    $metadataPath = __DIR__ . '/metadata.php';
    if (file_exists($metadataPath)) {
      include $metadataPath;
    }
    $googlePath = __DIR__ . '/google.php';
    if (file_exists($googlePath)) {
      include $googlePath;
    }
  ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@400;600;700&family=Work+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <?php
    $bootstrapCompatCssPath = __DIR__ . '/../css/bootstrap5-legacy-compat.css';
    if (file_exists($bootstrapCompatCssPath)):
      $bootstrapCompatCssVer = (string) filemtime($bootstrapCompatCssPath);
  ?>
    <link rel="stylesheet" href="<?php echo $baseURL; ?>/css/bootstrap5-legacy-compat.css?v=<?php echo rawurlencode($bootstrapCompatCssVer); ?>">
  <?php endif; ?>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" crossorigin="anonymous" referrerpolicy="no-referrer">
  <?php
    $styleCssPath = __DIR__ . '/../css/style.css';
    if (file_exists($styleCssPath)):
      $styleCssVer = (string) filemtime($styleCssPath);
  ?>
    <link rel="stylesheet" href="<?php echo $baseURL; ?>/css/style.css?v=<?php echo rawurlencode($styleCssVer); ?>">
  <?php endif; ?>
  <?php
    $siteCssPath = __DIR__ . '/../css/site.css';
    if (file_exists($siteCssPath)):
      $siteCssVer = (string) filemtime($siteCssPath);
  ?>
    <link rel="stylesheet" href="<?php echo $baseURL; ?>/css/site.css?v=<?php echo rawurlencode($siteCssVer); ?>">
  <?php endif; ?>
  <?php
    $menuBs5CssPath = __DIR__ . '/../css/menu-bs5.css';
    if (file_exists($menuBs5CssPath)):
      $menuBs5CssVer = (string) filemtime($menuBs5CssPath);
  ?>
    <link rel="stylesheet" href="<?php echo $baseURL; ?>/css/menu-bs5.css?v=<?php echo rawurlencode($menuBs5CssVer); ?>">
  <?php endif; ?>
  <?php
    $cmsImagesPath = __DIR__ . '/lib/cms_images.php';
    if (file_exists($cmsImagesPath)) {
      require_once $cmsImagesPath;
      // This site uses the local /css and /js MagicZoomPlus distribution.
      echo cms_magictoolbox_assets_html('magiczoomplus');
    }
  ?>
  <?php
    $customHeadPath = __DIR__ . '/custom-head.php';
    if (file_exists($customHeadPath)) {
      include $customHeadPath;
    }
    if (!empty($rowpage['headercode'])) {
      echo $rowpage['headercode'];
    }
    $prefscss = [
      'pref2ndCol' => '',
      'prefH2Col' => '',
      'prefH3Col' => '',
      'prefTextCol' => '',
      'prefTextLinkCol' => '',
      'prefTextHoverCol' => '',
      'prefMenuBGCol' => '',
      'prefMenuTextCol' => '',
      'prefMenuActiveTextCol' => '',
      'prefMenuActiveBGCol' => '',
      'prefMenuHoverTextCol' => '',
      'prefMenuHoverBGCol' => '',
      'prefMenuDDTextCol' => '',
      'prefMenuDDBGCol' => '',
      'prefMenuDDHoverBGCol' => '',
      'prefFooterBGCol' => '',
      'prefFooterH3TextCol' => '',
      'prefscssocialIconTextCol' => '',
      'prefscssocialIconHoverTextCol' => '',
    ];
    if (isset($conn) && $conn instanceof mysqli) {
      $skinQuery = mysqli_query($conn, "SELECT `name`, `value` FROM `cms_skin`");
      if ($skinQuery) {
        while ($skinRow = mysqli_fetch_assoc($skinQuery)) {
          if (isset($skinRow['name'])) {
            $prefscss[$skinRow['name']] = $skinRow['value'] ?? '';
          }
        }
      }
    }
    // Legacy templates already define nav visuals in style.css; avoid CMS skin
    // forcing a solid navbar background during migration.
    if ($useLegacyBootstrap) {
      $prefscss['prefMenuBGCol'] = '';
      $prefscss['prefMenuTextCol'] = '';
      $prefscss['prefMenuActiveTextCol'] = '';
      $prefscss['prefMenuActiveBGCol'] = '';
      $prefscss['prefMenuHoverTextCol'] = '';
      $prefscss['prefMenuHoverBGCol'] = '';
      $prefscss['prefMenuDDTextCol'] = '';
      $prefscss['prefMenuDDBGCol'] = '';
      $prefscss['prefMenuDDHoverBGCol'] = '';
    }
    $stylePrefsPath = __DIR__ . '/stylesheetcmscss.php';
    if (file_exists($stylePrefsPath)) {
      include $stylePrefsPath;
    }
  ?>
  <script src="https://kit.fontawesome.com/3e4371248d.js" crossorigin="anonymous"></script>
</head>
<body>
