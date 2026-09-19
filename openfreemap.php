<?php
/*
Plugin Name: OpenFreeMap
Description: Add an OpenFreeMap widget via shortcode [openfreemap]
Version:     0.2.0
Author:      Erwin Nindl
Author URI:  https://nindl.net
*/

if (!defined('ABSPATH'))
{
  exit; // Exit if accessed directly
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
    'zoom'       => '16.5'
  ), $atts, 'openfreemap');
  $lat = is_numeric($atts['lat']) ? (float) $atts['lat'] : 47.1092664;
  $lon = is_numeric($atts['lon']) ? (float) $atts['lon'] : 12.3453356;
  $zoom = is_numeric($atts['zoom']) ? (float) $atts['zoom'] : 16.5;
  $color = sanitize_hex_color($atts['color']);
  if (!$color) {
    $color = '#ff0000';
  }
  $height = sanitize_text_field($atts['height']);
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
        data-popup="<?php echo esc_attr($atts['popup_text']); ?>"
        data-style="<?php echo esc_url(plugin_dir_url(__FILE__) . 'vendor/openfreemap/styles/bright'); ?>"
    ></div>
<?php

  return ob_get_clean();
}
add_shortcode('openfreemap', 'openfreemap_handler');


// Detect shortcode presence in post content
function openfreemap_detect_shortcode($posts)
{
  if (empty($posts) || !is_array($posts)) {
    return $posts;
  }
  foreach ($posts as $post) {
    if (has_shortcode($post->post_content, 'openfreemap')) {
      add_action('wp_enqueue_scripts', 'openfreemap_enqueue_shortcode_assets');
      break; // Only need to enqueue once
    }
  }
  return $posts;
}
add_filter('the_posts', 'openfreemap_detect_shortcode');


// Enqueue scripts and styles only if shortcode is used
function openfreemap_enqueue_shortcode_assets()
{
  $plugin_url = plugin_dir_url(__FILE__);
  wp_enqueue_style(
    'openfreemap-style',
    $plugin_url . 'vendor/maplibre-gl/maplibre-gl.css'
  );
  $load_in_footer = false;
  wp_enqueue_script(
    'openfreemap-script',
    $plugin_url . 'assets/openfreemap.js',
    array(),
    null,
    $load_in_footer
  );
  add_filter('script_loader_tag', function ($tag, $handle, $src) {
    if ($handle === 'openfreemap-script') {
      return '<script type="module" src="' . esc_url($src) . '"></script>';
    }
    return $tag;
  }, 10, 3);
}
add_action('wp_enqueue_scripts', 'openfreemap_enqueue_shortcode_assets');

