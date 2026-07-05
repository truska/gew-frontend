<?php
/**
 * GEW home banner carousel.
 *
 * The layout is selected through the WCCMS content/layout record. Image rows
 * are intentionally hard-coded for this first pass and can move to gallery
 * records once the layout has been agreed.
 */
$bannerSlides = [
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
?>
<section class="home-banner" aria-label="Green Energy Wind highlights">
  <div id="homeBannerCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="6000">
    <div class="carousel-indicators">
      <?php foreach ($bannerSlides as $index => $slide): ?>
        <button type="button" data-bs-target="#homeBannerCarousel" data-bs-slide-to="<?php echo $index; ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>"<?php echo $index === 0 ? ' aria-current="true"' : ''; ?> aria-label="Slide <?php echo $index + 1; ?>"></button>
      <?php endforeach; ?>
    </div>
    <div class="carousel-inner">
      <?php foreach ($bannerSlides as $index => $slide): ?>
        <div class="carousel-item<?php echo $index === 0 ? ' active' : ''; ?>">
          <img src="/filestore/images/banners/<?php echo rawurlencode($slide['image']); ?>" class="d-block w-100" alt="<?php echo cms_h($slide['alt']); ?>">
        </div>
      <?php endforeach; ?>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#homeBannerCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#homeBannerCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Next</span>
    </button>
  </div>
</section>
