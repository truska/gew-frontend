<?php
/**
 * Frontend edit-link helpers for content blocks.
 *
 * Default usage inside a layout:
 *   echo cms_render_frontend_edit_button($contentItem, ['form_id' => $contentSourceFormId ?? null]);
 *
 * If source_form_id is present on the content row, the explicit form_id option
 * is optional. The option exists for layouts that need a manual override.
 */

function cms_frontend_edit_first_ip_value(?string $value): string {
  $value = trim((string) $value);
  if ($value === '') {
    return '';
  }

  if (strpos($value, ',') !== false) {
    $parts = explode(',', $value);
    $value = trim((string) ($parts[0] ?? ''));
  }

  return $value;
}

function cms_frontend_pref(string $name, $default = '') {
  $value = null;

  if (function_exists('cms_pref')) {
    $candidate = cms_pref($name, null);
    if ($candidate !== null && $candidate !== '') {
      $value = $candidate;
    }
  }

  if (($value === null || $value === '') && isset($GLOBALS['prefs']) && is_array($GLOBALS['prefs'])) {
    if (array_key_exists($name, $GLOBALS['prefs']) && $GLOBALS['prefs'][$name] !== null && $GLOBALS['prefs'][$name] !== '') {
      $value = $GLOBALS['prefs'][$name];
    }
  }

  if ($value === null || $value === '') {
    return $default;
  }

  return $value;
}

function cms_frontend_edit_allowed(): bool {
  static $allowed = null;

  if ($allowed !== null) {
    return $allowed;
  }

  $remoteAddr = cms_frontend_edit_first_ip_value((string) ($_SERVER['HTTP_CF_CONNECTING_IP'] ?? ''));
  if ($remoteAddr === '') {
    $remoteAddr = cms_frontend_edit_first_ip_value((string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''));
  }
  if ($remoteAddr === '') {
    $remoteAddr = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
  }

  $editPrefOn = (
    cms_frontend_pref('prefFrontendEditOn', 'No') === 'Yes'
    || cms_frontend_pref('prefContentEditOn', 'No') === 'Yes'
    || cms_frontend_pref('prefFooterDebugOn', 'No') === 'Yes'
  );

  $allowed = $editPrefOn
    || ($remoteAddr !== '' && $remoteAddr === (string) cms_frontend_pref('prefTruskaIP', ''))
    || ($remoteAddr !== '' && $remoteAddr === (string) cms_frontend_pref('prefCoderIP', ''))
    || ($remoteAddr !== '' && $remoteAddr === (string) cms_frontend_pref('prefClientIP', ''))
    || ($remoteAddr !== '' && $remoteAddr === (string) cms_frontend_pref('prefClient1IP', ''))
    || (function_exists('cms_is_logged_in') && cms_is_logged_in());

  return $allowed;
}

function cms_frontend_edit_hex(string $value, string $fallback): string {
  $value = trim($value);
  if (preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
    return strtoupper($value);
  }

  return $fallback;
}

function cms_frontend_edit_button_colors(): array {
  $bg = (string) cms_frontend_pref('prefContentEditBgColor', (string) cms_frontend_pref('prefFrontendEditBgColor', '#198754'));
  $fg = (string) cms_frontend_pref('prefContentEditTextColor', (string) cms_frontend_pref('prefFrontendEditTextColor', '#FFFFFF'));

  return [
    'bg' => cms_frontend_edit_hex($bg, '#198754'),
    'fg' => cms_frontend_edit_hex($fg, '#FFFFFF'),
  ];
}

function cms_frontend_edit_form_id(array $contentItem, array $options = []): int {
  $candidateKeys = ['form_id', 'source_form_id', 'frm'];

  foreach ($candidateKeys as $key) {
    if (isset($options[$key]) && is_numeric((string) $options[$key])) {
      return (int) $options[$key];
    }
  }

  foreach ($candidateKeys as $key) {
    if (isset($contentItem[$key]) && is_numeric((string) $contentItem[$key])) {
      return (int) $contentItem[$key];
    }
  }

  // Legacy frontend rows may not carry source_form_id; infer from cms_form.
  $tableName = trim((string) ($contentItem['table_name'] ?? $contentItem['table'] ?? 'content'));
  $resolved = cms_frontend_edit_resolve_form_id_for_table($tableName);
  if ($resolved > 0) {
    return $resolved;
  }

  return 0;
}

