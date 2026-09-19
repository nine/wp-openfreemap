import * as maplibregl from '../vendor/maplibre-gl/maplibre-gl.mjs';

document.querySelectorAll('.openfreemap').forEach((container) => {
  const center = [
    parseFloat(container.dataset.lon),
    parseFloat(container.dataset.lat)
  ];
  const zoom = parseFloat(container.dataset.zoom);
  const map = new maplibregl.Map({
    style: container.dataset.style,
    center: center,
    zoom: zoom,
    container: container
  });
  const marker = new maplibregl.Marker({
    color: container.dataset.color
  })
    .setLngLat(center)
    .addTo(map);
  const popupText = container.dataset.popup;
  if (popupText) {
    const popup = new maplibregl.Popup({
      closeOnClick: false
    }).setHTML(popupText);
    marker.setPopup(popup);
    marker.togglePopup();
  }
  map.addControl(new maplibregl.NavigationControl());
});

