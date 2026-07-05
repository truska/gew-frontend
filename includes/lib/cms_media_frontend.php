<?php
/**
 * Read-only media path helpers used by the public frontend.
 *
 * Uploading and image processing remain CMS concerns. Keeping these small
 * helpers here means rendering does not depend on a local WCCMS checkout.
 */

if (!function_exists('cms_media_base_dir')) {
  function cms_media_base_dir(): string {
    return rtrim(dirname(__DIR__, 2) . '/filestore', '/');
  }
}

if (!function_exists('cms_media_path')) {
  function cms_media_path(string $mediatype, string $folder, string $size = ''): string {
    $parts = [cms_media_base_dir(), trim($mediatype, '/')];
    if ($folder !== '') {
      $parts[] = trim($folder, '/');
    }
    if ($size !== '') {
      $parts[] = trim($size, '/');
    }
    return rtrim(implode('/', $parts), '/') . '/';
  }
}

if (!function_exists('cms_media_url')) {
  function cms_media_url(string $mediatype, string $folder, string $filename, string $size = '', bool $preferWebp = true): string {
    $segments = ['filestore', trim($mediatype, '/')];
    if ($folder !== '') {
      $segments[] = trim($folder, '/');
    }
    if ($size !== '') {
      $segments[] = trim($size, '/');
    }

    if ($preferWebp && preg_match('/\.(jpe?g|png|gif)$/i', $filename)) {
      $webp = (string) preg_replace('/\.[^.]+$/', '.webp', $filename);
      if (is_file(cms_media_path($mediatype, $folder, $size) . $webp)) {
        $filename = $webp;
      }
    }

    $segments[] = ltrim($filename, '/');
    return cms_base_url('/' . implode('/', array_map(
      static fn(string $segment): string => implode('/', array_map('rawurlencode', explode('/', $segment))),
      $segments
    )));
  }
}
