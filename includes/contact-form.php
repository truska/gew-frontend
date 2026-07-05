<!-- START contact-form.php -->
<?php
require_once __DIR__ . '/lib/cms_log.php';
require_once __DIR__ . '/lib/forms.php';
require_once __DIR__ . '/../wccms/includes/email.php';

$contactSlug = $rowpage['slug'] ?? 'contact';
$baseURL = $baseURL ?? cms_base_url();

// Match dev-wc behavior first, then fall back to site-specific slugs.
$formSlugCandidates = array_values(array_unique(array_filter([
  'contact',
  $contactSlug,
  'contact-us',
  'contact-ms-timber',
])));

$form = cms_default_contact_form();
foreach ($formSlugCandidates as $candidateSlug) {
  $candidate = cms_load_form_by_slug((string) $candidateSlug);
  if ((int) ($candidate['id'] ?? 0) > 0) {
    $form = $candidate;
    break;
  }
}

$fields = cms_load_form_fields((int) ($form['id'] ?? 0));

if (empty($_SESSION['contact_form_token'])) {
  $_SESSION['contact_form_token'] = bin2hex(random_bytes(16));
}

$captchaVersion = trim((string) cms_pref('prefCaptchaVer', '2'));
if ($captchaVersion !== '3') {
  $captchaVersion = '2';
}
$captchaSiteKey = trim((string) cms_pref(
  $captchaVersion === '3' ? 'prefCaptchaSiteKeyv3' : 'prefCaptchaSiteKey',
  (string) cms_pref('prefCaptchaSiteKey', (string) ($prefs['prefCaptchaSiteKey'] ?? ''))
));
$captchaSecret = trim((string) cms_pref(
  $captchaVersion === '3' ? 'prefCaptchaSecretv3' : 'prefCaptchaSecret',
  (string) cms_pref('prefCaptchaSecret', (string) cms_pref('prefCaptchaSecretKey', (string) cms_pref('prefCaptchaSecretKeyV3', (string) ($prefs['prefCaptchaSecretKey'] ?? ''))))
));
$captchaEnabled = ((string) cms_pref('prefCaptchaEnabled', (string) ($prefs['prefCaptcha'] ?? 'No')) === 'Yes');
$useCaptcha = $captchaEnabled && $captchaSiteKey !== '' && $captchaSecret !== '';
$captchaMinScore = (float) cms_pref('prefCaptchaMinScore', 0.3);

$spamLookupEnabled = cms_pref('prefIPSpamCheck', 'No') === 'Yes';
$spamOkThreshold = (int) cms_pref('prefSpamOK', 10);
$spamNoSendThreshold = (int) cms_pref('prefSpamNoSend', 30);
$spamNoSaveThreshold = (int) cms_pref('prefSpamNoSave', 60);
$honeypotPoints = 40;

$honeypotFields = ['name', 'email', 'tel', 'realname', 'company'];

$formStatus = null;
$fieldErrors = [];
$fieldValues = $_SESSION['contact_form_values'] ?? [];
unset($_SESSION['contact_form_values']);

if (!empty($_SESSION['contact_form_flash'])) {
  $formStatus = $_SESSION['contact_form_flash'];
  unset($_SESSION['contact_form_flash']);
}