function cms_frontend_edit_record_id(array $contentItem, array $options = []): int {
  $candidateKeys = ['record_id', 'id'];

  foreach ($candidateKeys as $key) {
    if (isset($options[$key]) && is_numeric((string) $options[$key])) {
      return (int) $options[$key];
    }
  }

  foreach ($candidateKeys as $key) {
    if (isset($contentItem[$key]) && is_numeric((string) $contentItem[$key])) {
      return (int) $contentItem[$key];
    }
  }

  return 0;
}

function cms_frontend_edit_record_url(int $formId, int $recordId): string {
  if ($formId <= 0 || $recordId <= 0) {
    return '';
  }

  return cms_base_url('/wccms/recordEditv5.php')
    . '?frm=' . rawurlencode((string) $formId)
    . '&id=' . rawurlencode((string) $recordId);
}

function cms_frontend_edit_resolve_form_id_for_table(string $tableName): int {
  static $cache = [];

  $tableName = strtolower(trim($tableName));
  if ($tableName === '') {
    return 0;
  }

  if (array_key_exists($tableName, $cache)) {
    return $cache[$tableName];
  }

  $cache[$tableName] = 0;
  global $pdo, $conn;

  if ($pdo instanceof PDO) {
    try {
      $sql = "SELECT f.id
              FROM cms_form f
              LEFT JOIN cms_table t ON t.id = f.`table`
              WHERE LOWER(COALESCE(t.name, '')) = :table_name
                 OR LOWER(COALESCE(f.`table`, '')) = :table_name
              ORDER BY f.id ASC
              LIMIT 1";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([':table_name' => $tableName]);
      $id = (int) ($stmt->fetchColumn() ?: 0);
      if ($id > 0) {
        $cache[$tableName] = $id;
        return $id;
      }
    } catch (Throwable $e) {
      // Fall through to mysqli attempt.
    }
  }

  if ($conn instanceof mysqli) {
    $safe = mysqli_real_escape_string($conn, $tableName);
    $sql = "SELECT f.id
            FROM cms_form f
            LEFT JOIN cms_table t ON t.id = f.`table`
            WHERE LOWER(IFNULL(t.name, '')) = '{$safe}'
               OR LOWER(IFNULL(f.`table`, '')) = '{$safe}'
            ORDER BY f.id ASC
            LIMIT 1";
    $res = mysqli_query($conn, $sql);
    if ($res instanceof mysqli_result) {
      $row = mysqli_fetch_assoc($res);
      $id = (int) ($row['id'] ?? 0);
      if ($id > 0) {
        $cache[$tableName] = $id;
        return $id;
      }
    }
  }

  return 0;
}

function cms_render_frontend_edit_button(array $contentItem, array $options = []): string {
  if (!cms_frontend_edit_allowed()) {
    return '';
  }

  $formId = cms_frontend_edit_form_id($contentItem, $options);
  $recordId = cms_frontend_edit_record_id($contentItem, $options);
  $url = cms_frontend_edit_record_url($formId, $recordId);
  if ($url === '') {
    return '';
  }

  $label = trim((string) ($options['label'] ?? 'Edit'));
  if ($label === '') {
    $label = 'Edit';
  }

  $title = trim((string) ($options['title'] ?? 'Edit this content in WCCMS'));
  if ($title === '') {
    $title = 'Edit this content in WCCMS';
  }

  $extraClass = trim((string) ($options['class'] ?? ''));
  $classes = 'cms-frontend-edit-button';
  if ($extraClass !== '') {
    $classes .= ' ' . $extraClass;
  }

  $colors = cms_frontend_edit_button_colors();

  return '<a class="' . cms_h($classes) . '"'
    . ' href="' . cms_h($url) . '"'
    . ' target="_blank"'
    . ' rel="noopener"'
    . ' title="' . cms_h($title) . '"'
    . ' aria-label="' . cms_h($title) . '"'
    . ' style="--cms-edit-bg:' . cms_h($colors['bg']) . ';--cms-edit-fg:' . cms_h($colors['fg']) . ';"'
    . '>'
    . '<i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>'
    . '</a>';
}
