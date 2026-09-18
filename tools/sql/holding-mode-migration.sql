-- Run this once on the live database before enabling prefHoldingMode.
-- It is safe to run more than once: it will not duplicate the CMS field or
-- holding-page Content record.

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

-- Add the frontend switch, initially disabled. Set value to Yes when the
-- holding page is ready to replace the normal public site.
INSERT INTO `cms_preferences`
  (`name`, `label`, `value`, `notes`, `prefCat`, `field`, `class`, `sort`,
   `comment`, `placeholder`, `required`, `max`, `min`, `step`, `tooltip`,
   `showoncms`, `showonweb`, `allowedit`, `archived`)
SELECT
  'prefHoldingMode', 'Holding mode', 'No',
  'Show only holding-page content to public visitors. CMS users see the full website.',
  1, 1, 'medium', 999, '', '', 'Yes', 0, 0, 0, '', 'Yes', 'Yes', 'Yes', 0
WHERE NOT EXISTS (
  SELECT 1 FROM `cms_preferences` WHERE `name` = 'prefHoldingMode'
);

-- Add the supplied holding copy to the public welcome page. It uses the
-- existing Full width text layout and is flagged for holding mode only.
INSERT INTO `content`
  (`name`, `heading`, `showheading`, `subheading`, `page`, `source_form_id`,
   `source_form_name`, `layout`, `sort`, `text`, `bgcolor`, `showoncms`,
   `showonweb`, `showonholding`, `archived`)
SELECT
  'Holding page introduction',
  'Green Energy Wind',
  'Yes', '', p.`id`,
  (SELECT f.`id`
     FROM `cms_form` f
     INNER JOIN `cms_table` t ON t.`id` = f.`table`
    WHERE LOWER(t.`name`) = 'content'
    ORDER BY f.`id`
    LIMIT 1),
  'content',
  (SELECT l.`id` FROM `layout` l WHERE l.`url` = 'fullwidthtext.php' LIMIT 1),
  6,
  '<p><strong><em>Harnessing the Wind. Storing the Energy.</em></strong></p>
<p><strong>Innovation is in the Air:</strong> Our main website is currently undergoing a digital upgrade to better serve our clients, but our field operations remain fully active during this brief transition.</p>
<p>As <strong>Wind Turbine and Energy Storage Specialists</strong>, we continue to deliver market-leading engineering solutions across the UK and Ireland.</p>
<h2>What We Do:</h2>
<ul>
<li><strong>New &amp; Remanufactured Installations:</strong> High-performance new builds and expertly refurbished wind turbine engineering.</li>
<li><strong>Decommissioning &amp; Repowering:</strong> Comprehensive asset updates, turbine repowering, and technical upgrades.</li>
<li><strong>24/7 Service, Maintenance &amp; Rapid Response:</strong> Round-the-clock technical support ensuring maximum operational uptime.</li>
<li><strong>Blade &amp; Component Engineering:</strong> Precision blade refurbishment and rigorous component inspections.</li>
<li><strong>Expert Consultancy Services:</strong> Strategic guidance for grid integration, asset management, and load optimisation.</li>
</ul>
<h2>Our Operational Core:</h2>
<p>&#128737; <strong>Zero-Harm Health &amp; Safety Excellence</strong> &mdash; Protecting our people, your assets, and the environment in everything we do.</p>
<h2>Get in Touch While We Build:</h2>
<p>For immediate project tenders, maintenance requests, or inquiries regarding our upcoming showcase with <strong>DEIF</strong> in October 2026, contact our team directly:</p>
<ul>
<li>&#9993; <strong>Email:</strong> <a href="mailto:info@greenenergywind.co.uk">info@greenenergywind.co.uk</a></li>
<li>&#128222; <strong>Phone:</strong> <a href="tel:02873771989">028 7377 1989</a></li>
</ul>',
  'white', 'Yes', 'Yes', 'Yes', 0
FROM `pages` p
WHERE p.`slug` = 'welcome'
  AND p.`showonweb` = 'Yes'
  AND p.`archived` = 0
  AND NOT EXISTS (
    SELECT 1
    FROM `content` existing
    WHERE existing.`page` = p.`id`
      AND existing.`name` = 'Holding page introduction'
      AND existing.`archived` = 0
  );

-- To make the holding page public, run this after checking the content:
-- UPDATE `cms_preferences` SET `value` = 'Yes' WHERE `name` = 'prefHoldingMode';
