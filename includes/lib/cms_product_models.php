<?php

if (!function_exists('cms_product_model')) {
    function cms_product_model(array $rowproduct, string $baseUrl): ?array {
        $filename = trim(stripslashes((string) ($rowproduct['3dfilename'] ?? '')));
        if ($filename === '') {
            return null;
        }

        $filename = basename(str_replace('\\', '/', $filename));
        $extension = strtolower((string) pathinfo($filename, PATHINFO_EXTENSION));
        if ($filename === '' || !in_array($extension, ['gltf', 'glb'], true)) {
            return null;
        }

        $modelPath = dirname(__DIR__, 2) . '/filestore/images/products/3dimages/' . $filename;
        if (!is_file($modelPath)) {
            return null;
        }

        return [
            'filename' => $filename,
            'url' => rtrim($baseUrl, '/') . '/filestore/images/products/3dimages/' . rawurlencode($filename),
        ];
    }
}

if (!function_exists('cms_render_product_model')) {
    function cms_render_product_model(?array $model, string $alt): string {
        if ($model === null) {
            return '';
        }

        $src = htmlspecialchars((string) $model['url'], ENT_QUOTES, 'UTF-8');
        $alt = htmlspecialchars($alt, ENT_QUOTES, 'UTF-8');

        return
            '<div class="product-model-viewer">'
            . '<model-viewer src="' . $src . '" alt="' . $alt . '" '
            . 'camera-controls auto-rotate shadow-intensity="1" loading="lazy">'
            . '</model-viewer>'
            . '<p class="product-model-viewer__hint">Drag to rotate. Scroll to zoom.</p>'
            . '</div>';
    }
}

if (!function_exists('cms_product_lifestyle_image')) {
    function cms_product_lifestyle_image(array $rowproduct, string $baseUrl): ?array {
        $filename = '';
        foreach ($rowproduct as $field => $value) {
            $normalizedField = strtolower(preg_replace('/[^a-z0-9]+/i', '', (string) $field));
            if (strpos($normalizedField, 'lifestyle') !== false && trim((string) $value) !== '') {
                $filename = trim(stripslashes((string) $value));
                break;
            }
        }

        if ($filename === '') {
            return null;
        }

        $filename = basename(str_replace('\\', '/', $filename));
        $extension = strtolower((string) pathinfo($filename, PATHINFO_EXTENSION));
        if ($filename === '' || !in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            return null;
        }

        foreach (['originals', 'original'] as $folder) {
            $imagePath = dirname(__DIR__, 2) . '/filestore/images/products/' . $folder . '/' . $filename;
            if (is_file($imagePath)) {
                return [
                    'filename' => $filename,
                    'url' => rtrim($baseUrl, '/') . '/filestore/images/products/' . $folder . '/' . rawurlencode($filename),
                ];
            }
        }

        return null;
    }
}

if (!function_exists('cms_render_product_lifestyle_image')) {
    function cms_render_product_lifestyle_image(?array $image, string $alt): string {
        if ($image === null) {
            return '';
        }

        $src = htmlspecialchars((string) $image['url'], ENT_QUOTES, 'UTF-8');
        $alt = htmlspecialchars($alt, ENT_QUOTES, 'UTF-8');

        return
            '<div class="product-lifestyle-image">'
            . '<img src="' . $src . '" alt="' . $alt . '" loading="lazy">'
            . '</div>';
    }
}
