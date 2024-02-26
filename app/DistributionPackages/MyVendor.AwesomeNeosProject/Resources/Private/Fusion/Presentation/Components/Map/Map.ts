// NOTE: in your esbuild config, set `external: ['/_maptiles/frontend/v1/map-main.js']`
// for the import below to work.

import { AlpineComponent } from 'alpinejs'

export type MapComponent = {
    loadMap: () => void
}

type MapData = {
    lng: number
    lat: number
    zoom: number
    popupText: string
}

export default (data: MapData): AlpineComponent<MapComponent> => ({
    loadMap() {
        // @ts-ignore
        import('/_maptiles/frontend/v1/map-main.js').then(({ maplibregl, createMap }) => {
            let map = createMap(`${window.location.protocol}//${window.location.host}/_maptiles`, {
                container: this.$el,
                center: [data.lng, data.lat], // starting position [lng, lat]
                zoom: data.zoom, // starting zoom
                cooperativeGestures: true, // use cmd/ctrl + Scroll to zoom
            })

            map.addControl(new maplibregl.NavigationControl(), 'top-left')

            new maplibregl.Marker()
                .setLngLat([data.lng, data.lat])
                .setPopup(new maplibregl.Popup({ offset: 25 }).setText(data.popupText))
                .addTo(map)
        })
    }
})
