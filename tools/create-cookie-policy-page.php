<?php
declare(strict_types=1);

require dirname(__DIR__) . '/../private/dbcon.php';

if (empty($DB_OK) || !($pdo instanceof PDO)) {
    fwrite(STDERR, "Database connection unavailable.\n");
    exit(1);
}

$policyHtml = <<<'HTML'
<p>Cookies are small text files stored on your device when you visit a website. They help websites remember information about your visit and provide features you have requested.</p>

<h2>How we use cookies</h2>
<p>We use necessary cookies to operate this website and remember choices you make. Optional analytics cookies will only be used when Google Analytics has been enabled by the site administrator and you select <strong>Accept all</strong>. You can choose <strong>Necessary only</strong> and continue to use the website.</p>

<h2>Cookies used on this website</h2>
<div class="table-responsive">
  <table class="table">
    <thead><tr><th>Cookie</th><th>Category</th><th>Purpose</th><th>Typical duration</th></tr></thead>
    <tbody>
      <tr><td><code>gew_cookie_consent</code></td><td>Necessary</td><td>Records whether you accepted or declined optional cookies.</td><td>365 days</td></tr>
      <tr><td><code>announcement_seen_*</code></td><td>Necessary / functional</td><td>Remembers your request to hide a site announcement so it is not shown repeatedly.</td><td>Set for each announcement; normally 7 days</td></tr>
      <tr><td><code>_ga</code>, <code>_ga_*</code></td><td>Optional analytics</td><td>May be set by Google Analytics to measure how visitors use the website. These cookies are not loaded unless Analytics is enabled and you select <strong>Accept all</strong>.</td><td>Up to 2 years</td></tr>
    </tbody>
  </table>
</div>

<h2>Changing your choice</h2>
<p>You can remove cookies through your browser settings. After deleting <code>gew_cookie_consent</code>, this website will ask for your choice again on your next visit. Your browser can also block cookies, although some requested features may then be unable to remember your preferences.</p>

<h2>More information</h2>
<p>For independent guidance about cookies and browser controls, visit the <a href="https://ico.org.uk/for-the-public/online/cookies/" target="_blank" rel="noopener">Information Commissioner’s Office cookie guidance</a>.</p>

<p><em>Last updated: 5 July 2026.</em></p>
HTML;

$pdo->beginTransaction();
try {
    $pageStatement = $pdo->prepare('SELECT id FROM pages WHERE slug = :slug LIMIT 1');
    $pageStatement->execute([':slug' => 'cookies']);
    $pageId = (int) ($pageStatement->fetchColumn() ?: 0);

    if ($pageId === 0) {
        $insertPage = $pdo->prepare(
            "INSERT INTO pages
             (title, name, slug, imagebg, titletag, metadescription, metakeywords, layout, ishomepage, sort, showmenu, pagesearch, googlesitemap, showoncms, showonweb, archived)
             VALUES
             (:title, :name, :slug, :imagebg, :titletag, :description, :keywords, :layout, 'No', :sort, 'No', 'Yes', 'Yes', 'Yes', 'Yes', 0)"
        );
        $insertPage->execute([
            ':title' => 'Cookies',
            ':name' => 'Cookie Policy',
            ':slug' => 'cookies',
            ':imagebg' => '',
            ':titletag' => 'Cookie Policy | Green Energy Wind',
            ':description' => 'How Green Energy Wind uses cookies and how visitors can manage their cookie choices.',
            ':keywords' => 'cookies, cookie policy, privacy',
            ':layout' => '1',
            ':sort' => 99,
        ]);
        $pageId = (int) $pdo->lastInsertId();
    }

    $contentStatement = $pdo->prepare('SELECT id FROM content WHERE page = :page AND name = :name AND archived = 0 LIMIT 1');
    $contentStatement->execute([':page' => $pageId, ':name' => 'Cookie Policy']);
    $contentId = (int) ($contentStatement->fetchColumn() ?: 0);

    if ($contentId === 0) {
        $insertContent = $pdo->prepare(
            "INSERT INTO content
             (name, heading, showheading, subheading, page, source_form_id, source_form_name, layout, sort, text, text2, `padding-top`, `padding-bottom`, bgcolor, showoncms, showonweb, archived)
             VALUES
             (:name, :heading, 'Yes', '', :page, 3, 'Content', 5, 20, :text, '', 40, 60, 'white', 'Yes', 'Yes', 0)"
        );
        $insertContent->execute([
            ':name' => 'Cookie Policy',
            ':heading' => 'Cookie Policy',
            ':page' => $pageId,
            ':text' => $policyHtml,
        ]);
    } else {
        $updateContent = $pdo->prepare('UPDATE content SET heading = :heading, text = :text, showonweb = \'Yes\', archived = 0 WHERE id = :id');
        $updateContent->execute([':heading' => 'Cookie Policy', ':text' => $policyHtml, ':id' => $contentId]);
    }

    $pdo->commit();
    echo "/cookies page is ready (page {$pageId}).\n";
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    throw $exception;
}
