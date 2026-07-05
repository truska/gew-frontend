<!-- START menu-bs5.php -->
<?php
$debugcheck = 'No';

// Panel finder data.
$selectPanelAll = "SELECT * FROM `panelfinder` WHERE `showonweb` = 'Yes' ORDER BY `level1`, `level2`, `level3`";
$queryPanelAll = mysqli_query($conn, $selectPanelAll);
$panelRows = [];
if ($queryPanelAll) {
  while ($panelRow = mysqli_fetch_assoc($queryPanelAll)) {
    $panelRows[] = $panelRow;
  }
}

$panelTree = [];
foreach ($panelRows as $panelRow) {
  $level1 = (string) ($panelRow['level1'] ?? '0');
  $level2 = (string) ($panelRow['level2'] ?? '0');
  $level3 = (string) ($panelRow['level3'] ?? '0');

  if ($level1 === '0') {
    continue;
  }

  if (!isset($panelTree[$level1])) {
    $panelTree[$level1] = [
      'item' => null,
      'children' => []
    ];
  }

  if ($level2 === '0') {
    $panelTree[$level1]['item'] = $panelRow;
    continue;
  }

  if (!isset($panelTree[$level1]['children'][$level2])) {
    $panelTree[$level1]['children'][$level2] = [
      'item' => null,
      'children' => []
    ];
  }

  if ($level3 === '0') {
    $panelTree[$level1]['children'][$level2]['item'] = $panelRow;
    continue;
  }

  $panelTree[$level1]['children'][$level2]['children'][$level3] = [
    'item' => $panelRow
  ];
}

ksort($panelTree, SORT_NUMERIC);
foreach ($panelTree as &$panelLevel1Node) {
  if (!empty($panelLevel1Node['children'])) {
    ksort($panelLevel1Node['children'], SORT_NUMERIC);
    foreach ($panelLevel1Node['children'] as &$panelLevel2Node) {
      if (!empty($panelLevel2Node['children'])) {
        ksort($panelLevel2Node['children'], SORT_NUMERIC);
      }
    }
    unset($panelLevel2Node);
  }
}
unset($panelLevel1Node);

// Navigation data.
$selectMenuAll = "SELECT * FROM `menu` WHERE `menu` > '20' AND `showonweb` = 'Yes' ORDER BY `menu`, `submenu`, `submenu1`";
$queryMenuAll = mysqli_query($conn, $selectMenuAll);
$menuRows = [];
if ($queryMenuAll) {
  while ($menuRow = mysqli_fetch_assoc($queryMenuAll)) {
    $menuRows[] = $menuRow;
  }
}

$menuTree = [];
foreach ($menuRows as $menuRow) {
  $topKey = (string) ($menuRow['menu'] ?? '0');
  $subKey = (string) ($menuRow['submenu'] ?? '0');
  $sub1Key = (string) ($menuRow['submenu1'] ?? '0');

  if (!isset($menuTree[$topKey])) {
    $menuTree[$topKey] = [
      'item' => null,
      'children' => []
    ];
  }

  if ($subKey === '0') {
    $menuTree[$topKey]['item'] = $menuRow;
    continue;
  }

  if (!isset($menuTree[$topKey]['children'][$subKey])) {
    $menuTree[$topKey]['children'][$subKey] = [
      'item' => null,
      'children' => []
    ];
  }

  if ($sub1Key === '0') {
    $menuTree[$topKey]['children'][$subKey]['item'] = $menuRow;
    continue;
  }

  $menuTree[$topKey]['children'][$subKey]['children'][$sub1Key] = [
    'item' => $menuRow
  ];
}

ksort($menuTree, SORT_NUMERIC);
foreach ($menuTree as &$topNode) {
  if (!empty($topNode['children'])) {
    ksort($topNode['children'], SORT_NUMERIC);
    foreach ($topNode['children'] as &$midNode) {
      if (!empty($midNode['children'])) {
        ksort($midNode['children'], SORT_NUMERIC);
      }
    }
    unset($midNode);
  }
}
unset($topNode);

$pageSlugCache = [];
$productSlugCache = [];

$getPageSlug = function ($pageId) use ($conn, &$pageSlugCache) {
  $pageId = (int) $pageId;
  if ($pageId < 1) {
    return '';
  }
  if (array_key_exists($pageId, $pageSlugCache)) {
    return $pageSlugCache[$pageId];
  }

  $selectPageSlug = "SELECT `slug` FROM `pages` WHERE `id` = '" . $pageId . "' AND `showonweb` = 'Yes' LIMIT 1";
  $queryPageSlug = mysqli_query($conn, $selectPageSlug);
  $rowPageSlug = $queryPageSlug ? mysqli_fetch_assoc($queryPageSlug) : null;
  $slug = !empty($rowPageSlug['slug']) ? strtolower((string) $rowPageSlug['slug']) : '';
  $pageSlugCache[$pageId] = $slug;
  return $slug;
};

