<?php
/**
 * Standard, content-driven image/text layout.
 *
 * The page controller supplies visible content rows in sort order. Gallery
 * visibility and ordering are enforced by cms_content_gallery_images().
 */

require_once dirname(__DIR__) . '/lib/cms_images.php';

$heading = trim((string) ($contentItem['heading'] ?? ''));
$subheading = trim((string) ($contentItem['subheading'] ?? ''));
$subheading1 = trim((string) ($contentItem['subheading1'] ?? ''));
$subheading2 = trim((string) ($contentItem['subheading2'] ?? ''));
$body = cms_apply_shortcodes((string) ($contentItem['text'] ?? ''));
$body2 = cms_apply_shortcodes((string) ($contentItem['text2'] ?? ''));
$contentActions = cms_render_content_actions($contentItem);

// Accommodate the historic field name and the two variants used by WCCMS forms.
$showHeader = (string) ($contentItem['showheading']
  ?? $contentItem['showheader']
  ?? $contentItem['showhearder']
  ?? 'Yes');
$headingTag = ($showHeader === 'Yes' && $heading !== '') ? cms_page_heading_tag() : '';

$formContext = cms_form_context_for_table('content');
if (isset($contentItem['source_form_id']) && is_numeric((string) $contentItem['source_form_id'])) {
  $formContext['form_id'] = (int) $contentItem['source_form_id'];
}
$galleryImages = cms_content_gallery_images($contentItem, $formContext);
if (!$galleryImages && trim((string) ($contentItem['image'] ?? '')) !== '') {
  $galleryImages = cms_content_single_image_fallback($contentItem);
}
foreach ($galleryImages as &$galleryImage) {
  $mediaType = (string) ($galleryImage['mediatype'] ?? 'images');
  $folder = (string) ($galleryImage['folder'] ?? 'content');
  $filename = (string) ($galleryImage['filename'] ?? '');
  if ($filename === '') {
    continue;
  }

  // Keep each gallery role tied to its intended derivative. In particular,
  // do not attach the generic srcset here: its breakpoint labels describe
  // viewport widths rather than the real derivative widths and can make the
  // browser choose the 300px /sm/ file for the initial featured image.
  $galleryImage['thumb'] = cms_content_pick_image_url(
    $mediaType,
    $folder,
    $filename,
    ['sm', 'xs', 'md', 'lg']
  );
  $galleryImage['display'] = cms_content_pick_image_url(
    $mediaType,
    $folder,
    $filename,
    ['md', 'lg', 'sm', 'xs']
  );
  $galleryImage['zoom'] = cms_content_pick_image_url(
    $mediaType,
    $folder,
    $filename,
    ['lg', 'master', 'md', 'sm']
  );
  $galleryImage['srcset'] = '';
}
unset($galleryImage);
$hasGallery = count($galleryImages) > 0;
$portraitImages = 0;
$landscapeImages = 0;
foreach ($galleryImages as $galleryImage) {
  $mediaType = (string) ($galleryImage['mediatype'] ?? 'images');
  $folder = (string) ($galleryImage['folder'] ?? 'content');
  $filename = (string) ($galleryImage['filename'] ?? '');
  foreach (['md', 'lg', 'master', ''] as $size) {
    $imagePath = cms_media_path($mediaType, $folder, $size) . $filename;
    if (!is_file($imagePath)) {
      continue;
    }
    $dimensions = @getimagesize($imagePath);
    if (is_array($dimensions)) {
      if ((int) $dimensions[1] > (int) $dimensions[0]) {
        $portraitImages++;
      } elseif ((int) $dimensions[0] > (int) $dimensions[1]) {
        $landscapeImages++;
      }
    }
    break;
  }
}
$portraitGallery = $portraitImages > $landscapeImages;
$hasBody2 = trim(strip_tags($body2)) !== '';
$imagePosition = strtolower(trim((string) ($contentItem['imageposition'] ?? 'left')));
$imageOnRight = $imagePosition === 'right';

