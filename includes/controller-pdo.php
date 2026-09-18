<?php
declare(strict_types=1);

if (empty($DB_OK) || !isset($pdo) || !($pdo instanceof PDO)) {
    http_response_code(500);
    exit('Database connection unavailable.');
}

$requestedPath = trim((string) ($_GET['url'] ?? ''), '/');
$pageSlug = explode('/', $requestedPath !== '' ? $requestedPath : 'welcome')[0];
$pageNotFound = false;
$frontendUnavailable = false;
$pageData = [];
$pageContentItems = [];
$menuItems = [];
$footerTestimonial = [];

// prefHoldingMode is intentionally opt-in: live sites remain unchanged until
// the preference is added in CMS Preferences and set to Yes.
$cmsUserIsLoggedIn = !empty($_SESSION['cms_user']['id']);
$holdingModeEnabled = strcasecmp((string) cms_pref('prefHoldingMode', 'No'), 'Yes') === 0;
$holdingPageActive = $holdingModeEnabled && !$cmsUserIsLoggedIn;

// Public visitors may only see the root page while holding mode is enabled.
// The home rewrite internally sets url=welcome, so use the actual request URI
// here rather than $requestedPath to avoid redirecting / back to itself.
$requestUriPath = (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/');
$isRootRequest = trim($requestUriPath, '/') === '';
// Redirect rather than disclose a page title, content, or a useful 404 map.
if ($holdingPageActive && !$isRootRequest) {
    header('Location: /', true, 302);
    exit;
}

try {
    $pageStatement = $pdo->prepare(
        "SELECT * FROM pages
         WHERE slug = :slug AND showonweb = 'Yes' AND archived = 0
         LIMIT 1"
    );
    $pageStatement->execute(['slug' => $pageSlug]);
    $pageData = $pageStatement->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $exception) {
    error_log('Frontend pages query failed: ' . $exception->getMessage());
    $frontendUnavailable = true;
    // Keep older/server-managed entry points from attempting normal content rendering.
    $pageNotFound = true;
}

if ($frontendUnavailable) {
    $pageTitle = 'Website temporarily unavailable';
} elseif (!$pageData) {
    $pageNotFound = true;
    $pageTitle = 'Page not found';
} else {
    $pageTitle = $pageData['titletag'] ?: ($pageData['name'] ?: cms_pref('prefSiteName', 'Green Energy Wind'));
    $pageMetaDescription = (string) ($pageData['metadescription'] ?? '');

    try {
        $holdingContentFilter = '';
        if ($holdingPageActive) {
            // Fail closed if the migration has not yet been applied. This
            // avoids accidentally showing the work-in-progress homepage.
            $contentColumns = $pdo->query('SHOW COLUMNS FROM content')->fetchAll(PDO::FETCH_COLUMN);
            $holdingContentFilter = in_array('showonholding', $contentColumns, true)
                ? " AND c.showonholding = 'Yes'"
                : ' AND 1 = 0';
        }
        $contentStatement = $pdo->prepare(
            "SELECT c.*, l.url AS layout_url, l.name AS layout_name
             FROM content c
             LEFT JOIN layout l ON l.id = c.layout
             WHERE c.page = :page_id AND c.showonweb = 'Yes' AND c.archived = 0"
             . $holdingContentFilter .
            ' ORDER BY c.sort, c.id'
        );
        $contentStatement->execute(['page_id' => (int) $pageData['id']]);
        $pageContentItems = $contentStatement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $exception) {
        error_log('Frontend content query failed: ' . $exception->getMessage());
        $pageContentItems = [];
    }
}

if (!$holdingPageActive) try {
    $menuStatement = $pdo->query(
        "SELECT mi.*, p.slug AS page_slug
         FROM menu_items mi
         INNER JOIN menus m ON m.id = mi.menu_id
         LEFT JOIN pages p ON p.id = mi.page_id
         WHERE m.location = 'header' AND m.active = 1 AND m.showonweb = 'Yes'
           AND m.archived = 0 AND mi.showonweb = 'Yes' AND mi.archived = 0
         ORDER BY COALESCE(mi.parent_id, mi.id), mi.parent_id IS NOT NULL, mi.sort, mi.id"
    );
    $menuItems = $menuStatement ? ($menuStatement->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
} catch (PDOException $exception) {
    error_log('Frontend menu query failed: ' . $exception->getMessage());
    $menuItems = [];
}

$menuTree = [];
foreach ($menuItems as $item) {
    if (empty($item['parent_id'])) {
        $item['children'] = [];
        $menuTree[(int) $item['id']] = $item;
    }
}
foreach ($menuItems as $item) {
    $parentId = (int) ($item['parent_id'] ?? 0);
    if ($parentId && isset($menuTree[$parentId])) {
        $menuTree[$parentId]['children'][] = $item;
    }
}

// Load one footer testimonial while tolerating optional WCCMS columns.
if (!$holdingPageActive) try {
    $testimonialColumns = $pdo->query('SHOW COLUMNS FROM testimonials')->fetchAll(PDO::FETCH_COLUMN);
    if ($testimonialColumns) {
        $testimonialWhere = [];
        if (in_array('showonweb', $testimonialColumns, true)) {
            $testimonialWhere[] = "showonweb = 'Yes'";
        }
        if (in_array('archived', $testimonialColumns, true)) {
            $testimonialWhere[] = 'archived = 0';
        }

        $testimonialSql = 'SELECT * FROM testimonials';
        if ($testimonialWhere) {
            $testimonialSql .= ' WHERE ' . implode(' AND ', $testimonialWhere);
        }
        $testimonialSql .= ' ORDER BY RAND() LIMIT 1';
        $footerTestimonial = $pdo->query($testimonialSql)->fetch(PDO::FETCH_ASSOC) ?: [];
    }
} catch (PDOException $exception) {
    $footerTestimonial = [];
}

function gew_menu_url(array $item): string
{
    if (!empty($item['url'])) {
        return (string) $item['url'];
    }
    if (($item['page_slug'] ?? '') === 'welcome') {
        return '/';
    }
    return !empty($item['page_slug']) ? '/' . rawurlencode((string) $item['page_slug']) : '#';
}

/**
 * Return the correct tag for a primary layout heading on this page.
 * The first visible layout heading is H1; subsequent ones are H2.
 */
function cms_page_heading_tag(): string
{
    static $h1Used = false;

    if (!$h1Used) {
        $h1Used = true;
        return 'h1';
    }

    return 'h2';
}
