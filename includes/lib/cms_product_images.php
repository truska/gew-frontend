<?php

/**
 * Product image resolver:
 * - Prefer gallery rows (new + legacy storage patterns)
 * - Fallback to legacy products.image handling in layout templates
 */

if (!function_exists('cms_db_table_has_column')) {
  function cms_db_table_has_column(mysqli $conn, string $table, string $column): bool {
    static $cache = [];
    $key = strtolower($table) . '.' . strtolower($column);
    if (array_key_exists($key, $cache)) {
      return $cache[$key];
    }

    $tableEsc = mysqli_real_escape_string($conn, $table);
    $colEsc = mysqli_real_escape_string($conn, $column);
    $sql = "SHOW COLUMNS FROM `{$tableEsc}` LIKE '{$colEsc}'";
    $res = mysqli_query($conn, $sql);
    $cache[$key] = ($res instanceof mysqli_result) && mysqli_num_rows($res) > 0;
    return $cache[$key];
  }
}

if (!function_exists('cms_product_gallery_rows')) {
  function cms_product_gallery_rows(mysqli $conn, int $productId): array {
    if ($productId <= 0) {
      return [];
    }

    $hasProduct = cms_db_table_has_column($conn, 'gallery', 'product');
    $hasRecordId = cms_db_table_has_column($conn, 'gallery', 'record_id');
    $hasFormId = cms_db_table_has_column($conn, 'gallery', 'form_id');
    $hasFormName = cms_db_table_has_column($conn, 'gallery', 'form_name');
    $hasShowOnWeb = cms_db_table_has_column($conn, 'gallery', 'showonweb');
    $hasArchived = cms_db_table_has_column($conn, 'gallery', 'archived');
    $hasSort = cms_db_table_has_column($conn, 'gallery', 'sort');

    $orClauses = [];
    if ($hasProduct) {
      $orClauses[] = "`product` = {$productId}";
    }

    if ($hasRecordId) {
      $formId = 0;
      $formName = 'products';

      if (cms_db_table_has_column($conn, 'cms_form', 'id')) {
        $tableCol = cms_db_table_has_column($conn, 'cms_form', 'table')
          ? 'table'
          : (cms_db_table_has_column($conn, 'cms_form', 'tableID') ? 'tableID' : '');
        $nameCol = cms_db_table_has_column($conn, 'cms_form', 'name')
          ? 'name'
          : (cms_db_table_has_column($conn, 'cms_form', 'title') ? 'title' : '');

        if ($tableCol !== '') {
          $tableMatch = "LOWER(CAST(`{$tableCol}` AS CHAR)) = 'products'";
          if (cms_db_table_has_column($conn, 'cms_table', 'id') && cms_db_table_has_column($conn, 'cms_table', 'name')) {
            $tableMatch .= " OR `{$tableCol}` = (SELECT `id` FROM `cms_table` WHERE LOWER(`name`) = 'products' LIMIT 1)";
          }

          $nameSql = $nameCol !== '' ? "TRIM(`{$nameCol}`)" : "'products'";
          $formSql = "SELECT `id`, COALESCE(NULLIF({$nameSql},''), 'products') AS form_name
                      FROM `cms_form`
                      WHERE ({$tableMatch})
                      ORDER BY `id` ASC LIMIT 1";
          $formRes = mysqli_query($conn, $formSql);
          if ($formRes instanceof mysqli_result) {
            $formRow = mysqli_fetch_assoc($formRes);
            if (!empty($formRow['id'])) {
              $formId = (int) $formRow['id'];
            }
            if (!empty($formRow['form_name'])) {
              $formName = strtolower(trim((string) $formRow['form_name']));
            }
          }
        }
      }

      if ($hasFormId && $formId > 0) {
        $orClauses[] = "(`record_id` = {$productId} AND `form_id` = {$formId})";
      } elseif ($hasFormName) {
        $formNameEsc = mysqli_real_escape_string($conn, $formName);
        $orClauses[] = "(`record_id` = {$productId} AND LOWER(TRIM(`form_name`)) = '{$formNameEsc}')";
      } else {
        $orClauses[] = "`record_id` = {$productId}";
      }
    }

    if (empty($orClauses)) {
      return [];
    }

    $where = '(' . implode(' OR ', $orClauses) . ')';
    if ($hasShowOnWeb) {
      $where .= " AND (`showonweb` = 'Yes' OR `showonweb` = 1 OR `showonweb` = '1')";
    }
    if ($hasArchived) {
      $where .= " AND (`archived` = 0 OR `archived` = '0' OR `archived` IS NULL)";
    }

    $order = $hasSort ? '`sort` ASC, `id` ASC' : '`id` ASC';
    $sql = "SELECT * FROM `gallery` WHERE {$where} ORDER BY {$order}";
    $res = mysqli_query($conn, $sql);
    if (!($res instanceof mysqli_result)) {
      return [];
    }

    $rows = [];
    while ($row = mysqli_fetch_assoc($res)) {
      $rows[] = $row;
    }
    return $rows;
  }
}

if (!function_exists('cms_product_gallery_image_file')) {
  function cms_product_gallery_image_file(array $row): string {
    $candidates = ['image', 'filename', 'file', 'source'];
    foreach ($candidates as $key) {
      if (!empty($row[$key])) {
        return trim(stripslashes((string) $row[$key]));
      }
    }
    return '';
  }
}

if (!function_exists('cms_product_gallery_image_alt')) {
  function cms_product_gallery_image_alt(array $row, string $defaultAlt): string {
    $candidates = ['alttag', 'title', 'name', 'heading', 'caption'];
    foreach ($candidates as $key) {
      if (!empty($row[$key])) {
        return trim((string) $row[$key]);
      }
    }
    return $defaultAlt;
  }
}

