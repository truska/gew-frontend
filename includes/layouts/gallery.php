<?php
/**
 * Project gallery.
 *
 * This first pass deliberately uses the latest ten usable gallery rows. Once
 * the CMS has a project-gallery flag, add that condition to the query below.
 */

require_once dirname(__DIR__) . '/lib/cms_images.php';

$galleryHeading = trim((string) ($contentItem['heading'] ?? ''));
$gallerySubheading = trim((string) ($contentItem['subheading'] ?? ''));
$galleryBody = cms_apply_shortcodes((string) ($contentItem['text'] ?? ''));
$galleryActions = cms_render_content_actions($contentItem);
$galleryShowHeading = (string) ($contentItem['showheading']
  ?? $contentItem['showheader']
  ?? $contentItem['showhearder']
  ?? 'Yes');
$galleryHeadingTag = ($galleryShowHeading === 'Yes' && $galleryHeading !== '') ? cms_page_heading_tag() : '';
$magicGalleryId = 'project-gallery-' . (int) ($contentItem['id'] ?? 0);
$projectGalleryImages = [];

if (!empty($DB_OK) && $pdo instanceof PDO && cms_content_table_exists('gallery')) {
  $galleryWhere = ["image IS NOT NULL", "TRIM(image) <> ''"];
  if (cms_content_table_has_column('gallery', 'archived')) {
    $galleryWhere[] = "(archived IS NULL OR archived = 0 OR archived = '0' OR LOWER(CAST(archived AS CHAR)) IN ('no', 'false'))";
  }

  try {
    // Scan a few extra rows because old gallery records may reference files
    // that have since been removed; the rendered collection remains capped at 10.
    $gallerySql = 'SELECT * FROM gallery WHERE ' . implode(' AND ', $galleryWhere) . ' ORDER BY id DESC LIMIT 30';
    $galleryRows = $pdo->query($gallerySql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
  } catch (PDOException $exception) {
    error_log('Project gallery query failed: ' . $exception->getMessage());
    $galleryRows = [];
  }

  foreach ($galleryRows as $galleryRow) {
    if (count($projectGalleryImages) >= 10) {
      break;
    }
    $filename = ltrim(trim((string) ($galleryRow['image'] ?? '')), '/');
    $folderName = trim((string) ($galleryRow['folder_name'] ?? ''), '/');
    $mediaType = trim((string) ($galleryRow['mediatype'] ?? ''), '/');
    $folder = '';

    if ($folderName !== '') {
      $folderParts = array_values(array_filter(explode('/', $folderName), 'strlen'));
      if (count($folderParts) > 1) {
        $mediaType = $mediaType !== '' ? $mediaType : (string) $folderParts[0];
        $folder = implode('/', array_slice($folderParts, 1));
      } elseif ($folderParts) {
        $folder = (string) $folderParts[0];
      }
    }
    $mediaType = $mediaType !== '' ? $mediaType : 'images';
    $folder = $folder !== '' ? $folder : 'content';

    $displayUrl = cms_content_pick_image_url($mediaType, $folder, $filename, ['sm']);
    if ($displayUrl === '') {
      continue;
    }
    $zoomUrl = cms_content_pick_image_url($mediaType, $folder, $filename, ['lg']);
    $caption = trim((string) ($galleryRow['caption'] ?? ''));
    $title = trim((string) ($galleryRow['title'] ?? ''));
    $alt = trim((string) ($galleryRow['alttag'] ?? ''));
    if ($alt === '') {
      $alt = $title !== '' ? $title : ($caption !== '' ? $caption : $galleryHeading);
    }

    $projectGalleryImages[] = [
      'display' => $displayUrl,
      'zoom' => $zoomUrl !== '' ? $zoomUrl : $displayUrl,
      'caption' => $caption,
      'title' => $title,
      'alt' => $alt,
    ];
  }
}
?>
<section class="content-block project-gallery-layout" aria-label="<?php echo cms_h($galleryHeading !== '' ? $galleryHeading : 'Project gallery'); ?>">
  <div class="container">
    <?php if ($galleryHeadingTag !== '' || $gallerySubheading !== '' || $galleryBody !== ''): ?>
      <header class="project-gallery-intro">
        <?php if ($galleryHeadingTag !== ''): ?><<?php echo $galleryHeadingTag; ?> class="page-layout-heading"><?php echo nl2br(cms_h($galleryHeading)); ?></<?php echo $galleryHeadingTag; ?>><?php endif; ?>
        <?php if ($gallerySubheading !== ''): ?><p class="lead"><?php echo nl2br(cms_h($gallerySubheading)); ?></p><?php endif; ?>
        <?php if ($galleryBody !== ''): ?><div class="content-copy"><?php echo $galleryBody; ?></div><?php endif; ?>
      </header>
    <?php endif; ?>

    <?php if ($projectGalleryImages): ?>
      <div class="project-gallery-grid" role="list">
        <?php foreach ($projectGalleryImages as $galleryIndex => $galleryImage):
          $imageTitle = $galleryImage['title'] !== '' ? $galleryImage['title'] : $galleryImage['caption'];
        ?>
          <figure class="project-gallery-item" role="listitem">
            <a class="MagicZoomPlus project-gallery-image" href="<?php echo cms_h($galleryImage['zoom']); ?>"
              data-gallery="<?php echo cms_h($magicGalleryId); ?>"
              data-options="zoomMode: off; expand: true; expandZoomMode: zoom"
              data-caption="<?php echo cms_h($galleryImage['caption']); ?>"
              title="<?php echo cms_h($imageTitle); ?>">
              <img src="<?php echo cms_h($galleryImage['display']); ?>"
                alt="<?php echo cms_h($galleryImage['alt']); ?>" class="img-fluid w-100"
                loading="<?php echo $galleryIndex < 2 ? 'eager' : 'lazy'; ?>" decoding="async">
            </a>
            <figcaption<?php echo $galleryImage['caption'] === '' ? ' aria-hidden="true"' : ''; ?>><?php echo $galleryImage['caption'] !== '' ? cms_h($galleryImage['caption']) : '&nbsp;'; ?></figcaption>
          </figure>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="project-gallery-empty">Gallery images will appear here soon.</p>
    <?php endif; ?>

    <?php echo $galleryActions; ?>
  </div>
</section>