$getProductSlug = function ($productId) use ($conn, &$productSlugCache) {
  $productId = (int) $productId;
  if ($productId < 1) {
    return '';
  }
  if (array_key_exists($productId, $productSlugCache)) {
    return $productSlugCache[$productId];
  }

  $selectProductSlug = "SELECT `slug` FROM `products` WHERE `id` = '" . $productId . "' AND `showonweb` = 'Yes' LIMIT 1";
  $queryProductSlug = mysqli_query($conn, $selectProductSlug);
  $rowProductSlug = $queryProductSlug ? mysqli_fetch_assoc($queryProductSlug) : null;
  $slug = !empty($rowProductSlug['slug']) ? strtolower((string) $rowProductSlug['slug']) : '';
  $productSlugCache[$productId] = $slug;
  return $slug;
};

$menuItemUrl = function ($item, $topItem = null) use ($baseURL, $getPageSlug, $getProductSlug) {
  if (!is_array($item)) {
    return '#';
  }

  if (!empty($item['section'])) {
    return $baseURL . '/product-list-section/' . rawurlencode((string) $item['section']);
  }

  $menuValue = (int) ($item['menu'] ?? 0);
  $submenuValue = (int) ($item['submenu'] ?? 0);
  $submenu1Value = (int) ($item['submenu1'] ?? 0);

  // Top-level link.
  if ($submenuValue === 0) {
    if ($menuValue === 30 && (int) ($item['product'] ?? 0) > 0) {
      return $baseURL . '/product';
    }

    $topSlug = $getPageSlug((int) ($item['page'] ?? 0));
    return $topSlug !== '' ? ($baseURL . '/' . $topSlug) : '#';
  }

  // 2nd-level link format in legacy nav: /{topSlug}/{id}/{productSlug}
  if ($submenu1Value === 0) {
    $topSlug = '';
    if (is_array($topItem)) {
      $topSlug = $getPageSlug((int) ($topItem['page'] ?? 0));
    }

    $pageProduct = !empty($item['page']) ? (int) $item['page'] : (int) ($item['product'] ?? 0);
    $productSlug = $getProductSlug($pageProduct);

    if ($topSlug !== '' && $pageProduct > 0 && $productSlug !== '') {
      return $baseURL . '/' . $topSlug . '/' . $pageProduct . '/' . $productSlug;
    }

    $itemPageSlug = $getPageSlug((int) ($item['page'] ?? 0));
    return $itemPageSlug !== '' ? ($baseURL . '/' . $itemPageSlug) : '#';
  }

  // 3rd-level link format in legacy nav: /{pageSlug}/{id}/{productSlug}
  $pageSlug = $getPageSlug((int) ($item['page'] ?? 0));
  $pageProduct = !empty($item['page']) ? (int) ($item['product'] ?? 0) : (int) ($item['page'] ?? 0);
  $productSlug = $getProductSlug($pageProduct);

  if ($pageSlug !== '' && $pageProduct > 0 && $productSlug !== '') {
    return $baseURL . '/' . $pageSlug . '/' . $pageProduct . '/' . $productSlug;
  }

  return $pageSlug !== '' ? ($baseURL . '/' . $pageSlug) : '#';
};

$cleanPanelName = function ($value) {
  $name = (string) $value;
  $name = preg_replace('/\btest\b/i', '', $name);
  $name = preg_replace('/\s{2,}/', ' ', $name);
  return trim($name);
};

$panelItemUrl = function ($item) use ($baseURL) {
  if (!is_array($item) || empty($item['id'])) {
    return '#';
  }
  return $baseURL . '/product-range/' . (int) $item['id'];
};

?>

