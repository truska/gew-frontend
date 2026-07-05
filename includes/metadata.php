<?php
/**
 * Central document metadata.
 *
 * Supports both the current PDO page variables and the legacy page controller.
 * This file should be included once, near the start of <head>.
 */

$metadataPage = [];
if (isset($pageData) && is_array($pageData)) {
    $metadataPage = $pageData;
} elseif (isset($rowpage) && is_array($rowpage)) {
    $metadataPage = $rowpage;
}

$metadataPrefs = isset($prefs) && is_array($prefs) ? $prefs : [];
$metadataPreference = static function (string $name, string $default = '') use ($metadataPrefs): string {
    if (function_exists('cms_pref')) {
        return trim((string) cms_pref($name, $default));
    }
    if (isset($metadataPrefs[$name])) {
        return trim((string) $metadataPrefs[$name]);
    }
    return $default;
};

$metadataEscape = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$metadataSiteName = $metadataPreference('prefSiteName', 'Green Energy Wind');
$metadataCompanyName = $metadataPreference('prefCompanyName', $metadataSiteName);
$metadataSpecific = isset($specificMetadata) && is_array($specificMetadata) ? $specificMetadata : [];
$metadataPageName = trim((string) ($metadataPage['name'] ?? ''));
$metadataFinalFallback = implode(' | ', array_values(array_filter(
    [$metadataCompanyName, $metadataPageName],
    static fn(string $value): bool => $value !== ''
)));

// Rule 1: a controller may supply record-specific values before loading header.
$metadataTitle = trim((string) ($metadataSpecific['title'] ?? ''));
$metadataDescription = trim((string) ($metadataSpecific['description'] ?? ''));
$metadataKeywords = trim((string) ($metadataSpecific['keywords'] ?? ''));

// Rule 2: use metadata stored against the page.
if ($metadataTitle === '') {
    $metadataTitle = trim((string) ($metadataPage['titletag'] ?? ''));
}
if ($metadataDescription === '') {
    $metadataDescription = trim((string) ($metadataPage['metadescription'] ?? ''));
}
if ($metadataKeywords === '') {
    $metadataKeywords = trim((string) ($metadataPage['metakeywords'] ?? ''));
}

// Rule 3: use the global metadata preferences.
if ($metadataTitle === '') {
    $metadataTitle = $metadataPreference('prefMetaTitle');
}
if ($metadataDescription === '') {
    $metadataDescription = $metadataPreference('prefMetaDescription');
}
if ($metadataKeywords === '') {
    $metadataKeywords = $metadataPreference('prefMetaKeywords');
}

// Final fallback for any value still blank.
$metadataTitle = $metadataTitle !== '' ? $metadataTitle : $metadataFinalFallback;
$metadataDescription = $metadataDescription !== '' ? $metadataDescription : $metadataFinalFallback;
$metadataKeywords = $metadataKeywords !== '' ? $metadataKeywords : $metadataFinalFallback;

$metadataBaseUrl = isset($baseURL) && trim((string) $baseURL) !== ''
    ? rtrim((string) $baseURL, '/')
    : (function_exists('cms_base_url') ? rtrim(cms_base_url(), '/') : '');
$metadataRequestPath = (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/');
$metadataCanonical = $metadataBaseUrl . ($metadataRequestPath === '/' ? '/' : '/' . ltrim($metadataRequestPath, '/'));

$metadataSiteSearchEnabled = strcasecmp($metadataPreference('prefSiteSearchOn', 'Yes'), 'No') !== 0;
$metadataRobots = $metadataSiteSearchEnabled ? 'index,follow' : 'noindex,nofollow';
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $metadataEscape($metadataTitle); ?></title>
<?php if ($metadataDescription !== ''): ?>
  <meta name="description" content="<?php echo $metadataEscape($metadataDescription); ?>">
<?php endif; ?>
<?php if ($metadataKeywords !== ''): ?>
  <meta name="keywords" content="<?php echo $metadataEscape($metadataKeywords); ?>">
<?php endif; ?>
<meta name="robots" content="<?php echo $metadataRobots; ?>">
<?php if ($metadataCompanyName !== ''): ?>
  <meta name="author" content="<?php echo $metadataEscape($metadataCompanyName); ?>">
  <meta name="copyright" content="<?php echo $metadataEscape($metadataCompanyName); ?>">
<?php endif; ?>
<?php if ($metadataCanonical !== ''): ?>
  <link rel="canonical" href="<?php echo $metadataEscape($metadataCanonical); ?>">
<?php endif; ?>
<meta property="og:type" content="website">
<meta property="og:title" content="<?php echo $metadataEscape($metadataTitle); ?>">
<?php if ($metadataDescription !== ''): ?>
  <meta property="og:description" content="<?php echo $metadataEscape($metadataDescription); ?>">
<?php endif; ?>
<?php if ($metadataCanonical !== ''): ?>
  <meta property="og:url" content="<?php echo $metadataEscape($metadataCanonical); ?>">
<?php endif; ?>
<?php if ($metadataSiteName !== ''): ?>
  <meta property="og:site_name" content="<?php echo $metadataEscape($metadataSiteName); ?>">
<?php endif; ?>
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="<?php echo $metadataEscape($metadataTitle); ?>">
<?php if ($metadataDescription !== ''): ?>
  <meta name="twitter:description" content="<?php echo $metadataEscape($metadataDescription); ?>">
<?php endif; ?>