function cms_map_form_fields(array $fields, array $values): array {
  $mapped = [
    'name' => null,
    'email' => null,
    'tel' => null,
    'message' => null,
    'alt1' => null,
    'alt2' => null,
    'alt3' => null,
    'alt4' => null,
    'alt5' => null,
  ];

  $idMap = [
    1 => 'name',
    7 => 'email',
    12 => 'tel',
    20 => 'message',
  ];

  foreach ($fields as $field) {
    $name = $field['input_name'] ?? $field['name'] ?? '';
    $fieldId = (int) ($field['field_id'] ?? 0);
    $mapKey = $idMap[$fieldId] ?? '';

    if ($name === '') {
      continue;
    }

    // Avoid clobbering canonical keys (name/email/tel/message) when the same
    // field type appears multiple times (e.g. two "text" fields).
    if ($mapKey !== '' && !empty($mapped[$mapKey])) {
      $mapKey = '';
    }

    if ($mapKey === '' && array_key_exists($name, $mapped) && empty($mapped[$name])) {
      $mapKey = $name;
    }

    if ($mapKey === '') {
      foreach (['alt1', 'alt2', 'alt3', 'alt4', 'alt5'] as $altKey) {
        if (empty($mapped[$altKey])) {
          $mapKey = $altKey;
          break;
        }
      }
    }

    if ($mapKey === '') {
      continue;
    }

    $mapped[$mapKey] = $values[$name] ?? null;
  }

  return $mapped;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $postedToken = $_POST['csrf_token'] ?? '';
  if ($postedToken === '' || !hash_equals($_SESSION['contact_form_token'], $postedToken)) {
    $formStatus = [
      'type' => 'error',
      'message' => 'Your session expired. Please refresh and try again.',
    ];
  } else {
    foreach ($fields as $field) {
      $name = $field['input_name'] ?? $field['name'] ?? '';
      if ($name === '') {
        continue;
      }
      $value = trim((string) ($_POST[$name] ?? ''));
      $fieldValues[$name] = $value;

      $required = (int) ($field['required'] ?? 0) === 1;
      if ($required && $value === '') {
        $fieldErrors[$name] = 'This field is required.';
        continue;
      }

      $type = strtolower((string) ($field['field_type'] ?? 'text'));
      if ($type === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
        $fieldErrors[$name] = 'Please enter a valid email address.';
      }
    }

    if ($useCaptcha) {
      $captchaToken = trim((string) ($_POST['g-recaptcha-response'] ?? ($_POST['recaptcha_token'] ?? '')));
      if ($captchaToken === '') {
        $formStatus = [
          'type' => 'error',
          'message' => 'Please complete the captcha check.',
        ];
      } else {
        $verifyContext = stream_context_create([
          'http' => [
            'method' => 'POST',
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query([
              'secret' => $captchaSecret,
              'response' => $captchaToken,
              'remoteip' => cms_get_client_ip(),
            ]),
            'timeout' => 4,
          ],
        ]);
        $captchaResponse = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $verifyContext);
        $captchaData = $captchaResponse ? json_decode($captchaResponse, true) : null;
        $captchaOk = is_array($captchaData) && !empty($captchaData['success']);
        if ($captchaOk && $captchaVersion === '3') {
          $captchaScore = (float) ($captchaData['score'] ?? 0);
          $captchaAction = trim((string) ($captchaData['action'] ?? ''));
          if ($captchaScore < $captchaMinScore || ($captchaAction !== '' && $captchaAction !== 'contact_form')) {
            $captchaOk = false;
          }
        }
        if (!$captchaOk) {
          $formStatus = [
            'type' => 'error',
            'message' => 'Captcha verification failed. Please try again.',
          ];
        }
      }
    }

    if ($formStatus === null && !empty($fieldErrors)) {
      $formStatus = [
        'type' => 'error',
        'message' => 'Please fix the highlighted fields and resubmit.',
      ];
    }
  }

  if ($formStatus === null) {
    $honeypotValues = [];
    foreach ($honeypotFields as $honeypotField) {
      $honeypotValues[$honeypotField] = trim((string) ($_POST[$honeypotField] ?? ''));
    }

    $meta = cms_collect_request_meta($spamLookupEnabled);
    $spamNotesMode = cms_spam_notes_mode();
    $includeZeroChecks = ($spamNotesMode === 'all');
    $spamReasons = [];
    $spamAuditLines = [];
    $spamScore = cms_score_honeypot($honeypotValues, $spamReasons, $honeypotPoints);
    foreach ($honeypotValues as $honeypotField => $honeypotValue) {
      $honeypotHit = (trim((string) $honeypotValue) !== '');
      if ($honeypotHit || $includeZeroChecks) {
        $spamAuditLines[] = 'Honeypot [' . $honeypotField . '] ' . ($honeypotHit ? $honeypotPoints : 0);
      }
    }

    $mappedFields = cms_map_form_fields($fields, $fieldValues);
    $valuesByFormFieldId = [];
    foreach ($fields as $field) {
      $formFieldId = (int) ($field['form_field_id'] ?? 0);
      $inputName = $field['input_name'] ?? $field['name'] ?? '';
      if ($formFieldId > 0 && $inputName !== '') {
        $valuesByFormFieldId[$formFieldId] = $fieldValues[$inputName] ?? '';
      }
    }

    $rules = cms_load_spam_rules((int) ($form['id'] ?? 0));
    $spamScore += cms_score_spam_rules($valuesByFormFieldId, $rules, $spamReasons);
    $spamAuditLines = array_merge($spamAuditLines, cms_build_spam_rule_audit($valuesByFormFieldId, $rules, $includeZeroChecks));

    $spamAction = 'ok';
    $sendAdmin = true;
    $sendUser = true;
    $saveSubmission = true;

    if ($spamScore > $spamNoSaveThreshold) {
      $spamAction = 'nosave';
      $sendAdmin = false;
      $sendUser = false;
      $saveSubmission = false;
    } elseif ($spamScore > $spamOkThreshold) {
      $spamAction = 'nosend';
      $sendAdmin = false;
    }

    $ipData = $meta['ip_lookup'] ?? [];
    $countryRow = null;
    if (!empty($ipData['country'])) {
      $countryRow = cms_country_lookup((string) $ipData['country']);
    }
    $countryName = $countryRow['name'] ?? ($ipData['country'] ?? null);
    $countrySpamScore = (int) ($countryRow['formspamscore'] ?? 0);
    $countryCode = strtoupper(trim((string) ($ipData['country'] ?? '')));

    if ($countrySpamScore > 0) {
      $spamScore += $countrySpamScore;
      $spamReasons[] = 'Country [' . $countryCode . '] ' . $countrySpamScore;
    }
    if ($countryCode !== '' || $includeZeroChecks) {
      $spamAuditLines[] = 'Country [' . ($countryCode !== '' ? $countryCode : 'n/a') . '] ' . $countrySpamScore;
    }
    $spamNotesOutput = $includeZeroChecks ? $spamAuditLines : $spamReasons;
    $spamNotesText = !empty($spamNotesOutput) ? implode("\n", $spamNotesOutput) : null;

    $submissionId = null;
    if ($saveSubmission && cms_form_submissions_table_ready()) {
      try {
        $stmt = $pdo->prepare(
          'INSERT INTO contact_forms
            (form_id, name, email, tel, message, alt1, alt2, alt3, alt4, alt5,
             spam_score, spam_action, spam_notes, is_spam, honeypot_hit,
             ip, ip_country, ip_country_code, ip_region, ip_city, ip_timezone, ip_postal,
             ip_org, ip_isp, ip_lat, ip_lon,
             user_agent, browser, platform, language, referer,
             data_json, meta_json, showonweb, archived)
           VALUES
            (:form_id, :name, :email, :tel, :message, :alt1, :alt2, :alt3, :alt4, :alt5,
             :spam_score, :spam_action, :spam_notes, :is_spam, :honeypot_hit,
             :ip, :ip_country, :ip_country_code, :ip_region, :ip_city, :ip_timezone, :ip_postal,
             :ip_org, :ip_isp, :ip_lat, :ip_lon,
             :user_agent, :browser, :platform, :language, :referer,
             :data_json, :meta_json, :showonweb, :archived)'
        );
        $stmt->execute([
          ':form_id' => (int) ($form['id'] ?? 0),
          ':name' => $mappedFields['name'],
          ':email' => $mappedFields['email'],
          ':tel' => $mappedFields['tel'],
          ':message' => $mappedFields['message'],
          ':alt1' => $mappedFields['alt1'],
          ':alt2' => $mappedFields['alt2'],
          ':alt3' => $mappedFields['alt3'],
          ':alt4' => $mappedFields['alt4'],
          ':alt5' => $mappedFields['alt5'],
          ':spam_score' => $spamScore,
          ':spam_action' => $spamAction,
          ':spam_notes' => $spamNotesText,
          ':is_spam' => ($spamAction !== 'ok') ? 1 : 0,
          ':honeypot_hit' => ($spamScore > 0 && !empty(array_filter($honeypotValues))) ? 1 : 0,
          ':ip' => $meta['ip'] ?? null,
          ':ip_country' => $countryName,
          ':ip_country_code' => $ipData['country'] ?? null,
          ':ip_region' => $ipData['region'] ?? null,
          ':ip_city' => $ipData['city'] ?? null,
          ':ip_timezone' => $ipData['timezone'] ?? null,
          ':ip_postal' => $ipData['postal'] ?? null,
          ':ip_org' => $ipData['org'] ?? null,
          ':ip_isp' => $ipData['org'] ?? null,
          ':ip_lat' => $ipData['loc_lat'] ?? null,
          ':ip_lon' => $ipData['loc_lon'] ?? null,
          ':user_agent' => $meta['user_agent'] ?? null,
          ':browser' => $meta['browser'] ?? null,
          ':platform' => $meta['platform'] ?? null,
          ':language' => $meta['language'] ?? null,
          ':referer' => $meta['referer'] ?? null,
          ':data_json' => json_encode($fieldValues, JSON_UNESCAPED_UNICODE),
          ':meta_json' => json_encode([
            'honeypot' => $honeypotValues,
            'spam_reasons' => $spamReasons,
            'ip_lookup' => $ipData,
          ], JSON_UNESCAPED_UNICODE),
          ':showonweb' => 'Yes',
          ':archived' => 0,
        ]);
        $submissionId = (int) $pdo->lastInsertId();
      } catch (PDOException $e) {
        $saveSubmission = false;
      }
    }

    $userEmail = $mappedFields['email'];
    $adminEmail = (string) cms_pref('prefManageEmail', (string) cms_pref('prefEmail', ''));
    $adminSubject = $form['admin_subject'] ?? 'New contact form submission';
    $userSubject = $form['ack_subject'] ?? 'We received your request';

    if ($sendUser && $userEmail) {
      $userBody = '<h2>Thanks for reaching out!</h2>'
        . '<p>We have received your message and will reply shortly.</p>'
        . '<p><strong>Name:</strong> ' . cms_h((string) ($mappedFields['name'] ?? '')) . '<br>'
        . '<strong>Email:</strong> ' . cms_h((string) $userEmail) . '</p>';
      cms_send_mail((string) $userEmail, (string) $userSubject, $userBody, '', 'web');
    }

    if ($sendAdmin && $adminEmail !== '') {
      $honeypotPassed = empty(array_filter($honeypotValues));
      $honeypotLabel = $honeypotPassed ? 'PASSED' : 'FAILED';
      $honeypotColor = $honeypotPassed ? '#198754' : '#dc3545';
      $submissionLabel = $submissionId ? (' [' . (int) $submissionId . ']') : '';

      $detailsRows = '';
      foreach ($fieldValues as $key => $value) {
        $label = $key;
        foreach ($fields as $field) {
          $fieldName = $field['input_name'] ?? $field['name'] ?? '';
          if ($fieldName === $key) {
            $label = $field['label'] ?? $key;
            break;
          }
        }
        $detailsRows .= '<tr><td style="padding:6px 10px;border:1px solid #e5e7eb;"><strong>' . cms_h((string) $label) . '</strong></td>'
          . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">' . nl2br(cms_h((string) $value)) . '</td></tr>';
      }

      $metaRows = '';
      $metaRows .= '<tr><td style="padding:6px 10px;border:1px solid #e5e7eb;"><strong>IP</strong></td>'
        . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">' . cms_h((string) ($meta['ip'] ?? '')) . '</td></tr>';
      $metaRows .= '<tr><td style="padding:6px 10px;border:1px solid #e5e7eb;"><strong>Browser</strong></td>'
        . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">' . cms_h((string) ($meta['browser'] ?? '')) . '</td></tr>';
      $metaRows .= '<tr><td style="padding:6px 10px;border:1px solid #e5e7eb;"><strong>Platform</strong></td>'
        . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">' . cms_h((string) ($meta['platform'] ?? '')) . '</td></tr>';
      $metaRows .= '<tr><td style="padding:6px 10px;border:1px solid #e5e7eb;"><strong>Language</strong></td>'
        . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">' . cms_h((string) ($meta['language'] ?? '')) . '</td></tr>';
      $metaRows .= '<tr><td style="padding:6px 10px;border:1px solid #e5e7eb;"><strong>Spam Score</strong></td>'
        . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">' . cms_h((string) $spamScore) . ' (' . cms_h((string) $spamAction) . ') [' . cms_h((string) $spamOkThreshold) . ' | ' . cms_h((string) $spamNoSendThreshold) . ' | ' . cms_h((string) $spamNoSaveThreshold) . ']</td></tr>';

      if (!empty($ipData)) {
        $metaRows .= '<tr><td style="padding:6px 10px;border:1px solid #e5e7eb;"><strong>Country Code</strong></td>'
          . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">' . cms_h((string) ($ipData['country'] ?? '')) . '</td></tr>';
        $metaRows .= '<tr><td style="padding:6px 10px;border:1px solid #e5e7eb;"><strong>Location</strong></td>'
          . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">'
          . cms_h(trim((string) (($ipData['city'] ?? '') . ', ' . ($ipData['region'] ?? '') . ' ' . ($countryName ?? ''))))
          . '</td></tr>';
        $metaRows .= '<tr><td style="padding:6px 10px;border:1px solid #e5e7eb;"><strong>Timezone</strong></td>'
          . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">' . cms_h((string) ($ipData['timezone'] ?? '')) . '</td></tr>';
        $metaRows .= '<tr><td style="padding:6px 10px;border:1px solid #e5e7eb;"><strong>ISP/Org</strong></td>'
          . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">' . cms_h((string) ($ipData['org'] ?? '')) . '</td></tr>';
      }

      if (!empty($spamReasons)) {
        $metaRows .= '<tr><td style="padding:6px 10px;border:1px solid #e5e7eb;"><strong>Spam Notes</strong></td>'
          . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">' . cms_h(implode(' | ', $spamReasons)) . '</td></tr>';
      }

      $adminBody = '<h2>New Contact Submission'
        . '<span style="font-size:12px;font-weight:400;color:#9ca3af;">' . cms_h($submissionLabel) . '</span>'
        . '</h2>'
        . '<p><strong>Form:</strong> ' . cms_h((string) ($form['name'] ?? 'Contact'))
        . ' <span style="font-size:12px;font-weight:600;color:#b8860b;margin-left:8px;"><i class="fa-regular fa-honey-pot" aria-hidden="true"></i></span>'
        . ' <span style="font-size:12px;font-weight:600;color:' . $honeypotColor . ';margin-left:2px;">' . $honeypotLabel . '</span>'
        . '</p>'
        . '<h3>Submitted Details</h3>'
        . '<table style="border-collapse:collapse;width:100%;">' . $detailsRows . '</table>'
        . '<h3 style="margin-top:18px;">Meta / Device Info</h3>'
        . '<table style="border-collapse:collapse;width:100%;">' . $metaRows . '</table>';

      cms_send_mail($adminEmail, (string) $adminSubject, $adminBody, '', 'web');
    }

    cms_log_action('contact_form_submit', 'contact_forms', $submissionId, null, $form['name'] ?? 'Contact', 'web');
    $_SESSION['contact_form_flash'] = [
      'type' => 'success',
      'message' => $form['success_message'] ?? 'Thanks! Your message has been received.',
    ];

    if (!headers_sent()) {
      header('Location: ' . $baseURL . '/' . $contactSlug . '?sent=1');
      exit;
    }
    $formStatus = $_SESSION['contact_form_flash'];
    unset($_SESSION['contact_form_flash']);
    $fieldValues = [];
  } else {
    $_SESSION['contact_form_values'] = $fieldValues;
  }
}
?>

