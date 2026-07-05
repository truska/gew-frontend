<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<?php
  $bootstrapCompatJsPath = __DIR__ . '/../js/bootstrap5-legacy-compat.js';
  if (file_exists($bootstrapCompatJsPath)):
    $bootstrapCompatJsVer = (string) filemtime($bootstrapCompatJsPath);
?>
  <script src="<?php echo $baseURL; ?>/js/bootstrap5-legacy-compat.js?v=<?php echo rawurlencode($bootstrapCompatJsVer); ?>"></script>
<?php endif; ?>
<?php
  $siteJsPath = __DIR__ . '/../js/site.js';
  if (file_exists($siteJsPath)):
    $siteJsVer = (string) filemtime($siteJsPath);
?>
  <script src="<?php echo $baseURL; ?>/js/site.js?v=<?php echo rawurlencode($siteJsVer); ?>"></script>
<?php endif; ?>
<?php
  $menuBs5JsPath = __DIR__ . '/../js/menu-bs5.js';
  if (file_exists($menuBs5JsPath)):
    $menuBs5JsVer = (string) filemtime($menuBs5JsPath);
?>
  <script src="<?php echo $baseURL; ?>/js/menu-bs5.js?v=<?php echo rawurlencode($menuBs5JsVer); ?>"></script>
<?php endif; ?>
<?php
  $customFooterPath = __DIR__ . '/custom-footer.php';
  if (file_exists($customFooterPath)) {
    include $customFooterPath;
  }
  if (!empty($rowpage['footercode'])) {
    echo $rowpage['footercode'];
  }
?>
</body>
</html>
