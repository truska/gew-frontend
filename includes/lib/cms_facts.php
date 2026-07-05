<?php

function cms_load_facts_for_content(int $contentId): array
{
    global $pdo, $DB_OK;

    if ($contentId <= 0 || empty($DB_OK) || !($pdo instanceof PDO)) {
        return [];
    }

    try {
        $statement = $pdo->prepare(
            "SELECT * FROM facts
             WHERE contentid = :contentid
               AND showonweb = 'Yes'
               AND archived = 0
             ORDER BY sort ASC, id ASC"
        );
        $statement->execute(['contentid' => $contentId]);
        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $exception) {
        return [];
    }
}

function cms_fact_url($value): string
{
    $url = trim((string) $value);
    if ($url === '') {
        return '';
    }

    // Site-relative paths and on-page anchors are allowed.
    if (($url[0] === '/' && !str_starts_with($url, '//')) || $url[0] === '#') {
        return $url;
    }

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return '';
    }

    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
    return in_array($scheme, ['http', 'https'], true) ? $url : '';
}