<style>
  .contact-v5-form .hp-field {
    position: absolute !important;
    left: -9999px !important;
    width: 1px !important;
    height: 1px !important;
    overflow: hidden !important;
  }
  .contact-v5-form .invalid-feedback {
    display: block;
  }
  .contact-v5-form textarea.form-control {
    min-height: 140px;
    resize: vertical;
  }
  .contact-v5-form .form-label {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
  }
  .contact-v5-form .form-tooltip-icon {
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    background: #6c757d;
    color: #fff;
    font-size: 0.7rem;
    line-height: 1rem;
    text-align: center;
    font-weight: 700;
    cursor: help;
    display: inline-block;
  }
  .contact-v5-form .form-check-label .form-tooltip-icon {
    margin-left: 0.25rem;
  }
  .contact-v5-form .form-text {
    display: block;
    margin-top: 0.65rem;
    margin-bottom: 0.2rem;
    padding-top: 0.2rem;
  }
  .contact-v5-form .form-field-block {
    margin-bottom: 1.8rem;
  }
  .contact-v5-form .form-field-block:last-of-type {
    margin-bottom: 1rem;
  }
  .contact-v5-form .form-note {
    margin-top: 0.75rem;
    margin-bottom: 0;
    color: #6c757d;
    font-size: 0.875rem;
  }
  .contact-v5-form .contact-v5-submit {
    background-color: #3ea244;
    border-color: #3ea244;
    color: #fff;
    font-weight: 600;
    transition: background-color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
  }
  .contact-v5-form .contact-v5-submit:hover,
  .contact-v5-form .contact-v5-submit:focus {
    background-color: #37913d;
    border-color: #37913d;
    color: #fff;
    box-shadow: 0 4px 10px rgba(62, 162, 68, 0.25);
    transform: translateY(-1px);
  }
  .contact-v5-form .contact-v5-submit:active {
    background-color: #2f7f35;
    border-color: #2f7f35;
    transform: translateY(0);
    box-shadow: 0 2px 5px rgba(62, 162, 68, 0.2);
  }
  .contact-v5-status {
    border-radius: 0.375rem;
    padding: 0.75rem 1rem;
    margin-bottom: 1rem;
    font-size: 0.95rem;
  }
  .contact-v5-status-success {
    background: #d1e7dd;
    color: #0f5132;
    border: 1px solid #badbcc;
  }
  .contact-v5-status-error {
    background: #f8d7da;
    color: #842029;
    border: 1px solid #f5c2c7;
  }