if (!function_exists('cms_product_gallery_pick')) {
  function cms_product_gallery_pick(array $relativePaths, string $baseUrl): string {
    $docRoot = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    foreach ($relativePaths as $path) {
      if ($path === '') {
        continue;
      }
      $full = $docRoot . $path;
      if (is_file($full)) {
        return rtrim($baseUrl, '/') . $path;
      }
    }
    return '';
  }
}

if (!function_exists('cms_product_gallery_variants')) {
  function cms_product_gallery_variants(string $filename, string $baseUrl): array {
    if ($filename === '') {
      return ['zoom' => '', 'main' => '', 'thumb' => ''];
    }

    $newZoom = [
      "/filestore/images/products/lg/{$filename}",
      "/filestore/images/products/md/{$filename}",
      "/filestore/images/products/sm/{$filename}",
      "/filestore/images/products/{$filename}",
    ];
    $newMain = [
      "/filestore/images/products/md/{$filename}",
      "/filestore/images/products/sm/{$filename}",
      "/filestore/images/products/lg/{$filename}",
      "/filestore/images/products/{$filename}",
    ];
    $newThumb = [
      "/filestore/images/products/sm/{$filename}",
      "/filestore/images/products/md/{$filename}",
      "/filestore/images/products/lg/{$filename}",
      "/filestore/images/products/{$filename}",
    ];

    $legacyZoom = [
      "/filestore/images/content/lg-{$filename}",
      "/filestore/images/content/{$filename}",
    ];
    $legacyMain = [
      "/filestore/images/content/sm-{$filename}",
      "/filestore/images/content/{$filename}",
      "/filestore/images/content/lg-{$filename}",
    ];
    $legacyThumb = [
      "/filestore/images/content/tn-{$filename}",
      "/filestore/images/content/sm-{$filename}",
      "/filestore/images/content/{$filename}",
    ];

    $zoom = cms_product_gallery_pick($newZoom, $baseUrl);
    $main = cms_product_gallery_pick($newMain, $baseUrl);
    $thumb = cms_product_gallery_pick($newThumb, $baseUrl);

    if ($zoom === '' && $main === '' && $thumb === '') {
      $zoom = cms_product_gallery_pick($legacyZoom, $baseUrl);
      $main = cms_product_gallery_pick($legacyMain, $baseUrl);
      $thumb = cms_product_gallery_pick($legacyThumb, $baseUrl);
    }

    if ($main === '' && $zoom !== '') {
      $main = $zoom;
    }
    if ($zoom === '' && $main !== '') {
      $zoom = $main;
    }
    if ($thumb === '' && $main !== '') {
      $thumb = $main;
    }

    return ['zoom' => $zoom, 'main' => $main, 'thumb' => $thumb];
  }
}

if (!function_exists('cms_product_gallery_images')) {
  function cms_product_gallery_images(mysqli $conn, int $productId, string $baseUrl, string $defaultAlt): array {
    $rows = cms_product_gallery_rows($conn, $productId);
    if (!$rows) {
      return [];
    }

    $images = [];
    foreach ($rows as $row) {
      $file = cms_product_gallery_image_file($row);
      if ($file === '') {
        continue;
      }
      $variants = cms_product_gallery_variants($file, $baseUrl);
      if ($variants['main'] === '' && $variants['zoom'] === '') {
        continue;
      }
      $images[] = [
        'zoom' => $variants['zoom'],
        'main' => $variants['main'],
        'thumb' => $variants['thumb'],
        'alt' => cms_product_gallery_image_alt($row, $defaultAlt),
      ];
    }
    return $images;
  }
}

if (!function_exists('cms_product_legacy_card_image_url')) {
  function cms_product_legacy_card_image_url(array $rowproduct, string $baseUrl): string {
    $legacyFile = '';

    if (!empty($rowproduct['image'])) {
      $img = trim(stripslashes((string) $rowproduct['image']));
      $smPath = "/filestore/images/content/sm-{$img}";
      $rawPath = "/filestore/images/content/{$img}";

      $docRoot = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
      if (is_file($docRoot . $smPath)) {
        $legacyFile = $smPath;
      } elseif (is_file($docRoot . $rawPath)) {
        $legacyFile = $rawPath;
      }
    }

    if ($legacyFile === '' && !empty($rowproduct['brandimage'])) {
      $brand = trim(stripslashes((string) $rowproduct['brandimage']));
      $brandPath = "/filestore/images/content/{$brand}";
      $docRoot = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
      if (is_file($docRoot . $brandPath)) {
        $legacyFile = $brandPath;
      }
    }

    if ($legacyFile === '') {
      $legacyFile = '/filestore/images/content/brand-default.jpg';
    }

    return rtrim($baseUrl, '/') . $legacyFile;
  }
}

if (!function_exists('cms_product_card_image_url')) {
  function cms_product_card_image_url(mysqli $conn, array $rowproduct, string $baseUrl): string {
    $productId = (int) ($rowproduct['id'] ?? 0);
    if ($productId > 0) {
      $gallery = cms_product_gallery_images(
        $conn,
        $productId,
        $baseUrl,
        (string) ($rowproduct['name'] ?? '')
      );
      if (!empty($gallery)) {
        // Always use first ordered gallery image for list/range cards.
        return (string) ($gallery[0]['thumb'] ?? $gallery[0]['main'] ?? $gallery[0]['zoom'] ?? '');
      }
    }

    // Legacy fallback: keep old products.image/brandimage behavior.
    return cms_product_legacy_card_image_url($rowproduct, $baseUrl);
  }
}
