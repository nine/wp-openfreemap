import * as maplibregl from '../vendor/maplibre-gl/maplibre-gl.mjs';

const NO_WEBGL_MESSAGE =
  'The map could not be displayed. Please enable WebGL or use a different browser.';

function readNumber(value, fallback) {
  const parsed = parseFloat(value);
  return Number.isFinite(parsed) ? parsed : fallback;
}

document.querySelectorAll('.openfreemap').forEach((container) => {
  const center = [
    readNumber(container.dataset.lon, 12.3453356),
    readNumber(container.dataset.lat, 47.1092664)
  ];
  const zoom = readNumber(container.dataset.zoom, 16.5);
  const popupText = container.dataset.popup;

  let map;
  try {
    map = new maplibregl.Map({
      style: container.dataset.style,
      center: center,
      zoom: zoom,
      container: container
    });
  } catch (error) {
    // MapLibre throws when WebGL is unavailable, leaving an empty box behind.
    console.error('[OpenFreeMap] Could not initialise the map.', error);
    container.textContent = NO_WEBGL_MESSAGE;
    return;
  }

  map.on('error', (event) => {
    console.warn('[OpenFreeMap]', event.error);
  });

  const marker = new maplibregl.Marker({
    color: container.dataset.color
  })
    .setLngLat(center)
    .addTo(map);
  if (popupText) {
    const popup = new maplibregl.Popup({
      closeOnClick: false
    }).setHTML(popupText);
    marker.setPopup(popup);
    marker.togglePopup();
  }
  map.addControl(new maplibregl.NavigationControl());
});
