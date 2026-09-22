var openfreemapModuleUrl;
var openfreemapStyleUrl;

if (document.querySelector('.openfreemap') && typeof openfreemapModuleUrl === 'string') {
  if (typeof openfreemapStyleUrl === 'string') {
    var link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = openfreemapStyleUrl;
    link.onerror = function () {
      console.error('[OpenFreeMap] Failed to load the map stylesheet.');
    };
    document.head.appendChild(link);
  }

  import(openfreemapModuleUrl).catch(function (error) {
    console.error('[OpenFreeMap] Failed to load the map library.', error);
  });
}