</style>

<form class="contact-v5-form" method="post" action="<?php echo cms_h($baseURL . '/' . $contactSlug); ?>" novalidate data-captcha-version="<?php echo cms_h($captchaVersion); ?>">
  <input type="hidden" name="csrf_token" value="<?php echo cms_h((string) $_SESSION['contact_form_token']); ?>">

  <?php if ($formStatus): ?>
    <div class="contact-v5-status contact-v5-status-<?php echo cms_h((string) ($formStatus['type'] ?? 'error')); ?>">
      <?php echo cms_h((string) ($formStatus['message'] ?? '')); ?>
    </div>
  <?php endif; ?>

  <div class="hp-field">
    <label for="name">Name</label>
    <input type="text" name="name" id="name" autocomplete="off">
  </div>
  <div class="hp-field">
    <label for="email">Email</label>
    <input type="text" name="email" id="email" autocomplete="off">
  </div>
  <div class="hp-field">
    <label for="tel">Telephone</label>
    <input type="text" name="tel" id="tel" autocomplete="off">
  </div>
  <div class="hp-field">
    <label for="realname">Real name</label>
    <input type="text" name="realname" id="realname" autocomplete="off">
  </div>
  <div class="hp-field">
    <label for="company">Company</label>
    <input type="text" name="company" id="company" autocomplete="off">
  </div>

  <?php foreach ($fields as $field): ?>
    <?php
      $name = $field['input_name'] ?? $field['name'] ?? '';
      if ($name === '') {
        continue;
      }
      $label = $field['label'] ?? $name;
      $type = strtolower((string) ($field['field_type'] ?? 'text'));
      $placeholder = (string) ($field['placeholder'] ?? '');
      $required = (int) ($field['required'] ?? 0) === 1;
      $value = $fieldValues[$name] ?? '';
      $hasError = isset($fieldErrors[$name]);
      $tooltip = trim((string) ($field['tooltip'] ?? ''));
      $fieldId = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
      if ($fieldId === '') {
        $fieldId = 'field_' . uniqid();
      }
    ?>
    <div class="form-field-block">
      <?php if ($type === 'checkbox'): ?>
        <div class="form-check">
          <input class="form-check-input <?php echo $hasError ? 'is-invalid' : ''; ?>" type="checkbox" name="<?php echo cms_h($name); ?>" id="<?php echo cms_h($fieldId); ?>" value="Yes" <?php echo ($value === 'Yes') ? 'checked' : ''; ?>>
          <label class="form-check-label" for="<?php echo cms_h($fieldId); ?>">
            <?php echo cms_h((string) $label); ?>
            <?php if ($required): ?><span class="text-danger">*</span><?php endif; ?>
            <?php if ($tooltip !== ''): ?>
              <span class="form-tooltip-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo cms_h($tooltip); ?>" aria-label="<?php echo cms_h($tooltip); ?>">i</span>
            <?php endif; ?>
          </label>
        </div>
      <?php else: ?>
        <label class="form-label" for="<?php echo cms_h($fieldId); ?>">
          <?php echo cms_h((string) $label); ?>
          <?php if ($required): ?><span class="text-danger">*</span><?php endif; ?>
          <?php if ($tooltip !== ''): ?>
            <span class="form-tooltip-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo cms_h($tooltip); ?>" aria-label="<?php echo cms_h($tooltip); ?>">i</span>
          <?php endif; ?>
        </label>
        <?php if ($type === 'textarea'): ?>
          <textarea class="form-control <?php echo $hasError ? 'is-invalid' : ''; ?>" name="<?php echo cms_h($name); ?>" id="<?php echo cms_h($fieldId); ?>" rows="4" placeholder="<?php echo cms_h($placeholder); ?>"><?php echo cms_h((string) $value); ?></textarea>
        <?php else: ?>
          <input class="form-control <?php echo $hasError ? 'is-invalid' : ''; ?>" type="<?php echo cms_h($type); ?>" name="<?php echo cms_h($name); ?>" id="<?php echo cms_h($fieldId); ?>" value="<?php echo cms_h((string) $value); ?>" placeholder="<?php echo cms_h($placeholder); ?>">
        <?php endif; ?>
      <?php endif; ?>
      <?php if (!empty($field['help_text'])): ?>
        <small class="form-text text-muted"><?php echo cms_h((string) $field['help_text']); ?></small>
      <?php endif; ?>
      <?php if ($hasError): ?>
        <div class="invalid-feedback"><?php echo cms_h((string) $fieldErrors[$name]); ?></div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>

  <?php if ($useCaptcha && $captchaVersion === '2'): ?>
    <div class="mb-3">
      <div class="g-recaptcha" data-sitekey="<?php echo cms_h($captchaSiteKey); ?>"></div>
    </div>
  <?php endif; ?>
  <?php if ($useCaptcha && $captchaVersion === '3'): ?>
    <input type="hidden" id="contact_recaptcha_token" name="recaptcha_token" value="">
  <?php endif; ?>

  <button type="submit" class="btn btn-lg w-100 contact-v5-submit">Send Message</button>
  <p class="form-note">By submitting, you agree to be contacted about your request.</p>
