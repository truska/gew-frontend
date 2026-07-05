<?php
// Frontend preferences loader:
// - Loads modern cms_pref()/cms_load_preferences() helpers.
// - Keeps legacy $prefs[key] => value compatibility for older templates.
require_once __DIR__ . '/lib/cms_prefs.php';

$cmsShortcodesPath = __DIR__ . '/lib/cms_shortcodes.php';
if (file_exists($cmsShortcodesPath)) {
  require_once $cmsShortcodesPath;
}
require_once __DIR__ . '/lib/cms_frontend_edit.php';
require_once __DIR__ . '/lib/cms_facts.php';
require_once __DIR__ . '/lib/cms_icons.php';

$CMS_PREFS = cms_load_preferences('web');
$legacyPrefs = [];

foreach ($CMS_PREFS as $name => $row) {
  if (is_array($row) && array_key_exists('value', $row)) {
    $legacyPrefs[$name] = $row['value'];
  } else {
    $legacyPrefs[$name] = $row;
  }
}

if (!isset($prefs) || !is_array($prefs)) {
  $prefs = $legacyPrefs;
} else {
  // Keep existing runtime values first, backfill missing keys from CMS prefs.
  $prefs += $legacyPrefs;
}
