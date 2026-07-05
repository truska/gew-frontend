<?php
/**
 * Full testimonial listing.
 */
$testimonials = [];
try {
    $statement = $pdo->query(
        "SELECT * FROM testimonials
         WHERE showonweb = 'Yes' AND archived = 0
         ORDER BY id ASC"
    );
    $testimonials = $statement ? ($statement->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
} catch (PDOException $exception) {
    $testimonials = [];
}

$testimonialPageHeading = trim((string) ($contentItem['heading'] ?? ''));
$testimonialPageShowHeading = (string) ($contentItem['showheading'] ?? 'Yes');
$testimonialPageSubheading = trim((string) ($contentItem['subheading'] ?? ''));
$testimonialPageText = cms_apply_shortcodes((string) ($contentItem['text'] ?? ''));
$testimonialPageActions = cms_render_content_actions($contentItem);
$testimonialPageHeadingTag = ($testimonialPageShowHeading === 'Yes' && $testimonialPageHeading !== '') ? cms_page_heading_tag() : '';

function gew_testimonial_image_url(string $image): string
{
    $image = trim($image);
    if ($image === '') {
        return '';
    }
    if (filter_var($image, FILTER_VALIDATE_URL)) {
        return $image;
    }

    $image = ltrim($image, '/');
    $candidates = [
        $image,
        'filestore/images/testimonials/' . $image,
        'filestore/images/content/lg/' . $image,
        'filestore/images/content/' . $image,
        'filestore/images/' . $image,
    ];
    foreach ($candidates as $candidate) {
        if (is_file(dirname(__DIR__, 2) . '/' . $candidate)) {
            return '/' . implode('/', array_map('rawurlencode', explode('/', $candidate)));
        }
    }
    return '';
}
?>
<section class="testimonials-page">
  <div class="container">
    <?php if (($testimonialPageShowHeading === 'Yes' && $testimonialPageHeading !== '') || $testimonialPageSubheading !== '' || trim(strip_tags($testimonialPageText)) !== '' || $testimonialPageActions !== ''): ?>
      <div class="testimonials-page-intro">
        <?php if ($testimonialPageHeadingTag !== ''): ?><<?php echo $testimonialPageHeadingTag; ?> class="page-layout-heading"><?php echo cms_h($testimonialPageHeading); ?></<?php echo $testimonialPageHeadingTag; ?>><?php endif; ?>
        <?php if ($testimonialPageSubheading !== ''): ?><h5><?php echo cms_h($testimonialPageSubheading); ?></h5><?php endif; ?>
        <?php if (trim(strip_tags($testimonialPageText)) !== ''): ?><div class="testimonials-page-copy"><?php echo $testimonialPageText; ?></div><?php endif; ?>
        <?php echo $testimonialPageActions; ?>
      </div>
    <?php endif; ?>

    <div class="testimonial-list">
      <?php foreach ($testimonials as $testimonial):
        $testimonialId = (int) ($testimonial['id'] ?? 0);
        $heading = trim((string) ($testimonial['heading'] ?? ''));
        $text = cms_apply_shortcodes((string) ($testimonial['text'] ?? ''));
        $name = trim((string) ($testimonial['name'] ?? ''));
        $company = trim((string) ($testimonial['company'] ?? ''));
        $attribution = implode(', ', array_values(array_filter([$name, $company], static fn(string $value): bool => $value !== '')));
        $imageUrl = gew_testimonial_image_url((string) ($testimonial['image'] ?? ''));
      ?>
        <article class="testimonial-card cms-edit-target"<?php echo $testimonialId > 0 ? ' id="testimonial-' . $testimonialId . '"' : ''; ?>>
          <?php echo cms_render_frontend_edit_button($testimonial + ['table_name' => 'testimonials'], ['form_id' => 5, 'class' => 'cms-testimonial-edit-button', 'title' => 'Edit this testimonial in WCCMS']); ?>
          <div class="row align-items-center g-4">
            <div class="<?php echo $imageUrl !== '' ? 'col-12 col-lg-8' : 'col-12 col-lg-10 mx-lg-auto'; ?>">
              <div class="testimonial-card-copy">
                <i class="fa-solid fa-quote-left" aria-hidden="true"></i>
                <?php if ($heading !== ''): ?><h3><?php echo cms_h($heading); ?></h3><?php endif; ?>
                <?php if (trim(strip_tags($text)) !== ''): ?><div class="testimonial-full-text"><?php echo $text; ?></div><?php endif; ?>
                <?php if ($attribution !== ''): ?><p class="testimonial-attribution"><em><?php echo cms_h($attribution); ?></em></p><?php endif; ?>
              </div>
            </div>
            <?php if ($imageUrl !== ''): ?>
              <div class="col-12 col-lg-4 testimonial-card-image">
                <img src="<?php echo cms_h($imageUrl); ?>" alt="<?php echo cms_h($attribution !== '' ? $attribution : $heading); ?>">
              </div>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