$renderGallery = static function (array $images, array $item, bool $portrait = false): string {
  $id = 'standard-gallery-' . (int) ($item['id'] ?? 0);
  $defaultAlt = trim((string) ($item['heading'] ?? ''));
  $main = $images[0];
  $mainAlt = $main['alt'] !== '' ? $main['alt'] : $defaultAlt;
  $mainTitle = trim((string) ($main['title'] ?? $main['caption'] ?? ''));
  $mainCaption = trim((string) ($main['caption'] ?? $main['title'] ?? ''));
  $srcset = $main['srcset'] !== ''
    ? ' srcset="' . cms_h($main['srcset']) . '" sizes="(max-width: 991px) 100vw, 50vw"'
    : '';
  $options = 'zoomMode: off; expand: true; expandZoomMode: zoom';

  $galleryClass = 'standard-image-gallery mb-0' . ($portrait ? ' standard-image-gallery-portrait' : ' standard-image-gallery-landscape');
  $html = '<figure class="' . $galleryClass . '" data-image-count="' . count($images) . '">';
  if ($portrait) {
    $visibleCount = min(6, max(1, count($images)));
    $html .= '<div class="standard-image-gallery-frame" style="--gallery-visible-count:' . $visibleCount . '">';
  }
  $html .= '<a id="' . cms_h($id) . '" class="MagicZoomPlus" href="' . cms_h($main['zoom']) . '"'
    . ' data-options="' . cms_h($options) . '" data-caption="' . cms_h($mainCaption) . '" title="' . cms_h($mainTitle) . '">';
  $html .= '<img src="' . cms_h($main['display']) . '" alt="' . cms_h($mainAlt) . '" title="' . cms_h($mainTitle) . '" class="img-fluid w-100"' . $srcset . '>';
  $html .= '</a>';

  if (count($images) > 1) {
    $html .= $portrait
      ? '<div class="standard-image-thumbnails standard-image-thumbnail-rail" role="list" aria-label="Gallery images">'
      : '<div class="row g-2 mt-2 standard-image-thumbnails" role="list" aria-label="Gallery images">';
    foreach ($images as $image) {
      $alt = $image['alt'] !== '' ? $image['alt'] : $defaultAlt;
      $title = trim((string) ($image['title'] ?? $image['caption'] ?? ''));
      $caption = trim((string) ($image['caption'] ?? $image['title'] ?? ''));
      $html .= $portrait ? '<div class="standard-image-thumbnail" role="listitem">' : '<div class="col-4" role="listitem">';
      $html .= '<a data-zoom-id="' . cms_h($id) . '" href="' . cms_h($image['zoom']) . '"'
        . ' data-image="' . cms_h($image['display']) . '" data-caption="' . cms_h($caption) . '" title="' . cms_h($title) . '">';
      $html .= '<img src="' . cms_h($image['thumb']) . '" alt="' . cms_h($alt) . '" title="' . cms_h($title) . '" class="img-fluid w-100">';
      $html .= '</a></div>';
    }
    $html .= '</div>';
  }
  if ($portrait) {
    $html .= '</div>';
  }

  if ($mainCaption !== '') {
    $html .= '<figcaption class="small mt-2">' . cms_h($mainCaption) . '</figcaption>';
  }
  $html .= '</figure>';
  return $html;
};
?>
<section class="content-block standard-image-text-layout">
  <div class="container">
    <?php if ($headingTag !== '' || $subheading !== ''): ?>
      <div class="row">
        <div class="col-12 text-center">
          <?php if ($headingTag !== ''): ?><<?php echo $headingTag; ?> class="page-layout-heading"><?php echo nl2br(cms_h($heading)); ?></<?php echo $headingTag; ?>><?php endif; ?>
          <?php if ($subheading !== ''): ?><h3><?php echo nl2br(cms_h($subheading)); ?></h3><?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($hasGallery): ?>
      <div class="row g-5 align-items-start">
        <div class="col-12 <?php echo $portraitGallery ? 'col-lg-7' : 'col-lg-6'; ?><?php echo $imageOnRight ? ' order-lg-2' : ''; ?>">
          <?php echo $renderGallery($galleryImages, $contentItem, $portraitGallery); ?>
        </div>
        <div class="col-12 <?php echo $portraitGallery ? 'col-lg-5' : 'col-lg-6'; ?><?php echo $imageOnRight ? ' order-lg-1' : ''; ?>">
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
        <?php if ($contentActions !== ''): ?>
          <div class="col-12"><?php echo $contentActions; ?></div>
        <?php endif; ?>
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
