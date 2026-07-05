<?php
/**
 * Full-width facts grid linked by facts.contentid = content.id.
 */
$facts = cms_load_facts_for_content((int) ($contentItem['id'] ?? 0));
$factsHaveIcons = false;
$sectionHeading = trim((string) ($contentItem['heading'] ?? ''));
$sectionShowHeading = (string) ($contentItem['showheading'] ?? 'Yes');
$sectionSubheading = trim((string) ($contentItem['subheading'] ?? ''));
$sectionText = cms_apply_shortcodes((string) ($contentItem['text'] ?? ''));
$sectionActions = cms_render_content_actions($contentItem);
$sectionHeadingTag = ($sectionShowHeading === 'Yes' && $sectionHeading !== '') ? cms_page_heading_tag() : '';

foreach ($facts as $factRow) {
    if (trim((string) ($factRow['icon'] ?? '')) !== '') {
        $factsHaveIcons = true;
        break;
    }
}

$factColumnClass = $factsHaveIcons ? 'col-12 col-md-6 col-lg-4' : 'col-12 col-sm-6 col-lg-3';
?>
<?php if ($facts): ?>
<section class="facts-section">
  <div class="container">
    <?php if (($sectionShowHeading === 'Yes' && $sectionHeading !== '') || $sectionSubheading !== ''): ?>
      <div class="facts-section-heading">
        <?php if ($sectionHeadingTag !== ''): ?><<?php echo $sectionHeadingTag; ?> class="page-layout-heading"><?php echo cms_h($sectionHeading); ?></<?php echo $sectionHeadingTag; ?>><?php endif; ?>
        <?php if ($sectionSubheading !== ''): ?><h5><?php echo cms_h($sectionSubheading); ?></h5><?php endif; ?>
      </div>
    <?php endif; ?>
    <div class="row g-4 justify-content-center">
      <?php foreach ($facts as $index => $factRow):
        $factHeading = trim((string) ($factRow['heading'] ?? ''));
        $factShowHeading = (string) ($factRow['showheading'] ?? 'Yes');
        $factSubheading = trim((string) ($factRow['subheading'] ?? ''));
        $factText = trim((string) ($factRow['text'] ?? ''));
        $factValue = trim((string) ($factRow['fact'] ?? ''));
        $factUrl = cms_fact_url($factRow['url'] ?? '');
        $factLinkLabel = $factHeading !== '' ? $factHeading : ($factSubheading !== '' ? $factSubheading : 'View details');
        $factIcon = null;
        if (!empty($factRow['icon'])) {
            $factIcon = cms_icon_class($pdo, $factRow['icon']);
        }
      ?>
        <div class="<?php echo $factColumnClass; ?>">
          <div class="fact-card cms-edit-target fact-card-<?php echo ($index % 4) + 1; ?><?php echo $factsHaveIcons ? ' fact-card-with-icon' : ''; ?><?php echo $factUrl !== '' ? ' fact-card-has-link' : ''; ?>">
            <?php echo cms_render_frontend_edit_button($factRow + ['table_name' => 'facts'], ['form_id' => 4, 'class' => 'cms-fact-edit-button', 'title' => 'Edit this fact in WCCMS']); ?>
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
    <?php if (trim(strip_tags($sectionText)) !== ''): ?>
      <div class="row justify-content-center facts-section-copy-row">
        <div class="col-12 col-lg-6">
          <div class="facts-section-copy"><?php echo $sectionText; ?></div>
        </div>
      </div>
    <?php endif; ?>
    <?php if ($sectionActions !== ''): ?>
      <div class="row justify-content-center"><div class="col-12 col-lg-6"><?php echo $sectionActions; ?></div></div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>
