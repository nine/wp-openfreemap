<?php
/*
Plugin Name: OpenFreeMap
Description: Add an OpenFreeMap widget via shortcode [openfreemap]
Version:     0.3.0
Author:      Erwin Nindl
Author URI:  https://nindl.net
License:     MIT
Requires at least: 5.9
Requires PHP: 7.4
Text Domain: openfreemap
*/

if (!defined('ABSPATH'))
{
  exit; // Exit if accessed directly
}


// Clamp a numeric shortcode attribute, falling back to $default when it is not a number
function openfreemap_clamp_float($value, $default, $min, $max)
{
  if (!is_numeric($value)) {
    return $default;
  }
  return max($min, min($max, (float) $value));
}


// Allow only plain CSS lengths so the value can be interpolated into a style attribute
function openfreemap_sanitize_css_length($value, $default)
{
  $value = trim((string) $value);
  if (preg_match('/^\d+(?:\.\d+)?(?:px|pt|em|rem|ch|%|vh|vw|vmin|vmax)$/i', $value)) {
    return $value;
  }
  return $default;
}


// Read the release version from the plugin header, keeping the header authoritative
function openfreemap_plugin_version()
{
  static $version = null;
  if (null === $version) {
    $header = get_file_data(__FILE__, array('Version' => 'Version'), 'plugin');
    $version = isset($header['Version']) && $header['Version'] ? $header['Version'] : null;
  }
  return $version;
}


// Append a cache busting query argument to a bundled asset URL. Every bundled
// asset (vendored or not) shares the plugin's Version header, so bumping that
// header on release is what invalidates caches -- including for vendored
// files (e.g. a new MapLibre or OpenFreeMap style release) that don't carry
// their own version number.
function openfreemap_versioned_url($relative_path)
{
  $url = plugin_dir_url(__FILE__) . $relative_path;
  $version = openfreemap_plugin_version();
  return $version ? add_query_arg('ver', $version, $url) : $url;
}


// URL of a bundled OpenFreeMap style, versioned like every other shipped asset
function openfreemap_style_url($style)
{
  return openfreemap_versioned_url('vendor/openfreemap/styles/' . sanitize_key($style));
}


// Styles bundled under vendor/openfreemap/styles/. Kept as an explicit allow-list
// so the shortcode can't be used to probe for arbitrary files on disk.
function openfreemap_available_styles()
{
  return array('bright', 'dark', 'fiord', 'liberty', 'positron');
}


// Restrict the shortcode's style attribute to a known, bundled style
function openfreemap_sanitize_style($value, $default)
{
  $value = sanitize_key($value);
  if (in_array($value, openfreemap_available_styles(), true)) {
    return $value;
  }
  return $default;
}


// Register the shortcode
function openfreemap_handler($atts)
{
  $atts = shortcode_atts(array(
    'lat'        => '47.1092664',
    'lon'        => '12.3453356',
    'popup_text' => '',
    'color'      => '#ff0000',
    'height'     => '450px',
    'zoom'       => '16.5',
    'style'      => 'bright'
  ), $atts, 'openfreemap');
  $lat = openfreemap_clamp_float($atts['lat'], 47.1092664, -90, 90);
  $lon = openfreemap_clamp_float($atts['lon'], 12.3453356, -180, 180);
  $zoom = openfreemap_clamp_float($atts['zoom'], 16.5, 0, 22);
  $color = sanitize_hex_color($atts['color']);
  if (!$color) {
    $color = '#ff0000';
  }
  $height = openfreemap_sanitize_css_length($atts['height'], '450px');
  $popup_text = wp_kses_post($atts['popup_text']);
  $style = openfreemap_sanitize_style($atts['style'], 'bright');
  $map_id = wp_unique_id('openfreemap-');

  ob_start();
?>
    <div
        id="<?php echo esc_attr($map_id); ?>"
        class="openfreemap"
        style="width: 100%; height: <?php echo esc_attr($height); ?>;"
        data-lat="<?php echo esc_attr($lat); ?>"
        data-lon="<?php echo esc_attr($lon); ?>"
        data-zoom="<?php echo esc_attr($zoom); ?>"
        data-color="<?php echo esc_attr($color); ?>"
        data-popup="<?php echo esc_attr($popup_text); ?>"
        data-style="<?php echo esc_url(openfreemap_style_url($style)); ?>"
    ></div>
<?php

  return ob_get_clean();
}
add_shortcode('openfreemap', 'openfreemap_handler');


// Enqueue the map stylesheet and a small loader
function openfreemap_enqueue_assets()
{
  static $enqueued = false;
  if ($enqueued) {
    return;
  }

  // Skip entirely on singular posts/pages that have neither the shortcode
  // in their content nor a shortcode block. Widgets, template parts, and
  // secondary loops aren't covered by this check, so it's a partial
  // optimization, not a guarantee.
  if (is_singular()) {
    $post = get_post();
    if ($post && !has_shortcode($post->post_content, 'openfreemap') && !has_block('core/shortcode', $post)) {
      return;
    }
  }

  $enqueued = true;

  // The loader is a classic script: it conditionally dynamic-imports the ES
  // module below and injects the stylesheet, so both the ~1 MB of MapLibre JS
  // and its CSS are only fetched on pages that actually render a map.
  // Detection happens in the DOM rather than in post content, which also
  // covers widgets, blocks, template parts and secondary loops. The
  // is_singular() check above is just a cheap best-effort early exit that
  // skips even the tiny loader request on obviously map-free singular pages;
  // it is not relied on for correctness.
  wp_enqueue_script(
    'openfreemap-loader',
    plugin_dir_url(__FILE__) . 'assets/loader.js',
    array(),
    openfreemap_plugin_version(),
    true
  );

  $module_url = openfreemap_versioned_url('assets/openfreemap.js');
  $style_href = openfreemap_versioned_url('vendor/maplibre-gl/maplibre-gl.css');

  // JSON_HEX_TAG keeps the values from terminating the inline script early.
  wp_add_inline_script(
    'openfreemap-loader',
    'var openfreemapModuleUrl = ' . wp_json_encode($module_url, JSON_HEX_TAG | JSON_UNESCAPED_SLASHES) . ';' .
    'var openfreemapStyleUrl = ' . wp_json_encode($style_href, JSON_HEX_TAG | JSON_UNESCAPED_SLASHES) . ';',
    'before'
  );
}
add_action('wp_enqueue_scripts', 'openfreemap_enqueue_assets');
