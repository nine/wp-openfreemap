# WP OpenFreeMap - OpenFreeMap WordPress Plugin

A [WordPress plugin](https://wordpress.org/plugins) that renders [OpenFreeMap](https://openfreemap.org) using [MapLibre GL](https://maplibre.org).

## Installation
1. Upload the plugin to your `/wp-plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to "Settings > OpenFreemap" to configure the plugin


## Basic Usage
Once activated you can render [OpenFreeMap](https://openfreemap.org) map widgets using the shortcode
```
[openfreemap [options...]]
```
The options are as follows
- `lat`  
   Vertical center of the map (default: `47.1092664`)
- `lon`  
   Horizontal center of the map (default: `12.3453356`)
- `zoom`  
   Initial zoom of the map (default: `16.5`)
- `color`  
   Marker color in CSS notation (default: `#ff0000`)
- `height`  
   Height of the map widget in CSS notation (default: `450px`)

## Links
- https://openfreemap.org
- https://maplibre.org
