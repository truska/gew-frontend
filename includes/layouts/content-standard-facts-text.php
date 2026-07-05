<?php
/**
 * Standard facts/text layout: a two-across facts grid beside body copy.
 */

$heading = trim((string) ($contentItem['heading'] ?? ''));
$subheading = trim((string) ($contentItem['subheading'] ?? ''));
$subheading1 = trim((string) ($contentItem['subheading1'] ?? ''));
$subheading2 = trim((string) ($contentItem['subheading2'] ?? ''));
$body = cms_apply_shortcodes((string) ($contentItem['text'] ?? ''));
$body2 = cms_apply_shortcodes((string) ($contentItem['text2'] ?? ''));
$contentActions = cms_render_content_actions($contentItem);

$showHeader = (string) ($contentItem['showheading']
  ?? $contentItem['showheader']
  ?? $contentItem['showhearder']
  ?? 'Yes');
$headingTag = ($showHeader === 'Yes' && $heading !== '') ? cms_page_heading_tag() : '';

$facts = array_slice(cms_load_facts_for_content((int) ($contentItem['id'] ?? 0)), 0, 4);
$factsHaveIcons = false;
foreach ($facts as $fact) {
  if (trim((string) ($fact['icon'] ?? '')) !== '') {
    $factsHaveIcons = true;
    break;
  }
}

$hasFacts = count($facts) > 0;
$hasBody2 = trim(strip_tags($body2)) !== '';
$factsPosition = strtolower(trim((string) ($contentItem['imageposition'] ?? 'left')));
$factsOnRight = $factsPosition === 'right';
?>
<section class="content-block standard-facts-text-layout">
  <div class="container">
    <?php if ($headingTag !== '' || $subheading !== ''): ?>
      <div class="row">
        <div class="col-12 text-center">
          <?php if ($headingTag !== ''): ?><<?php echo $headingTag; ?> class="page-layout-heading"><?php echo nl2br(cms_h($heading)); ?></<?php echo $headingTag; ?>><?php endif; ?>
          <?php if ($subheading !== ''): ?><h3><?php echo nl2br(cms_h($subheading)); ?></h3><?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($hasFacts): ?>
      <div class="row g-5 align-items-start">
        <div class="col-12 col-lg-6<?php echo $factsOnRight ? ' order-lg-2' : ''; ?>">
          <div class="row g-4 standard-facts-grid">
            <?php foreach ($facts as $index => $fact):
              $factHeading = trim((string) ($fact['heading'] ?? ''));
              $factShowHeading = (string) ($fact['showheading'] ?? 'Yes');
              $factSubheading = trim((string) ($fact['subheading'] ?? ''));
              $factText = trim((string) ($fact['text'] ?? ''));
              $factValue = trim((string) ($fact['fact'] ?? ''));
              $factUrl = cms_fact_url($fact['url'] ?? '');
              $factLinkLabel = $factHeading !== '' ? $factHeading : ($factSubheading !== '' ? $factSubheading : 'View details');
              $factIcon = !empty($fact['icon']) ? cms_icon_class($pdo, $fact['icon']) : null;
            ?>
              <div class="col-12 col-sm-6">
                <div class="fact-card cms-edit-target fact-card-<?php echo ($index % 4) + 1; ?><?php echo $factsHaveIcons ? ' fact-card-with-icon' : ''; ?><?php echo $factUrl !== '' ? ' fact-card-has-link' : ''; ?>">
                  <?php echo cms_render_frontend_edit_button($fact + ['table_name' => 'facts'], ['form_id' => 4, 'class' => 'cms-fact-edit-button', 'title' => 'Edit this fact in WCCMS']); ?>
                  <?php if ($factUrl !== ''): ?><a class="fact-card-link" href="<?php echo cms_h($factUrl); ?>" aria-label="<?php echo cms_h($factLinkLabel); ?>"></a><?php endif; ?>
                  <?php if ($factsHaveIcons): ?>
                    <div class="fact-icon" aria-hidden="true"><?php if ($factIcon): ?><i class="<?php echo cms_h($factIcon); ?>"></i><?php endif; ?></div>
                  <?php endif; ?>
                  <div class="fact-content">
                    <?php if ($factShowHeading === 'Yes' && $factHeading !== ''): ?><h3><?php echo cms_h($factHeading); ?></h3><?php endif; ?>
                    <?php if ($factSubheading !== ''): ?><h5><?php echo cms_h($factSubheading); ?></h5><?php endif; ?>
                    <?php if ($factText !== ''): ?><p class="facttext"><?php echo nl2br(cms_h($factText)); ?></p><?php endif; ?>
                    <?php if ($factValue !== ''): ?><div class="fact-value"><?php echo cms_h($factValue); ?></div><?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="col-12 col-lg-6<?php echo $factsOnRight ? ' order-lg-1' : ''; ?>">
          <?php if ($body !== ''): ?><div class="content-copy"><?php echo $body; ?></div><?php endif; ?>
          <?php echo $contentActions; ?>
        </div>
      </div>
    <?php elseif ($hasBody2): ?>
      <div class="row g-4">
        <div class="col-12 col-lg-6">
          <?php if ($subheading1 !== ''): ?><h4><?php echo nl2br(cms_h($subheading1)); ?></h4><?php endif; ?>
          <?php if ($body !== ''): ?><div class="content-copy"><?php echo $body; ?></div><?php endif; ?>
        </div>
        <div class="col-12 col-lg-6">
          <?php if ($subheading2 !== ''): ?><h4><?php echo nl2br(cms_h($subheading2)); ?></h4><?php endif; ?>
          <div class="content-copy"><?php echo $body2; ?></div>
        </div>
        <?php if ($contentActions !== ''): ?><div class="col-12"><?php echo $contentActions; ?></div><?php endif; ?>
      </div>
    <?php else: ?>
      <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
          <?php if ($body !== ''): ?><div class="content-copy"><?php echo $body; ?></div><?php endif; ?>
          <?php echo $contentActions; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>
