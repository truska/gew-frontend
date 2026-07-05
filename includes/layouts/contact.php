<?php
/** Public contact layout, independent of WCCMS runtime files. */
$heading = trim((string) ($contentItem['heading'] ?? $pageData['name'] ?? 'Contact us'));
$body = cms_apply_shortcodes((string) ($contentItem['text'] ?? ''));
$rowpage = $pageData; // Compatibility context consumed by contact-form.php.
$headingTag = $heading !== '' ? cms_page_heading_tag() : '';
?>
<section class="content-block contact-layout">
  <div class="container">
    <div class="row g-5">
      <div class="col-12 col-lg-6">
        <?php if ($headingTag !== ''): ?><<?php echo $headingTag; ?> class="page-layout-heading"><?php echo cms_h($heading); ?></<?php echo $headingTag; ?>><?php endif; ?>
        <?php if ($body !== ''): ?><div class="content-copy"><?php echo $body; ?></div><?php endif; ?>
      </div>
      <div class="col-12 col-lg-6">
        <?php include dirname(__DIR__) . '/contact-form.php'; ?>
      </div>
    </div>
  </div>
</section>
