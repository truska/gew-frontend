<?php
/**
 * Full-width YouTube video banner.
 * Accepts either a YouTube ID or URL in content.videoref.
 */
$videoReference = trim((string) ($contentItem['videoref'] ?? ''));
if ($videoReference === '') {
    $videoReference = 'xNIxttcDhks';
}

$youtubeId = $videoReference;
if (filter_var($videoReference, FILTER_VALIDATE_URL)) {
    $videoParts = parse_url($videoReference);
    $videoHost = strtolower((string) ($videoParts['host'] ?? ''));
    if (str_contains($videoHost, 'youtu.be')) {
        $youtubeId = trim((string) ($videoParts['path'] ?? ''), '/');
    } else {
        parse_str((string) ($videoParts['query'] ?? ''), $videoQuery);
        $youtubeId = (string) ($videoQuery['v'] ?? '');
    }
}

$youtubeId = preg_replace('/[^A-Za-z0-9_-]/', '', $youtubeId);
$videoHeading = trim((string) ($contentItem['heading'] ?? ''));
$videoTitle = $videoHeading !== '' ? $videoHeading : 'Green Energy Wind video';
$videoShowHeading = (string) ($contentItem['showheading'] ?? 'Yes');
$videoSubheading = trim((string) ($contentItem['subheading'] ?? ''));
$videoText = cms_apply_shortcodes((string) ($contentItem['text'] ?? ''));
$videoActions = cms_render_content_actions($contentItem);
$videoHeadingTag = ($videoShowHeading === 'Yes' && $videoHeading !== '') ? cms_page_heading_tag() : '';
?>
<?php if ($youtubeId !== ''): ?>
<section class="video-banner-layout" aria-label="<?php echo cms_h($videoTitle); ?>">
  <?php if (($videoShowHeading === 'Yes' && $videoHeading !== '') || $videoSubheading !== ''): ?>
    <div class="container video-banner-heading">
      <?php if ($videoHeadingTag !== ''): ?><<?php echo $videoHeadingTag; ?> class="page-layout-heading"><?php echo cms_h($videoHeading); ?></<?php echo $videoHeadingTag; ?>><?php endif; ?>
      <?php if ($videoSubheading !== ''): ?><h5><?php echo cms_h($videoSubheading); ?></h5><?php endif; ?>
    </div>
  <?php endif; ?>
  <div class="video-banner">
    <iframe
      src="https://www.youtube-nocookie.com/embed/<?php echo rawurlencode($youtubeId); ?>?rel=0"
      title="<?php echo cms_h($videoTitle); ?>"
      loading="lazy"
      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
      referrerpolicy="strict-origin-when-cross-origin"
      allowfullscreen
    ></iframe>
  </div>
  <?php if (trim(strip_tags($videoText)) !== '' || $videoActions !== ''): ?>
    <div class="container video-banner-copy-wrap">
      <div class="row justify-content-center">
        <div class="col-12 col-lg-6"><div class="video-banner-copy"><?php echo $videoText; ?></div><?php echo $videoActions; ?></div>
      </div>
    </div>
  <?php endif; ?>
</section>
<?php endif; ?>
