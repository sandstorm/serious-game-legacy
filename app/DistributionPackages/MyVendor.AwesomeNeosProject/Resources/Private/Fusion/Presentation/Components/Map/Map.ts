// @ts-ignore
import { Alpine as AlpineType } from 'alpinejs';

// NOTE: in your esbuild config, set `external: ['/_maptiles/frontend/v1/map-main.js']`
// for the import below to work.

export function initMap(Alpine: AlpineType) {
    Alpine.data('map', ({lng, lat, zoom, popupText}: any) => ({
        loadMap() {
            // @ts-ignore
            import('/_maptiles/frontend/v1/map-main.js')
                .then(({maplibregl, createMap}) => {
                    let map = createMap(window.location.protocol + '//' + window.location.host + '/_maptiles', {
                        // @ts-ignore
                        container: this.$el,
                        center: [lng, lat], // starting position [lng, lat]
                        zoom: zoom, // starting zoom
                        cooperativeGestures: true // use cmd/ctrl + Scroll to zoom
                    });

                    map.addControl(new maplibregl.NavigationControl(), 'top-left');

                    new maplibregl.Marker()
                        .setLngLat([lng, lat])
                        .setPopup(new maplibregl.Popup({ offset: 25 }).setText(popupText))
                        .addTo(map);
                });
        }
    }));
}
