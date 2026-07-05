<?php
if (!function_exists('cms_render_content_actions')) {
  function cms_render_content_actions(array $item, int $limit = 2): string
  {
    $actions = [];
    for ($number = 1; $number <= $limit; $number++) {
      $label = trim((string) ($item['link_label_' . $number] ?? ''));
      $url = trim((string) ($item['link_url_' . $number] ?? ''));
      if ($label === '' || $url === '') {
        continue;
      }

      $icon = trim((string) ($item['link_icon_' . $number] ?? ''));
      $target = trim((string) ($item['link_target_' . $number] ?? $item['link_taget_' . $number] ?? ''));
      $targetAttribute = in_array($target, ['_blank', '_self', '_parent', '_top'], true)
        ? ' target="' . cms_h($target) . '"'
        : '';
      $relAttribute = $target === '_blank' ? ' rel="noopener noreferrer"' : '';

      $html = '<a class="content-action content-action-' . $number . '" href="' . cms_h($url) . '"'
        . $targetAttribute . $relAttribute . '>';
      if ($icon !== '') {
        $html .= '<i class="' . cms_h($icon) . '" aria-hidden="true"></i>';
      }
      $html .= '<span>' . cms_h($label) . '</span></a>';
      $actions[] = $html;
    }

    return $actions ? '<div class="content-actions">' . implode('', $actions) . '</div>' : '';
  }
}
$GLOBALS['cms_content_map'] = [];
$GLOBALS['cms_content_debug'] = [];
?>
<main>
<?php if (!$pageContentItems): ?>
  <section class="content-block"><div class="container"><h1 class="page-layout-heading"><?php echo cms_h((string) ($pageData['name'] ?? '')); ?></h1></div></section>
<?php else: ?>
  <?php foreach ($pageContentItems as $index => $contentItem):
    $layoutFile = basename((string) ($contentItem['layout_url'] ?? ''));
    $contentElementId = (int) ($contentItem['id'] ?? 0);
    $contentMapItem = [
      'id' => $contentElementId,
      'name' => trim((string) ($contentItem['name'] ?? $contentItem['heading'] ?? '')),
      'layout' => (int) ($contentItem['layout'] ?? 0),
      'layout_name' => trim((string) ($contentItem['layout_name'] ?? '')),
      'layout_url' => (string) ($contentItem['layout_url'] ?? ''),
      'sort' => $contentItem['sort'] ?? null,
    ];
    $GLOBALS['cms_content_map'][] = $contentMapItem;
    $GLOBALS['cms_content_debug'][] = $contentMapItem;
    $contentBackgroundMap = [
      'green' => 'brand1',
      'blue' => 'brand2',
      'medium' => 'brand3',
      'light' => 'brand4',
      'dark' => 'brand5',
      'white' => 'white',
      'black' => 'black',
    ];
    $contentBackground = strtolower(trim((string) ($contentItem['bgcolor'] ?? '')));
    $contentBackgroundClass = $contentBackgroundMap[$contentBackground] ?? '';
    $contentPaddingRules = [];
    if (isset($contentItem['padding-top']) && is_numeric($contentItem['padding-top'])) {
      $contentPaddingRules[] = '--content-padding-top:' . max(0, (int) $contentItem['padding-top']) . 'px';
    }
    if (isset($contentItem['padding-bottom']) && is_numeric($contentItem['padding-bottom'])) {
      $contentPaddingRules[] = '--content-padding-bottom:' . max(0, (int) $contentItem['padding-bottom']) . 'px';
    }
    echo '<div class="content-element cms-edit-target content-element-' . $contentElementId;
    if ($contentBackgroundClass !== '') {
      echo ' has-content-bg content-bg-' . cms_h($contentBackgroundClass);
    }
    echo '"';
    if ($contentPaddingRules) {
      echo ' style="' . cms_h(implode(';', $contentPaddingRules)) . '"';
    }
    echo '>';
    echo cms_render_frontend_edit_button(
      $contentItem + ['table_name' => 'content'],
      ['form_id' => 3]
    );
    if ($layoutFile === 'banner-large-bootstrap-carousel.php') {
      include __DIR__ . '/layouts/banner-large-bootstrap-carousel.php';
      echo '</div>';
      continue;
    }
    if ($layoutFile === 'banner-narrow.php') {
      include __DIR__ . '/layouts/banner-narrow.php';
      echo '</div>';
      continue;
    }
    if ($layoutFile === 'full-width-facts.php') {
      include __DIR__ . '/layouts/full-width-facts.php';
      echo '</div>';
      continue;
    }
    if ($layoutFile === 'banner-video.php') {
      include __DIR__ . '/layouts/banner-video.php';
      echo '</div>';
      continue;
    }
    if ($layoutFile === 'testimonial.php') {
      include __DIR__ . '/layouts/testimonial.php';
      echo '</div>';
      continue;
    }
    if ($layoutFile === 'contact.php') {
      include __DIR__ . '/layouts/contact.php';
      echo '</div>';
      continue;
    }
    if ($layoutFile === 'content-standard-image-text.php') {
      include __DIR__ . '/layouts/content-standard-image-text.php';
      echo '</div>';
      continue;
    }
    if ($layoutFile === 'content-standard-facts-text.php') {
      include __DIR__ . '/layouts/content-standard-facts-text.php';
      echo '</div>';
      continue;
    }
    $heading = trim((string) ($contentItem['heading'] ?? ''));
    $subheading = trim((string) ($contentItem['subheading'] ?? ''));
    $body = cms_apply_shortcodes((string) ($contentItem['text'] ?? ''));
    $body2 = cms_apply_shortcodes((string) ($contentItem['text2'] ?? ''));
    $image = trim((string) ($contentItem['image'] ?? ''));
    $contentHeadingTag = (($contentItem['showheading'] ?? 'Yes') === 'Yes' && $heading !== '') ? cms_page_heading_tag() : '';
    $imageUrl = '';
    foreach (['content/' . $image, $image] as $relativeImage) {
      if ($image !== '' && is_file(__DIR__ . '/../filestore/images/' . $relativeImage)) {
        $imageUrl = '/filestore/images/' . implode('/', array_map('rawurlencode', explode('/', $relativeImage)));
        break;
      }
    }
  ?>
    <section class="content-block <?php echo ($index % 2) ? 'content-block-alt' : ''; ?>">
      <div class="container">
        <div class="row align-items-center g-4">
          <div class="<?php echo $imageUrl !== '' ? 'col-lg-8' : 'col-12'; ?>">
            <?php if ($contentHeadingTag !== ''): ?><<?php echo $contentHeadingTag; ?> class="page-layout-heading"><?php echo nl2br(cms_h($heading)); ?></<?php echo $contentHeadingTag; ?>><?php endif; ?>
            <?php if ($subheading !== ''): ?><p class="lead"><?php echo nl2br(cms_h($subheading)); ?></p><?php endif; ?>
            <?php if ($body !== ''): ?><div class="content-copy"><?php echo $body; ?></div><?php endif; ?>
            <?php if ($body2 !== ''): ?><div class="content-copy"><?php echo $body2; ?></div><?php endif; ?>
            <?php echo cms_render_content_actions($contentItem); ?>
          </div>
          <?php if ($imageUrl !== ''): ?><div class="col-lg-4"><img class="content-image" src="<?php echo cms_h($imageUrl); ?>" alt=""></div><?php endif; ?>
        </div>
      </div>
    </section>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
</main>
