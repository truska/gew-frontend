<?php
/**
 * Site-wide announcement shown below the menu when its date window is active.
 */
if (empty($DB_OK) || !($pdo instanceof PDO)) {
  return;
}

try {
  $announcementStatement = $pdo->prepare(
    "SELECT * FROM announcement
     WHERE showonweb = 'Yes'
       AND archived = 0
       AND showfrom <= CURDATE()
       AND showto >= CURDATE()
     ORDER BY sort ASC, id ASC
     LIMIT 1"
  );
  $announcementStatement->execute();
  $announcement = $announcementStatement->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $exception) {
  $announcement = [];
}

if (!$announcement) {
  return;
}

$announcementId = (int) ($announcement['id'] ?? 0);
$announcementTitle = trim((string) ($announcement['title'] ?? ''));
$announcementSubtitle = trim((string) ($announcement['subtitle'] ?? ''));
$announcementHeading = trim((string) ($announcement['heading'] ?? ''));
$announcementSubheading = trim((string) ($announcement['subheading'] ?? ''));
$announcementText = cms_apply_shortcodes((string) ($announcement['text'] ?? ''));
$announcementStatus = strtolower(trim((string) ($announcement['status'] ?? 'show')));
$announcementExpanded = $announcementStatus === 'show';

$announcementSanitizeColor = static function (string $color, string $fallback): string {
  $color = trim($color, " \t\n\r\0\x0B'\"");
  return preg_match('/^(#[0-9a-fA-F]{3,6}|[a-zA-Z]+)$/', $color) ? $color : $fallback;
};
$announcementBg = $announcementSanitizeColor((string) ($announcement['bgcolor'] ?? ''), '#344952');
$announcementColor = $announcementSanitizeColor((string) ($announcement['textcolor'] ?? ''), '#ffffff');
$announcementOpenColor = $announcementSanitizeColor((string) ($announcement['iconopencolor'] ?? ''), '#5CB647');
$announcementCloseColor = $announcementSanitizeColor((string) ($announcement['iconclosecolor'] ?? ''), '#80D1F5');

$announcementCookieDays = min(3650, max(1, (int) ($announcement['dismissdays'] ?? 7)));
$announcementCookieName = 'announcement_seen_' . $announcementId;
if (!empty($_COOKIE[$announcementCookieName])) {
  return;
}
?>
<section
  class="announcement-bar cms-edit-target <?php echo $announcementExpanded ? 'is-expanded' : 'is-collapsed'; ?>"
  style="--announcement-bg:<?php echo cms_h($announcementBg); ?>;--announcement-text:<?php echo cms_h($announcementColor); ?>;--announcement-icon-open:<?php echo cms_h($announcementOpenColor); ?>;--announcement-icon-close:<?php echo cms_h($announcementCloseColor); ?>;"
  data-announcement
  data-cookie-name="<?php echo cms_h($announcementCookieName); ?>"
  data-cookie-days="<?php echo $announcementCookieDays; ?>"
  aria-expanded="<?php echo $announcementExpanded ? 'true' : 'false'; ?>"
>
  <?php echo cms_render_frontend_edit_button($announcement + ['table_name' => 'announcement'], ['form_id' => 2, 'class' => 'cms-announcement-edit-button', 'title' => 'Edit this announcement in WCCMS']); ?>
  <button class="announcement-toggle" type="button" aria-expanded="<?php echo $announcementExpanded ? 'true' : 'false'; ?>">
    <span class="container announcement-toggle-inner">
      <span class="announcement-heading-group">
        <?php if ($announcementTitle !== ''): ?><span class="announcement-heading"><?php echo cms_h($announcementTitle); ?></span><?php endif; ?>
        <?php if ($announcementSubtitle !== ''): ?><span class="announcement-summary"><?php echo cms_h($announcementSubtitle); ?></span><?php endif; ?>
      </span>
      <span class="announcement-icon" aria-hidden="true">
        <i class="fa-solid fa-angles-down announcement-icon-open"></i>
        <i class="fa-solid fa-angles-up announcement-icon-close"></i>
      </span>
    </span>
  </button>
  <div class="announcement-body">
    <div class="container announcement-content">
      <?php if ($announcementHeading !== ''): ?><h2 class="announcement-title"><?php echo cms_h($announcementHeading); ?></h2><?php endif; ?>
      <?php if ($announcementSubheading !== ''): ?><h3 class="announcement-subheading"><?php echo cms_h($announcementSubheading); ?></h3><?php endif; ?>
      <?php if (trim(strip_tags($announcementText)) !== ''): ?><div class="announcement-text"><?php echo $announcementText; ?></div><?php endif; ?>
      <button class="announcement-dismiss" type="button" title="Hide announcement for <?php echo $announcementCookieDays; ?> days">
        <span>Hide Announcement</span>
        <i class="fa-solid fa-eye-slash" aria-hidden="true"></i>
      </button>
    </div>
  </div>
</section>