</form>

<?php if ($useCaptcha && $captchaVersion === '2'): ?>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>
<?php if ($useCaptcha && $captchaVersion === '3'): ?>
  <script src="https://www.google.com/recaptcha/api.js?render=<?php echo rawurlencode($captchaSiteKey); ?>"></script>
<?php endif; ?>
<script>
  (function() {
    if (typeof bootstrap === 'undefined' || typeof bootstrap.Tooltip === 'undefined') {
      return;
    }
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(el) {
      new bootstrap.Tooltip(el);
    });

    <?php if ($useCaptcha && $captchaVersion === '3'): ?>
    var form = document.querySelector('.contact-v5-form');
    var siteKey = <?php echo json_encode($captchaSiteKey); ?>;
    if (form) {
      form.addEventListener('submit', function(event) {
        if ((form.getAttribute('data-captcha-version') || '') !== '3') {
          return;
        }
        if (form.getAttribute('data-captcha-pending') === '1') {
          return;
        }
        event.preventDefault();
        if (typeof grecaptcha === 'undefined') {
          form.submit();
          return;
        }
        grecaptcha.ready(function() {
          grecaptcha.execute(siteKey, { action: 'contact_form' }).then(function(token) {
            var tokenInput = document.getElementById('contact_recaptcha_token');
            if (tokenInput) {
              tokenInput.value = token;
            }
            form.setAttribute('data-captcha-pending', '1');
            form.submit();
          });
        });
      });
    }
    <?php endif; ?>
  })();
</script>
<!-- END contact-form.php -->
