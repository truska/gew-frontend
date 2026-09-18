<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../private/dbcon.php';
require_once __DIR__ . '/includes/prefs.php';
require_once __DIR__ . '/includes/controller-pdo.php';

// Holding mode deliberately uses a separate, minimal layout.  CMS users keep
// seeing the normal site so they can continue building it before launch.
$layoutSuffix = !empty($holdingPageActive) ? '-holding' : '-basic';
include __DIR__ . '/includes/header' . $layoutSuffix . '.php';

if ($pageNotFound) {
    http_response_code(404);
    echo '<main class="container py-5"><h1>Page not found</h1></main>';
} else {
    include __DIR__ . '/includes/content-basic.php';
}

include __DIR__ . '/includes/footer' . $layoutSuffix . '.php';
