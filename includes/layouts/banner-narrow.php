<?php
/**
 * Narrow full-width banner for internal pages.
 *
 * Images are hard-coded for the first pass and will move to WCCMS gallery
 * records later. A single slide renders as a plain image; multiple slides
 * automatically enable the Bootstrap carousel interface.
 */
$narrowBannerSlides = [
    [
        'image' => 'gew-turbine-arial.jpg',
        'alt' => 'Wind turbines viewed from above',
    ],
    [
        'image' => 'gew-man-florecent.jpg',
        'alt' => 'Wind energy engineer at work',
    ],
    [
        'image' => 'istockphoto-2244984250-1024x1024.jpg',
        'alt' => 'Renewable wind energy',
    ],
];

$narrowBannerSlides = array_values(array_filter(
    $narrowBannerSlides,
    static fn(array $slide): bool => !empty($slide['image'])
));
$narrowBannerCount = count($narrowBannerSlides);
$narrowBannerId = 'narrowBannerCarousel-' . (int) ($contentItem['id'] ?? 0);
?>
<?php if ($narrowBannerCount === 1): ?>
  <section class="narrow-banner" aria-label="Page banner">
    <img src="/filestore/images/banners/<?php echo rawurlencode($narrowBannerSlides[0]['image']); ?>" alt="<?php echo cms_h($narrowBannerSlides[0]['alt']); ?>">
  </section>
<?php elseif ($narrowBannerCount > 1): ?>
  <section class="narrow-banner" aria-label="Page highlights">
    <div id="<?php echo cms_h($narrowBannerId); ?>" class="carousel slide" data-bs-ride="carousel" data-bs-interval="6000">
      <div class="carousel-indicators">
        <?php foreach ($narrowBannerSlides as $index => $slide): ?>
          <button type="button" data-bs-target="#<?php echo cms_h($narrowBannerId); ?>" data-bs-slide-to="<?php echo $index; ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>"<?php echo $index === 0 ? ' aria-current="true"' : ''; ?> aria-label="Slide <?php echo $index + 1; ?>"></button>
        <?php endforeach; ?>
      </div>
      <div class="carousel-inner">
        <?php foreach ($narrowBannerSlides as $index => $slide): ?>
          <div class="carousel-item<?php echo $index === 0 ? ' active' : ''; ?>">
            <img src="/filestore/images/banners/<?php echo rawurlencode($slide['image']); ?>" alt="<?php echo cms_h($slide['alt']); ?>">
          </div>
        <?php endforeach; ?>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#<?php echo cms_h($narrowBannerId); ?>" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#<?php echo cms_h($narrowBannerId); ?>" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
      </button>
    </div>
  </section>
<?php endif; ?>
