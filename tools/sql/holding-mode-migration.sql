-- Run once before enabling prefHoldingMode.
-- Holding mode is disabled unless cms_preferences.prefHoldingMode = 'Yes'.

ALTER TABLE `content`
  ADD COLUMN IF NOT EXISTS `showonholding` ENUM('Yes', 'No') NOT NULL DEFAULT 'No' AFTER `showonweb`;

-- Expose the flag on the existing Content editor. Field type 17 is WCCMS's
-- built-in Yes/No selector. This is safe to run repeatedly.
INSERT INTO `cms_form_field`
  (`title`, `form`, `table`, `tab`, `sort`, `field`, `label`, `name`, `class`,
   `placeholder`, `required`, `selected`, `comment`, `sourcesqlWHERE`,
   `override_filename`, `issortable`, `showadd`, `showedit`, `allowedit`,
   `showonweb`, `archived`)
SELECT
  'Show on holding page', f.`id`, t.`id`, 1, 999, 17, 'Show on holding page',
  'showonholding', 'medium', '', 'Yes', 'No',
  'Only content marked Yes is public while holding mode is enabled.', '', 'No',
  'No', 'Yes', 'Yes', 'Yes', 'Yes', 0
FROM `cms_form` f
INNER JOIN `cms_table` t ON t.`id` = f.`table`
WHERE LOWER(t.`name`) = 'content'
  AND NOT EXISTS (
    SELECT 1 FROM `cms_form_field` existing
    WHERE existing.`form` = f.`id`
      AND existing.`name` = 'showonholding'
      AND existing.`archived` = 0
  );

-- In CMS Preferences, add/update this row using the normal preference editor:
-- name: prefHoldingMode
-- value: Yes
-- Keep it No (or remove it) to show the normal public website.