<div class="bottom-header menu-bs5">
  <div class="container inner">
    <div class="row">
      <div class="col-md-3 col-xs-6">
        <div class="dropdown panel-finder-bs5" data-bs-auto-close="outside">
          <button type="button" class="panel-finder-bs5-toggle" id="panelFinderBs5Toggle" aria-expanded="false">
            <span>panel </span> finder <span><i class="fa fa-sort-desc" aria-hidden="true"></i></span>
          </button>
          <ul class="dropdown-menu panel-finder-bs5-menu" aria-labelledby="panelFinderBs5Toggle">
            <?php
            foreach ($panelTree as $level1Key => $level1Node) {
              $level1Item = $level1Node['item'] ?? null;
              if (!is_array($level1Item)) {
                continue;
              }
              $level1Title = htmlspecialchars($cleanPanelName($level1Item['name'] ?? ''), ENT_QUOTES, 'UTF-8');
              $level1Url = htmlspecialchars($panelItemUrl($level1Item), ENT_QUOTES, 'UTF-8');
              $level2Children = $level1Node['children'] ?? [];

              if (empty($level2Children)) {
                echo "<li><a class='dropdown-item' href='" . $level1Url . "'>" . $level1Title . "</a></li>";
                continue;
              }

              echo "<li class='dropdown-submenu dropend panel-finder-level1'>";
              echo "<a class='dropdown-item dropdown-toggle panel-finder-subtoggle' href='#' role='button' aria-expanded='false'>" . $level1Title . " <span class='caret'></span></a>";
              echo "<ul class='dropdown-menu panel-finder-bs5-submenu level2'>";

              foreach ($level2Children as $level2Node) {
                $level2Item = $level2Node['item'] ?? null;
                if (!is_array($level2Item)) {
                  continue;
                }
                $level2Title = htmlspecialchars($cleanPanelName($level2Item['name'] ?? ''), ENT_QUOTES, 'UTF-8');
                $level2Url = htmlspecialchars($panelItemUrl($level2Item), ENT_QUOTES, 'UTF-8');
                $level3Children = $level2Node['children'] ?? [];

                if (empty($level3Children)) {
                  echo "<li><a class='dropdown-item' href='" . $level2Url . "'>" . $level2Title . "</a></li>";
                  continue;
                }

                echo "<li class='dropdown-submenu dropend panel-finder-level2'>";
                echo "<a class='dropdown-item dropdown-toggle panel-finder-subtoggle' href='#' role='button' aria-expanded='false'>" . $level2Title . " <span class='caret'></span></a>";
                echo "<ul class='dropdown-menu panel-finder-bs5-submenu level3'>";

                foreach ($level3Children as $level3Node) {
                  $level3Item = $level3Node['item'] ?? null;
                  if (!is_array($level3Item)) {
                    continue;
                  }
                  $level3Title = htmlspecialchars($cleanPanelName($level3Item['name'] ?? ''), ENT_QUOTES, 'UTF-8');
                  $level3Url = htmlspecialchars($panelItemUrl($level3Item), ENT_QUOTES, 'UTF-8');
                  echo "<li><a class='dropdown-item' href='" . $level3Url . "'>" . $level3Title . "</a></li>";
                }

                echo "</ul></li>";
              }

              echo "</ul></li>";
            }
            ?>
          </ul>
        </div>
      </div>

      <div class="col-md-9 col-xs-6">
        <nav class="navbar navbar-expand-lg navbar-default menu-bs5-navbar p-0 m-0">
          <div class="container-fluid p-0 justify-content-end">
            <button class="navbar-toggler menu-bs5-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuBs5Main" aria-controls="menuBs5Main" aria-expanded="false" aria-label="Toggle navigation">
              <i class="fa fa-bars" aria-hidden="true"></i>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="menuBs5Main">
              <ul class="navbar-nav menu-bs5-desktop-nav d-none d-lg-flex">
                <?php
                foreach ($menuTree as $topKey => $topNode) {
                  $topItem = $topNode['item'] ?? null;
                  if (!is_array($topItem)) {
                    continue;
                  }

                  $topTitle = htmlspecialchars((string) ($topItem['title'] ?? ''), ENT_QUOTES, 'UTF-8');
                  $children = $topNode['children'] ?? [];
                  $topUrl = $menuItemUrl($topItem, null);

                  if (!empty($children)) {
                    echo "<li class='nav-item dropdown toplevel menu-bs5-top-dropdown'>";
                    echo "<a class='nav-link dropdown-toggle' href='#' role='button' data-bs-toggle='dropdown' data-bs-auto-close='outside' aria-expanded='false'>" . $topTitle . " <span class='caret' style='color:#3ea244;'></span></a>";
                    echo "<ul class='dropdown-menu'>";
                    echo "<li class='menu-bs5-choose-label'><span>CHOOSE FROM:</span></li>";

                    foreach ($children as $childNode) {
                      $childItem = $childNode['item'] ?? null;
                      if (!is_array($childItem)) {
                        continue;
                      }

                      $childTitle = htmlspecialchars((string) ($childItem['title'] ?? ''), ENT_QUOTES, 'UTF-8');
                      $childTitleRaw = strtolower(trim((string) ($childItem['title'] ?? '')));
                      if ($childTitleRaw === 'choose from' || $childTitleRaw === 'choose from:') {
                        continue;
                      }
                      $grandChildren = $childNode['children'] ?? [];

                      if (!empty($grandChildren)) {
                        echo "<li class='dropdown-submenu dropend'>";
                        echo "<a class='dropdown-item dropdown-toggle' href='#' role='button' data-bs-toggle='dropdown' data-bs-auto-close='outside' aria-expanded='false'>" . $childTitle . " <span class='caret'></span></a>";
                        echo "<ul class='dropdown-menu'>";

                        foreach ($grandChildren as $grandNode) {
                          $grandItem = $grandNode['item'] ?? null;
                          if (!is_array($grandItem)) {
                            continue;
                          }

                          $grandTitle = htmlspecialchars((string) ($grandItem['title'] ?? ''), ENT_QUOTES, 'UTF-8');
                          $grandUrl = htmlspecialchars($menuItemUrl($grandItem, $topItem), ENT_QUOTES, 'UTF-8');
                          echo "<li><a class='dropdown-item' href='" . $grandUrl . "'>" . $grandTitle . "</a></li>";
                        }

                        echo "</ul></li>";
                      } else {
                        $childUrl = htmlspecialchars($menuItemUrl($childItem, $topItem), ENT_QUOTES, 'UTF-8');
                        echo "<li><a class='dropdown-item' href='" . $childUrl . "'>" . $childTitle . "</a></li>";
                      }
                    }

                    echo "</ul></li>";
                  } else {
                    echo "<li class='nav-item toplevel'><a class='nav-link' href='" . htmlspecialchars($topUrl, ENT_QUOTES, 'UTF-8') . "'>" . $topTitle . "</a></li>";
                  }
                }
                ?>
              </ul>

              <div class="menu-bs5-mobile d-lg-none">
                <ul class="menu-bs5-mobile-list">
                  <?php
                  foreach ($menuTree as $topKey => $topNode) {
                    $topItem = $topNode['item'] ?? null;
                    if (!is_array($topItem)) {
                      continue;
                    }

                    $topTitle = htmlspecialchars((string) ($topItem['title'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $topChildren = $topNode['children'] ?? [];
                    $topUrl = htmlspecialchars($menuItemUrl($topItem, null), ENT_QUOTES, 'UTF-8');
                    $topId = 'mbs5-top-' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $topKey);

                    if (empty($topChildren)) {
                      echo "<li class='menu-bs5-mobile-item'><a href='" . $topUrl . "'>" . $topTitle . "</a></li>";
                      continue;
                    }

                    echo "<li class='menu-bs5-mobile-item has-children'>";
                    echo "<button type='button' class='menu-bs5-mobile-toggle collapsed' data-bs-toggle='collapse' data-bs-target='#" . $topId . "' aria-expanded='false' aria-controls='" . $topId . "'>" . $topTitle . " <span class='caret'></span></button>";
                    echo "<ul class='collapse menu-bs5-mobile-submenu' id='" . $topId . "'>";

                    foreach ($topChildren as $subKey => $subNode) {
                      $subItem = $subNode['item'] ?? null;
                      if (!is_array($subItem)) {
                        continue;
                      }

                      $subTitle = htmlspecialchars((string) ($subItem['title'] ?? ''), ENT_QUOTES, 'UTF-8');
                      $subChildren = $subNode['children'] ?? [];
                      $subUrl = htmlspecialchars($menuItemUrl($subItem, $topItem), ENT_QUOTES, 'UTF-8');
                      $subId = $topId . '-sub-' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $subKey);

                      if (empty($subChildren)) {
                        echo "<li class='menu-bs5-mobile-item level-2'><a href='" . $subUrl . "'>" . $subTitle . "</a></li>";
                        continue;
                      }

                      echo "<li class='menu-bs5-mobile-item level-2 has-children'>";
                      echo "<button type='button' class='menu-bs5-mobile-toggle collapsed' data-bs-toggle='collapse' data-bs-target='#" . $subId . "' aria-expanded='false' aria-controls='" . $subId . "'>" . $subTitle . " <span class='caret'></span></button>";
                      echo "<ul class='collapse menu-bs5-mobile-submenu level-3' id='" . $subId . "'>";

                      foreach ($subChildren as $leafNode) {
                        $leafItem = $leafNode['item'] ?? null;
                        if (!is_array($leafItem)) {
                          continue;
                        }

                        $leafTitle = htmlspecialchars((string) ($leafItem['title'] ?? ''), ENT_QUOTES, 'UTF-8');
                        $leafUrl = htmlspecialchars($menuItemUrl($leafItem, $topItem), ENT_QUOTES, 'UTF-8');
                        echo "<li class='menu-bs5-mobile-item level-3'><a href='" . $leafUrl . "'>" . $leafTitle . "</a></li>";
                      }

                      echo "</ul></li>";
                    }

                    echo "</ul></li>";
                  }
                  ?>
                </ul>
              </div>
            </div>
          </div>
        </nav>
      </div>
    </div>
  </div>
</div>
<!-- END menu-bs5.php -->
