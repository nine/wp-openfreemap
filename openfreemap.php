<?php
/*
Plugin Name: OpenFreeMap
Description: Add an OpenFreeMap widget via shortcode [openfreemap]
Version:     0.1.0
Author:      Erwin Nindl
Author URI:  https://nindl.net
*/

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

// Register the shortcode
function openfreemap_handler($atts)
{
  // Set default values
  $atts = shortcode_atts(array(
    'lat' => '47.1092664',
    'lon' => '12.3453356',
    'popup_text' => '',
    'color' => '#ff0000',
    'height' => '450px',
    'zoom' => '16.5'
  ), $atts, 'openfreemap');

  // Escape attributes for output
  $lat = esc_js($atts['lat']);
  $lon = esc_js($atts['lon']);
  $popup_text = esc_js($atts['popup_text']);
  $color = esc_js($atts['color']);
  $height = esc_attr($atts['height']);
  $zoom = esc_js($atts['zoom']);

  ob_start(); ?>
  <div id="openfreemap" style="width: 100%; height: <?php echo $height; ?>"></div>
  <script>
    const center = [<?php echo $lon; ?>, <?php echo $lat; ?>];
    const zoom = <?php echo is_numeric($zoom) ? $zoom : 16.5; ?>;
    const map = new maplibregl.Map({
      style: '<?php echo plugin_dir_url(__FILE__) . '/vendor/openfreemap/styles/bright'; ?>',
      center: center,
      zoom: zoom,
      container: 'openfreemap',
    });
    const marker = new maplibregl.Marker({ color: '<?php echo $color; ?>' })
      .setLngLat(center)
      .addTo(map);
    <?php if (!empty($popup_text)): ?>
      const popup = new maplibregl.Popup({ closeOnClick: false }).setHTML('<?php echo $popup_text; ?>');
      marker.setPopup(popup);
      marker.togglePopup();
    <?php endif; ?>
    map.addControl(new maplibregl.NavigationControl());
  </script>
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
  wp_enqueue_style('openfreemap-style', plugin_dir_url(__FILE__) . 'vendor/maplibre-gl/maplibre-gl.css');
  $load_in_footer = false;
  wp_enqueue_script('openfreemap-script', plugin_dir_url(__FILE__) . 'vendor/maplibre-gl/maplibre-gl.js', array('jquery'), null, $load_in_footer);
}
add_action('wp_enqueue_scripts', 'openfreemap_enqueue_shortcode_assets');
