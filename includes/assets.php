<?php
/**
 * Cache busting: agrega ?v=<fecha de modificación> a cada CSS/JS.
 * Así el navegador vuelve a descargar el archivo automáticamente cada vez
 * que lo cambiamos, sin tener que borrar caché ni usar incógnito.
 */
function asset_url(string $path): string
{
    $fullPath = __DIR__ . '/../' . $path;
    $version = file_exists($fullPath) ? filemtime($fullPath) : time();
    return $path . '?v=' . $version;
}